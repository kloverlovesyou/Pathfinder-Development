<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
    public function o_register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'websiteURL'  => 'nullable|string|max:255',
            'emailAddress'=> 'required|email|unique:organization,emailAddress',
            'password'    => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organization = Organization::create([
            'name' => $request->input('name'),
            'location' => $request->input('location'),
            'websiteURL' => $request->input('websiteURL'),
            'emailAddress' => $request->input('emailAddress'),
            'password' => Hash::make($request->input('password')),
            'adminID' => $request->input('adminID'),
        ]);

        return response()->json([
            'message' => 'Registration successful',
            'organization' => $organization
        ], 201);
    }

    // 👇 Add this login method
    public function login(Request $request)
    {
        $request->validate([
            'emailAddress' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $organization = Organization::where('emailAddress', $request->emailAddress)->first();

        if (!$organization || !Hash::check($request->password, $organization->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'organization' => $organization,
        ]);
    }
}