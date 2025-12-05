<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;

class JwtService
{
    private string $secret;
    private int $expirationMinutes;

    public function __construct()
    {
        $this->secret = config('app.jwt_secret', env('JWT_SECRET', 'dev-secret-change-me'));
        $this->expirationMinutes = (int) config('app.jwt_expiration_minutes', env('JWT_EXPIRATION_MINUTES', 60));
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'sub' => (string) $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'exp' => now()->addMinutes($this->expirationMinutes)->timestamp,
            'iat' => now()->timestamp,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function validateCredentials(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}

