@extends('layouts.app')

@section('title', 'Modifier le voyage - SETRAG')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.trips.index') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à la liste</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Modifier le voyage #{{ $trip->id }}</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.trips.update', $trip) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gare de départ *</label>
                    <select name="origin_station_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
                        <option value="">Sélectionner...</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}" {{ ($trip->origin_station_id == $station->id || old('origin_station_id') == $station->id) ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gare d'arrivée *</label>
                    <select name="destination_station_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
                        <option value="">Sélectionner...</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}" {{ ($trip->destination_station_id == $station->id || old('destination_station_id') == $station->id) ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date et heure de départ *</label>
                    <input type="datetime-local" name="departure_time" 
                           value="{{ old('departure_time', $trip->departure_time->format('Y-m-d\TH:i')) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date et heure d'arrivée *</label>
                    <input type="datetime-local" name="arrival_time" 
                           value="{{ old('arrival_time', $trip->arrival_time->format('Y-m-d\TH:i')) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                           required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de places *</label>
                    <input type="number" name="seat_count" value="{{ old('seat_count', $seatCount) }}" min="1" max="500" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                           required>
                    <p class="text-sm text-gray-500 mt-1">
                        Nombre total de sièges. Les sièges vendus ne seront pas supprimés.
                    </p>
                </div>

                <!-- Prix par classe -->
                <div class="md:col-span-2 border-t pt-6 mt-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tarification par classe</h3>
                    <p class="text-sm text-gray-600 mb-4">Définissez les prix pour chaque classe. Si non renseignés, les prix par défaut seront utilisés.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prix 2ème classe (FCFA)</label>
                            <input type="number" name="price_second_class" 
                                   value="{{ old('price_second_class', $trip->price_second_class) }}" 
                                   min="0" step="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                                   placeholder="Prix par défaut">
                            <p class="text-xs text-gray-500 mt-1">Prix de base pour la 2ème classe</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prix 1ère classe (FCFA)</label>
                            <input type="number" name="price_first_class" 
                                   value="{{ old('price_first_class', $trip->price_first_class) }}" 
                                   min="0" step="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                                   placeholder="Prix par défaut">
                            <p class="text-xs text-gray-500 mt-1">Prix pour la 1ère classe (+50% si non défini)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prix VIP (FCFA)</label>
                            <input type="number" name="price_vip" 
                                   value="{{ old('price_vip', $trip->price_vip) }}" 
                                   min="45000" max="100000" step="1000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                                   placeholder="45000 - 100000">
                            <p class="text-xs text-gray-500 mt-1">Prix VIP (entre 45 000 et 100 000 FCFA)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                <a href="{{ route('admin.trips.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

