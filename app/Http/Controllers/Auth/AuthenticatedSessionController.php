<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'Your account is temporarily locked due to multiple failed login attempts. Please try again later.',
            ]);
        }

        // Rate limiting
        $key = 'login_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        try {
            $request->authenticate();

            if ($user) {
                $user->resetFailedLogins();
                RateLimiter::clear($key);
            }

            $request->session()->regenerate();

            // Redirect based on role
            if (auth()->user()->isAdmin()) {
                return redirect()->intended(route('dashboard', absolute: false));
            }

            return redirect()->intended(RouteServiceProvider::HOME);

        } catch (ValidationException $e) {
            RateLimiter::hit($key, 300); // 5 minutes

            if ($user) {
                $user->incrementFailedLogins();
            }

            throw $e;
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
