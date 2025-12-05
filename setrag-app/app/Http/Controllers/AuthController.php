<?php

namespace App\Http\Controllers;

use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct(
        private JwtService $jwtService
    ) {
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = $this->jwtService->validateCredentials(
            $request->email,
            $request->password
        );

        if (!$user) {
            return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();
        }

        // Store token in session
        $token = $this->jwtService->generateToken($user);
        session(['auth_token' => $token, 'user' => $user]);

        return redirect()->intended(route('home'))->with('success', 'Connexion réussie');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'full_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'role' => 'user',
        ]);

        // Auto login after registration
        $token = $this->jwtService->generateToken($user);
        session(['auth_token' => $token, 'user' => $user]);

        return redirect()->route('home')->with('success', 'Compte créé avec succès');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['auth_token', 'user']);
        
        return redirect()->route('home')->with('success', 'Déconnexion réussie');
    }
}

