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

    if (!$header || strpos($header, 'Bearer ') !== 0) {
        return response()->json(['message' => 'Unauthorized - No Bearer token found in headers'], 401);
    }

    $token = trim(str_replace('Bearer ', '', $header));

    try {
        $user = Organization::where('api_token', $token)->first()
             ?? Applicant::where('api_token', $token)->first();
    } catch (\Exception $e) {
        return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
    }

    if (!$user) {
        return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
    }

    $request->setUserResolver(fn() => $user);

    return $next($request);
}
}