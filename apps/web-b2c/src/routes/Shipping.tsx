import { useState, useEffect } from 'react'
import Header from '../components/Header'

interface Parcel {
  id: string
  tracking_number: string
  origin: string
  destination: string
  status: string
  weight: number
  created_at: string
}

export default function Shipping() {
  const [parcels, setParcels] = useState<Parcel[]>([])
  const [newParcel, setNewParcel] = useState({
    origin: '',
    destination: '',
    weight: '',
    description: ''
  })
  const [showForm, setShowForm] = useState(false)

  useEffect(() => {
    // Load parcels from localStorage for demo
    const savedParcels = localStorage.getItem('parcels')
    if (savedParcels) {
      setParcels(JSON.parse(savedParcels))
    }
  }, [])

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    
    const parcel: Parcel = {
      id: Date.now().toString(),
      tracking_number: `SETRAG${Date.now()}`,
      origin: newParcel.origin,
      destination: newParcel.destination,
      status: 'En attente de collecte',
      weight: parseFloat(newParcel.weight),
      created_at: new Date().toISOString()
    }

    const updatedParcels = [...parcels, parcel]
    setParcels(updatedParcels)
    localStorage.setItem('parcels', JSON.stringify(updatedParcels))
    
    setNewParcel({ origin: '', destination: '', weight: '', description: '' })
    setShowForm(false)
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'En attente de collecte':
        return 'bg-yellow-100 text-yellow-800'
      case 'En transit':
        return 'bg-blue-100 text-blue-800'
      case 'Livré':
        return 'bg-green-100 text-green-800'
      case 'Retardé':
        return 'bg-red-100 text-red-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  return (
    <div className="space-y-8">
      <Header 
        title="Expédition de colis" 
        description="Envoyez vos colis et suivez leur livraison"
      />

      {/* Action Buttons */}
      <div className="flex justify-center gap-4">
        <button
          onClick={() => setShowForm(true)}
          className="btn-primary"
        >
          Nouvelle expédition
        </button>
        <button
          onClick={() => {/* TODO: Implement tracking */}}
          className="btn-secondary"
        >
          Suivre un colis
        </button>
      </div>

      {/* New Parcel Form */}
      {showForm && (
        <div className="card p-6">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-xl font-semibold text-gray-900">Nouvelle expédition</h2>
            <button
              onClick={() => setShowForm(false)}
              className="text-gray-500 hover:text-gray-700"
            >
              ✕
            </button>
          </div>
          
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label htmlFor="origin" className="block text-sm font-medium text-gray-700 mb-1">
                  Origine
                </label>
                <input
                  type="text"
                  id="origin"
                  value={newParcel.origin}
                  onChange={(e) => setNewParcel({ ...newParcel, origin: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                  required
                />
              </div>
              
              <div>
                <label htmlFor="destination" className="block text-sm font-medium text-gray-700 mb-1">
                  Destination
                </label>
                <input
                  type="text"
                  id="destination"
                  value={newParcel.destination}
                  onChange={(e) => setNewParcel({ ...newParcel, destination: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                  required
                />
              </div>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label htmlFor="weight" className="block text-sm font-medium text-gray-700 mb-1">
                  Poids (kg)
                </label>
                <input
                  type="number"
                  id="weight"
                  value={newParcel.weight}
                  onChange={(e) => setNewParcel({ ...newParcel, weight: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                  required
                  min="0.1"
                  step="0.1"
                />
              </div>
              
              <div>
                <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-1">
                  Description
                </label>
                <input
                  type="text"
                  id="description"
                  value={newParcel.description}
                  onChange={(e) => setNewParcel({ ...newParcel, description: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                  placeholder="Contenu du colis"
                />
              </div>
            </div>
            
            <div className="flex justify-end gap-4">
              <button
                type="button"
                onClick={() => setShowForm(false)}
                className="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Annuler
              </button>
              <button
                type="submit"
                className="btn-primary"
              >
                Créer l'expédition
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Parcels List */}
      <div className="card p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-6">Mes expéditions</h2>
        
        {parcels.length === 0 ? (
          <div className="text-center py-12 text-gray-500">
            <div className="text-4xl mb-4">📦</div>
            <p className="text-lg">Aucune expédition pour le moment</p>
            <p className="text-sm">Créez votre première expédition pour commencer</p>
          </div>
        ) : (
          <div className="space-y-4">
            {parcels.map((parcel) => (
              <div key={parcel.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div className="flex justify-between items-start mb-3">
                  <div>
                    <h3 className="font-semibold text-gray-900">Colis #{parcel.tracking_number}</h3>
                    <p className="text-sm text-gray-600">
                      {parcel.origin} → {parcel.destination}
                    </p>
                  </div>
                  <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(parcel.status)}`}>
                    {parcel.status}
                  </span>
                </div>
                
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div>
                    <span className="text-gray-600">Poids:</span>
                    <span className="ml-1 font-medium">{parcel.weight} kg</span>
                  </div>
                  <div>
                    <span className="text-gray-600">Créé le:</span>
                    <span className="ml-1 font-medium">
                      {new Date(parcel.created_at).toLocaleDateString()}
                    </span>
                  </div>
                  <div>
                    <span className="text-gray-600">Numéro:</span>
                    <span className="ml-1 font-medium">{parcel.tracking_number}</span>
                  </div>
                  <div className="text-right">
                    <button className="text-setrag-primary hover:text-setrag-primary-dark text-sm font-medium">
                      Suivre →
                    </button>
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


