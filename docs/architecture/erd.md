# ERD (brouillon)

## Voyageurs
- Station(id, name, latitude, longitude)
- Trip(id, origin_station_id -> Station.id, destination_station_id -> Station.id, departure_time, arrival_time)
- SeatMap(id, trip_id -> Trip.id, coach_no, seat_no)
- Inventory(id, trip_id -> Trip.id, seat_no, status[AVAILABLE|HELD|SOLD], hold_expires_at)
- FareRule(id, trip_id -> Trip.id, class, price, currency)
- Booking(id, pnr, trip_id, passenger_count, amount, currency, status[PENDING|CONFIRMED|CANCELLED])
- Payment(id, booking_id -> Booking.id, provider, amount, currency, status[AUTHORIZED|CAPTURED|FAILED|REFUNDED])

## Expédition
- Parcel(id, tracking_no, weight_kg, status[CREATED|IN_TRANSIT|DELIVERED|CANCELLED])
- ParcelEvent(id, tracking_no -> Parcel.tracking_no, event_type, occurred_at, meta)

## Tracking
- TrainPosition(id, train_id, latitude, longitude, speed_kmh, bearing_deg, timestamp_utc)

Relations clés:
- Booking 1..1 Payment
- Trip 1..* Booking
- Trip 1..* Inventory
- Parcel 1..* ParcelEvent
