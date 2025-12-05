@extends('layouts.app')

@section('title', 'Inscription - SETRAG')

@section('content')
<div class="max-w-md mx-auto">
    <div class="mb-4">
        <a href="{{ route('home') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à l'accueil</span>
        </a>
    </div>
    <div class="card">
        <h1 class="text-2xl font-bold mb-6">Créer un compte</h1>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                       required autofocus>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                       required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                <input type="password" name="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                       required>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                       required>
            </div>

            <button type="submit" class="btn-primary w-full mb-4">Créer mon compte</button>
        </form>

        <p class="text-center text-gray-600">
            Déjà un compte ? 
            <a href="{{ route('auth') }}" class="text-setrag-primary hover:underline">Se connecter</a>
        </p>
    </div>
</div>
@endsection

