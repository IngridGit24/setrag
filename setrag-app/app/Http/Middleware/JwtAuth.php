<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuth
{
    public function __construct(
        private JwtService $jwtService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Remove "Bearer " prefix if present
        $token = str_replace('Bearer ', '', $token);

        $decoded = $this->jwtService->decodeToken($token);

        if (!$decoded || !isset($decoded['sub'])) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $user = User::find($decoded['sub']);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
