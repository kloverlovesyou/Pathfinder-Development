<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Support\PasswordCompatibility;

class AdminController extends Controller
{
    use PasswordCompatibility;
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
     * Admin Login - Step 1: Verify credentials and send OTP
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
        if (!$this->passwordMatches($request->password, $admin->password, $admin)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Generate 6-digit OTP
        $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes

        // Store OTP
        $admin->login_otp = $otp;
        $admin->login_otp_expires_at = $expiresAt;
        $admin->save();

        // Send OTP email
        try {
            $brevoService = app(\App\Services\BrevoEmailService::class);
            $brevoService->sendLoginOTP(
                $admin->emailAddress,
                $admin->name,
                $otp
            );

            Log::info('Admin login OTP sent', [
                'email' => $admin->emailAddress,
                'admin_id' => $admin->adminID,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin login OTP email', [
                'email' => $admin->emailAddress,
                'error' => $e->getMessage(),
            ]);
            // Still return success for security (don't reveal if email failed)
        }

        return response()->json([
            'message' => 'OTP has been sent to your email address. Please check your inbox.',
            'otp_required' => true,
            'otp_expires_in' => 10, // minutes
        ], 200);
    }

    /**
     * Admin Login - Step 2: Verify OTP and complete login
     */
    public function verifyOTP(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'emailAddress' => 'required|email',
            'password' => 'required',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
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

        // Verify password again
        if (!$this->passwordMatches($request->password, $admin->password, $admin)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Verify OTP
        if (!$admin->login_otp || $admin->login_otp !== $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP. Please check your email and try again.'
            ], 401);
        }

        // Check if OTP has expired
        if ($admin->login_otp_expires_at && Carbon::now()->gt($admin->login_otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new OTP.'
            ], 401);
        }

        // Clear OTP after successful verification
        $admin->login_otp = null;
        $admin->login_otp_expires_at = null;
        $admin->save();

        // Create token (Laravel Sanctum)
        $token = $admin->createToken('adminToken')->plainTextToken;

        Log::info('Admin login successful with OTP', [
            'email' => $admin->emailAddress,
            'admin_id' => $admin->adminID,
        ]);

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
        if (!$this->passwordMatches($request->currentPassword, $admin->password, $admin)) {
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
