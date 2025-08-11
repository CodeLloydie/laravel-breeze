<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver('google')->user();

            $user = User::where('google_id', $google_user->getId())
                ->orWhere('email', $google_user->getEmail())
                ->first();

            if (!$user) {
                // First time login - create new user
                $new_user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId()
                ]);

                Auth::login($new_user);
                return redirect()->intended('dashboard');
            } else {
                // Existing user - log them in
                Auth::login($user);
                return redirect()->intended('dashboard'); // Added this line!
            }
        } catch (\Throwable $th) {
            return redirect('/login')->withErrors(['msg' => 'Google login failed.']);
        }
    }
}
