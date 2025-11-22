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

        // Send verification email
        $verificationUrl = url('/api/verify-email?token=' . $verificationToken . '&type=organization');
        $userName = $request->input('name');
        
        $emailSent = false;
        $emailError = null;
        $mailConfig = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'username_set' => !empty(config('mail.mailers.smtp.username')),
            'password_set' => !empty(config('mail.mailers.smtp.password')),
        ];
        
        try {
            \Log::info('Attempting to send verification email', [
                'email' => $request->input('emailAddress'),
                'mail_config' => $mailConfig
            ]);
            
            Mail::to($request->input('emailAddress'))->send(new EmailVerification($verificationUrl, $userName, 'organization'));
            $emailSent = true;
            \Log::info('Verification email sent successfully', [
                'email' => $request->input('emailAddress'),
                'url' => $verificationUrl
            ]);
        } catch (\Exception $e) {
            $emailError = $e->getMessage();
            \Log::error('Failed to send verification email', [
                'email' => $request->input('emailAddress'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_config' => $mailConfig
            ]);
        }

        $response = [
            'message' => $emailSent 
                ? 'Registration successful! Please check your email to verify your account.'
                : 'Registration successful! However, the verification email could not be sent.',
            'organization' => $organization,
            'email_sent' => $emailSent,
            'mail_config' => $mailConfig, // Always include for debugging
        ];
        
        // Always include email status and verification info
        if ($emailError) {
            $response['email_error'] = $emailError;
            $response['warning'] = 'Email sending failed. Check your email configuration in .env file.';
            $response['verification_url'] = $verificationUrl; // Include URL for manual testing
            $response['verification_token'] = $verificationToken; // Include token for manual testing
            $response['help'] = [
                'check_env' => 'Verify MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD in .env',
                'test_endpoint' => 'Use POST /api/test-email to test your email configuration',
                'manual_verify' => 'You can manually verify using the verification_url above',
            ];
        } else if (!$emailSent) {
            // Email didn't send but no error was caught (silent failure)
            $response['warning'] = 'Email may not have been sent. Check your email configuration.';
            $response['verification_url'] = $verificationUrl;
            $response['verification_token'] = $verificationToken;
        }

        return response()->json($response, 201);
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
