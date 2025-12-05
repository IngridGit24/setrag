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
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-xs text-gray-500">
                                        Réservation créée le {{ $booking->created_at->format('d/m/Y à H:i') }}
                                    </p>
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
@endsection

