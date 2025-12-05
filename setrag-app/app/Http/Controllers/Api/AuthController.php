<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        private JwtService $jwtService
    ) {
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'full_name' => 'required|string|max:255',
            'role' => 'sometimes|string|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'role' => $request->role ?? 'user',
        ]);

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
        ], 201);
    }

    public function token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $this->jwtService->validateCredentials(
            $request->username,
            $request->password
        );

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 400);
        }

        $token = $this->jwtService->generateToken($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 60 * 60,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
        ]);
    }
}
