<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user') || !session('auth_token')) {
            return redirect()->route('auth')->with('error', 'Veuillez vous connecter pour accéder à cette page');
        }

        return $next($request);
    }
}

