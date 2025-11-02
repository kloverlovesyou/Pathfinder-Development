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
        // 🧠 Railway sometimes renames or strips Authorization headers
        $authHeader = $request->header('Authorization');

        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // ✅ Restore Authorization header if found
        if ($authHeader && !$request->header('Authorization')) {
            $request->headers->set('Authorization', $authHeader);
        }

        $token = $request->bearerToken();

        Log::info('🔹 [AUTH CHECK]', [
            'header' => $authHeader,
            'token' => $token,
        ]);

        // ✅ Try to find user in either Applicants or Organizations
        $user = Applicant::where('api_token', $token)->first()
            ?? Organization::where('api_token', $token)->first();

        if (!$user) {
            Log::warning('🚫 Unauthorized - token not found or invalid', ['token' => $token]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info('✅ Authenticated', [
            'type' => $user instanceof Organization ? 'Organization' : 'Applicant',
            'id' => $user->organizationID ?? $user->applicantID ?? null,
        ]);

        $request->setUserResolver(fn() => $user);
        return $next($request);
    }
}