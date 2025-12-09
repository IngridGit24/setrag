@extends('layouts.app')

@section('title', 'Paiement réussi - SETRAG')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card text-center">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement réussi !</h1>
        <p class="text-gray-600 mb-8">Votre réservation a été confirmée avec succès.</p>

        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Détails de la réservation</h2>
            <div class="space-y-2 text-left">
                <div class="flex justify-between">
                    <span class="text-gray-600">PNR:</span>
                    <span class="font-bold text-setrag-primary">{{ $bookingData['pnr'] ?? ($booking->pnr ?? 'N/A') }}</span>
                </div>
                @if($booking)
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
                @endif
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="text-gray-600 font-semibold">Montant:</span>
                    <span class="font-bold text-setrag-primary text-lg">
                        {{ number_format($bookingData['amount'] ?? ($booking->amount ?? 0), 0, ',', ' ') }} 
                        {{ $bookingData['currency'] ?? ($booking->currency ?? 'FCFA') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Informations de paiement -->
        @if(isset($bookingData['payment_data']))
            <div class="bg-blue-50 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Informations de paiement</h2>
                <div class="space-y-2 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Méthode:</span>
                        <span class="font-semibold">
                            @if($bookingData['payment_method'] === 'airtel')
                                Airtel Money
                            @elseif($bookingData['payment_method'] === 'moov')
                                Moov Money
                            @else
                                Visa / Mastercard
                            @endif
                        </span>
                    </div>
                    @if(isset($bookingData['payment_data']['phone_number']))
                        <div class="flex justify-between">
                            <span class="text-gray-600">Numéro utilisé:</span>
                            <span class="font-semibold">{{ $bookingData['payment_data']['phone_number'] }}</span>
                        </div>
                    @endif
                    @if(isset($bookingData['payment_data']['card_number']))
                        <div class="flex justify-between">
                            <span class="text-gray-600">Carte (4 derniers chiffres):</span>
                            <span class="font-semibold">**** **** **** {{ $bookingData['payment_data']['card_number'] }}</span>
                        </div>
                    @endif
                    @if(isset($booking->transaction_id))
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID:</span>
                            <span class="font-mono text-sm">{{ $booking->transaction_id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-green-800">
                <strong>Mode simulation :</strong> Ce paiement a été simulé en local. En production, vous recevrez une confirmation par email.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('dashboard') }}" class="btn-primary">Voir mes réservations</a>
            <a href="{{ route('home') }}" class="btn-secondary">Retour à l'accueil</a>
        </div>
    </div>
</div>
@endsection

