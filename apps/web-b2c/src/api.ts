const INVENTORY_BASE = 'http://localhost:8105'
const PRICING_BASE = 'http://localhost:8106'

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
  const response = await fetch(`${INVENTORY_BASE}/stations`)
  if (!response.ok) {
    throw new Error('Erreur lors du chargement des stations')
  }
  return response.json()
}

export async function createStation(station: Omit<Station, 'id'>): Promise<Station> {
  const response = await fetch(`${INVENTORY_BASE}/stations`, {
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
  const response = await fetch(`${INVENTORY_BASE}/trips`)
  if (!response.ok) {
    throw new Error('Erreur lors du chargement des voyages')
  }
  return response.json()
}

export async function createTrip(trip: Omit<Trip, 'id'>): Promise<Trip> {
  const response = await fetch(`${INVENTORY_BASE}/trips`, {
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
  const response = await fetch(`${INVENTORY_BASE}/trips/${tripId}/seats/seed`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ num_seats: numSeats }),
  })
  if (!response.ok) {
    throw new Error('Erreur lors de l\'initialisation des sièges')
  }
}

export async function quotePrice(request: { trip_id: number; seat_no: string }): Promise<any> {
  const response = await fetch(`${PRICING_BASE}/price/quote`, {
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

  const response = await fetch(`${PRICING_BASE}/booking`, {
    method: 'POST',
    headers,
    body: JSON.stringify(booking),
  })
  
  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))
    throw new Error(errorData.detail || 'Erreur lors de la création de la réservation')
  }
  
  return response.json()
}


