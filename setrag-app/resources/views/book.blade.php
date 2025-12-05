@extends('layouts.app')

@section('title', 'Réserver un billet - SETRAG')

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
    <h1 class="text-3xl font-bold text-gray-900">Réserver un billet</h1>

    <form method="GET" action="{{ route('book') }}" class="card mb-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gare de départ</label>
                <select name="origin_station_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
                    <option value="">Sélectionner...</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gare d'arrivée</label>
                <select name="destination_station_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
                    <option value="">Sélectionner...</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de départ</label>
                <input type="date" name="departure_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de passagers</label>
                <input type="number" name="passengers" min="1" max="10" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="btn-primary">Rechercher des trajets</button>
        </div>
    </form>


    @if($trips->count() > 0)
        <div class="card">
            <h2 class="text-2xl font-bold mb-4">Trajets disponibles</h2>
            <div class="space-y-4">
                @foreach($trips as $trip)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold">{{ $trip->originStation->name }} → {{ $trip->destinationStation->name }}</p>
                                <p class="text-sm text-gray-600">
                                    Départ: {{ $trip->departure_time->format('d/m/Y H:i') }}
                                    Arrivée: {{ $trip->arrival_time->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('payment.store') }}" class="inline">
                                @csrf
                                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                                <input type="hidden" name="passengers" value="{{ request('passengers', 1) }}">
                                <button type="submit" class="btn-primary">Sélectionner</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(request()->has('origin_station_id'))
        <div class="card">
            <p class="text-gray-600">Aucun trajet disponible pour cette recherche.</p>
        </div>
    @endif
</div>
@endsection

