import { useState, useEffect } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import Header from '../components/Header'

interface BookingDetails {
  pnr: string
  trip: any
  seat: string
  quote: any
  searchForm: any
  passengerInfo: any
  paymentMethod: any
  timestamp: string
}

export default function Success() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [bookingDetails, setBookingDetails] = useState<BookingDetails | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Récupérer les détails depuis l'URL ou localStorage
    const pnr = searchParams.get('pnr')
    const savedBooking = localStorage.getItem('pending_booking')
    
    if (pnr && savedBooking) {
      const booking = JSON.parse(savedBooking)
      const details: BookingDetails = {
        pnr: pnr,
        trip: booking.trip,
        seat: booking.seat,
        quote: booking.quote,
        searchForm: booking.searchForm,
        passengerInfo: booking.passengerInfo || {},
        paymentMethod: booking.paymentMethod || {},
        timestamp: new Date().toISOString()
      }
      setBookingDetails(details)
      
      // Nettoyer le localStorage
      localStorage.removeItem('pending_booking')
    } else {
      // Rediriger si pas de détails
      navigate('/')
    }
    
    setLoading(false)
  }, [searchParams, navigate])

  const handleDownload = () => {
    if (!bookingDetails) return
    
    // Créer le contenu de la facture
    const invoiceContent = generateInvoiceContent(bookingDetails)
    
    // Créer un blob et télécharger
    const blob = new Blob([invoiceContent], { type: 'text/plain' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `SETRAG_${bookingDetails.pnr}.txt`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  }

  const generateInvoiceContent = (details: BookingDetails) => {
    const departureDate = new Date(details.trip.departure_time)
    const arrivalDate = new Date(details.trip.arrival_time)
    
    return `
SETRAG - FACTURE DE RÉSERVATION
================================

Référence: ${details.pnr}
Date: ${new Date().toLocaleDateString('fr-FR')}
Heure: ${new Date().toLocaleTimeString('fr-FR')}

INFORMATIONS PASSAGER
--------------------
Nom: ${details.passengerInfo.full_name || 'Non spécifié'}
Email: ${details.passengerInfo.email || 'Non spécifié'}
Téléphone: ${details.passengerInfo.phone || 'Non spécifié'}
Ville: ${details.passengerInfo.city || 'Non spécifié'}

DÉTAILS DU VOYAGE
-----------------
Trajet: ${details.trip.origin_station_name} → ${details.trip.destination_station_name}
Date de départ: ${departureDate.toLocaleDateString('fr-FR')}
Heure de départ: ${departureDate.toLocaleTimeString('fr-FR')}
Heure d'arrivée: ${arrivalDate.toLocaleTimeString('fr-FR')}
Siège: ${details.seat}
Type de voyage: ${details.searchForm.trip_type === 'one_way' ? 'Aller simple' : 'Aller-retour'}

MODE DE PAIEMENT
----------------
Type: ${details.paymentMethod.type === 'mobile_money' ? 'Mobile Money' : 'Carte bancaire'}
${details.paymentMethod.type === 'mobile_money' ? 
  `Opérateur: ${details.paymentMethod.provider === 'airtel' ? 'Airtel Money' : 'Moov Money'}
   Téléphone: ${details.paymentMethod.phone || 'Non spécifié'}` :
  `Carte: ${details.paymentMethod.provider === 'visa' ? 'Visa' : 'Mastercard'}
   Numéro: ${details.paymentMethod.card_number ? '**** **** **** ' + details.paymentMethod.card_number.slice(-4) : 'Non spécifié'}`
}

DÉTAILS FINANCIERS
------------------
Prix de base: ${details.quote.base_price} FCFA
Commission (5%): ${details.quote.taxes} FCFA
${details.searchForm.promo_code ? `Code promo: ${details.searchForm.promo_code}
Réduction: -${Math.round(details.quote.total_price * 0.1)} FCFA` : ''}
TOTAL: ${details.searchForm.promo_code ? Math.round(details.quote.total_price * 0.9) : details.quote.total_price} FCFA

INSTRUCTIONS IMPORTANTES
------------------------
1. Présentez cette facture à l'embarquement
2. Arrivez 30 minutes avant le départ
3. Ayez une pièce d'identité valide
4. Le PNR est votre référence unique

CONTACT SETRAG
--------------
Email: contact@setrag.ga
Téléphone: +241 01 76 00 00
Adresse: Libreville, Gabon

Merci de votre confiance !
SETRAG - Votre partenaire de confiance pour le transport ferroviaire au Gabon.
    `.trim()
  }

  if (loading) {
    return (
      <div className="text-center py-8">
        <p>Chargement de votre facture...</p>
      </div>
    )
  }

  if (!bookingDetails) {
    return (
      <div className="text-center py-8">
        <p>Redirection vers l'accueil...</p>
      </div>
    )
  }

  const departureDate = new Date(bookingDetails.trip.departure_time)
  const arrivalDate = new Date(bookingDetails.trip.arrival_time)

  return (
    <div className="space-y-8">
      <Header 
        title="Réservation confirmée" 
        description="Votre voyage SETRAG a été réservé avec succès"
      />

      {/* Message de succès */}
      <div className="card p-6 bg-green-50 border-green-200">
        <div className="text-center">
          <div className="text-6xl mb-4">🎉</div>
          <h1 className="text-2xl font-bold text-green-800 mb-2">
            Réservation confirmée !
          </h1>
          <p className="text-green-700">
            Votre billet a été réservé avec succès. Voici votre facture.
          </p>
        </div>
      </div>

      {/* Facture */}
      <div className="card p-8">
        <div className="flex justify-between items-start mb-6">
          <div>
            <h2 className="text-2xl font-bold text-gray-900">SETRAG</h2>
            <p className="text-gray-600">Votre partenaire de confiance pour le transport ferroviaire au Gabon</p>
          </div>
          <div className="text-right">
            <div className="text-sm text-gray-600">Référence</div>
            <div className="text-xl font-bold text-setrag-primary">{bookingDetails.pnr}</div>
            <div className="text-sm text-gray-600 mt-1">
              {new Date().toLocaleDateString('fr-FR')}
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          {/* Informations passager */}
          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Informations passager</h3>
            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-gray-600">Nom:</span>
                <span className="font-medium">{bookingDetails.passengerInfo.full_name || 'Non spécifié'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Email:</span>
                <span className="font-medium">{bookingDetails.passengerInfo.email || 'Non spécifié'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Téléphone:</span>
                <span className="font-medium">{bookingDetails.passengerInfo.phone || 'Non spécifié'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Ville:</span>
                <span className="font-medium">{bookingDetails.passengerInfo.city || 'Non spécifié'}</span>
              </div>
            </div>
          </div>

          {/* Détails du voyage */}
          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Détails du voyage</h3>
            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-gray-600">Trajet:</span>
                <span className="font-medium">{bookingDetails.trip.origin_station_name} → {bookingDetails.trip.destination_station_name}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Date:</span>
                <span className="font-medium">{departureDate.toLocaleDateString('fr-FR')}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Départ:</span>
                <span className="font-medium">{departureDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Arrivée:</span>
                <span className="font-medium">{arrivalDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Siège:</span>
                <span className="font-medium text-setrag-primary">{bookingDetails.seat}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Mode de paiement */}
        <div className="mb-8">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Mode de paiement</h3>
          <div className="bg-gray-50 p-4 rounded-lg">
            <div className="flex items-center space-x-2 mb-2">
              <span className="text-2xl">
                {bookingDetails.paymentMethod.type === 'mobile_money' ? '📱' : '💳'}
              </span>
              <span className="font-medium">
                {bookingDetails.paymentMethod.type === 'mobile_money' ? 'Mobile Money' : 'Carte bancaire'}
              </span>
            </div>
            {bookingDetails.paymentMethod.type === 'mobile_money' ? (
              <div className="text-sm text-gray-600">
                {bookingDetails.paymentMethod.provider === 'airtel' ? 'Airtel Money' : 'Moov Money'} - 
                {bookingDetails.paymentMethod.phone || 'Numéro non spécifié'}
              </div>
            ) : (
              <div className="text-sm text-gray-600">
                {bookingDetails.paymentMethod.provider === 'visa' ? 'Visa' : 'Mastercard'} - 
                {bookingDetails.paymentMethod.card_number ? '**** **** **** ' + bookingDetails.paymentMethod.card_number.slice(-4) : 'Numéro non spécifié'}
              </div>
            )}
          </div>
        </div>

        {/* Détails financiers */}
        <div className="border-t pt-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Détails financiers</h3>
          <div className="space-y-2">
            <div className="flex justify-between">
              <span>Prix de base:</span>
              <span>{bookingDetails.quote.base_price} FCFA</span>
            </div>
            <div className="flex justify-between">
              <span>Commission (5%):</span>
              <span>{bookingDetails.quote.taxes} FCFA</span>
            </div>
            {bookingDetails.searchForm.promo_code && (
              <div className="flex justify-between text-green-600">
                <span>Code promo ({bookingDetails.searchForm.promo_code}):</span>
                <span>-{Math.round(bookingDetails.quote.total_price * 0.1)} FCFA</span>
              </div>
            )}
            <div className="flex justify-between text-lg font-bold border-t pt-2">
              <span>Total:</span>
              <span className="text-setrag-primary">
                {bookingDetails.searchForm.promo_code ? Math.round(bookingDetails.quote.total_price * 0.9) : bookingDetails.quote.total_price} FCFA
              </span>
            </div>
          </div>
        </div>

        {/* Instructions */}
        <div className="mt-8 p-4 bg-blue-50 rounded-lg">
          <h3 className="font-semibold text-blue-900 mb-2">Instructions importantes</h3>
          <ul className="text-sm text-blue-800 space-y-1">
            <li>• Présentez cette facture à l'embarquement</li>
            <li>• Arrivez 30 minutes avant le départ</li>
            <li>• Ayez une pièce d'identité valide</li>
            <li>• Le PNR {bookingDetails.pnr} est votre référence unique</li>
          </ul>
        </div>

        {/* Contact */}
        <div className="mt-8 text-center text-sm text-gray-600">
          <p><strong>SETRAG</strong> - Votre partenaire de confiance pour le transport ferroviaire au Gabon</p>
          <p className="mt-1">Email: contact@setrag.ga | Téléphone: +241 01 76 00 00 | Libreville, Gabon</p>
        </div>
      </div>

      {/* Actions */}
      <div className="flex flex-col sm:flex-row gap-4 justify-center">
        <button
          onClick={handleDownload}
          className="btn-primary px-8 py-3"
        >
          📄 Télécharger la facture
        </button>
        <button
          onClick={() => navigate('/')}
          className="btn-secondary px-8 py-3"
        >
          🏠 Retour à l'accueil
        </button>
      </div>
    </div>
  )
}
