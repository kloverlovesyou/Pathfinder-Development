<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Applicant;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken(); // looks for "Authorization: Bearer <token>"

        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Optionally, attach the authenticated user to the request
        $request->merge(['applicant' => $applicant]);

        return $next($request);
    }
}