<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function AdminInfo(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'adminID' => $admin->adminID,
            'name' => $admin->name,
            'location' => $admin->location,
            'websiteURL' => $admin->websiteURL,
            'emailAddress' => $admin->emailAddress,
            'role' => 'admin'
        ]);
    }


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
        $admin = Admin::where('emailAddress', $request->emailAddress)->first();

        if (!$admin) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Verify password
        if (!Hash::check($request->password, $admin->password)) {
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

    /**
     * Update Admin Password
     */
    public function update(Request $request)
    {
        $admin = $request->user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'newPassword' => 'nullable|string|min:8',
            'confirmPassword' => 'nullable|same:newPassword',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->currentPassword, $admin->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 401);
        }

        // Update password if new password is provided
        if ($request->newPassword) {
            if ($request->newPassword !== $request->confirmPassword) {
                return response()->json([
                    'message' => 'New password and confirm password do not match.'
                ], 422);
            }

            $admin->password = Hash::make($request->newPassword);
            $admin->save();
        }

        return response()->json([
            'message' => 'Password updated successfully.',
            'admin' => [
                'adminID' => $admin->adminID,
                'name' => $admin->name,
                'location' => $admin->location,
                'websiteURL' => $admin->websiteURL,
                'emailAddress' => $admin->emailAddress,
                'role' => 'admin'
            ]
        ]);
    }
}