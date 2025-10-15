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

        $user = \App\Models\Applicant::where('api_token', $token)->first()
            ?? \App\Models\Organization::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}