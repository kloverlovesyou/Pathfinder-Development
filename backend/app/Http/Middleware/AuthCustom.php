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
        $token = $request->bearerToken();

        // Try to match token to either Applicant or Organization
        $user = Applicant::where('api_token', $token)->first()
              ?? Organization::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Attach authenticated user for $request->user()
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}