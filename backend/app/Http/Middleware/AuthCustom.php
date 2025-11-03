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
        // 🧾 Log all headers for debugging (you'll see this in Railway logs)
        \Log::info('🧾 FULL HEADER DUMP:', $request->headers->all());

        // ✅ Try to get the token from "Authorization: Bearer"
        $token = $request->bearerToken();

        // ✅ Fallback: support X-Auth-Token header (if frontend sends that)
        if (!$token && $request->hasHeader('X-Auth-Token')) {
            $token = $request->header('X-Auth-Token');
        }

        // 🧠 Log the token info to confirm what was received
        \Log::info('🔹 Incoming Token Check:', [
            'Authorization' => $request->header('Authorization'),
            'X-Auth-Token' => $request->header('X-Auth-Token'),
            'TokenUsed' => $token,
        ]);

        // ✅ Try to match token from Applicant or Organization
        $user = Applicant::whereRaw('BINARY api_token = ?', [trim($token)])->first()
        ?? Organization::whereRaw('BINARY api_token = ?', [trim($token)])->first();

        if (!$user) {
            \Log::warning('🚫 Unauthorized', ['token' => $token]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Authenticated successfully
        \Log::info('✅ Authenticated user', [
            'type' => $user instanceof Organization ? 'Organization' : 'Applicant',
            'id' => $user->organizationID ?? $user->applicantID,
        ]);

        $request->setUserResolver(fn() => $user);
        auth()->setUser($user); // ✅ Add this line
        return $next($request);
    }
}