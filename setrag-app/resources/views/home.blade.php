@extends('layouts.app')

@section('title', 'Accueil - SETRAG')

@section('content')
<div class="space-y-12">
    <!-- Hero Section -->
    <div class="text-center py-12 bg-gradient-to-r from-setrag-primary to-setrag-primary-dark text-white rounded-xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Bienvenue sur SETRAG</h1>
        <p class="text-xl md:text-2xl mb-8">Votre partenaire de confiance pour le transport ferroviaire au Gabon</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('book') }}" class="btn-primary bg-white text-setrag-primary hover:bg-gray-100">
                Réserver un billet
            </a>
            <a href="{{ route('track') }}" class="btn-secondary bg-transparent border-2 border-white text-white hover:bg-white hover:text-setrag-primary">
                Suivre un train
            </a>
        </div>
    </div>

    <!-- Services Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card text-center">
            <div class="text-4xl mb-4">🎫</div>
            <h3 class="text-xl font-semibold mb-2">Réservation de billets</h3>
            <p class="text-gray-600">Réservez vos billets de train en ligne</p>
            <a href="{{ route('book') }}" class="btn-primary mt-4 inline-block">Réserver</a>
        </div>

        <div class="card text-center">
            <div class="text-4xl mb-4">📍</div>
            <h3 class="text-xl font-semibold mb-2">Suivi en temps réel</h3>
            <p class="text-gray-600">Suivez la position de vos trains en direct</p>
            <a href="{{ route('track') }}" class="btn-primary mt-4 inline-block">Suivre</a>
        </div>

        <div class="card text-center">
            <div class="text-4xl mb-4">📦</div>
            <h3 class="text-xl font-semibold mb-2">Expédition de colis</h3>
            <p class="text-gray-600">Envoyez vos colis en toute sécurité</p>
            <a href="{{ route('shipping') }}" class="btn-primary mt-4 inline-block">Expédier</a>
        </div>
    </div>

    <!-- Info Section -->
    <div class="card">
        <h2 class="text-2xl font-bold mb-4">À propos de SETRAG</h2>
        <p class="text-gray-700 mb-4">
            SETRAG est la compagnie ferroviaire nationale du Gabon, offrant des services de transport de passagers 
            et de marchandises à travers le pays. Nous connectons les principales villes gabonaises avec un service 
            fiable et moderne.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div>
                <h3 class="font-semibold mb-2">Nos destinations</h3>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Libreville</li>
                    <li>Franceville</li>
                    <li>Moanda</li>
                    <li>Owendo</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Nos services</h3>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Transport de passagers</li>
                    <li>Transport de marchandises</li>
                    <li>Réservation en ligne</li>
                    <li>Suivi GPS en temps réel</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

