<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('auth')->with('error', 'Veuillez vous connecter');
        }

        return view('profile', compact('user'));
    }
}

