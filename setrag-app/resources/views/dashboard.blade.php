@extends('layouts.app')

@section('title', 'Mes réservations - SETRAG')

@section('content')
<div class="space-y-8">
    <div class="mb-4">
        <a href="{{ route('home') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à l'accueil</span>
        </a>
    </div>
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Mes réservations</h1>
            <p class="text-gray-600 mt-1">Consultez l'historique de vos billets</p>
        </div>
        <a href="{{ route('book') }}" class="btn-primary">Nouvelle réservation</a>
    </div>

    <!-- Mes réservations -->
    <div class="card">

        @if($bookings->count() > 0)
            <div class="space-y-4">
                @foreach($bookings as $booking)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <p class="font-bold text-lg text-setrag-primary">PNR: {{ $booking->pnr }}</p>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        {{ $booking->status }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Trajet</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $booking->trip->originStation->name }} → {{ $booking->trip->destinationStation->name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Date de départ</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $booking->trip->departure_time->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Date d'arrivée</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $booking->trip->arrival_time->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Siège</p>
                                        <p class="font-semibold text-gray-900">{{ $booking->seat_no }}</p>
                                    </div>
                                    @if($booking->class)
                                        <div>
                                            <p class="text-sm text-gray-600">Classe</p>
                                            <p class="font-semibold text-gray-900">
                                                @if($booking->class === 'VIP')
                                                    VIP
                                                @elseif($booking->class === 'first_class')
                                                    1ère classe
                                                @else
                                                    2ème classe
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                    @if($booking->passenger_type)
                                        <div>
                                            <p class="text-sm text-gray-600">Type de passager</p>
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
                                    @if($booking->passenger_name)
                                        <div>
                                            <p class="text-sm text-gray-600">Passager</p>
                                            <p class="font-semibold text-gray-900">{{ $booking->passenger_name }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm text-gray-600">Montant</p>
                                        <p class="font-bold text-lg text-setrag-primary">
                                            {{ number_format($booking->amount, 0, ',', ' ') }} {{ $booking->currency }}
                                        </p>
                                        @if($booking->discount_amount && $booking->discount_amount > 0)
                                            <p class="text-xs text-green-600 mt-1">
                                                Réduction: -{{ number_format($booking->discount_amount, 0, ',', ' ') }} FCFA
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                                    <p class="text-xs text-gray-500">
                                        Réservation créée le {{ $booking->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('booking.show', $booking) }}" class="btn-secondary text-sm px-4 py-2">
                                            Voir détails
                                        </a>
                                        @if($booking->status !== 'CONFIRMED')
                                            <button type="button" 
                                                    onclick="openDeleteModal({{ $booking->id }}, '{{ $booking->pnr }}')" 
                                                    class="btn-danger text-sm px-4 py-2">
                                                Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-600 text-lg mb-2">Vous n'avez aucune réservation pour le moment.</p>
                <p class="text-gray-500 text-sm mb-6">Commencez par réserver votre premier billet !</p>
                <a href="{{ route('book') }}" class="btn-primary inline-block">Réserver un billet</a>
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

