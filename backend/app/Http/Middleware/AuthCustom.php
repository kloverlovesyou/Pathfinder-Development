<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Organization;

class AuthCustom
{
    public function handle(Request $request, Closure $next)
    {
        // 🩹 Fix for missing Authorization header in some hosts (like Railway)
        if (!$request->bearerToken() && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
        }

        $token = $request->bearerToken();

        $user = \App\Models\Applicant::where('api_token', $token)->first()
            ?? \App\Models\Organization::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}