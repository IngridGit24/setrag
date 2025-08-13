from __future__ import annotations

import os
from datetime import datetime, timedelta, timezone
from typing import Optional

import jwt
from fastapi import Depends, FastAPI, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from pydantic import BaseModel, Field
from sqlalchemy import Column, Integer, String, create_engine, select
from sqlalchemy.orm import declarative_base, sessionmaker
from passlib.context import CryptContext


Base = declarative_base()


class UserORM(Base):
    __tablename__ = "users"
    id = Column(Integer, primary_key=True, autoincrement=True)
    email = Column(String(255), unique=True, nullable=False, index=True)
    password_hash = Column(String(255), nullable=False)
    full_name = Column(String(255), nullable=False)
    role = Column(String(64), nullable=False, default="user")


class User(BaseModel):
    id: int
    email: str
    full_name: str
    role: str


class UserCreate(BaseModel):
    email: str
    password: str
    full_name: str
    role: str = Field(default="user")


pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/oauth/token")


def get_settings():
    secret = os.getenv("USERS_JWT_SECRET", "dev-secret-change-me")
    db_url = os.getenv("USERS_DATABASE_URL", "sqlite:///./users.db")
    token_exp_minutes = int(os.getenv("USERS_TOKEN_MINUTES", "60"))
    return secret, db_url, token_exp_minutes


def create_app() -> FastAPI:
    app = FastAPI(title="SETRAG Users Service")

    secret, db_url, token_exp_minutes = get_settings()

    app.add_middleware(
        CORSMiddleware,
        allow_origins=["http://localhost:5173", "http://127.0.0.1:5173"],
        allow_methods=["*"],
        allow_headers=["*"],
    )

    engine = create_engine(db_url, future=True, pool_pre_ping=True,
                           connect_args={"check_same_thread": False} if db_url.startswith("sqlite") else {})
    SessionLocal = sessionmaker(bind=engine, autoflush=False, autocommit=False, future=True)
    Base.metadata.create_all(bind=engine)

    def create_access_token(user_id: int, email: str, role: str) -> str:
        payload = {
            "sub": str(user_id),
            "email": email,
            "role": role,
            "exp": datetime.now(timezone.utc) + timedelta(minutes=token_exp_minutes),
            "iat": datetime.now(timezone.utc),
        }
        return jwt.encode(payload, secret, algorithm="HS256")

    def get_current_user(token: str = Depends(oauth2_scheme)) -> User:
        try:
            payload = jwt.decode(token, secret, algorithms=["HS256"])
            user_id = int(payload.get("sub"))
        except Exception:
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid token")
        with SessionLocal() as session:
            row = session.execute(select(UserORM).where(UserORM.id == user_id)).scalar_one_or_none()
            if row is None:
                raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="User not found")
            return User(id=row.id, email=row.email, full_name=row.full_name, role=row.role)

    @app.get("/health")
    def health() -> dict:
        return {"status": "ok"}

    @app.post("/users", response_model=User, status_code=201)
    def create_user(user: UserCreate) -> User:
        with SessionLocal() as session:
            exists = session.execute(select(UserORM).where(UserORM.email == user.email)).scalar_one_or_none()
            if exists:
                raise HTTPException(status_code=409, detail="Email already exists")
            row = UserORM(
                email=user.email,
                password_hash=pwd_context.hash(user.password),
                full_name=user.full_name,
                role=user.role,
            )
            session.add(row)
            session.commit()
            session.refresh(row)
            return User(id=row.id, email=row.email, full_name=row.full_name, role=row.role)

    @app.post("/oauth/token")
    def token(form: OAuth2PasswordRequestForm = Depends()):
        with SessionLocal() as session:
            row = session.execute(select(UserORM).where(UserORM.email == form.username)).scalar_one_or_none()
            if row is None or not pwd_context.verify(form.password, row.password_hash):
                raise HTTPException(status_code=400, detail="Invalid credentials")
            access_token = create_access_token(row.id, row.email, row.role)
            return {"access_token": access_token, "token_type": "bearer", "expires_in": 60 * 60}

    @app.get("/me", response_model=User)
    def me(current: User = Depends(get_current_user)) -> User:
        return current

    return app


app = create_app()


