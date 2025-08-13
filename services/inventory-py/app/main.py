from __future__ import annotations

import os
from datetime import datetime, timedelta
from typing import List, Optional

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from sqlalchemy import Column, DateTime, Float, Integer, String, ForeignKey, create_engine, select, and_, func
from sqlalchemy.orm import declarative_base, relationship, sessionmaker


Base = declarative_base()


class StationORM(Base):
    __tablename__ = "stations"
    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(128), nullable=False)
    latitude = Column(Float, nullable=False)
    longitude = Column(Float, nullable=False)


class TripORM(Base):
    __tablename__ = "trips"
    id = Column(Integer, primary_key=True, autoincrement=True)
    origin_station_id = Column(Integer, ForeignKey("stations.id"), nullable=False)
    destination_station_id = Column(Integer, ForeignKey("stations.id"), nullable=False)
    departure_time = Column(DateTime, nullable=False)
    arrival_time = Column(DateTime, nullable=False)
    origin = relationship("StationORM", foreign_keys=[origin_station_id])
    destination = relationship("StationORM", foreign_keys=[destination_station_id])


class SeatORM(Base):
    __tablename__ = "seats"
    id = Column(Integer, primary_key=True, autoincrement=True)
    trip_id = Column(Integer, ForeignKey("trips.id"), nullable=False, index=True)
    seat_no = Column(String(16), nullable=False)
    status = Column(String(16), nullable=False, default="AVAILABLE")
    hold_expires_at = Column(DateTime, nullable=True)


class Station(BaseModel):
    id: int
    name: str
    latitude: float
    longitude: float


class StationCreate(BaseModel):
    name: str
    latitude: float
    longitude: float


class Trip(BaseModel):
    id: int
    origin_station_id: int
    destination_station_id: int
    departure_time: datetime
    arrival_time: datetime


class TripCreate(BaseModel):
    origin_station_id: int
    destination_station_id: int
    departure_time: datetime
    arrival_time: datetime


class Seat(BaseModel):
    seat_no: str
    status: str
    hold_expires_at: Optional[datetime] = None


def create_app() -> FastAPI:
    app = FastAPI(title="SETRAG Inventory Service (Python)")

    db_url = os.getenv("INVENTORY_DATABASE_URL", "sqlite:///./inventory.db")
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

    @app.get("/health")
    def health() -> dict:
        return {"status": "ok"}

    @app.get("/stations")
    def list_stations():
        with SessionLocal() as session:
            stations = session.execute(select(StationORM)).scalars().all()
            return [{"id": s.id, "name": s.name, "latitude": s.latitude, "longitude": s.longitude} for s in stations]

    @app.get("/stations/{station_id}")
    def get_station(station_id: int):
        with SessionLocal() as session:
            station = session.execute(select(StationORM).where(StationORM.id == station_id)).scalar_one_or_none()
            if not station:
                raise HTTPException(status_code=404, detail="Station not found")
            return {"id": station.id, "name": station.name, "latitude": station.latitude, "longitude": station.longitude}

    @app.post("/stations", response_model=Station, status_code=201)
    def create_station(body: StationCreate) -> Station:
        with SessionLocal() as session:
            row = StationORM(name=body.name, latitude=body.latitude, longitude=body.longitude)
            session.add(row)
            session.commit()
            session.refresh(row)
            return Station(id=row.id, name=row.name, latitude=row.latitude, longitude=row.longitude)

    @app.get("/trips", response_model=List[Trip])
    def list_trips() -> List[Trip]:
        with SessionLocal() as session:
            rows = session.execute(select(TripORM)).scalars().all()
            return [
                Trip(
                    id=r.id,
                    origin_station_id=r.origin_station_id,
                    destination_station_id=r.destination_station_id,
                    departure_time=r.departure_time,
                    arrival_time=r.arrival_time,
                )
                for r in rows
            ]

    @app.post("/trips", response_model=Trip, status_code=201)
    def create_trip(body: TripCreate) -> Trip:
        with SessionLocal() as session:
            origin = session.get(StationORM, body.origin_station_id)
            dest = session.get(StationORM, body.destination_station_id)
            if not origin or not dest:
                raise HTTPException(status_code=400, detail="Invalid station id")
            row = TripORM(
                origin_station_id=body.origin_station_id,
                destination_station_id=body.destination_station_id,
                departure_time=body.departure_time,
                arrival_time=body.arrival_time,
            )
            session.add(row)
            session.commit()
            session.refresh(row)
            return Trip(
                id=row.id,
                origin_station_id=row.origin_station_id,
                destination_station_id=row.destination_station_id,
                departure_time=row.departure_time,
                arrival_time=row.arrival_time,
            )

    @app.post("/trips/{trip_id}/seats/seed", response_model=List[Seat])
    def seed_seats(trip_id: int, count: int = 100) -> List[Seat]:
        with SessionLocal() as session:
            trip = session.get(TripORM, trip_id)
            if not trip:
                raise HTTPException(status_code=404, detail="Trip not found")
            # Create N seats like 1A,1B,...
            created: List[Seat] = []
            existing_count = session.execute(select(func.count(SeatORM.id)).where(SeatORM.trip_id == trip_id)).scalar_one()
            if existing_count and existing_count > 0:
                # Do not duplicate
                rows = session.execute(select(SeatORM).where(SeatORM.trip_id == trip_id)).scalars().all()
                return [Seat(seat_no=r.seat_no, status=r.status, hold_expires_at=r.hold_expires_at) for r in rows]
            rows_to_add: List[SeatORM] = []
            for i in range(1, count + 1):
                seat_no = f"{i}A"
                rows_to_add.append(SeatORM(trip_id=trip_id, seat_no=seat_no, status="AVAILABLE"))
            session.add_all(rows_to_add)
            session.commit()
            rows = session.execute(select(SeatORM).where(SeatORM.trip_id == trip_id).order_by(SeatORM.id)).scalars().all()
            return [Seat(seat_no=r.seat_no, status=r.status, hold_expires_at=r.hold_expires_at) for r in rows]

    @app.get("/trips/{trip_id}/seats", response_model=List[Seat])
    def list_seats(trip_id: int) -> List[Seat]:
        with SessionLocal() as session:
            rows = session.execute(select(SeatORM).where(SeatORM.trip_id == trip_id).order_by(SeatORM.id)).scalars().all()
            return [Seat(seat_no=r.seat_no, status=r.status, hold_expires_at=r.hold_expires_at) for r in rows]

    @app.post("/trips/{trip_id}/seats/allocate", response_model=Seat)
    def allocate_seat(trip_id: int, hold_minutes: int = 15) -> Seat:
        now = datetime.utcnow()
        with SessionLocal() as session:
            # Try to pick an AVAILABLE or expired HELD seat
            row = (
                session.execute(
                    select(SeatORM)
                    .where(
                        SeatORM.trip_id == trip_id,
                        and_(
                            (SeatORM.status == "AVAILABLE") |
                            ((SeatORM.status == "HELD") & ((SeatORM.hold_expires_at == None) | (SeatORM.hold_expires_at < now)))
                        ),
                    )
                    .order_by(SeatORM.id)
                )
                .scalars()
                .first()
            )
            if not row:
                raise HTTPException(status_code=409, detail="No seats available")
            row.status = "HELD"
            row.hold_expires_at = now + timedelta(minutes=hold_minutes)
            session.commit()
            return Seat(seat_no=row.seat_no, status=row.status, hold_expires_at=row.hold_expires_at)

    @app.post("/trips/{trip_id}/seats/{seat_no}/confirm", response_model=Seat)
    def confirm_seat(trip_id: int, seat_no: str) -> Seat:
        with SessionLocal() as session:
            row = session.execute(
                select(SeatORM).where(SeatORM.trip_id == trip_id, SeatORM.seat_no == seat_no)
            ).scalar_one_or_none()
            if not row:
                raise HTTPException(status_code=404, detail="Seat not found")
            if row.status not in ("HELD", "AVAILABLE"):
                raise HTTPException(status_code=409, detail="Seat not confirmable")
            row.status = "SOLD"
            row.hold_expires_at = None
            session.commit()
            return Seat(seat_no=row.seat_no, status=row.status)

    return app


app = create_app()


