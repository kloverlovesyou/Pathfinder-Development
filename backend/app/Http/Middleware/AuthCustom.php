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
        // ✅ Extract token safely using Laravel’s helper
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No Bearer token'], 401);
        }

        // ✅ Search for either an Organization or Applicant by token
        $user = \App\Models\Organization::whereRaw('BINARY api_token = ?', [$token])->first()
            ?? \App\Models\Applicant::whereRaw('BINARY api_token = ?', [$token])->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }

        // ✅ Attach user to the request and Auth system
        $request->setUserResolver(fn() => $user);
        auth()->setUser($user);

        return $next($request);
    }
}