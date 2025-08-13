import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { getToken, fetchMe } from '../auth'
import { createBooking } from '../api'
import Header from '../components/Header'

interface PassengerInfo {
  full_name: string
  email: string
  phone: string
  city: string
  birthday: string
}

interface PaymentMethod {
  type: 'mobile_money' | 'card'
  provider?: 'airtel' | 'moov' | 'visa' | 'mastercard'
  phone?: string
  card_number?: string
  expiry_date?: string
  cvv?: string
}

interface BookingInfo {
  trip: any
  seat: string
  quote: any
  searchForm: any
  timestamp: string
}

export default function Payment() {
  const navigate = useNavigate()
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [userInfo, setUserInfo] = useState<any>(null)
  const [bookingInfo, setBookingInfo] = useState<BookingInfo | null>(null)
  const [passengerInfo, setPassengerInfo] = useState<PassengerInfo>({
    full_name: '',
    email: '',
    phone: '',
    city: '',
    birthday: ''
  })
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>({
    type: 'mobile_money',
    provider: 'airtel'
  })
  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState('')

  useEffect(() => {
    // Récupérer les informations de réservation
    const savedBooking = localStorage.getItem('pending_booking')
    if (!savedBooking) {
      navigate('/book')
      return
    }

    const booking = JSON.parse(savedBooking)
    setBookingInfo(booking)

    // Vérifier l'authentification
    const token = getToken()
    if (token) {
      setIsAuthenticated(true)
      fetchMe().then(user => {
        setUserInfo(user)
        // Pré-remplir les informations si l'utilisateur est connecté
        setPassengerInfo({
          full_name: user.full_name || '',
          email: user.email || '',
          phone: '',
          city: '',
          birthday: ''
        })
      }).catch(() => {
        setIsAuthenticated(false)
      })
    }
  }, [navigate])

  const handlePassengerInfoChange = (field: keyof PassengerInfo, value: string) => {
    setPassengerInfo(prev => ({ ...prev, [field]: value }))
  }

  const handlePaymentMethodChange = (field: keyof PaymentMethod, value: any) => {
    setPaymentMethod(prev => ({ ...prev, [field]: value }))
  }

  const validateForm = () => {
    if (!isAuthenticated) {
      // Validation pour utilisateurs non authentifiés
      if (!passengerInfo.full_name || !passengerInfo.email || !passengerInfo.phone || !passengerInfo.city || !passengerInfo.birthday) {
        setMessage('Veuillez remplir tous les champs obligatoires')
        return false
      }
    }

    // Validation du mode de paiement
    if (paymentMethod.type === 'mobile_money') {
      if (!paymentMethod.phone) {
        setMessage('Veuillez saisir votre numéro de téléphone')
        return false
      }
    } else {
      if (!paymentMethod.card_number || !paymentMethod.expiry_date || !paymentMethod.cvv) {
        setMessage('Veuillez remplir tous les champs de la carte')
        return false
      }
    }

    return true
  }

  const handlePayment = async () => {
    if (!validateForm() || !bookingInfo) return

    setLoading(true)
    setMessage('')

    try {
      // Simuler le processus de paiement
      await new Promise(resolve => setTimeout(resolve, 2000))

      // Générer un PNR fictif pour la démonstration
      const pnr = 'PNR' + Math.random().toString(36).substr(2, 8).toUpperCase()
      
      // Sauvegarder les informations passager et mode de paiement
      const updatedBooking = {
        ...bookingInfo,
        passengerInfo: passengerInfo,
        paymentMethod: paymentMethod
      }
      localStorage.setItem('pending_booking', JSON.stringify(updatedBooking))
      
      // Rediriger vers la page de succès avec le PNR
      navigate(`/success?pnr=${pnr}`)

    } catch (error) {
      setMessage('Erreur lors du paiement : ' + (error as Error).message)
      setLoading(false)
    }
  }

  if (!bookingInfo) {
    return (
      <div className="text-center py-8">
        <p>Redirection vers la page de réservation...</p>
      </div>
    )
  }

  const { trip, seat, quote, searchForm } = bookingInfo
  const originName = trip.origin_station_name || `Station ${trip.origin_station_id}`
  const destName = trip.destination_station_name || `Station ${trip.destination_station_id}`
  const departureDate = new Date(trip.departure_time)
  const arrivalDate = new Date(trip.arrival_time)

  return (
    <div className="space-y-8">
      <Header 
        title="Paiement" 
        description="Finalisez votre réservation SETRAG"
      />

      {message && (
        <div className={`p-4 rounded-lg ${
          message.includes('Erreur') 
            ? 'bg-red-100 text-red-700 border border-red-200' 
            : 'bg-green-100 text-green-700 border border-green-200'
        }`}>
          {message}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Détails du voyage */}
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Détails du voyage</h2>
          
          <div className="space-y-4">
            <div className="flex justify-between items-center">
              <span className="font-medium">Trajet:</span>
              <span className="text-lg font-semibold">{originName} → {destName}</span>
            </div>
            
            <div className="flex justify-between items-center">
              <span className="font-medium">Date:</span>
              <span>{departureDate.toLocaleDateString('fr-FR')}</span>
            </div>
            
            <div className="flex justify-between items-center">
              <span className="font-medium">Heure départ:</span>
              <span>{departureDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
            </div>
            
            <div className="flex justify-between items-center">
              <span className="font-medium">Heure arrivée:</span>
              <span>{arrivalDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
            </div>
            
            <div className="flex justify-between items-center">
              <span className="font-medium">Siège:</span>
              <span className="text-setrag-primary font-semibold">{seat}</span>
            </div>
            
            <div className="border-t pt-4">
              <div className="flex justify-between items-center">
                <span className="font-medium">Prix de base:</span>
                <span>{quote.base_price} FCFA</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="font-medium">Commission:</span>
                <span>{quote.taxes} FCFA</span>
              </div>
              {searchForm.promo_code && (
                <div className="flex justify-between items-center text-green-600">
                  <span>Réduction promo:</span>
                  <span>-{Math.round(quote.total_price * 0.1)} FCFA</span>
                </div>
              )}
              <div className="flex justify-between items-center text-lg font-bold border-t pt-2">
                <span>Total:</span>
                <span className="text-setrag-primary">
                  {searchForm.promo_code ? Math.round(quote.total_price * 0.9) : quote.total_price} FCFA
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Formulaire de paiement */}
        <div className="space-y-6">
          {/* Informations passager (si non authentifié) */}
          {!isAuthenticated && (
            <div className="card p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Informations passager</h2>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Nom complet *
                  </label>
                  <input
                    type="text"
                    value={passengerInfo.full_name}
                    onChange={(e) => handlePassengerInfoChange('full_name', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Email *
                  </label>
                  <input
                    type="email"
                    value={passengerInfo.email}
                    onChange={(e) => handlePassengerInfoChange('email', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Téléphone *
                  </label>
                  <input
                    type="tel"
                    value={passengerInfo.phone}
                    onChange={(e) => handlePassengerInfoChange('phone', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Ville *
                  </label>
                  <input
                    type="text"
                    value={passengerInfo.city}
                    onChange={(e) => handlePassengerInfoChange('city', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Date de naissance *
                  </label>
                  <input
                    type="date"
                    value={passengerInfo.birthday}
                    onChange={(e) => handlePassengerInfoChange('birthday', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    required
                  />
                </div>
              </div>
            </div>
          )}

          {/* Mode de paiement */}
          <div className="card p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Mode de paiement</h2>
            
            <div className="space-y-4">
              {/* Sélection du type de paiement */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Type de paiement
                </label>
                <div className="grid grid-cols-2 gap-4">
                  <button
                    onClick={() => handlePaymentMethodChange('type', 'mobile_money')}
                    className={`p-4 border rounded-lg text-center ${
                      paymentMethod.type === 'mobile_money'
                        ? 'border-setrag-primary bg-setrag-primary bg-opacity-10'
                        : 'border-gray-300 hover:border-setrag-primary'
                    }`}
                  >
                    <div className="text-2xl mb-2">📱</div>
                    <div className="font-medium">Mobile Money</div>
                  </button>
                  
                  <button
                    onClick={() => handlePaymentMethodChange('type', 'card')}
                    className={`p-4 border rounded-lg text-center ${
                      paymentMethod.type === 'card'
                        ? 'border-setrag-primary bg-setrag-primary bg-opacity-10'
                        : 'border-gray-300 hover:border-setrag-primary'
                    }`}
                  >
                    <div className="text-2xl mb-2">💳</div>
                    <div className="font-medium">Carte bancaire</div>
                  </button>
                </div>
              </div>

              {/* Détails Mobile Money */}
              {paymentMethod.type === 'mobile_money' && (
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Opérateur
                    </label>
                    <select
                      value={paymentMethod.provider}
                      onChange={(e) => handlePaymentMethodChange('provider', e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    >
                      <option value="airtel">Airtel Money</option>
                      <option value="moov">Moov Money</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Numéro de téléphone *
                    </label>
                    <input
                      type="tel"
                      value={paymentMethod.phone || ''}
                      onChange={(e) => handlePaymentMethodChange('phone', e.target.value)}
                      placeholder="Ex: +241 01234567"
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                      required
                    />
                  </div>
                </div>
              )}

              {/* Détails Carte bancaire */}
              {paymentMethod.type === 'card' && (
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Type de carte
                    </label>
                    <select
                      value={paymentMethod.provider}
                      onChange={(e) => handlePaymentMethodChange('provider', e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                    >
                      <option value="visa">Visa</option>
                      <option value="mastercard">Mastercard</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Numéro de carte *
                    </label>
                    <input
                      type="text"
                      value={paymentMethod.card_number || ''}
                      onChange={(e) => handlePaymentMethodChange('card_number', e.target.value)}
                      placeholder="1234 5678 9012 3456"
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                      required
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Date d'expiration *
                      </label>
                      <input
                        type="text"
                        value={paymentMethod.expiry_date || ''}
                        onChange={(e) => handlePaymentMethodChange('expiry_date', e.target.value)}
                        placeholder="MM/AA"
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                        required
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        CVV *
                      </label>
                      <input
                        type="text"
                        value={paymentMethod.cvv || ''}
                        onChange={(e) => handlePaymentMethodChange('cvv', e.target.value)}
                        placeholder="123"
                        maxLength={4}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                        required
                      />
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Bouton de paiement */}
          <div className="text-center">
            <button
              onClick={handlePayment}
              disabled={loading}
              className="btn-primary text-lg px-8 py-3 w-full disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Traitement en cours...' : 'Confirmer le paiement'}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
