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
    // ✅ List of paths to skip
    $publicPaths = [
        'api/trainings/total',
        'api/trainings/counts-partial',
        'api/careers/total',
        'api/careers/counts-partial',
    ];

    if (in_array($request->path(), $publicPaths)) {
        return $next($request);
    }

    $header = $request->header('Authorization');

    if (!$header || strpos($header, 'Bearer ') !== 0) {
        return response()->json(['message' => 'Unauthorized - No Bearer token found in headers'], 401);
    }

    $token = trim(str_replace('Bearer ', '', $header));

    $user = Organization::where('api_token', $token)->first()
         ?? Applicant::where('api_token', $token)->first();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
    }

    $request->setUserResolver(fn() => $user);

    return $next($request);
}

}