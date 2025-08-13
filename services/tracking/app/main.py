from __future__ import annotations

import asyncio
import json
import os
from datetime import datetime, timezone
from typing import Dict, List, Optional, Set

from fastapi import FastAPI, HTTPException, WebSocket, WebSocketDisconnect, Query
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
try:
    import paho.mqtt.client as mqtt  # type: ignore
except Exception:  # pragma: no cover
    mqtt = None  # Fallback si non installé

# Persistence (SQLAlchemy)
from sqlalchemy import (
    Column,
    DateTime,
    Float,
    Integer,
    String,
    create_engine,
    select,
    desc,
    asc,
    Index,
)
from sqlalchemy.orm import declarative_base, sessionmaker


class TrainPosition(BaseModel):
    train_id: str = Field(..., description="Identifiant unique du train")
    latitude: float = Field(..., ge=-90, le=90)
    longitude: float = Field(..., ge=-180, le=180)
    speed_kmh: Optional[float] = Field(None, ge=0)
    bearing_deg: Optional[float] = Field(None, ge=0, le=360)
    timestamp_utc: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class PositionsIngestRequest(BaseModel):
    positions: List[TrainPosition]


Base = declarative_base()


class TrainPositionORM(Base):
    __tablename__ = "train_positions"
    id = Column(Integer, primary_key=True, autoincrement=True)
    train_id = Column(String(64), nullable=False, index=True)
    latitude = Column(Float, nullable=False)
    longitude = Column(Float, nullable=False)
    speed_kmh = Column(Float)
    bearing_deg = Column(Float)
    timestamp_utc = Column(DateTime(timezone=True), nullable=False, index=True)


Index("ix_train_time", TrainPositionORM.train_id, TrainPositionORM.timestamp_utc)


def create_app() -> FastAPI:
    app = FastAPI(title="SETRAG Tracking Service")

    # CORS: autoriser le front Vite en dev
    app.add_middleware(
        CORSMiddleware,
        allow_origins=[
            "http://localhost:5173",
            "http://127.0.0.1:5173",
        ],
        allow_credentials=False,
        allow_methods=["*"],
        allow_headers=["*"],
    )

    # DB setup
    database_url = os.getenv("DATABASE_URL", "sqlite:///./tracking.db")
    connect_args = {"check_same_thread": False} if database_url.startswith("sqlite") else {}
    engine = create_engine(database_url, future=True, pool_pre_ping=True, connect_args=connect_args)
    SessionLocal = sessionmaker(bind=engine, autoflush=False, autocommit=False, future=True)

    app.state.db_engine = engine
    app.state.SessionLocal = SessionLocal

    # Mémoire locale simple pour les dernières positions
    last_position_by_train: Dict[str, TrainPosition] = {}
    websocket_clients: Set[WebSocket] = set()

    async def broadcast_position(position: TrainPosition) -> None:
        if not websocket_clients:
            return
        message = position.model_dump()
        stale_clients: List[WebSocket] = []
        for ws in websocket_clients:
            try:
                await ws.send_json(message)
            except Exception:
                stale_clients.append(ws)
        for ws in stale_clients:
            websocket_clients.discard(ws)

    @app.get("/health")
    def health() -> dict:
        return {"status": "ok"}

    @app.get("/positions", response_model=List[TrainPosition])
    def get_positions() -> List[TrainPosition]:
        # Dernière position connue par train (en mémoire)
        return list(last_position_by_train.values())

    @app.get("/trains/{train_id}/position", response_model=TrainPosition)
    def get_train_position(train_id: str) -> TrainPosition:
        position = last_position_by_train.get(train_id)
        if position is None:
            raise HTTPException(status_code=404, detail="Train not found")
        return position

    @app.post("/position", response_model=TrainPosition)
    async def ingest_position(position: TrainPosition) -> TrainPosition:
        # Persist
        session = SessionLocal()
        try:
            row = TrainPositionORM(
                train_id=position.train_id,
                latitude=position.latitude,
                longitude=position.longitude,
                speed_kmh=position.speed_kmh,
                bearing_deg=position.bearing_deg,
                timestamp_utc=position.timestamp_utc,
            )
            session.add(row)
            session.commit()
        finally:
            session.close()
        # Update cache + broadcast
        last_position_by_train[position.train_id] = position
        await broadcast_position(position)
        return position

    @app.post("/positions", response_model=List[TrainPosition])
    async def ingest_positions(payload: PositionsIngestRequest) -> List[TrainPosition]:
        session = SessionLocal()
        try:
            for p in payload.positions:
                row = TrainPositionORM(
                    train_id=p.train_id,
                    latitude=p.latitude,
                    longitude=p.longitude,
                    speed_kmh=p.speed_kmh,
                    bearing_deg=p.bearing_deg,
                    timestamp_utc=p.timestamp_utc,
                )
                session.add(row)
                last_position_by_train[p.train_id] = p
            session.commit()
        finally:
            session.close()
        for p in payload.positions:
            await broadcast_position(p)
        return payload.positions

    @app.get("/positions/search", response_model=List[TrainPosition])
    def search_positions(
        train_id: Optional[str] = None,
        since: Optional[datetime] = Query(None, description="ISO8601 UTC start time"),
        until: Optional[datetime] = Query(None, description="ISO8601 UTC end time"),
        limit: int = Query(100, ge=1, le=1000),
        offset: int = Query(0, ge=0),
        order: str = Query("desc", pattern="^(asc|desc)$"),
    ) -> List[TrainPosition]:
        session = SessionLocal()
        try:
            stmt = select(TrainPositionORM)
            if train_id:
                stmt = stmt.where(TrainPositionORM.train_id == train_id)
            if since:
                stmt = stmt.where(TrainPositionORM.timestamp_utc >= since)
            if until:
                stmt = stmt.where(TrainPositionORM.timestamp_utc <= until)
            stmt = stmt.order_by(desc(TrainPositionORM.timestamp_utc) if order == "desc" else asc(TrainPositionORM.timestamp_utc))
            stmt = stmt.offset(offset).limit(limit)
            rows = session.execute(stmt).scalars().all()
            return [
                TrainPosition(
                    train_id=r.train_id,
                    latitude=r.latitude,
                    longitude=r.longitude,
                    speed_kmh=r.speed_kmh,
                    bearing_deg=r.bearing_deg,
                    timestamp_utc=r.timestamp_utc,
                )
                for r in rows
            ]
        finally:
            session.close()

    @app.websocket("/ws")
    async def websocket_endpoint(websocket: WebSocket) -> None:
        await websocket.accept()
        websocket_clients.add(websocket)
        try:
            while True:
                # Nous n'attendons pas de messages côté client pour l'instant
                await websocket.receive_text()
        except WebSocketDisconnect:
            websocket_clients.discard(websocket)
        except Exception:
            websocket_clients.discard(websocket)

    @app.on_event("startup")
    async def on_startup() -> None:
        app.state.loop = asyncio.get_running_loop()
        # DB: create tables + warm last positions cache
        Base.metadata.create_all(bind=engine)
        # Warm cache with last known position per train
        try:
            session = SessionLocal()
            train_ids = [tid for (tid,) in session.execute(select(TrainPositionORM.train_id).distinct())]
            for tid in train_ids:
                row = (
                    session.execute(
                        select(TrainPositionORM)
                        .where(TrainPositionORM.train_id == tid)
                        .order_by(desc(TrainPositionORM.timestamp_utc))
                        .limit(1)
                    )
                    .scalars()
                    .first()
                )
                if row is not None:
                    last_position_by_train[tid] = TrainPosition(
                        train_id=row.train_id,
                        latitude=row.latitude,
                        longitude=row.longitude,
                        speed_kmh=row.speed_kmh,
                        bearing_deg=row.bearing_deg,
                        timestamp_utc=row.timestamp_utc,
                    )
        except Exception:
            pass
        finally:
            try:
                session.close()
            except Exception:
                pass
        mqtt_host = os.getenv("MQTT_HOST")
        mqtt_port = int(os.getenv("MQTT_PORT", "1883"))
        mqtt_topic = os.getenv("MQTT_TOPIC", "setrag/tracking/position")
        if mqtt_host and mqtt is not None:
            client = mqtt.Client()

            def on_connect(_client, _userdata, _flags, rc):
                if rc == 0:
                    _client.subscribe(mqtt_topic)

            def on_message(_client, _userdata, msg):
                try:
                    payload = msg.payload.decode("utf-8")
                    data = json.loads(payload)
                    position = TrainPosition.model_validate(data)
                    last_position_by_train[position.train_id] = position
                    asyncio.run_coroutine_threadsafe(
                        broadcast_position(position), app.state.loop
                    )
                except Exception:
                    # Ignorer silencieusement les messages invalides
                    return

            client.on_connect = on_connect
            client.on_message = on_message

            stop_event = asyncio.Event()

            async def run_mqtt() -> None:
                client.connect(mqtt_host, mqtt_port, keepalive=60)
                client.loop_start()
                try:
                    await stop_event.wait()
                finally:
                    client.loop_stop()
                    try:
                        client.disconnect()
                    except Exception:
                        pass

            app.state.mqtt_stop_event = stop_event
            app.state.mqtt_task = asyncio.create_task(run_mqtt())

    @app.on_event("shutdown")
    async def on_shutdown() -> None:
        stop_event: asyncio.Event | None = getattr(app.state, "mqtt_stop_event", None)
        task: asyncio.Task | None = getattr(app.state, "mqtt_task", None)
        if stop_event is not None:
            stop_event.set()
        if task is not None:
            try:
                await task
            except Exception:
                pass

    return app


app = create_app()


