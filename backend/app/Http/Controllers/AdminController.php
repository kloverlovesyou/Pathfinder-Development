<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Admin Login
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'emailAddress' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid login credentials.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find admin by email
        $admin = Admin::where('EmailAddress', $request->emailAddress)->first();

        if (!$admin) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Verify password
        if (!Hash::check($request->password, $admin->Password)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Create token (Laravel Sanctum)
        $token = $admin->createToken('adminToken')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful.',
            'admin' => [
                'adminID' => $admin->adminID,
                'name' => $admin->name,
                'location' => $admin->location,
                'websiteURL' => $admin->websiteURL,
                'emailAddress' => $admin->emailAddress,
                'role' => 'admin'
            ],
            'token' => $token
        ], 200);
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}