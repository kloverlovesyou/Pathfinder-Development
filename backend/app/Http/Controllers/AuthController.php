<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Organization;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;
use App\Services\BrevoEmailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;


class AuthController extends Controller
{
    /**
     * Send verification email with automatic fallback to Brevo API
     */
    private function sendVerificationEmail($email, $verificationUrl, $userName, $type)
    {
        $emailSent = false;
        $emailError = null;
        $emailException = null;
        $usedBrevoApi = false;
        
        // Try SMTP first
        try {
            Mail::to($email)->send(new EmailVerification($verificationUrl, $userName, $type));
            $emailSent = true;
            \Log::info('Verification email sent via SMTP', ['email' => $email]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $emailException = $e;
            
            // Check if it's a connection timeout/refused error
            $isConnectionError = str_contains($errorMessage, 'Connection timed out') ||
                                 str_contains($errorMessage, 'Connection refused') ||
                                 str_contains($errorMessage, 'could not be established');
            
            if ($isConnectionError) {
                \Log::warning('SMTP connection failed, trying Brevo API', [
                    'email' => $email,
                    'error' => $errorMessage
                ]);
                
                // Fallback to Brevo API
                try {
                    $brevoService = new BrevoEmailService();
                    $htmlContent = View::make('emails.verification', [
                        'verificationUrl' => $verificationUrl,
                        'userName' => $userName,
                        'userType' => $type
                    ])->render();
                    
                    $brevoService->send(
                        $email,
                        'Verify Your Email Address - Pathfinder',
                        $htmlContent
                    );
                    
                    $emailSent = true;
                    $usedBrevoApi = true;
                    \Log::info('Verification email sent via Brevo API', ['email' => $email]);
                } catch (\Exception $brevoError) {
                    $emailError = 'SMTP failed: ' . $errorMessage . ' | Brevo API failed: ' . $brevoError->getMessage();
                    \Log::error('Both SMTP and Brevo API failed', [
                        'email' => $email,
                        'smtp_error' => $errorMessage,
                        'brevo_error' => $brevoError->getMessage()
                    ]);
                }
            } else {
                // Other SMTP error, don't try Brevo API
                $emailError = $errorMessage;
                \Log::error('SMTP error (not connection issue)', [
                    'email' => $email,
                    'error' => $errorMessage
                ]);
            }
        }
        
        return [
            'email_sent' => $emailSent,
            'email_error' => $emailError,
            'email_exception' => $emailException,
            'used_brevo_api' => $usedBrevoApi,
        ];
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

        // Send verification email
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=applicant');
        $userName = $validated['firstName'] . ' ' . $validated['lastName'];
        
        \Log::info('Attempting to send verification email (Applicant)', [
            'email' => $validated['emailAddress']
        ]);
        
        $emailResult = $this->sendVerificationEmail(
            $validated['emailAddress'],
            $verificationUrl,
            $userName,
            'applicant'
        );
        
        $emailSent = $emailResult['email_sent'];
        $emailError = $emailResult['email_error'];
        $emailException = $emailResult['email_exception'];
        
        // Get mail configuration for debugging
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
            'brevo_api_key_set' => !empty(config('services.brevo.api_key')),
            'used_brevo_api' => $emailResult['used_brevo_api'] ?? false,
        ];

        $response = [
            'status'  => 'success',
            'message' => 'Registration successful! Please check your email to verify your account.',
            'data'    => $applicant,
            'email_sent' => $emailSent,
            'mail_config' => $mailConfig, // Always include for debugging
        ];
        
        // Always include email status
        if ($emailError) {
            $response['email_error'] = $emailError;
            $response['error_class'] = $emailException ? get_class($emailException) : 'Unknown';
            $response['warning'] = 'Email sending failed. You can use the resend verification endpoint or check your email configuration.';
            $response['verification_url'] = $verificationUrl; // Include URL for manual testing
            $response['verification_token'] = $verificationToken; // Include token for manual testing
        }
        
        // In development, include more debug info
        if (config('app.debug')) {
            $response['debug'] = [
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'verification_url' => $verificationUrl,
            ];
        }
        
        return response()->json($response);
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

        // Generate verification token
        $verificationToken = Str::random(64);

        $organization = Organization::create([
            'name'         => $validated['name'],
            'location'     => $validated['location'] ?? '',
            'websiteURL'   => $validated['websiteURL'] ?? '',
            'emailAddress' => $validated['emailAddress'],
            'phoneNumber'  => $validated['phoneNumber'],
            'password'     => Hash::make($validated['password']),
            'email_verification_token' => $verificationToken,
            'email_verified_at' => null,
        ]);

        // Send verification email
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=organization');
        $userName = $validated['name'];
        
        \Log::info('Attempting to send verification email (Organization)', [
            'email' => $validated['emailAddress']
        ]);
        
        $emailResult = $this->sendVerificationEmail(
            $validated['emailAddress'],
            $verificationUrl,
            $userName,
            'organization'
        );
        
        $emailSent = $emailResult['email_sent'];
        $emailError = $emailResult['email_error'];
        $emailException = $emailResult['email_exception'];
        
        // Get mail configuration for debugging
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
            'brevo_api_key_set' => !empty(config('services.brevo.api_key')),
            'used_brevo_api' => $emailResult['used_brevo_api'] ?? false,
        ];

        $response = [
            'status'  => 'success',
            'message' => 'Registration successful! Please check your email to verify your account.',
            'data'    => $organization,
            'email_sent' => $emailSent,
            'mail_config' => $mailConfig, // Always include for debugging
        ];
        
        // Always include email status
        if ($emailError) {
            $response['email_error'] = $emailError;
            $response['error_class'] = $emailException ? get_class($emailException) : 'Unknown';
            $response['warning'] = 'Email sending failed. You can use the resend verification endpoint or check your email configuration.';
            $response['verification_url'] = $verificationUrl; // Include URL for manual testing
            $response['verification_token'] = $verificationToken; // Include token for manual testing
        }
        
        // In development, include more debug info
        if (config('app.debug')) {
            $response['debug'] = [
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'verification_url' => $verificationUrl,
            ];
        }
        
        return response()->json($response);
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
        
        $emailResult = $this->sendVerificationEmail($email, $verificationUrl, $userName, $type);
        
        if (!$emailResult['email_sent']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification email. Please try again later.',
                'error' => $emailResult['email_error'],
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
        
        $emailResult = $this->sendVerificationEmail($testEmail, $verificationUrl, 'Test User', 'applicant');
        $mailConfig['used_brevo_api'] = $emailResult['used_brevo_api'] ?? false;
        
        if ($emailResult['email_sent']) {
            \Log::info('Test email sent successfully', ['email' => $testEmail]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Test email sent successfully! Check your inbox (and spam folder).',
                'mail_config' => $mailConfig,
                'method' => $emailResult['used_brevo_api'] ? 'Brevo API' : 'SMTP',
            ]);
        } else {
            \Log::error('Test email failed', [
                'email' => $testEmail,
                'error' => $emailResult['email_error'],
                'mail_config' => $mailConfig
            ]);
            
            $troubleshooting = [
                'check_brevo_api_key' => 'Set BREVO_API_KEY in Railway environment variables (not just .env file)',
                'check_smtp_config' => 'Verify SMTP settings if using SMTP',
                'check_connection' => 'Railway blocks SMTP - Brevo API fallback should work if BREVO_API_KEY is set',
            ];
            
            if (!$mailConfig['brevo_api_key_set']) {
                $troubleshooting['action'] = 'BREVO_API_KEY is NOT set in Railway. Add it in Railway Dashboard → Variables';
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send test email',
                'error' => $emailResult['email_error'],
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
}