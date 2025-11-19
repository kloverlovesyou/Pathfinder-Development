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
    // Log POST requests to /applications
    if ($request->is('api/applications') && $request->isMethod('POST')) {
        Log::info('AuthCustom: POST /applications request received', [
            'url' => $request->fullUrl(),
            'has_file' => $request->hasFile('requirement_directory'),
            'all_files' => array_keys($request->allFiles()),
        ]);
    }
    
    // ✅ Public endpoints that do NOT require authentication
    $publicPaths = [
        'api/trainings/total',
        'api/trainings/counts-partial',
        'api/careers/total',
        'api/careers/counts-partial',
    ];

    if (in_array($request->path(), $publicPaths)) {
        return $next($request);
    }

    // ----------------------------------------------------
    // ✅ MULTI-SOURCE TOKEN EXTRACTION
    // ----------------------------------------------------

    // Method 1: Laravel's built-in Bearer extraction
    $token = $request->bearerToken();

    // Method 2: Raw Authorization header
    if (!$token) {
        $authHeader = $request->header('Authorization');
        if ($authHeader) {
            $token = preg_replace('/^Bearer\s+/i', '', trim($authHeader));
        }
    }

    // Method 3: Query parameter fallback
    if (!$token) {
        $token = $request->query('token');
    }

    // Clean token
    $token = $token ? trim($token) : null;

    // ----------------------------------------------------
    // Handle missing token
    // ----------------------------------------------------
    if (!$token || empty($token)) {
        Log::warning('AuthCustom: No token provided', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'authorization_header' => $request->header('Authorization'),
        ]);
        return response()->json(['message' => 'Unauthorized - No token provided'], 401);
    }

    // ----------------------------------------------------
    // Authenticate token (Organization OR Applicant)
    // ----------------------------------------------------
    $user = Applicant::where('api_token', $token)->first()
         ?? Organization::where('api_token', $token)->first();

    if (!$user) {
        Log::warning('AuthCustom: Invalid token', [
            'url' => $request->fullUrl(),
            'token_prefix' => substr($token, 0, 10) . '...',
        ]);
        return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
    }

    // Attach the user so you can access $request->user()
    $request->setUserResolver(fn() => $user);

    return $next($request);
}

}