@extends('layouts.app')

@section('title', 'Suivi des trains - SETRAG')

@section('content')
<div class="space-y-6">
    <div class="mb-4">
        <a href="{{ route('home') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à l'accueil</span>
        </a>
    </div>
    <h1 class="text-3xl font-bold text-gray-900">Suivi des trains en temps réel</h1>

    @if($positions->count() > 0)
        <div class="card">
            <h2 class="text-2xl font-bold mb-4">Positions actuelles</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Train</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Longitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vitesse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Direction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière mise à jour</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($positions as $position)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $position->train_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($position->latitude, 5) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($position->longitude, 5) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $position->speed_kmh ? number_format($position->speed_kmh, 1) . ' km/h' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $position->bearing_deg ? number_format($position->bearing_deg, 1) . '°' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $position->timestamp_utc->format('d/m/Y H:i:s') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card text-center py-12">
            <p class="text-gray-600 text-lg">Aucune position de train disponible pour le moment.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-refresh every 5 seconds
    setTimeout(function() {
        location.reload();
    }, 5000);
</script>
@endpush
@endsection

