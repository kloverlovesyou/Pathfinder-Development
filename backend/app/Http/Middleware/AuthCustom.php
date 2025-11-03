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
        // ✅ Try multiple sources for the Authorization token
        $header = $request->header('Authorization')
                ?? $request->server('HTTP_AUTHORIZATION')
                ?? $request->server('REDIRECT_HTTP_AUTHORIZATION');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized - No Bearer token found in headers'], 401);
        }

        $token = trim(str_replace('Bearer ', '', $header));

        // 🔍 Check both Organization and Applicant
        $user = Organization::whereRaw('BINARY api_token = ?', [$token])->first()
              ?? Applicant::whereRaw('BINARY api_token = ?', [$token])->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }

        // ✅ Attach authenticated user
        $request->setUserResolver(fn() => $user);
        auth()->setUser($user);

        return $next($request);
    }
}