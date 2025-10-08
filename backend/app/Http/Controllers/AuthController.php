<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Organization;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    // ✅ Applicant Registration
    public function a_register(Request $request)
    {
        $validated = $request->validate([
            'firstName'    => 'required|string|max:255',
            'lastName'     => 'required|string|max:255',
            'address'      => 'required|string|max:255',
            'emailAddress' => 'required|email|unique:applicant,emailAddress',
            'phoneNumber'  => 'required|string|max:20',
            'password'     => 'required|string|min:8',
        ]);

        $applicant = Applicant::create([
            'firstName'    => $validated['firstName'],
            'lastName'     => $validated['lastName'],
            'address'      => $validated['address'],
            'emailAddress' => $validated['emailAddress'],
            'phoneNumber'  => $validated['phoneNumber'],
            'password'     => Hash::make($validated['password']),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Applicant registered successfully',
            'data'    => $applicant,
        ]);
    }

    // ✅ Organization Registration
    public function o_register(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'location'     => 'nullable|string|max:255',
            'websiteURL'   => 'nullable|string|max:255',
            'emailAddress' => 'required|email|unique:organization,emailAddress',
            'phoneNumber'  => 'required|string|max:20',
            'password'     => 'required|string|min:8',
        ]);

        $organization = Organization::create([
            'name'         => $validated['name'],
            'location'     => $validated['location'] ?? '',
            'websiteURL'   => $validated['websiteURL'] ?? '',
            'emailAddress' => $validated['emailAddress'],
            'phoneNumber'  => $validated['phoneNumber'],
            'password'     => Hash::make($validated['password']),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Organization registered successfully',
            'data'    => $organization,
        ]);
    }

        public function login(Request $request)
{
    $request->validate([
        'emailAddress' => 'required|email',
        'password'     => 'required|string|min:8',
    ]);

    // Check applicant
    $applicant = Applicant::where('emailAddress', $request->emailAddress)->first();
    if ($applicant && Hash::check($request->password, $applicant->password)) {
        // Generate token
        $applicant->api_token = Str::random(60);
        $applicant->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'token'   => $applicant->api_token,
            'user'    => array_merge($applicant->toArray(), ['role' => 'applicant']),
        ]);
    }

    // Check organization
    $organization = Organization::where('emailAddress', $request->emailAddress)->first();
    if ($organization && Hash::check($request->password, $organization->password)) {
        $organization->api_token = Str::random(60);
        $organization->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'token'   => $organization->api_token,
            'user'    => array_merge($organization->toArray(), ['role' => 'organization']),
        ]);
    }

    return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
}
}