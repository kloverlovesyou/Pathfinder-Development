<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\SendVerificationEmailJob;
class ApplicantController extends Controller
{

public function a_register(Request $request)
{
    // Validation
    $validator = \Validator::make($request->all(), [
        'firstName'    => 'required|string|max:255',
        'lastName'     => 'required|string|max:255',
        'middleName'   => 'nullable|string|max:255',
        'address'      => 'required|string|max:255',
        'emailAddress' => [
            'required',
            'email',
            'unique:applicant,emailAddress', // exact column in applicant table
            function ($attribute, $value, $fail) {
                if (\App\Models\Organization::where('emailAddress', $value)->exists()) {
                    $fail('The email has already been taken by an organization.');
                }
            },
        ],
        'phoneNumber'  => 'required|string|max:11',
        'password'     => 'required|string|min:8',
    ]);

    // Return validation errors immediately
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Generate verification token
    $verificationToken = Str::random(64);

    // Create applicant
    $applicant = Applicant::create([
        'firstName'    => $request->firstName,
        'middleName'   => $request->middleName,
        'lastName'     => $request->lastName,
        'address'      => $request->address,
        'emailAddress' => $request->emailAddress, // exact DB column
        'phoneNumber'  => $request->phoneNumber,
        'password'     => bcrypt($request->password),
        'api_token'    => Str::random(60),
        'email_verification_token' => $verificationToken,
        'email_verified_at' => null,
    ]);

    // Prepare verification URL and user info
    $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=applicant');
    $userName = $request->firstName . ' ' . $request->lastName;
    $userEmail = $request->emailAddress;

    // Return response immediately
    $response = response()->json([
        'status'  => 'success',
        'message' => 'Registration successful! Please check your email to verify your account.',
        'user'    => $applicant,
        'email_sent' => true, // Assume it will be sent
        'verification_url' => $verificationUrl, // Include for manual verification
        'verification_token' => $verificationToken,
    ], 201);

    // Send email asynchronously (after response is sent)
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    try {
        \Log::info('Queueing verification email (Applicant)', [
            'email' => $userEmail,
        ]);

        SendVerificationEmailJob::dispatch(
            $userEmail,
            $verificationUrl,
            $userName,
            'applicant'
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

    $applicant = Applicant::where('emailAddress', $request->emailAddress)->first();

    if (!$applicant || !Hash::check($request->password, $applicant->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Check if email is verified
    if (!$applicant->email_verified_at) {
        return response()->json([
            'message' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
            'email_verified' => false,
        ], 403);
    }

    // ✅ Only generate if missing
    if (!$applicant->api_token) {
        $applicant->api_token = Str::random(60);
        $applicant->save();
    }

    return response()->json([
        'message' => 'Login successful',
        'user' => $applicant,
        'token' => $applicant->api_token,
    ]);
}

  // 🧩 Update applicant profile
    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Validate input
        $validated = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'emailAddress' => 'nullable|email|max:255',
            'phoneNumber' => 'nullable|string|max:20',
            'displayPicture_directory' => 'nullable|string|max:255',
            // Accept DisplayPicture_directory for backward compatibility but normalize it
            'DisplayPicture_directory' => 'nullable|string|max:255',
        ]);

        // ✅ Update profile fields
        $applicant->fill([
            'firstName' => $request->firstName ?? $applicant->firstName,
            'middleName' => $request->middleName ?? $applicant->middleName,
            'lastName' => $request->lastName ?? $applicant->lastName,
            'address' => $request->address ?? $applicant->address,
            'emailAddress' => $request->emailAddress ?? $applicant->emailAddress,
            'phoneNumber' => $request->phoneNumber ?? $applicant->phoneNumber,
        ]);

        // Handle display picture - normalize to displayPicture_directory (camelCase)
        $displayPicturePath = $request->input('displayPicture_directory') ??
            $request->input('DisplayPicture_directory');
        
        if ($displayPicturePath !== null) {
            // Use setAttribute directly with the exact database column name (camelCase)
            // This ensures Laravel uses the correct column name, not DisplayPicture_directory
            $applicant->setAttribute('displayPicture_directory', $displayPicturePath);
        }

        $applicant->save();

        // Return updated applicant data including the displayPicture_directory
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $applicant->fresh() // Return fresh data from database
        ]);
    }
// Delete applicant account
   public function destroy(Request $request)
{
    $token = $request->bearerToken();
    $user = Applicant::where('api_token', $token)->first();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Verify password
    if (!Hash::check($request->currentPassword, $user->password)) {
        return response()->json(['message' => 'Incorrect password'], 403);
    }

    $user->delete();

    return response()->json(['message' => 'Account deleted successfully']);
}

   // 🧩 Update password separately
    public function updatePassword(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Validate fields
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        // ✅ Check if current password matches
        if (!Hash::check($validated['currentPassword'], $applicant->password)) {
            return response()->json(['message' => 'The current password is incorrect.'], 403);
        }

        // ✅ Update password
        $applicant->password = Hash::make($validated['newPassword']);
        $applicant->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

      public function index()
    {
        $applicants = Applicant::select(
            'applicantID',
            'firstName',
            'middleName',
            'lastName',
            'emailAddress',
            'address',
            'phoneNumber'
        )->get();

        return response()->json($applicants);
    }

    // 🗑️ 2. Delete applicant by ID (for admin use)
    public function destroyById($id)
    {
        $applicant = Applicant::find($id);

        if (!$applicant) {
            return response()->json(['message' => 'Applicant not found'], 404);
        }

        // 🔥 1. Delete child tables FIRST (avoid FK constraint errors)
        $applicant->applications()->delete();
        $applicant->certifications()->delete();
        $applicant->registrations()->delete();
        $applicant->resumes()->delete();

        // 🔥 2. Now delete applicant (safe)
        $applicant->delete();

        return response()->json(['message' => 'Applicant deleted successfully']);
    }

}