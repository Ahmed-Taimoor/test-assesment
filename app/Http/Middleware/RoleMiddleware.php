<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect('/login');
        }

        if (auth()->user()->role !== $role) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden: Unauthorized access.'], 403)
                : abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
