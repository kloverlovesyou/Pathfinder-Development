<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Training;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;
use App\Services\BrevoEmailService;
use Illuminate\Support\Facades\View;

class OrganizationController extends Controller
{
    /**
     * Send verification email with automatic fallback to Brevo API
     * Optimized to skip SMTP on Railway (known to be blocked) and go straight to Brevo API
     */
    private function sendVerificationEmail($email, $verificationUrl, $userName, $type)
    {
        $emailSent = false;
        $emailError = null;
        $emailException = null;
        $usedBrevoApi = false;
        
        // Skip SMTP on Railway (it's blocked) and go straight to Brevo API for speed
        $brevoApiKey = config('services.brevo.api_key', env('BREVO_API_KEY'));
        
        if (!empty($brevoApiKey)) {
            // Use Brevo API directly (faster, no SMTP timeout)
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
                $emailError = 'Brevo API failed: ' . $brevoError->getMessage();
                \Log::error('Brevo API failed', [
                    'email' => $email,
                    'error' => $brevoError->getMessage()
                ]);
            }
        } else {
            // Fallback to SMTP only if Brevo API key is not set
            try {
                Mail::to($email)->send(new EmailVerification($verificationUrl, $userName, $type));
                $emailSent = true;
                \Log::info('Verification email sent via SMTP', ['email' => $email]);
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                \Log::error('SMTP failed', [
                    'email' => $email,
                    'error' => $e->getMessage()
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
                \Illuminate\Validation\Rule::unique('organization', 'emailAddress')
            ],
            'phoneNumber' => 'nullable|string|max:20',
            'password'    => 'required|string|min:8',
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
            \Log::info('Sending verification email asynchronously (Organization)', [
                'email' => $userEmail
            ]);
            
            $this->sendVerificationEmail(
                $userEmail,
                $verificationUrl,
                $userName,
                'organization'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email asynchronously', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
        }
        
        return $response;
    }

    // ----------------------
    // Login organization
    // ----------------------
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

        if ($organization->status === 'pending') {
            return response()->json(['message' => 'Your registration is still under review.'], 403);
        }

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
        $org->save();

        return response()->json(['message' => 'Organization approved']);
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
    $org->save();

    return response()->json([
        'message' => 'Organization rejected successfully',
        'rejectionReason' => $org->rejectionReason
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
                $training->trainingbookmarks()->delete();
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
                'password'         => '' // never send hashed password
            ]
        ]);
    }
}
