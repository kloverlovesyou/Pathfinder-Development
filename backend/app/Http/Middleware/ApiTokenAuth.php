<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        // ✅ Define the message and key for verification
        $expectedToken = hash_hmac('sha256', 'pathfinder-api', env('APP_KEY'));

        // ✅ Compare the provided token with expected one
        if (!hash_equals($expectedToken, $token)) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}