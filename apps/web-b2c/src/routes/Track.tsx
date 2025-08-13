import { useState, useEffect } from 'react'
import MapView from '../MapView'
import Header from '../components/Header'

interface TrainPosition {
  train_id: string
  latitude: number
  longitude: number
  timestamp: string
  speed: number
  direction: number
}

export default function Track() {
  const [positions, setPositions] = useState<TrainPosition[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchPositions()
  }, [])

  const fetchPositions = async () => {
    try {
      setLoading(true)
      const response = await fetch('http://localhost:8001/positions')
      if (!response.ok) {
        throw new Error('Erreur lors du chargement des positions')
      }
      const data = await response.json()
      setPositions(data)
    } catch (err) {
      setError('Impossible de charger les positions des trains')
      console.error('Erreur:', err)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-setrag-primary mx-auto mb-4"></div>
          <p className="text-gray-600">Chargement des positions...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="text-center py-8">
        <div className="bg-red-100 text-red-700 p-4 rounded-lg max-w-md mx-auto">
          <p className="font-semibold">Erreur</p>
          <p>{error}</p>
          <button 
            onClick={fetchPositions}
            className="btn-primary mt-4"
          >
            Réessayer
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Header 
        title="Suivi en temps réel" 
        description="Visualisez la position de tous les trains SETRAG"
      />

      {/* Map View */}
      <div className="card p-4">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Carte des trains</h2>
        <div className="h-96 rounded-lg overflow-hidden">
          <MapView positions={positions} />
        </div>
      </div>

      {/* Train List */}
      <div className="card p-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-semibold text-gray-900">Trains en circulation</h2>
          <button 
            onClick={fetchPositions}
            className="btn-secondary text-sm"
          >
            Actualiser
          </button>
        </div>
        
        {positions.length === 0 ? (
          <div className="text-center py-8 text-gray-500">
            Aucun train en circulation pour le moment
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {positions.map((position) => (
              <div key={position.train_id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="font-semibold text-gray-900">Train {position.train_id}</h3>
                  <span className="text-sm text-gray-500">
                    {new Date(position.timestamp).toLocaleTimeString()}
                  </span>
                </div>
                
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-gray-600">Vitesse:</span>
                    <span className="font-medium">{position.speed} km/h</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Direction:</span>
                    <span className="font-medium">{position.direction}°</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Position:</span>
                    <span className="font-medium">
                      {position.latitude.toFixed(4)}, {position.longitude.toFixed(4)}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}


