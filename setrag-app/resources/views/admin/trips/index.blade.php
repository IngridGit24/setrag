@extends('layouts.app')

@section('title', 'Gestion des voyages - SETRAG')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Gestion des voyages</h1>
        <a href="{{ route('admin.trips.create') }}" class="btn-primary">+ Nouveau voyage</a>
    </div>

    <!-- Filtres -->
    <div class="card">
        <form method="GET" action="{{ route('admin.trips.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gare de départ</label>
                <select name="origin_station_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Toutes</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}" {{ request('origin_station_id') == $station->id ? 'selected' : '' }}>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gare d'arrivée</label>
                <select name="destination_station_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Toutes</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}" {{ request('destination_station_id') == $station->id ? 'selected' : '' }}>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="btn-primary">Filtrer</button>
                <a href="{{ route('admin.trips.index') }}" class="btn-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Liste des voyages -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trajet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Départ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arrivée</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Places</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($trips as $trip)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $trip->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="font-medium">{{ $trip->originStation->name }}</div>
                                <div class="text-gray-500">→ {{ $trip->destinationStation->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $trip->departure_time->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $trip->arrival_time->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $totalSeats = $trip->seats()->count();
                                    $availableSeats = $trip->seats()->where('status', 'AVAILABLE')->count();
                                    $soldSeats = $trip->seats()->where('status', 'SOLD')->count();
                                @endphp
                                <div class="text-gray-900">{{ $availableSeats }} / {{ $totalSeats }}</div>
                                <div class="text-xs text-gray-500">{{ $soldSeats }} vendus</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="text-setrag-primary hover:underline">Voir</a>
                                    <a href="{{ route('admin.trips.edit', $trip) }}" class="text-blue-600 hover:underline">Modifier</a>
                                    <form method="POST" action="{{ route('admin.trips.destroy', $trip) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce voyage ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Aucun voyage trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $trips->links() }}
        </div>
    </div>
</div>
@endsection

