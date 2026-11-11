<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $inputUsername = strtolower(trim($request->input('username')));

        // Pemetaan alias fleksibel: mendukung username pendek maupun format divisi lama di database
        $aliasMap = [
            'admin' => 'admin.utama',
            'amc' => 'amc.divisi',
            'pk' => 'arff.divisi',
            'arff' => 'arff.divisi',
            'pk.divisi' => 'arff.divisi',
            'safety' => 'smsohs.divisi',
            'sms' => 'smsohs.divisi',
            'smsohs' => 'smsohs.divisi',
            'sms.divisi' => 'smsohs.divisi',
            'security' => 'security.divisi',
            'avsec' => 'security.divisi',
            'avsec.divisi' => 'security.divisi',
            // Pemetaan sebaliknya jika database menggunakan username pendek
            'admin.utama' => 'admin',
            'amc.divisi' => 'amc',
            'arff.divisi' => 'pk',
            'smsohs.divisi' => 'safety',
            'security.divisi' => 'security',
        ];

        // 1. Cek langsung kecocokan username di database
        $matchedUser = User::where('username', $inputUsername)->first();

        // 2. Jika belum ditemukan, periksa melalui tabel alias
        if (! $matchedUser && isset($aliasMap[$inputUsername])) {
            $matchedUser = User::where('username', $aliasMap[$inputUsername])->first();
        }

        // 3. Fallback pencocokan awalan username (cth: amc -> amc.divisi)
        if (! $matchedUser) {
            $short = explode('.', $inputUsername)[0];
            $matchedUser = User::where('username', 'LIKE', $short.'%')->first();
        }

        $targetUsername = $matchedUser ? $matchedUser->username : $inputUsername;

        $credentials = [
            'username' => $targetUsername,
            'password' => $request->input('password'),
        ];

        $throttleKey = Str::transliterate(Str::lower($targetUsername).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam $seconds detik.",
            ])->onlyInput('username');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();
            if (! $user->aktif) {
                Auth::logout();

                return back()->withErrors([
                    'username' => 'Akun Anda sedang dinonaktifkan. Hubungi admin.',
                ]);
            }

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('user.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
