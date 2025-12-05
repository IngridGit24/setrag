@extends('layouts.app')

@section('title', 'Expédition de colis - SETRAG')

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
    <h1 class="text-3xl font-bold text-gray-900">Expédition de colis</h1>

    <div class="card">
        <p class="text-gray-600 mb-6">
            Le service d'expédition de colis sera bientôt disponible. 
            Contactez-nous pour plus d'informations.
        </p>

        <div class="bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Contact</h2>
            <div class="space-y-2">
                <p><strong>Email:</strong> contact@setrag.ga</p>
                <p><strong>Téléphone:</strong> +241 01 76 00 00</p>
                <p><strong>Adresse:</strong> Libreville, Gabon</p>
            </div>
        </div>
    </div>
</div>
@endsection

