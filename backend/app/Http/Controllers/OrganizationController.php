<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Training;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendVerificationEmailJob;

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
        'password'    => 'required|string|min:8',
        'logoPath'    => 'nullable|string|max:500',
        'logo_directory' => 'nullable|string|max:500',
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
        $org->statusDate = now(); // <-- ADD THIS LINE

        $org->save();

        return response()->json([
            'message' => 'Organization approved',
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
        $org->statusDate = now(); // <-- add this line
        $org->save();

        return response()->json([
            'message' => 'Organization rejected successfully',
            'rejectionReason' => $org->rejectionReason,
            'statusDate' => $org->statusDate // <-- optionally return it
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
                'password'         => '' // never send hashed password
            ]
        ]);
    }
}
