<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate user and create API token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $throttleKey = 'api-login:' . $request->ip();
        $maxAttempts = 5;
        $decayMinutes = 15;

        // Check for too many login attempts
        // if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
        //     $seconds = RateLimiter::availableIn($throttleKey);

        //     return response()->json([
        //         'status' => 'error',
        //         'message' => "Too many login attempts. Please try again in {$seconds} seconds."
        //     ], 429);
        // }

        // Find user by email
        $user = User::where('email', $request->email)->first();


        // Check if user exists and account is active
        if (!$user) {
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            return response()->json([
                'status' => 'error',
                'message' => 'These credentials do not match our records.'
            ], 401);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been locked. Please contact support.'
            ], 423);
        }

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Clear login attempts on successful login
        RateLimiter::clear($throttleKey);

        // Create new API token
        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60, // 30 days in minutes
                'user' => new UserResource($user)
            ]
        ]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                'is_active' => true
            ]);

            // Create API token for the new user
            $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 30 * 24 * 60, // 30 days in minutes
                    'user' => new UserResource($user)
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);

        } catch (\Exception $e) {
            \Log::error('Logout failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to log out. Please try again.'
            ], 500);
        }
    }
}
