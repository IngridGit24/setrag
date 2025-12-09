<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SETRAG - Transport Ferroviaire au Gabon')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Navigation -->
    <nav class="bg-setrag-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <span class="text-2xl">🚂</span>
                        <span class="text-white font-bold text-xl">SETRAG</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Accueil
                    </a>
                    <a href="{{ route('book') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Réserver
                    </a>
                    <a href="{{ route('track') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Suivi
                    </a>
                    <a href="{{ route('shipping') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Colis
                    </a>
                    @if(session('user'))
                        <!-- User Menu Dropdown -->
                        <div class="relative" id="user-menu-container">
                            <button id="user-menu-button" class="flex items-center space-x-2 text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ session('user')['full_name'] ?? session('user')['email'] }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mon profil
                                </a>
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mes réservations
                                </a>
                                @if((session('user')['role'] ?? 'user') === 'admin')
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="{{ route('admin.trips.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Administration
                                    </a>
                                @endif
                                <div class="border-t border-gray-200 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('auth') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Connexion
                        </a>
                    @endif
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button type="button" id="mobile-menu-button" class="text-white hover:text-gray-200 focus:outline-none focus:text-gray-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-setrag-primary-dark">
                <a href="{{ route('home') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                    Accueil
                </a>
                <a href="{{ route('book') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                    Réserver
                </a>
                <a href="{{ route('track') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                    Suivi
                </a>
                <a href="{{ route('shipping') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                    Colis
                </a>
                @if(session('user'))
                    <div class="px-3 py-2 text-white text-sm font-medium border-b border-setrag-primary-dark">
                        {{ session('user')['full_name'] ?? session('user')['email'] }}
                    </div>
                    <a href="{{ route('profile') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                        Mon profil
                    </a>
                    <a href="{{ route('dashboard') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                        Mes réservations
                    </a>
                    @if((session('user')['role'] ?? 'user') === 'admin')
                        <a href="{{ route('admin.trips.index') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                            Administration
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white block w-full text-left px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('auth') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                        Connexion
                    </a>
                @endif
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-setrag-primary text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">🚂</span>
                        <span class="font-bold text-xl">SETRAG</span>
                    </div>
                    <p class="text-gray-200">
                        Votre partenaire de confiance pour le transport ferroviaire au Gabon.
                    </p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-lg mb-4">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('book') }}" class="text-gray-200 hover:text-white transition-colors">Réservation billets</a></li>
                        <li><a href="{{ route('track') }}" class="text-gray-200 hover:text-white transition-colors">Suivi trains</a></li>
                        <li><a href="{{ route('shipping') }}" class="text-gray-200 hover:text-white transition-colors">Expédition colis</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold text-lg mb-4">Contact</h3>
                    <div class="space-y-2 text-gray-200">
                        <p>📧 contact@setrag.ga</p>
                        <p>📞 +241 01 76 00 00</p>
                        <p>📍 Libreville, Gabon</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-600 mt-8 pt-8 text-center text-gray-200">
                <p>&copy; {{ date('Y') }} SETRAG. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <!-- Bouton "Retour en haut" -->
    <button id="goToTopBtn" 
            class="fixed bottom-8 right-8 bg-setrag-primary hover:bg-setrag-primary-dark text-white p-4 rounded-full shadow-lg transition-all duration-300 opacity-0 invisible z-40 group"
            aria-label="Retour en haut">
        <svg class="w-6 h-6 transform group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <script>
        // Gestion du bouton "Retour en haut"
        const goToTopBtn = document.getElementById('goToTopBtn');
        
        // Afficher/masquer le bouton selon la position du scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                goToTopBtn.classList.remove('opacity-0', 'invisible');
                goToTopBtn.classList.add('opacity-100', 'visible');
            } else {
                goToTopBtn.classList.remove('opacity-100', 'visible');
                goToTopBtn.classList.add('opacity-0', 'invisible');
            }
        });
        
        // Scroll vers le haut au clic
        goToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>

