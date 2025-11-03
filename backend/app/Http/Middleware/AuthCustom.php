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
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized - No Bearer token found in headers'], 401);
        }

        $token = trim(str_replace('Bearer ', '', $header));

        // ✅ Look for token in Organization or Applicant
        $user = Organization::whereRaw('BINARY api_token = ?', [$token])->first()
              ?? Applicant::whereRaw('BINARY api_token = ?', [$token])->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }

        // ✅ Attach authenticated user for the request
        $request->setUserResolver(fn() => $user);

        // ❌ REMOVE THIS LINE:
        // auth()->setUser($user);

        return $next($request);
    }
}