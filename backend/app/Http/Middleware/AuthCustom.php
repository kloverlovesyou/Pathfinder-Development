<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;

class AuthCustom
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('🧾 FULL HEADER DUMP:', $request->headers->all());
        // ✅ Try different header names
        $token = $request->bearerToken();

        if (!$token && $request->hasHeader('X-Auth-Token')) {
            $token = $request->header('X-Auth-Token');
        }

        // 🧠 Log for debugging
        \Log::info('🔹 Incoming Token Check:', [
            'Authorization' => $request->header('Authorization'),
            'X-Auth-Token' => $request->header('X-Auth-Token'),
            'TokenUsed' => $token,
        ]);

        // ✅ Check token in both tables
        $user = \App\Models\Applicant::where('api_token', $token)->first()
            ?? \App\Models\Organization::where('api_token', $token)->first();

        if (!$user) {
            \Log::warning('🚫 Unauthorized', ['token' => $token]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Authenticated
        \Log::info('✅ Authenticated user', [
            'type' => $user instanceof \App\Models\Organization ? 'Organization' : 'Applicant',
            'id' => $user->organizationID ?? $user->applicantID,
        ]);

        $request->setUserResolver(fn() => $user);
        return $next($request);
    }
}