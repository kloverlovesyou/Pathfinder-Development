<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Organization;
use Illuminate\Support\Str;
use App\Jobs\SendVerificationEmailJob;
use App\Services\VerificationEmailSender;
use Carbon\Carbon;


class AuthController extends Controller
{
    public function __construct(
        private VerificationEmailSender $verificationEmailSender
    ) {
    }

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

        // Generate verification token
        $verificationToken = Str::random(64);

        $applicant = Applicant::create([
            'firstName'    => $validated['firstName'],
            'lastName'     => $validated['lastName'],
            'address'      => $validated['address'],
            'emailAddress' => $validated['emailAddress'],
            'phoneNumber'  => $validated['phoneNumber'],
            'password'     => Hash::make($validated['password']),
            'email_verification_token' => $verificationToken,
            'email_verified_at' => null,
        ]);

        // Prepare verification URL and user info
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=applicant');
        $userName = $validated['firstName'] . ' ' . $validated['lastName'];
        $userEmail = $validated['emailAddress'];
        
        // Return response immediately (don't wait for email)
        $response = response()->json([
            'status'  => 'success',
            'message' => 'Registration successful! Please check your email to verify your account.',
            'data'    => $applicant,
            'email_sent' => true, // Assume it will be sent
            'verification_url' => $verificationUrl, // Always include for manual verification
            'verification_token' => $verificationToken,
        ]);
        
        // Send email asynchronously (after response is sent)
        // Use fastcgi_finish_request() if available to send response immediately
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        
        // Send email in background (won't block response)
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
            'logoPath'     => 'nullable|string|max:500',
            'logo_directory' => 'nullable|string|max:500', // backward compatibility
        ]);

        // Generate verification token
        $verificationToken = Str::random(64);

        $organization = Organization::create([
            'name'         => $validated['name'],
            'location'     => $validated['location'] ?? '',
            'websiteURL'   => $validated['websiteURL'] ?? '',
            'emailAddress' => $validated['emailAddress'],
            'phoneNumber'  => $validated['phoneNumber'],
            'password'     => Hash::make($validated['password']),
            'logo_directory' => $validated['logoPath'] ?? $validated['logo_directory'] ?? $validated['Logo_directory'] ?? null,
            'email_verification_token' => $verificationToken,
            'email_verified_at' => null,
        ]);

        // Prepare verification URL and user info
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=organization');
        $userName = $validated['name'];
        $userEmail = $validated['emailAddress'];
        
        // Return response immediately (don't wait for email)
        $response = response()->json([
            'status'  => 'success',
            'message' => 'Registration successful! Please check your email to verify your account.',
            'data'    => $organization,
            'email_sent' => true, // Assume it will be sent
            'verification_url' => $verificationUrl, // Always include for manual verification
            'verification_token' => $verificationToken,
        ]);
        
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
            'password'     => 'required|string|min:8',
        ]);

        // Check applicant
        $applicant = Applicant::where('emailAddress', $request->emailAddress)->first();
        if ($applicant && Hash::check($request->password, $applicant->password)) {
            // Check if email is verified
            if (!$applicant->email_verified_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
                    'email_verified' => false,
                ], 403);
            }

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
            // Check if email is verified
            if (!$organization->email_verified_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
                    'email_verified' => false,
                ], 403);
            }

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

    // Email Verification
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'type' => 'required|string|in:applicant,organization',
        ]);

        $token = $request->input('token');
        $type = $request->input('type');

        \Log::info('Email verification attempt', [
            'token' => substr($token, 0, 10) . '...',
            'type' => $type
        ]);

        if ($type === 'applicant') {
            $user = Applicant::where('email_verification_token', $token)->first();
        } else {
            $user = Organization::where('email_verification_token', $token)->first();
        }

        if (!$user) {
            \Log::warning('Invalid verification token', [
                'token' => substr($token, 0, 10) . '...',
                'type' => $type
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification token.',
            ], 400);
        }

        if ($user->email_verified_at) {
            \Log::info('Email already verified', [
                'email' => $user->emailAddress,
                'type' => $type
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified.',
            ]);
        }

        // Verify the email
        $user->email_verified_at = Carbon::now();
        $user->email_verification_token = null;
        $user->save();

        \Log::info('Email verified successfully', [
            'email' => $user->emailAddress,
            'type' => $type
        ]);

        // Return HTML response for browser redirect
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully! You can now log in.',
            ]);
        }

        // Return HTML page for direct browser access
        return response()->view('emails.verification-success', [
            'message' => 'Email verified successfully! You can now log in.'
        ]);
    }

    // Resend Verification Email
    public function resendVerification(Request $request)
    {
        $request->validate([
            'emailAddress' => 'required|email',
            'type' => 'required|string|in:applicant,organization',
        ]);

        $email = $request->input('emailAddress');
        $type = $request->input('type');

        if ($type === 'applicant') {
            $user = Applicant::where('emailAddress', $email)->first();
            $userName = $user ? ($user->firstName . ' ' . $user->lastName) : 'User';
        } else {
            $user = Organization::where('emailAddress', $email)->first();
            $userName = $user ? $user->name : 'User';
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified.',
            ], 400);
        }

        // Generate new verification token
        $verificationToken = Str::random(64);
        $user->email_verification_token = $verificationToken;
        $user->save();

        // Send verification email
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=' . $type);
        
        $emailResult = $this->verificationEmailSender->send(
            $email,
            $verificationUrl,
            $userName,
            $type
        );
        
        if (!$emailResult['email_sent']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification email. Please try again later.',
                'error' => $emailResult['email_error'] ?? 'Unknown error',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification email sent successfully. Please check your inbox.',
        ]);
    }

    // Test Email Configuration (for debugging)
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $testEmail = $request->input('email');
        $verificationUrl = url('/api/verify-email?token=test_token&type=applicant');
        
        // Get full mail configuration for debugging
        $brevoApiKey = config('services.brevo.api_key', env('BREVO_API_KEY'));
        $mailConfig = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'username' => config('mail.mailers.smtp.username'),
            'username_set' => !empty(config('mail.mailers.smtp.username')),
            'password_set' => !empty(config('mail.mailers.smtp.password')),
            'password_length' => strlen(config('mail.mailers.smtp.password', '')),
            'brevo_api_key_set' => !empty($brevoApiKey),
            'brevo_api_key_length' => $brevoApiKey ? strlen($brevoApiKey) : 0,
        ];
        
        \Log::info('Testing email configuration', [
            'email' => $testEmail,
            'mail_config' => $mailConfig,
            'brevo_api_key_set' => !empty($brevoApiKey)
        ]);
        
        $emailResult = $this->verificationEmailSender->send(
            $testEmail,
            $verificationUrl,
            'Test User',
            'applicant'
        );
        $mailConfig['used_brevo_api'] = $emailResult['used_brevo_api'] ?? false;
        
        if ($emailResult['email_sent']) {
            \Log::info('Test email sent successfully', ['email' => $testEmail]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Test email sent successfully! Check your inbox (and spam folder).',
                'mail_config' => $mailConfig,
                'method' => $emailResult['method'] ?? null,
            ]);
        } else {
            $errorMessage = $emailResult['email_error'] ?? 'Unknown error';

            \Log::error('Test email failed', [
                'email' => $testEmail,
                'error' => $errorMessage,
                'mail_config' => $mailConfig
            ]);
            
            $troubleshooting = [
                'check_brevo_api_key' => 'Set BREVO_API_KEY in Render environment variables (not just .env file)',
                'check_smtp_config' => 'Verify SMTP settings if using SMTP',
                'check_connection' => 'Render may block SMTP - Brevo API fallback should work if BREVO_API_KEY is set',
            ];
            
            if (!$mailConfig['brevo_api_key_set']) {
                $troubleshooting['action'] = 'BREVO_API_KEY is NOT set in Render. Add it in Render Dashboard → Environment → Environment Variables';
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send test email',
                'error' => $errorMessage,
                'mail_config' => $mailConfig,
                'troubleshooting' => $troubleshooting,
            ], 500);
        }
    }

    // Get Verification Link (for testing in development)
    public function getVerificationLink($email)
    {
        if (!config('app.debug')) {
            return response()->json(['message' => 'Not available in production'], 403);
        }

        // Try applicant first
        $applicant = Applicant::where('emailAddress', $email)->first();
        if ($applicant && $applicant->email_verification_token) {
            $url = url('/api/verify-email?token=' . $applicant->email_verification_token . '&type=applicant');
            return response()->json([
                'email' => $email,
                'type' => 'applicant',
                'verified' => $applicant->email_verified_at ? true : false,
                'verification_url' => $url,
                'token' => $applicant->email_verification_token,
            ]);
        }

        // Try organization
        $organization = Organization::where('emailAddress', $email)->first();
        if ($organization && $organization->email_verification_token) {
            $url = url('/api/verify-email?token=' . $organization->email_verification_token . '&type=organization');
            return response()->json([
                'email' => $email,
                'type' => 'organization',
                'verified' => $organization->email_verified_at ? true : false,
                'verification_url' => $url,
                'token' => $organization->email_verification_token,
            ]);
        }

        return response()->json([
            'message' => 'User not found or already verified',
            'email' => $email,
        ], 404);
    }

    // Forgot Password - Send reset link
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'emailAddress' => 'required|email',
        ]);

        $email = $request->input('emailAddress');

        // Check applicant first
        $applicant = Applicant::where('emailAddress', $email)->first();
        $userType = null;
        $userName = null;
        $user = null;

        if ($applicant) {
            $userType = 'applicant';
            $userName = $applicant->firstName . ' ' . $applicant->lastName;
            $user = $applicant;
        } else {
            // Check organization
            $organization = Organization::where('emailAddress', $email)->first();
            if ($organization) {
                $userType = 'organization';
                $userName = $organization->name;
                $user = $organization;
            }
        }

        // Check if email exists in the system
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email address is not registered in our system. Please check your email and try again.',
            ], 404);
        }

        // Generate reset token
        $resetToken = Str::random(64);
        $expiresAt = Carbon::now()->addHours(1); // Token expires in 1 hour

        $user->password_reset_token = $resetToken;
        $user->password_reset_expires_at = $expiresAt;
        $user->save();

        // Prepare reset URL
        $resetUrl = url('/reset-password?token=' . $resetToken . '&type=' . $userType);

        // Send password reset email
        $emailResult = $this->verificationEmailSender->sendPasswordReset(
            $email,
            $resetUrl,
            $userName,
            $userType
        );

        if (!$emailResult['email_sent']) {
            \Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $emailResult['email_error'] ?? 'Unknown error',
            ]);
            // Still return success for security
            return response()->json([
                'status' => 'success',
                'message' => 'If an account with that email exists, we have sent a password reset link.',
            ]);
        }

        \Log::info('Password reset email sent', [
            'email' => $email,
            'user_type' => $userType,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'If an account with that email exists, we have sent a password reset link.',
        ]);
    }

    // Reset Password - Update password with token
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'type' => 'required|string|in:applicant,organization',
            'password' => 'required|string|min:8',
        ]);

        $token = $request->input('token');
        $type = $request->input('type');
        $newPassword = $request->input('password');

        // Find user by token and type
        if ($type === 'applicant') {
            $user = Applicant::where('password_reset_token', $token)->first();
        } else {
            $user = Organization::where('password_reset_token', $token)->first();
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Check if token has expired
        if ($user->password_reset_expires_at && Carbon::now()->gt($user->password_reset_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This password reset link has expired. Please request a new one.',
            ], 400);
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;
        $user->save();

        \Log::info('Password reset successfully', [
            'email' => $user->emailAddress,
            'user_type' => $type,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been reset successfully. You can now log in with your new password.',
        ]);
    }
}