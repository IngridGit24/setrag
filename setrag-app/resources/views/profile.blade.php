@extends('layouts.app')

@section('title', 'Mon profil - SETRAG')

@section('content')
<div class="space-y-8">
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
            <h1 class="text-3xl font-bold text-gray-900">Mon profil</h1>
            <p class="text-gray-600 mt-1">Gérez vos informations personnelles</p>
        </div>
    </div>

    <!-- Informations du profil -->
    <div class="card">
        <h2 class="text-xl font-bold mb-6">Informations du profil</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-2">Nom complet</p>
                <p class="font-semibold text-lg text-gray-900">{{ $user['full_name'] ?? 'Non renseigné' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">Email</p>
                <p class="font-semibold text-lg text-gray-900">{{ $user['email'] }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">Rôle</p>
                <p class="font-semibold">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        {{ ucfirst($user['role'] ?? 'user') }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">Membre depuis</p>
                <p class="font-semibold text-lg text-gray-900">
                    @if(isset($user['created_at']))
                        {{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">Pour modifier vos informations, veuillez contacter le support.</p>
            <a href="mailto:contact@setrag.ga" class="text-setrag-primary hover:text-setrag-primary-dark text-sm font-medium">
                Contacter le support →
            </a>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="text-lg font-semibold mb-3">Mes réservations</h3>
            <p class="text-gray-600 text-sm mb-4">Consultez l'historique de vos billets et gérez vos voyages.</p>
            <a href="{{ route('dashboard') }}" class="btn-primary inline-block">Voir mes réservations</a>
        </div>

        <div class="card">
            <h3 class="text-lg font-semibold mb-3">Nouvelle réservation</h3>
            <p class="text-gray-600 text-sm mb-4">Réservez un nouveau billet pour votre prochain voyage.</p>
            <a href="{{ route('book') }}" class="btn-primary inline-block">Réserver un billet</a>
        </div>
    </div>
</div>
@endsection

