<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;

class RequireVerifiedOrganization
{
    /**
     * Handle an incoming request.
     * Block unverified organizations from performing actions (POST, PUT, DELETE, PATCH)
     * Allow GET requests for viewing data
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Only check if user is an organization
        if ($user instanceof Organization) {
            $status = strtolower($user->status ?? '');
            $isVerified = in_array($status, ['approved', 'verified']);

            // Block write operations (POST, PUT, DELETE, PATCH) for unverified organizations
            if (!$isVerified && in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
                return response()->json([
                    'message' => 'Your organization account is not yet verified by the admin. Please wait for admin approval before performing this action.',
                    'verified' => false,
                ], 403);
            }
        }

        return $next($request);
    }
}

