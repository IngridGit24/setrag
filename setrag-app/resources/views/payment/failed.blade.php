@extends('layouts.app')

@section('title', 'Paiement échoué - SETRAG')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('book') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à la recherche</span>
        </a>
    </div>

    <div class="card text-center">
        <div class="text-6xl mb-4">❌</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement échoué</h1>
        <p class="text-gray-600 mb-8">
            Votre paiement n'a pas pu être effectué. Veuillez réessayer ou choisir une autre méthode de paiement.
        </p>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-red-900 mb-2">Raisons possibles :</h2>
            <ul class="text-left text-red-800 space-y-2">
                <li>• Solde insuffisant sur votre compte</li>
                <li>• Transaction annulée</li>
                <li>• Problème de connexion</li>
                <li>• Délai d'expiration dépassé</li>
            </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('book') }}" class="btn-primary">Nouvelle réservation</a>
            <a href="{{ route('dashboard') }}" class="btn-secondary">Mes réservations</a>
        </div>
    </div>
</div>
@endsection

