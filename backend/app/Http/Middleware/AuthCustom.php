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
        // Try multiple methods to get the token
        $token = $request->bearerToken();
        
        // If bearerToken() doesn't work, try getting from Authorization header directly
        if (!$token) {
            $authHeader = $request->header('Authorization');
            if ($authHeader) {
                // Remove 'Bearer ' prefix if present
                $token = preg_replace('/^Bearer\s+/i', '', trim($authHeader));
            }
        }
        
        // Also try query parameter as fallback (for debugging)
        if (!$token) {
            $token = $request->query('token');
        }
        
        // Trim any whitespace
        $token = $token ? trim($token) : null;

        if (!$token || empty($token)) {
            Log::warning('AuthCustom: No token provided', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'authorization_header' => $request->header('Authorization'),
            ]);
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        // Try to match token to either Applicant or Organization
        $user = Applicant::where('api_token', $token)->first();
        
        if (!$user) {
            $user = Organization::where('api_token', $token)->first();
        }

        if (!$user) {
            Log::warning('AuthCustom: Invalid token', [
                'url' => $request->fullUrl(),
                'token_prefix' => substr($token, 0, 10) . '...',
            ]);
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }

        // ✅ Attach authenticated user for $request->user()
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}