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
            return response()->json(['message' => 'Unauthorized - No Bearer token'], 401);
        }

        $token = trim(str_replace('Bearer ', '', $header));

        // 🔍 Try to find either an Organization or Applicant with matching token
        $user = Organization::whereRaw('BINARY api_token = ?', [$token])->first()
              ?? Applicant::whereRaw('BINARY api_token = ?', [$token])->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }

        // ✅ Attach authenticated user (organization or applicant)
        $request->setUserResolver(fn() => $user);
        auth()->setUser($user);

        return $next($request);
    }
}