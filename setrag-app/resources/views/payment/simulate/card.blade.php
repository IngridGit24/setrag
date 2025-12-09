@extends('layouts.app')

@section('title', 'Paiement Carte Bancaire - SETRAG')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('payment') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour au récapitulatif de paiement</span>
        </a>
    </div>

    <div class="card">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <path d="M2 10h20"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Paiement Visa / Mastercard</h1>
            <p class="text-gray-600">Mode simulation (développement local)</p>
        </div>

        <!-- Résumé de la réservation -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Résumé de la réservation</h2>
            <div class="space-y-2 text-left">
                <div class="flex justify-between">
                    <span class="text-gray-600">Trajet:</span>
                    <span class="font-semibold">{{ $booking->trip->originStation->name }} → {{ $booking->trip->destinationStation->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Départ:</span>
                    <span>{{ $booking->trip->departure_time->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Siège:</span>
                    <span class="font-semibold">{{ $booking->seat_no }}</span>
                </div>
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="text-gray-600 font-semibold">Montant:</span>
                    <span class="font-bold text-setrag-primary text-lg">{{ number_format($booking->amount, 0, ',', ' ') }} {{ $booking->currency }}</span>
                </div>
            </div>
        </div>

        <!-- Formulaire de paiement -->
        <form method="POST" action="{{ route('payment.simulation.card.process') }}" class="space-y-6" id="card-form">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Numéro de carte *
                </label>
                <input type="text" name="card_number" 
                       placeholder="1234 5678 9012 3456"
                       maxlength="16"
                       pattern="[0-9]{16}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary"
                       required>
                <p class="text-sm text-gray-500 mt-1">16 chiffres sans espaces</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Titulaire de la carte *
                </label>
                <input type="text" name="card_holder" 
                       placeholder="NOM PRENOM"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary uppercase"
                       required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date d'expiration *
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="expiry_month" 
                               placeholder="MM"
                               maxlength="2"
                               pattern="[0-9]{2}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary"
                               required>
                        <input type="text" name="expiry_year" 
                               placeholder="YYYY"
                               maxlength="4"
                               pattern="[0-9]{4}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary"
                               required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        CVV *
                    </label>
                    <input type="text" name="cvv" 
                           placeholder="123"
                           maxlength="3"
                           pattern="[0-9]{3}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary"
                           required>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                    <strong>Mode simulation :</strong> Ce paiement est simulé en local. Aucun débit réel ne sera effectué.
                </p>
            </div>

            <button type="submit" class="btn-primary w-full">
                Payer avec ma carte
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('card-form').addEventListener('submit', function(e) {
    // Formater le numéro de carte (supprimer les espaces)
    const cardNumber = document.querySelector('input[name="card_number"]');
    cardNumber.value = cardNumber.value.replace(/\s/g, '');
});
</script>
@endsection

