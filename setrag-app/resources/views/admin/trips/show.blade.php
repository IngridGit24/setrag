@extends('layouts.app')

@section('title', 'Détails du voyage - SETRAG')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="mb-6">
        <a href="{{ route('admin.trips.index') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à la liste</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Détails du voyage #{{ $trip->id }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations du voyage -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Informations du voyage</h2>
            <div class="space-y-3">
                <div>
                    <span class="text-gray-600">Trajet:</span>
                    <span class="font-semibold ml-2">{{ $trip->originStation->name }} → {{ $trip->destinationStation->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Départ:</span>
                    <span class="font-semibold ml-2">{{ $trip->departure_time->format('d/m/Y à H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Arrivée:</span>
                    <span class="font-semibold ml-2">{{ $trip->arrival_time->format('d/m/Y à H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Durée:</span>
                    <span class="font-semibold ml-2">{{ $trip->departure_time->diffForHumans($trip->arrival_time, true) }}</span>
                </div>
                @if($trip->price_second_class || $trip->price_first_class || $trip->price_vip)
                    <div class="border-t pt-3 mt-3">
                        <p class="text-gray-600 font-semibold mb-2">Tarification:</p>
                        @if($trip->price_second_class)
                            <div class="text-sm">
                                <span class="text-gray-600">2ème classe:</span>
                                <span class="font-semibold">{{ number_format($trip->price_second_class, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                        @if($trip->price_first_class)
                            <div class="text-sm">
                                <span class="text-gray-600">1ère classe:</span>
                                <span class="font-semibold">{{ number_format($trip->price_first_class, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                        @if($trip->price_vip)
                            <div class="text-sm">
                                <span class="text-gray-600">VIP:</span>
                                <span class="font-semibold text-setrag-primary">{{ number_format($trip->price_vip, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="border-t pt-3 mt-3">
                        <p class="text-gray-500 text-sm">Prix par défaut utilisés</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistiques des places -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Statistiques des places</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total:</span>
                    <span class="font-semibold">{{ $totalSeats }} places</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Disponibles:</span>
                    <span class="font-semibold text-green-600">{{ $availableSeats }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Réservées (en attente):</span>
                    <span class="font-semibold text-yellow-600">{{ $heldSeats }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Vendues:</span>
                    <span class="font-semibold text-red-600">{{ $soldSeats }}</span>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taux d'occupation:</span>
                        <span class="font-semibold">{{ $totalSeats > 0 ? round(($soldSeats / $totalSeats) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="card">
        <div class="flex gap-4">
            <a href="{{ route('admin.trips.edit', $trip) }}" class="btn-primary">Modifier le voyage</a>
            @if($totalSeats == 0)
                <form method="POST" action="{{ route('admin.trips.generate-seats', $trip) }}" class="inline">
                    @csrf
                    <input type="hidden" name="count" value="100">
                    <button type="submit" class="btn-secondary">Créer 100 places</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Réservations -->
    @if($trip->bookings->count() > 0)
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Réservations ({{ $trip->bookings->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PNR</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siège</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($trip->bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $booking->pnr }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div>{{ $booking->passenger_name ?? 'N/A' }}</div>
                                    <div class="text-gray-500 text-xs">{{ $booking->passenger_email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $booking->seat_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ number_format($booking->amount, 0, ',', ' ') }} {{ $booking->currency }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $booking->status === 'CONFIRMED' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

