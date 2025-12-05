@extends('layouts.app')

@section('title', 'Paiement en cours - SETRAG')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card text-center">
        <div class="mb-4">
            <svg class="mx-auto h-16 w-16 text-yellow-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement en cours de traitement</h1>
        <p class="text-gray-600 mb-6">
            Votre paiement est en cours de validation. Veuillez patienter quelques instants...
        </p>

        @if($booking)
            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h2 class="text-xl font-semibold mb-4">Détails de la réservation</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">PNR:</span>
                        <span class="font-bold text-setrag-primary">{{ $booking->pnr }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Trajet:</span>
                        <span>{{ $booking->trip->originStation->name }} → {{ $booking->trip->destinationStation->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Montant:</span>
                        <span class="font-semibold">{{ number_format($booking->amount, 0, ',', ' ') }} {{ $booking->currency }}</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-sm text-gray-500 mb-6">
            <p>Cette page se rafraîchira automatiquement dans quelques secondes.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('dashboard') }}" class="btn-secondary">Voir mes réservations</a>
            <a href="{{ route('book') }}" class="btn-primary">Nouvelle réservation</a>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 3 seconds
setTimeout(function() {
    location.reload();
}, 3000);
</script>
@endsection

