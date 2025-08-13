import React from 'react'
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

export type TrainPosition = {
  train_id: string
  latitude: number
  longitude: number
  speed_kmh?: number | null
  bearing_deg?: number | null
  timestamp_utc: string
}

const defaultIcon = L.icon({
  iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).toString(),
  iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).toString(),
  shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).toString(),
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
})

type Props = {
  positions: TrainPosition[]
}

export default function MapView({ positions }: Props) {
  const center: [number, number] = positions.length
    ? [positions[0].latitude, positions[0].longitude]
    : [0.3901, 9.4544]

  return (
    <div style={{ height: 480, borderRadius: 8, overflow: 'hidden', border: '1px solid #e5e5e5' }}>
      <MapContainer center={center} zoom={6} style={{ height: '100%', width: '100%' }}>
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {positions.map((p) => (
          <Marker key={p.train_id} position={[p.latitude, p.longitude]} icon={defaultIcon}>
            <Popup>
              <div>
                <div><strong>{p.train_id}</strong></div>
                <div>Lat: {p.latitude.toFixed(5)}</div>
                <div>Lng: {p.longitude.toFixed(5)}</div>
                <div>Vitesse: {p.speed_kmh ?? '-'} km/h</div>
                <div>Direction: {p.bearing_deg ?? '-'}°</div>
                <div>TS: {new Date(p.timestamp_utc).toLocaleString()}</div>
              </div>
            </Popup>
          </Marker>
        ))}
      </MapContainer>
    </div>
  )
}


