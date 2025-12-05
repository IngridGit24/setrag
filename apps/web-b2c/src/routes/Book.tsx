import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { listStations, listTrips, seedSeats, listSeats, quotePrice, createBooking } from '../api'
import { getToken } from '../auth'
import Header from '../components/Header'

interface Station {
  id: number
  name: string
  latitude: number
  longitude: number
}

interface Trip {
  id: number
  origin_station_id: number
  destination_station_id: number
  departure_time: string
  arrival_time: string
  price?: number
}

interface Seat {
  seat_no: string
  status: string
}

interface SearchForm {
  departure_station: string
  arrival_station: string
  trip_type: 'one_way' | 'round_trip'
  departure_date: string
  return_date: string
  promo_code: string
}

export default function Book() {
  const [stations, setStations] = useState<Station[]>([])
  const [trips, setTrips] = useState<Trip[]>([])
  const [filteredTrips, setFilteredTrips] = useState<Trip[]>([])
  const [selectedTrip, setSelectedTrip] = useState<Trip | null>(null)
  const [seats, setSeats] = useState<Seat[]>([])
  const [selectedSeat, setSelectedSeat] = useState<string>('')
  const [quote, setQuote] = useState<any>(null)
  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState('')
  const [showResults, setShowResults] = useState(false)
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  
  const [searchForm, setSearchForm] = useState<SearchForm>({
    departure_station: '',
    arrival_station: '',
    trip_type: 'one_way',
    departure_date: '',
    return_date: '',
    promo_code: ''
  })

  const navigate = useNavigate()

  useEffect(() => {
    loadStations()
    loadTrips()
    // Vérifier l'authentification au chargement
    const token = getToken()
    setIsAuthenticated(!!token)
  }, [])

  const loadStations = async () => {
    try {
      const data = await listStations()
      setStations(data)
    } catch (error) {
      console.error('Erreur chargement stations:', error)
    }
  }

  const loadTrips = async () => {
    try {
      const data = await listTrips()
      setTrips(data)
    } catch (error) {
      console.error('Erreur chargement voyages:', error)
    }
  }

  const getStationName = (stationId: number): string => {
    const station = stations.find(s => s.id === stationId)
    return station ? station.name : `Station ${stationId}`
  }

  const getTripPrice = (trip: Trip): number => {
    // Prix basés sur la distance (simulation)
    const originName = getStationName(trip.origin_station_id)
    const destName = getStationName(trip.destination_station_id)
    
    if (originName === 'Libreville' && destName === 'Franceville') return 25000
    if (originName === 'Franceville' && destName === 'Libreville') return 25000
    if (originName === 'Libreville' && destName === 'Moanda') return 15000
    if (originName === 'Moanda' && destName === 'Libreville') return 15000
    if (originName === 'Libreville' && destName === 'Owendo') return 5000
    if (originName === 'Owendo' && destName === 'Libreville') return 5000
    
    return 10000 // Prix par défaut
  }

  const handleSearch = () => {
    if (!searchForm.departure_station || !searchForm.arrival_station || !searchForm.departure_date) {
      setMessage('Veuillez remplir tous les champs obligatoires')
      return
    }

    // Filtrer les voyages selon les critères
    const filtered = trips.filter(trip => {
      const originName = getStationName(trip.origin_station_id)
      const destName = getStationName(trip.destination_station_id)
      const tripDate = new Date(trip.departure_time).toISOString().split('T')[0]
      
      return originName === searchForm.departure_station && 
             destName === searchForm.arrival_station &&
             tripDate === searchForm.departure_date
    })

    setFilteredTrips(filtered)
    setShowResults(true)
    setMessage('')
  }

  const handleTripSelect = async (trip: Trip) => {
    setSelectedTrip(trip)
    setSelectedSeat('')
    setQuote(null)
    
    try {
      // Seed seats for the selected trip
      await seedSeats(trip.id)
      
      // Get available seats
      const seatsData = await listSeats(trip.id)
      setSeats(seatsData)
    } catch (error) {
      console.error('Erreur chargement sièges:', error)
    }
  }

  const handleSeatSelect = async (seatNo: string) => {
    setSelectedSeat(seatNo)
    
    try {
      const quoteData = await quotePrice({
        trip_id: selectedTrip!.id,
        seat_no: seatNo
      })
      setQuote(quoteData)
    } catch (error) {
      console.error('Erreur calcul prix:', error)
    }
  }

  const handleBooking = async () => {
    if (!selectedTrip || !selectedSeat) return
    
    // Récupérer les noms des stations
    const originName = getStationName(selectedTrip.origin_station_id)
    const destName = getStationName(selectedTrip.destination_station_id)
    
    // Sauvegarder les informations de réservation dans localStorage
    const bookingInfo = {
      trip: {
        ...selectedTrip,
        origin_station_name: originName,
        destination_station_name: destName
      },
      seat: selectedSeat,
      quote: quote,
      searchForm: searchForm,
      timestamp: new Date().toISOString()
    }
    localStorage.setItem('pending_booking', JSON.stringify(bookingInfo))
    
    // Rediriger vers la page de paiement
    navigate('/payment')
  }

  const uniqueStations = stations.filter((station, index, self) => 
    index === self.findIndex(s => s.name === station.name)
  )

  return (
    <div className="space-y-8">
      <Header 
        title="Réserver un billet" 
        description="Trouvez et réservez votre voyage SETRAG"
      />

      {/* Authentication Status */}
      <div className="card p-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <div className={`w-3 h-3 rounded-full ${isAuthenticated ? 'bg-green-500' : 'bg-blue-500'}`}></div>
            <span className="text-sm font-medium">
              {isAuthenticated ? '✅ Connecté' : '👋 Bonjour, vous n\'etes pas encore inscrit !!'}
            </span>
          </div>
          {!isAuthenticated && (
            <button
              onClick={() => navigate('/auth')}
              className="btn-secondary text-sm px-4 py-2"
            >
              Se connecter
            </button>
          )}
        </div>
        {!isAuthenticated && (
          <p className="text-sm text-gray-600 mt-2">
            Rejoignez-nous pour bénéficier de promotions exclusives et suivre vos réservations !
          </p>
        )}
      </div>

      {message && (
        <div className={`p-4 rounded-lg ${
          message.includes('Erreur') 
            ? 'bg-red-100 text-red-700 border border-red-200' 
            : 'bg-green-100 text-green-700 border border-green-200'
        }`}>
          {message}
        </div>
      )}

      {/* Search Form */}
      <div className="card p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-6">Rechercher un voyage</h2>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
          {/* Gare de départ */}
          <div>
            <label htmlFor="departure" className="block text-sm font-medium text-gray-700 mb-2">
              Gare de départ *
            </label>
            <select
              id="departure"
              value={searchForm.departure_station}
              onChange={(e) => setSearchForm({...searchForm, departure_station: e.target.value})}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
              required
            >
              <option value="">Choisir une gare</option>
              {uniqueStations.map(station => (
                <option key={station.id} value={station.name}>{station.name}</option>
              ))}
            </select>
          </div>

          {/* Gare d'arrivée */}
          <div>
            <label htmlFor="arrival" className="block text-sm font-medium text-gray-700 mb-2">
              Gare d'arrivée *
            </label>
            <select
              id="arrival"
              value={searchForm.arrival_station}
              onChange={(e) => setSearchForm({...searchForm, arrival_station: e.target.value})}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
              required
            >
              <option value="">Choisir une gare</option>
              {uniqueStations.map(station => (
                <option key={station.id} value={station.name}>{station.name}</option>
              ))}
            </select>
          </div>

          {/* Type de voyage */}
          <div>
            <label htmlFor="trip_type" className="block text-sm font-medium text-gray-700 mb-2">
              Type de voyage
            </label>
            <select
              id="trip_type"
              value={searchForm.trip_type}
              onChange={(e) => setSearchForm({...searchForm, trip_type: e.target.value as 'one_way' | 'round_trip'})}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
            >
              <option value="one_way">Aller simple</option>
              <option value="round_trip">Aller-retour</option>
            </select>
          </div>

          {/* Date de départ */}
          <div>
            <label htmlFor="departure_date" className="block text-sm font-medium text-gray-700 mb-2">
              Date de départ *
            </label>
            <input
              type="date"
              id="departure_date"
              value={searchForm.departure_date}
              onChange={(e) => setSearchForm({...searchForm, departure_date: e.target.value})}
              min={new Date().toISOString().split('T')[0]}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
              required
            />
          </div>

          {/* Date de retour (si aller-retour) */}
          {searchForm.trip_type === 'round_trip' && (
            <div>
              <label htmlFor="return_date" className="block text-sm font-medium text-gray-700 mb-2">
                Date de retour
              </label>
              <input
                type="date"
                id="return_date"
                value={searchForm.return_date}
                onChange={(e) => setSearchForm({...searchForm, return_date: e.target.value})}
                min={searchForm.departure_date || new Date().toISOString().split('T')[0]}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
              />
            </div>
          )}

          {/* Code promo */}
          <div>
            <label htmlFor="promo_code" className="block text-sm font-medium text-gray-700 mb-2">
              Code promo
            </label>
            <input
              type="text"
              id="promo_code"
              value={searchForm.promo_code}
              onChange={(e) => setSearchForm({...searchForm, promo_code: e.target.value})}
              placeholder="Code promo (optionnel)"
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
            />
          </div>
        </div>

        {/* Bouton de recherche */}
        <div className="text-center">
          <button
            onClick={handleSearch}
            className="btn-primary text-lg px-8 py-3"
          >
            🔍 Rechercher des voyages
          </button>
        </div>
      </div>

      {/* Search Results */}
      {showResults && (
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            Voyages disponibles ({filteredTrips.length})
          </h2>
          
          {filteredTrips.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <div className="text-4xl mb-4">🚂</div>
              <p className="text-lg">Aucun voyage trouvé</p>
              <p className="text-sm">Essayez de modifier vos critères de recherche</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {filteredTrips.map((trip) => {
                const originName = getStationName(trip.origin_station_id)
                const destName = getStationName(trip.destination_station_id)
                const price = getTripPrice(trip)
                const departureDate = new Date(trip.departure_time)
                const arrivalDate = new Date(trip.arrival_time)
                
                return (
                  <div 
                    key={trip.id}
                    className={`p-4 border rounded-lg cursor-pointer transition-colors ${
                      selectedTrip?.id === trip.id 
                        ? 'border-setrag-primary bg-setrag-primary bg-opacity-10' 
                        : 'border-gray-200 hover:border-setrag-primary'
                    }`}
                    onClick={() => handleTripSelect(trip)}
                  >
                    <div className="space-y-2">
                      <div className="flex justify-between items-start">
                        <div className="flex-1">
                          <p className="font-semibold text-gray-900">{originName} → {destName}</p>
                          <p className="text-sm text-gray-600">
                            {departureDate.toLocaleDateString('fr-FR')}
                          </p>
                        </div>
                        <span className="text-setrag-primary font-bold text-lg">{price.toLocaleString()} FCFA</span>
                      </div>
                      
                      <div className="flex justify-between items-center text-sm text-gray-500">
                        <span>🚂 {departureDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
                        <span>→</span>
                        <span>🏁 {arrivalDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
                      </div>
                      
                      <div className="text-xs text-gray-400">
                        Durée: {Math.round((arrivalDate.getTime() - departureDate.getTime()) / (1000 * 60 * 60))}h
                      </div>
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>
      )}

      {/* Seat Selection */}
      {selectedTrip && (
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Sélection du siège</h2>
          <div className="grid grid-cols-6 md:grid-cols-10 gap-2">
            {seats.map((seat) => (
              <button
                key={seat.seat_no}
                onClick={() => handleSeatSelect(seat.seat_no)}
                disabled={seat.status === 'occupied'}
                className={`p-2 text-sm rounded ${
                  seat.status === 'occupied'
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    : selectedSeat === seat.seat_no
                    ? 'bg-setrag-primary text-white'
                    : 'bg-gray-100 hover:bg-setrag-primary hover:bg-opacity-20'
                }`}
              >
                {seat.seat_no}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Price Quote */}
      {quote && (
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Détails du prix</h2>
          <div className="space-y-2">
            <div className="flex justify-between">
              <span>Prix de base:</span>
              <span>{quote.base_price} FCFA</span>
            </div>
            <div className="flex justify-between">
              <span>Commission:</span>
              <span>{quote.taxes} FCFA</span>
            </div>
            {searchForm.promo_code && (
              <div className="flex justify-between text-green-600">
                <span>Réduction promo:</span>
                <span>-{Math.round(quote.total_price * 0.1)} FCFA</span>
              </div>
            )}
            <div className="border-t pt-2 flex justify-between font-bold">
              <span>Total:</span>
              <span>{searchForm.promo_code ? Math.round(quote.total_price * 0.9) : quote.total_price} FCFA</span>
            </div>
          </div>
        </div>
      )}

      {/* Booking Button */}
      {selectedTrip && selectedSeat && quote && (
        <div className="text-center">
          <button
            onClick={handleBooking}
            disabled={loading}
            className="btn-primary text-lg px-8 py-3 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Redirection...' : 'Procéder au paiement'}
          </button>
          {!isAuthenticated && (
            <p className="text-sm text-gray-600 mt-2">
              Vous pourrez saisir vos informations sur la page de paiement
            </p>
          )}
        </div>
      )}
    </div>
  )
}


