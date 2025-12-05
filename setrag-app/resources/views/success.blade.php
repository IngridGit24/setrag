@extends('layouts.app')

@section('title', 'Réservation confirmée - SETRAG')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à mes réservations</span>
        </a>
    </div>
    <div class="card text-center">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Réservation confirmée !</h1>
        <p class="text-gray-600 mb-8">Votre réservation a été effectuée avec succès.</p>

        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Détails de la réservation</h2>
            <div class="space-y-2 text-left">
                <div class="flex justify-between">
                    <span class="text-gray-600">PNR:</span>
                    <span class="font-bold text-setrag-primary">{{ $booking['pnr'] ?? $bookingData['pnr'] }}</span>
                </div>
                @if($booking)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Trajet:</span>
                        <span class="font-semibold">{{ $booking->trip->originStation->name }} → {{ $booking->trip->destinationStation->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Départ:</span>
                        <span>{{ $booking->trip->departure_time->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Siège:</span>
                        <span class="font-semibold">{{ $booking->seat_no }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Passager:</span>
                        <span>{{ $booking->passenger_name ?? 'N/A' }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="text-gray-600 font-semibold">Montant:</span>
                    <span class="font-bold text-setrag-primary text-lg">{{ number_format($booking['amount'] ?? $bookingData['amount'], 0, ',', ' ') }} {{ $booking['currency'] ?? $bookingData['currency'] }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('dashboard') }}" class="btn-primary">Voir mes réservations</a>
            <a href="{{ route('home') }}" class="btn-secondary">Retour à l'accueil</a>
        </div>
    </div>
</div>
@endsection

