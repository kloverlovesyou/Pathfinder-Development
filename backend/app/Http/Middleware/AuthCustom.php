<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Organization;

class AuthCustom
{
    ublic function handle(Request $request, Closure $next)
{
    // 🚨 Debug: Log all headers for testing
    \Log::info('Headers:', $request->headers->all());

    if (!$request->bearerToken() && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
    }

    $token = $request->bearerToken();

    \Log::info('Bearer Token:', [$token]);

    $user = \App\Models\Applicant::where('api_token', $token)->first()
          ?? \App\Models\Organization::where('api_token', $token)->first();

    if (!$user) {
        \Log::warning('Unauthorized - token not found', [$token]);
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->setUserResolver(fn() => $user);
    return $next($request);
}
}