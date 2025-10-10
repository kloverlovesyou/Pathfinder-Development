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

    $user = Applicant::where('api_token', $token)->first()
         ?? Organization::where('api_token', $token)->first();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // attach user directly
    $request->authUser = $user;

    return $next($request);
}
}