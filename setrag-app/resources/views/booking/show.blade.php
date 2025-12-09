@extends('layouts.app')

@section('title', 'Détails de la réservation - SETRAG')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="mb-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à mes réservations</span>
        </a>
    </div>

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Détails de la réservation</h1>
            <p class="text-gray-600 mt-1">PNR: <span class="font-semibold text-setrag-primary">{{ $booking->pnr }}</span></p>
        </div>
        <div>
            @if($booking->status !== 'CONFIRMED')
                <button type="button" 
                        onclick="openDeleteModal({{ $booking->id }}, '{{ $booking->pnr }}')" 
                        class="btn-danger">
                    Supprimer la réservation
                </button>
            @endif
        </div>
    </div>

    <!-- Statut de la réservation -->
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold mb-2">Statut de la réservation</h2>
                <p class="text-gray-600">Réservation créée le {{ $booking->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                @if($booking->status === 'CONFIRMED')
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        ✓ Confirmée
                    </span>
                @elseif($booking->status === 'PENDING')
                    <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        ⏳ En attente
                    </span>
                @elseif($booking->status === 'CANCELLED')
                    <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        ✗ Annulée
                    </span>
                @else
                    <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                        {{ $booking->status }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Informations du trajet -->
    <div class="card">
        <h2 class="text-xl font-semibold mb-4">Informations du trajet</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-1">Gare de départ</p>
                <p class="font-semibold text-lg text-gray-900">{{ $booking->trip->originStation->name }}</p>
                @if($booking->trip->originStation->latitude && $booking->trip->originStation->longitude)
                    <p class="text-xs text-gray-500 mt-1">
                        📍 {{ $booking->trip->originStation->latitude }}, {{ $booking->trip->originStation->longitude }}
                    </p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Gare d'arrivée</p>
                <p class="font-semibold text-lg text-gray-900">{{ $booking->trip->destinationStation->name }}</p>
                @if($booking->trip->destinationStation->latitude && $booking->trip->destinationStation->longitude)
                    <p class="text-xs text-gray-500 mt-1">
                        📍 {{ $booking->trip->destinationStation->latitude }}, {{ $booking->trip->destinationStation->longitude }}
                    </p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Date et heure de départ</p>
                <p class="font-semibold text-gray-900">{{ $booking->trip->departure_time->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Date et heure d'arrivée prévue</p>
                <p class="font-semibold text-gray-900">{{ $booking->trip->arrival_time->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Informations du passager -->
    <div class="card">
        <h2 class="text-xl font-semibold mb-4">Informations du passager</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-1">Nom complet</p>
                <p class="font-semibold text-gray-900">{{ $booking->passenger_name ?? 'Non renseigné' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Email</p>
                <p class="font-semibold text-gray-900">{{ $booking->passenger_email }}</p>
            </div>
            @if($booking->passenger_birth_date)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date de naissance</p>
                    <p class="font-semibold text-gray-900">{{ $booking->passenger_birth_date->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Âge: {{ $booking->passenger_birth_date->age }} ans
                    </p>
                </div>
            @endif
            @if($booking->passenger_type)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Type de passager</p>
                    <p class="font-semibold text-gray-900">
                        @if($booking->passenger_type === 'student')
                            Étudiant
                        @elseif($booking->passenger_type === 'senior')
                            3e âge
                        @elseif($booking->passenger_type === 'child')
                            Enfant
                        @else
                            Adulte
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Informations du siège et de la classe -->
    <div class="card">
        <h2 class="text-xl font-semibold mb-4">Informations du siège</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-1">Numéro de siège</p>
                <p class="font-semibold text-2xl text-setrag-primary">{{ $booking->seat_no }}</p>
            </div>
            @if($booking->class)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Classe</p>
                    <p class="font-semibold text-gray-900">
                        @if($booking->class === 'VIP')
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">VIP</span>
                        @elseif($booking->class === 'first_class')
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">1ère classe</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">2ème classe</span>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Informations de paiement -->
    <div class="card">
        <h2 class="text-xl font-semibold mb-4">Informations de paiement</h2>
        <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Prix de base</span>
                    <span class="font-semibold">{{ number_format($booking->base_price ?? $booking->amount, 0, ',', ' ') }} {{ $booking->currency }}</span>
                </div>
                @if($booking->discount_amount && $booking->discount_amount > 0)
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Réduction</span>
                        <span class="font-semibold text-green-600">-{{ number_format($booking->discount_amount, 0, ',', ' ') }} {{ $booking->currency }}</span>
                    </div>
                @endif
                @if($booking->commission && $booking->commission > 0)
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Commission</span>
                        <span class="font-semibold">{{ number_format($booking->commission, 0, ',', ' ') }} {{ $booking->currency }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                    <span class="text-lg font-semibold text-gray-900">Total payé</span>
                    <span class="text-2xl font-bold text-setrag-primary">{{ number_format($booking->amount, 0, ',', ' ') }} {{ $booking->currency }}</span>
                </div>
            </div>

            @if($booking->payment_method)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Méthode de paiement</p>
                    <p class="font-semibold text-gray-900">
                        @if($booking->payment_method === 'airtel')
                            Airtel Money
                        @elseif($booking->payment_method === 'moov')
                            Moov Money
                        @elseif($booking->payment_method === 'card')
                            Visa / Mastercard
                        @else
                            {{ $booking->payment_method }}
                        @endif
                    </p>
                </div>
            @endif

            @if($booking->payment_status)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Statut du paiement</p>
                    <p class="font-semibold">
                        @if($booking->payment_status === 'paid')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Payé</span>
                        @elseif($booking->payment_status === 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">En attente</span>
                        @elseif($booking->payment_status === 'failed')
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Échoué</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">{{ $booking->payment_status }}</span>
                        @endif
                    </p>
                </div>
            @endif

            @if($booking->transaction_id)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Transaction ID</p>
                    <p class="font-mono text-sm text-gray-900">{{ $booking->transaction_id }}</p>
                </div>
            @endif

            @if($booking->paid_at)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date de paiement</p>
                    <p class="font-semibold text-gray-900">{{ $booking->paid_at->format('d/m/Y à H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="btn-secondary">Retour à mes réservations</a>
        @if($booking->status === 'CONFIRMED')
            <div class="text-sm text-gray-600">
                <p>Cette réservation est confirmée. Pour toute modification, veuillez contacter le support.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Confirmer la suppression</h3>
            <p class="text-gray-600 text-center mb-1">
                Êtes-vous sûr de vouloir supprimer la réservation
            </p>
            <p class="text-gray-900 font-semibold text-center mb-6" id="modalPnr">
                PNR: <span class="text-setrag-primary"></span>
            </p>
            <p class="text-sm text-red-600 text-center mb-6">
                Cette action est irréversible. Le siège sera libéré.
            </p>
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeDeleteModal()" 
                        class="flex-1 btn-secondary">
                    Annuler
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full btn-danger">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDeleteModal(bookingId, pnr) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    const pnrSpan = document.querySelector('#modalPnr span');
    
    // Mettre à jour le formulaire avec l'URL de suppression
    form.action = `/booking/${bookingId}`;
    
    // Mettre à jour le PNR affiché
    pnrSpan.textContent = pnr;
    
    // Afficher le modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Empêcher le scroll
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Réactiver le scroll
}

// Fermer le modal en cliquant en dehors
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Fermer le modal avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endpush
@endsection

