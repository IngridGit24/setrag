from __future__ import annotations

import os
from datetime import datetime
from typing import Optional

import httpx
from fastapi import FastAPI, HTTPException, Depends, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import OAuth2PasswordBearer
import jwt
from pydantic import BaseModel
from sqlalchemy import Column, DateTime, Float, Integer, String, create_engine, select, UniqueConstraint
from sqlalchemy.orm import declarative_base, sessionmaker
from ulid import ULID


Base = declarative_base()


class BookingORM(Base):
    __tablename__ = "bookings"
    id = Column(Integer, primary_key=True, autoincrement=True)
    pnr = Column(String(26), unique=True, nullable=False, index=True)
    trip_id = Column(Integer, nullable=False)
    seat_no = Column(String(16), nullable=False)
    amount = Column(Float, nullable=False)
    currency = Column(String(8), nullable=False, default="XAF")
    status = Column(String(16), nullable=False, default="CONFIRMED")
    idempotency_key = Column(String(64), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    __table_args__ = (UniqueConstraint('idempotency_key', name='uq_idempotency_key'),)


class PriceQuoteRequest(BaseModel):
    trip_id: int
    seat_no: str
    passengers: int = 1


class PriceQuoteResponse(BaseModel):
    base_price: float
    taxes: float
    total_price: float
    currency: str


class BookingCreate(BaseModel):
    trip_id: int
    passengers: int = 1
    idempotency_key: Optional[str] = None


def create_app() -> FastAPI:
    app = FastAPI(title="SETRAG Pricing & Booking (Python)")

    inventory_base = os.getenv("INVENTORY_BASE_URL", "http://localhost:8105")
    users_public_secret = os.getenv("USERS_JWT_SECRET", "dev-secret-change-me")
    db_url = os.getenv("PRICING_DB_URL", "sqlite:///./pricing.db")
    engine = create_engine(db_url, future=True, pool_pre_ping=True,
                           connect_args={"check_same_thread": False} if db_url.startswith("sqlite") else {})
    SessionLocal = sessionmaker(bind=engine, autoflush=False, autocommit=False, future=True)
    Base.metadata.create_all(bind=engine)

    app.add_middleware(
        CORSMiddleware,
        allow_origins=["http://localhost:5173", "http://127.0.0.1:5173"],
        allow_methods=["*"],
        allow_headers=["*"],
    )

    oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/oauth/token")

    def require_user(token: str = Depends(oauth2_scheme)) -> dict:
        try:
            payload = jwt.decode(token, users_public_secret, algorithms=["HS256"])
            return payload
        except Exception:
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Unauthorized")

    @app.get("/health")
    def health() -> dict:
        return {"status": "ok"}

    @app.post("/price/quote", response_model=PriceQuoteResponse)
    def quote(body: PriceQuoteRequest) -> PriceQuoteResponse:
        # Récupérer les informations du voyage depuis inventory-py
        try:
            trip_response = httpx.get(f"{inventory_base}/trips/{body.trip_id}", timeout=10.0)
            trip_response.raise_for_status()
            trip_data = trip_response.json()
            
            # Récupérer les stations
            origin_response = httpx.get(f"{inventory_base}/stations/{trip_data['origin_station_id']}", timeout=10.0)
            origin_response.raise_for_status()
            origin_station = origin_response.json()
            
            dest_response = httpx.get(f"{inventory_base}/stations/{trip_data['destination_station_id']}", timeout=10.0)
            dest_response.raise_for_status()
            dest_station = dest_response.json()
            
        except Exception:
            # Fallback si erreur API
            base_price = 10000.0
        else:
            # Tarification basée sur les stations (même logique que le frontend)
            origin_name = origin_station['name']
            dest_name = dest_station['name']
            
            # Prix basés sur la distance (simulation)
            if origin_name == 'Libreville' and dest_name == 'Franceville':
                base_price = 25000.0
            elif origin_name == 'Franceville' and dest_name == 'Libreville':
                base_price = 25000.0
            elif origin_name == 'Libreville' and dest_name == 'Moanda':
                base_price = 15000.0
            elif origin_name == 'Moanda' and dest_name == 'Libreville':
                base_price = 15000.0
            elif origin_name == 'Libreville' and dest_name == 'Owendo':
                base_price = 5000.0
            elif origin_name == 'Owendo' and dest_name == 'Libreville':
                base_price = 5000.0
            else:
                base_price = 10000.0
        
        # Calcul de la commission (5% du prix de base)
        commission = base_price * 0.05
        total_price = base_price + commission
        
        return PriceQuoteResponse(
            base_price=base_price,
            taxes=commission,  # Renommé en commission dans le frontend
            total_price=total_price,
            currency="XAF"
        )

    @app.post("/booking", status_code=201)
    def booking(body: BookingCreate, user=Depends(require_user)):
        # Idempotence
        with SessionLocal() as session:
            if body.idempotency_key:
                exists = session.execute(
                    select(BookingORM).where(BookingORM.idempotency_key == body.idempotency_key)
                ).scalar_one_or_none()
                if exists:
                    return {"pnr": exists.pnr, "amount": exists.amount, "currency": exists.currency}

        # Allocation de siège via inventory-py
        try:
            r = httpx.post(f"{inventory_base}/trips/{body.trip_id}/seats/allocate", params={"hold_minutes": 20}, timeout=10.0)
            r.raise_for_status()
            seat_no = r.json()["seat_no"]
        except Exception:
            raise HTTPException(status_code=409, detail="Seat allocation failed")

        # Prix
        quote_res = quote(PriceQuoteRequest(trip_id=body.trip_id, seat_no=seat_no, passengers=body.passengers))

        # Confirmation siège
        try:
            rc = httpx.post(f"{inventory_base}/trips/{body.trip_id}/seats/{seat_no}/confirm", timeout=10.0)
            rc.raise_for_status()
        except Exception:
            raise HTTPException(status_code=409, detail="Seat confirmation failed")

        # Créer PNR
        pnr = str(ULID())
        with SessionLocal() as session:
            row = BookingORM(
                pnr=pnr,
                trip_id=body.trip_id,
                seat_no=seat_no,
                amount=quote_res.total_price,
                currency=quote_res.currency,
                status="CONFIRMED",
                idempotency_key=body.idempotency_key,
            )
            session.add(row)
            session.commit()
        return {"pnr": pnr, "amount": quote_res.total_price, "currency": quote_res.currency}

    return app


app = create_app()


