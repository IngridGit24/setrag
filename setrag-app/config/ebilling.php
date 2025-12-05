<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EBILLING Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration avec EBILLING (Digitech Africa)
    | Service de paiement en ligne via Mobile Money et Cartes bancaires
    |
    */

    'base_url' => env('EBILLING_BASE_URL', 'https://lab.billing-easy.net'),
    
    'username' => env('EBILLING_USERNAME', ''),
    
    'shared_key' => env('EBILLING_SHARED_KEY', ''),
    
    'callback_url' => env('EBILLING_CALLBACK_URL', env('APP_URL') . '/api/ebilling/callback'),
    
    'redirect_url_success' => env('EBILLING_REDIRECT_URL_SUCCESS', env('APP_URL') . '/payment/success'),
    
    'redirect_url_failure' => env('EBILLING_REDIRECT_URL_FAILURE', env('APP_URL') . '/payment/failed'),
    
    'expiry_period' => env('EBILLING_EXPIRY_PERIOD', 60), // Minutes
    
    'timeout' => env('EBILLING_TIMEOUT', 30), // Secondes
];

