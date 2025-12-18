<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Training;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendVerificationEmailJob;
use Carbon\Carbon;

class OrganizationController extends Controller
{

    // ----------------------
    // List approved organizations with careers & trainings
    // ----------------------
    public function index()
    {
        $organizations = Organization::with(['careers', 'trainings'])
            ->where('status', 'approved')
            ->get();

        return response()->json($organizations);
    }

    // ----------------------
    // Show single organization with related careers & trainings
    // ----------------------
    public function show($organizationID)
    {
        $organization = Organization::with(['careers', 'trainings'])
            ->where('organizationID', $organizationID)
            ->where('status', 'approved')
            ->firstOrFail();

        return response()->json($organization);
    }

    // ----------------------
    // Register new organization
    // ----------------------
    public function o_register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
        'name'        => 'required|string|max:255',
        'location'    => 'nullable|string|max:255',
        'websiteURL'  => 'nullable|string|max:255',
        'emailAddress'=> [
            'required',
            'email',
            'max:255',
            \Illuminate\Validation\Rule::unique('organization', 'emailAddress'),
            function ($attribute, $value, $fail) {
                if (\App\Models\Applicant::where('emailAddress', $value)->exists()) {
                    $fail('The email has already been taken by an applicant.');
                }
            },
        ],
        'phoneNumber' => 'nullable|string|max:20',
         'password' => [
        'required',
        'string',
        'min:8',
        'regex:/[a-z]/',      // lowercase
        'regex:/[A-Z]/',      // uppercase
        'regex:/[0-9]/',      // digit
        'regex:/[@$!%*#?&^()_\-+=\[\]{};:\'",.<>\/\\|`~]/', // special
    ],
        'logoPath'    => 'nullable|string|max:500',
        'logo_directory' => 'nullable|string|max:500',
        'registrationRequirements' => 'required|string|max:500',
    ]);

        if ($validator->fails()) {
            \Log::error('Organization registration validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate verification token
        $verificationToken = Str::random(64);

        $organization = Organization::create([
            'name'        => $request->input('name'),
            'location'    => $request->input('location'),
            'websiteURL'  => $request->input('websiteURL'),
            'emailAddress'=> $request->input('emailAddress'),
            'phoneNumber' => $request->input('phoneNumber'),
            'password'    => Hash::make($request->input('password')),
            'logo_directory' => $request->input('logoPath') ?? $request->input('logo_directory') ?? $request->input('Logo_directory') ?? null,
            'registrationRequirements' => $request->input('registrationRequirements') ?? $request->input('RegistrationRequirements'),
            'adminID'     => $request->input('adminID'),
            'status'      => 'pending',
            'email_verification_token' => $verificationToken,
            'email_verified_at' => null,
        ]);

        // Prepare verification URL and user info
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=organization');
        $userName = $request->input('name');
        $userEmail = $request->input('emailAddress');
        
        // Return response immediately (don't wait for email)
        $response = response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
            'organization' => $organization,
            'email_sent' => true, // Assume it will be sent
            'verification_url' => $verificationUrl, // Always include for manual verification
            'verification_token' => $verificationToken,
        ], 201);
        
        // Send email asynchronously (after response is sent)
        // Use fastcgi_finish_request() if available to send response immediately
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        
        // Send email in background (won't block response)
        try {
            \Log::info('Queueing verification email (Organization)', [
                'email' => $userEmail,
            ]);

            SendVerificationEmailJob::dispatch(
                $userEmail,
                $verificationUrl,
                $userName,
                'organization'
            )->afterResponse();
        } catch (\Throwable $e) {
            \Log::error('Failed to dispatch verification email job', [
                'email' => $userEmail,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $response;
    }


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

        // Check if email is verified
        if (!$organization->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
                'email_verified' => false,
            ], 403);
        }

        // Allow login even if status is pending (not yet verified by admin)
        // Organizations can now login and see their unverified status in the UI

        if ($organization->status === 'rejected') {
            return response()->json([
                'message' => 'Your registration was rejected.',
                'reason' => $organization->rejectionReason // <--- include rejection reason
            ], 403);
        }

        if (!$organization->api_token) {
            $organization->api_token = Str::random(60);
            $organization->save();
        }

        return response()->json([
            'message' => 'Login successful',
            'organization' => $organization,
            'token' => $organization->api_token,
        ]);
    }

    // ----------------------
    // Approve organization
    // ----------------------
    public function approve($id)
    {
        $org = Organization::findOrFail($id);

        $org->status = 'approved';
        $org->statusDate = now();
        $org->save();

        try {
            // Send approval email
            app(\App\Services\BrevoEmailService::class)
                ->sendOrganizationApprovedEmail(
                    $org->emailAddress,
                    $org->name
                );

        } catch (\Throwable $e) {
            \Log::error('Failed to send organization approval email', [
                'org_id' => $org->organizationID,
                'email' => $org->emailAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Organization approved and email sent.',
            'statusDate' => $org->statusDate
        ]);
    }

    // ----------------------
    // Reject organization
    // ----------------------
    public function reject($id, Request $request)
    {
        $org = Organization::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $org->status = 'rejected';
        $org->rejectionReason = $validated['reason'];
        $org->statusDate = now();
        $org->save();

        try {
            // Send rejection email
            app(\App\Services\BrevoEmailService::class)
                ->sendOrganizationRejectedEmail(
                    $org->emailAddress,
                    $org->name,
                    $validated['reason']
                );

        } catch (\Throwable $e) {
            \Log::error('Failed to send organization rejection email', [
                'org_id' => $org->organizationID,
                'email' => $org->emailAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Organization rejected successfully',
            'rejectionReason' => $org->rejectionReason,
            'statusDate' => $org->statusDate
        ]);
    }

    // ----------------------
    // List pending organizations
    // ----------------------
    public function pending()
    {
        $organizations = Organization::where('status', 'pending')->get();
        return response()->json($organizations);
    }

    // ----------------------
    // Delete organization along with all related data
    // ----------------------
    public function destroyById($id)
    {
        $organization = Organization::with(['trainings', 'careers'])->find($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        DB::transaction(function () use ($organization) {
            // Delete trainings & related data
            foreach ($organization->trainings as $training) {
                $training->registrations()->delete();
                if (method_exists($training, 'attendances')) {
                    $training->attendances()->delete();
                }
                $training->tags()->detach();
                $training->delete();
            }

            // Delete careers
            $organization->careers()->delete();

            // Delete organization
            $organization->delete();
        });

        return response()->json(['message' => 'Organization and all related data deleted successfully']);
    }

    public function rejected()
    {
        $organization = Organization::where('status', 'rejected')->get();
        return response()->json($organization);
    }

    public function getOrgDetails(Request $request)
    {
        // Get the currently authenticated organization
        $organization = $request->user(); // if using Sanctum
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json([
            'organization' => [
                'organizationName' => $organization->name,
                'location'         => $organization->location,
                'websiteURL'       => $organization->websiteURL,
                'emailAddress'     => $organization->emailAddress,
                'phoneNumber'      => $organization->phoneNumber ?? '',
                'logo_directory'   => $organization->logo_directory ?? null,
                'password'         => '' // never send hashed password
            ]
        ]);
    }

    // ----------------------
    // Request OTP for profile update
    // ----------------------
    public function requestProfileUpdateOTP(Request $request)
    {
        $organization = $request->user();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Verify password
        $validated = $request->validate([
            'confirmPassword' => 'required|string',
        ]);

        if (!Hash::check($validated['confirmPassword'], $organization->password)) {
            return response()->json(['message' => 'Invalid password. Please check your password and try again.'], 401);
        }

        // Generate 6-digit OTP
        $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes

        // Store OTP (reusing password_change_otp field for profile updates)
        $organization->password_change_otp = $otp;
        $organization->password_change_otp_expires_at = $expiresAt;
        $organization->save();

        // Send OTP email
        try {
            $brevoService = app(\App\Services\BrevoEmailService::class);
            $brevoService->sendPasswordChangeOTP(
                $organization->emailAddress,
                $organization->name,
                $otp
            );

            \Log::info('Profile update OTP sent', [
                'email' => $organization->emailAddress,
                'organization_id' => $organization->organizationID,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send profile update OTP email', [
                'email' => $organization->emailAddress,
                'error' => $e->getMessage(),
            ]);
            // Still return success for security (don't reveal if email failed)
        }

        return response()->json([
            'message' => 'OTP has been sent to your email address. Please check your inbox.',
            'otp_expires_in' => 10, // minutes
        ]);
    }

    // ----------------------
    // Update organization profile
    // ----------------------
    public function update(Request $request)
    {
        $organization = $request->user();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Validate input
        $validated = $request->validate([
            'organizationName' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'websiteURL' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|string|max:20',
            'confirmPassword' => 'required|string',
            'otp' => 'required|string|size:6',
            'logoPath' => 'nullable|string|max:500',
            'logo_directory' => 'nullable|string|max:500',
        ]);

        // Verify password
        if (!Hash::check($validated['confirmPassword'], $organization->password)) {
            return response()->json(['message' => 'Invalid password. Please check your password and try again.'], 401);
        }

        // Verify OTP
        if (!$organization->password_change_otp || $organization->password_change_otp !== $validated['otp']) {
            return response()->json(['message' => 'Invalid or expired OTP. Please request a new one.'], 400);
        }

        // Check if OTP has expired
        if ($organization->password_change_otp_expires_at && Carbon::now()->gt($organization->password_change_otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
        }

        // Update organization fields
        if (isset($validated['organizationName'])) {
            $organization->name = $validated['organizationName'];
        }
        if (isset($validated['location'])) {
            $organization->location = $validated['location'];
        }
        if (isset($validated['websiteURL'])) {
            $organization->websiteURL = $validated['websiteURL'];
        }
        if (isset($validated['phoneNumber'])) {
            $organization->phoneNumber = $validated['phoneNumber'];
        }
        
        // Update logo if provided
        if (isset($validated['logoPath']) || isset($validated['logo_directory'])) {
            $organization->logo_directory = $validated['logoPath'] ?? $validated['logo_directory'] ?? null;
        }

        // Clear OTP after successful update
        $organization->password_change_otp = null;
        $organization->password_change_otp_expires_at = null;
        $organization->save();

        \Log::info('Profile updated successfully', [
            'email' => $organization->emailAddress,
            'organization_id' => $organization->organizationID,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'organization' => [
                'organizationName' => $organization->name,
                'location' => $organization->location,
                'websiteURL' => $organization->websiteURL,
                'phoneNumber' => $organization->phoneNumber ?? '',
                'logo_directory' => $organization->logo_directory,
            ]
        ]);
    }

    // ----------------------
    // Request OTP for password change
    // ----------------------
    public function requestPasswordChangeOTP(Request $request)
    {
        $organization = $request->user();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Verify current password
        $validated = $request->validate([
            'currentPassword' => 'required|string',
        ]);

        if (!Hash::check($validated['currentPassword'], $organization->password)) {
            return response()->json(['message' => 'Invalid current password.'], 401);
        }

        // Generate 6-digit OTP
        $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes

        // Store OTP
        $organization->password_change_otp = $otp;
        $organization->password_change_otp_expires_at = $expiresAt;
        $organization->save();

        // Send OTP email
        try {
            $brevoService = app(\App\Services\BrevoEmailService::class);
            $brevoService->sendPasswordChangeOTP(
                $organization->emailAddress,
                $organization->name,
                $otp
            );

            \Log::info('Password change OTP sent', [
                'email' => $organization->emailAddress,
                'organization_id' => $organization->organizationID,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send password change OTP email', [
                'email' => $organization->emailAddress,
                'error' => $e->getMessage(),
            ]);
            // Still return success for security (don't reveal if email failed)
        }

        return response()->json([
            'message' => 'OTP has been sent to your email address. Please check your inbox.',
            'otp_expires_in' => 10, // minutes
        ]);
    }

    // ----------------------
    // Change password with OTP verification
    // ----------------------
    public function changePassword(Request $request)
    {
        $organization = $request->user();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
            'otp' => 'required|string|size:6',
        ]);

        // Verify current password
        if (!Hash::check($validated['currentPassword'], $organization->password)) {
            return response()->json(['message' => 'Invalid current password.'], 401);
        }

        // Verify OTP
        if (!$organization->password_change_otp || $organization->password_change_otp !== $validated['otp']) {
            return response()->json(['message' => 'Invalid or expired OTP. Please request a new one.'], 400);
        }

        // Check if OTP has expired
        if ($organization->password_change_otp_expires_at && Carbon::now()->gt($organization->password_change_otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
        }

        // Check if new password is different from current password
        if (Hash::check($validated['newPassword'], $organization->password)) {
            return response()->json(['message' => 'New password must be different from your current password.'], 400);
        }

        // Update password
        $organization->password = Hash::make($validated['newPassword']);
        $organization->password_change_otp = null;
        $organization->password_change_otp_expires_at = null;
        $organization->save();

        \Log::info('Password changed successfully', [
            'email' => $organization->emailAddress,
            'organization_id' => $organization->organizationID,
        ]);

        return response()->json([
            'message' => 'Password changed successfully!',
        ]);
    }
}
