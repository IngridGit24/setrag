import React, { useEffect, useMemo, useState } from 'react'
import MapView, { type TrainPosition } from './MapView'
import { clearToken, fetchMe, getToken, login, setToken, type User } from './auth'
import { createBooking, listTrips, quotePrice, seedSeats, type Trip } from './api'

type TrainPositionLocal = TrainPosition

function deriveWsUrl(httpUrl: string): string {
  try {
    const url = new URL(httpUrl)
    url.protocol = url.protocol === 'https:' ? 'wss:' : 'ws:'
    url.pathname = '/ws'
    url.search = ''
    url.hash = ''
    return url.toString()
  } catch {
    return 'ws://localhost:8001/ws'
  }
}

export default function App() {
  const [positions, setPositions] = useState<TrainPositionLocal[]>([])
  const [wsConnected, setWsConnected] = useState(false)
  const [user, setUser] = useState<User | null>(null)
  const [cred, setCred] = useState({ email: '', password: '' })
  const [trips, setTrips] = useState<Trip[]>([])
  const [selectedTrip, setSelectedTrip] = useState<number | null>(null)
  const [quote, setQuote] = useState<{ total_amount: number; currency: string } | null>(null)
  const [pnr, setPnr] = useState<{ pnr: string; amount: number; currency: string } | null>(null)

  const trackingBaseUrl = useMemo(() => {
    return import.meta.env.VITE_TRACKING_URL ?? 'http://localhost:8001'
  }, [])

  useEffect(() => {
    let closed = false
    async function load() {
      try {
        const res = await fetch(`${trackingBaseUrl}/positions`)
        if (!res.ok) return
        const data: TrainPositionLocal[] = await res.json()
        if (!closed) setPositions(data)
      } catch {}
    }
    load()
    return () => { closed = true }
  }, [trackingBaseUrl])

  useEffect(() => {
    const t = getToken()
    if (!t) return
    fetchMe(t).then(setUser).catch(() => clearToken())
  }, [])

  useEffect(() => {
    listTrips().then(setTrips).catch(() => {})
  }, [])

  useEffect(() => {
    const wsUrl = deriveWsUrl(trackingBaseUrl)
    const ws = new WebSocket(wsUrl)
    ws.onopen = () => setWsConnected(true)
    ws.onclose = () => setWsConnected(false)
    ws.onerror = () => setWsConnected(false)
    ws.onmessage = (evt) => {
      try {
        const p: TrainPositionLocal = JSON.parse(evt.data)
        setPositions((prev) => {
          const map = new Map(prev.map((x) => [x.train_id, x]))
          map.set(p.train_id, p)
          return Array.from(map.values()).sort((a, b) => a.train_id.localeCompare(b.train_id))
        })
      } catch {}
    }
    return () => ws.close()
  }, [trackingBaseUrl])

  return (
    <div style={{ maxWidth: 900, margin: '0 auto', padding: 24 }}>
      <h1>Suivi des trains</h1>

      <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
        {user ? (
          <>
            <span>Connecté: {user.full_name} ({user.email})</span>
            <button onClick={() => { clearToken(); setUser(null) }}>Se déconnecter</button>
          </>
        ) : (
          <form onSubmit={async (e) => { e.preventDefault(); try { const tok = await login(cred.email, cred.password); setToken(tok); const u = await fetchMe(tok); setUser(u) } catch {} }} style={{ display: 'flex', gap: 8 }}>
            <input placeholder="email" value={cred.email} onChange={e => setCred({ ...cred, email: e.target.value })} />
            <input placeholder="mot de passe" type="password" value={cred.password} onChange={e => setCred({ ...cred, password: e.target.value })} />
            <button type="submit">Se connecter</button>
          </form>
        )}
      </div>
      <p style={{ color: wsConnected ? 'green' : 'gray' }}>
        WebSocket: {wsConnected ? 'connecté' : 'déconnecté'}
      </p>
      <div style={{ marginTop: 16 }}>
        <MapView positions={positions} />
      </div>

      <div style={{ marginTop: 16 }}>
        {positions.length === 0 ? (
          <p>Aucune position pour le moment.</p>
        ) : (
          <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <thead>
              <tr>
                <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px' }}>Train</th>
                <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px' }}>Latitude</th>
                <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px' }}>Longitude</th>
                <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px' }}>Vitesse (km/h)</th>
                <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px' }}>Direction (°)</th>
                <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px' }}>Horodatage</th>
              </tr>
            </thead>
            <tbody>
              {positions.map((p) => (
                <tr key={p.train_id}>
                  <td style={{ borderBottom: '1px solid #eee', padding: '8px' }}>{p.train_id}</td>
                  <td style={{ borderBottom: '1px solid #eee', padding: '8px' }}>{p.latitude.toFixed(5)}</td>
                  <td style={{ borderBottom: '1px solid #eee', padding: '8px' }}>{p.longitude.toFixed(5)}</td>
                  <td style={{ borderBottom: '1px solid #eee', padding: '8px' }}>{p.speed_kmh ?? '-'}</td>
                  <td style={{ borderBottom: '1px solid #eee', padding: '8px' }}>{p.bearing_deg ?? '-'}</td>
                  <td style={{ borderBottom: '1px solid #eee', padding: '8px' }}>{new Date(p.timestamp_utc).toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      <hr style={{ margin: '24px 0' }} />
      <h2>Recherche / Réservation</h2>
      <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' }}>
        <select value={selectedTrip ?? ''} onChange={(e) => setSelectedTrip(e.target.value ? Number(e.target.value) : null)}>
          <option value="">Choisir un trajet</option>
          {trips.map(t => (
            <option key={t.id} value={t.id}>Trip #{t.id} ({new Date(t.departure_time).toLocaleString()} → {new Date(t.arrival_time).toLocaleString()})</option>
          ))}
        </select>
        <button disabled={!selectedTrip} onClick={async () => { if (!selectedTrip) return; await seedSeats(selectedTrip, 50) }}>Initialiser sièges</button>
        <button disabled={!selectedTrip} onClick={async () => { if (!selectedTrip) return; const q = await quotePrice(selectedTrip, 1); setQuote(q) }}>Devis</button>
        <button disabled={!selectedTrip} onClick={async () => { if (!selectedTrip) return; const b = await createBooking(selectedTrip, 1, `ui-${Date.now()}`); setPnr(b) }}>Réserver</button>
      </div>
      {quote && (
        <p>Devis: {quote.total_amount} {quote.currency}</p>
      )}
      {pnr && (
        <p>Réservation confirmée: PNR {pnr.pnr} – {pnr.amount} {pnr.currency}</p>
      )}
    </div>
  )
}


