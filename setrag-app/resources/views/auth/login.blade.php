@extends('layouts.app')

@section('title', 'Connexion - SETRAG')

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
        <h1 class="text-2xl font-bold mb-6">Connexion</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                       required autofocus>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                <input type="password" name="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                       required>
            </div>

            <button type="submit" class="btn-primary w-full mb-4">Se connecter</button>
        </form>

        <p class="text-center text-gray-600">
            Pas encore de compte ? 
            <a href="{{ route('register') }}" class="text-setrag-primary hover:underline">Créer un compte</a>
        </p>
    </div>
</div>
@endsection

