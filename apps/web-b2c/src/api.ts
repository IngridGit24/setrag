// Laravel API base URL
const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

export interface Station {
  id: number
  name: string
  latitude: number
  longitude: number
}

export interface Trip {
  id: number
  origin_station_id: number
  destination_station_id: number
  departure_time: string
  arrival_time: string
}

export interface Seat {
  seat_no: string
  status: string
}

export interface BookingRequest {
  trip_id: number
  seat_no: string
  passenger_name: string
  passenger_email: string
}

export interface BookingResponse {
  pnr: string
  amount: number
  currency: string
}

export async function listStations(): Promise<Station[]> {
  const response = await fetch(`${API_BASE}/stations`)
  if (!response.ok) {
    throw new Error('Erreur lors du chargement des stations')
  }
  return response.json()
}

export async function createStation(station: Omit<Station, 'id'>): Promise<Station> {
  const response = await fetch(`${API_BASE}/stations`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(station),
  })
  if (!response.ok) {
    throw new Error('Erreur lors de la création de la station')
  }
  return response.json()
}

export async function listTrips(): Promise<Trip[]> {
  const response = await fetch(`${API_BASE}/trips`)
  if (!response.ok) {
    throw new Error('Erreur lors du chargement des voyages')
  }
  return response.json()
}

export async function createTrip(trip: Omit<Trip, 'id'>): Promise<Trip> {
  const response = await fetch(`${API_BASE}/trips`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(trip),
  })
  if (!response.ok) {
    throw new Error('Erreur lors de la création du voyage')
  }
  return response.json()
}

export async function seedSeats(tripId: number, numSeats: number = 50): Promise<void> {
  const response = await fetch(`${API_BASE}/trips/${tripId}/seats/seed`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ count: numSeats }),
  })
  if (!response.ok) {
    throw new Error('Erreur lors de l\'initialisation des sièges')
  }
}

export async function listSeats(tripId: number): Promise<Seat[]> {
  const response = await fetch(`${API_BASE}/trips/${tripId}/seats`)
  if (!response.ok) {
    throw new Error('Erreur lors du chargement des sièges')
  }
  return response.json()
}

export async function quotePrice(request: { trip_id: number; seat_no?: string; passengers?: number }): Promise<any> {
  const response = await fetch(`${API_BASE}/price/quote`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(request),
  })
  if (!response.ok) {
    throw new Error('Erreur lors du calcul du prix')
  }
  return response.json()
}

export async function createBooking(booking: BookingRequest): Promise<BookingResponse> {
  const token = localStorage.getItem('auth_token')
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  }
  
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(`${API_BASE}/booking`, {
    method: 'POST',
    headers,
    body: JSON.stringify({
      trip_id: booking.trip_id,
      passengers: 1,
      passenger_name: booking.passenger_name,
      passenger_email: booking.passenger_email,
      idempotency_key: `ui-${Date.now()}`,
    }),
  })
  
  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))
    throw new Error(errorData.error || errorData.detail || 'Erreur lors de la création de la réservation')
  }
  
  return response.json()
}


