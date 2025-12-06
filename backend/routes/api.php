<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProfessionalExperienceController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CareerRecommendationController;
use App\Http\Controllers\ApplicationFileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MyActivityController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\AdminSearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LocationController;
// ----------------------
// Public routes (no auth)
// ----------------------

Route::post('/admin/login', [AdminController::class, 'login']);

Route::get('/dashboard', [DashboardController::class, 'getChartData']);

// Trainings
Route::get('/trainings', [TrainingController::class, 'index']);

Route::get('/regions', function () {
    try {
        // Try PSGC API
        $response = Http::get('https://psgc.gitlab.io/api/regions.json');
        if ($response->successful()) {
            return $response->json();
        }

        // Fallback 1: GitHub JSON
        $fallback1 = Http::get('https://raw.githubusercontent.com/simonbengtsson/jsondata/master/philippines/regions.json');
        if ($fallback1->successful()) {
            return $fallback1->json();
        }

        // Fallback 2: Another GitHub JSON
        $fallback2 = Http::get('https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/regions.json');
        if ($fallback2->successful()) {
            return $fallback2->json();
        }

        // Last resort: Hardcoded
        return response()->json([
            ["code" => "010000000", "name" => "Ilocos Region (Region I)"],
            ["code" => "020000000", "name" => "Cagayan Valley (Region II)"],
            ["code" => "030000000", "name" => "Central Luzon (Region III)"],
            // ... add the rest
        ]);
    } catch (\Exception $e) {
        return response()->json([
            "error" => "Failed to fetch regions",
            "message" => $e->getMessage()
        ], 500);
    }
});

Route::get('/provinces/{regionCode}', function ($regionCode) {
    try {
        $response = Http::get('https://psgc.gitlab.io/api/regions/'.$regionCode.'/provinces.json');
        if ($response->successful()) {
            return $response->json();
        }

        // fallback 1
        $fallback = Http::get('https://raw.githubusercontent.com/.../provinces.json'); // optional
        if ($fallback->successful()) {
            return $fallback->json();
        }

        return response()->json([], 404);
    } catch (\Exception $e) {
        return response()->json([
            "error" => "Failed to fetch provinces",
            "message" => $e->getMessage()
        ], 500);
    }
});

Route::get('/cities/{provinceCode}', function ($provinceCode) {
    try {
        // Try PSGC API
        $response = Http::get("https://psgc.gitlab.io/api/provinces/{$provinceCode}/cities-municipalities.json");
        if ($response->successful()) {
            return $response->json();
        }

        // Optional fallback: GitHub JSON
        $fallback = Http::get("https://raw.githubusercontent.com/.../cities.json"); 
        if ($fallback->successful()) {
            return $fallback->json();
        }

        return response()->json([], 404);
    } catch (\Exception $e) {
        return response()->json([
            "error" => "Failed to fetch cities",
            "message" => $e->getMessage()
        ], 500);
    }
});

Route::get('/barangays/{cityCode}', function ($cityCode) {
    try {
        // PSGC API for barangays
        $response = Http::get("https://psgc.gitlab.io/api/cities-municipalities/{$cityCode}/barangays.json");
        if ($response->successful()) {
            return $response->json();
        }

        // Optional fallback (GitHub JSON or hardcoded)
        // $fallback = Http::get("https://raw.githubusercontent.com/.../barangays.json");
        // if ($fallback->successful()) return $fallback->json();

        return response()->json([], 404);
    } catch (\Exception $e) {
        return response()->json([
            "error" => "Failed to fetch barangays",
            "message" => $e->getMessage()
        ], 500);
    }
});

// Careers with recommendations
Route::get('/careers', [CareerRecommendationController::class, 'index']);
Route::get('/careers/recommend/{careerID}', [CareerRecommendationController::class, 'recommendedCareers'])
     ->name('careers.recommendation');
Route::get('/careers/{careerID}/trainings', [CareerRecommendationController::class, 'recommendedTrainings']);
Route::get('/careers/{careerID}/details', [CareerRecommendationController::class, 'careerDetails']);
Route::get('/careers/{careerID}/organizations-choice-trainings', [CareerRecommendationController::class, 'getOrganizationsChoiceTrainings']);

//Certificate Issuance

Route::post('/certifications/{id}/issue', [CertificateController::class, 'issueCertificate']);


// CareerController
Route::get('/careers/recommend/manual/{id}', [CareerController::class, 'recommend'])
     ->name('careers.recommend.manual');
// Tags
Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);



// Attendance check-in
Route::post('/attendance/checkin', [TrainingController::class, 'attendanceCheckin']);
Route::post('/trainings/generate-qr', [TrainingController::class, 'generateQRCode']);


// Signed application requirements
Route::get('/signed/applications/{application}/{organization}/requirements',
    [ApplicationFileController::class, 'serveSigned'])
    ->name('signed.requirements.view')
    ->middleware('signed');

// ----------------------
// Auth routes
// ----------------------
Route::post('/a_register', [AuthController::class, 'a_register']);
Route::post('/o_register', [AuthController::class, 'o_register']);
Route::post('/login', [AuthController::class, 'login']);

// Email Verification routes
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::post('/test-email', [AuthController::class, 'testEmail']); // For debugging email configuration

// Password Reset routes
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Get verification link for testing (development only)
Route::get('/get-verification-link/{email}', [AuthController::class, 'getVerificationLink']);

// Clear config cache (for Railway free tier - no console access)
Route::post('/clear-cache', function() {
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear'); // Clear route cache
    \Artisan::call('view:clear'); // Clear view cache
    return response()->json([
        'status' => 'success',
        'message' => 'Config, cache, route, and view cache cleared successfully'
    ]);
});

// Diagnostic endpoint to check email configuration
Route::get('/check-email-config', function() {
    $brevoApiKeyFromConfig = config('services.brevo.api_key');
    $brevoApiKeyFromEnv = env('BREVO_API_KEY');
    $brevoApiKey = trim($brevoApiKeyFromConfig ?: $brevoApiKeyFromEnv ?: '');
    
    $mailConfig = [
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'username' => config('mail.mailers.smtp.username'),
        'encryption' => config('mail.mailers.smtp.encryption'),
        'from_address' => config('mail.from.address'),
        'from_name' => config('mail.from.name'),
    ];
    
    return response()->json([
        'brevo_api_key' => [
            'set' => !empty($brevoApiKey),
            'length' => $brevoApiKey ? strlen($brevoApiKey) : 0,
            'preview' => $brevoApiKey ? substr($brevoApiKey, 0, 10) . '...' : 'not set',
            'from_config' => !empty($brevoApiKeyFromConfig),
            'from_env' => !empty($brevoApiKeyFromEnv),
            'raw_config' => $brevoApiKeyFromConfig ? substr($brevoApiKeyFromConfig, 0, 10) . '...' : 'not set',
            'raw_env' => $brevoApiKeyFromEnv ? substr($brevoApiKeyFromEnv, 0, 10) . '...' : 'not set',
        ],
        'mail_config' => $mailConfig,
        'recommendation' => !empty($brevoApiKey) 
            ? 'Brevo API is configured. Emails should use Brevo API.' 
            : 'Brevo API key not found. Will fallback to SMTP. Add BREVO_API_KEY to .env file.',
    ]);
});

// Test Brevo API key directly
Route::post('/test-brevo-api', function(\Illuminate\Http\Request $request) {
    $brevoApiKeyFromConfig = config('services.brevo.api_key');
    $brevoApiKeyFromEnv = env('BREVO_API_KEY');
    $brevoApiKey = trim($brevoApiKeyFromConfig ?: $brevoApiKeyFromEnv ?: '');
    
    if (empty($brevoApiKey)) {
        return response()->json([
            'success' => false,
            'error' => 'Brevo API key not found'
        ], 400);
    }
    
    $testEmail = $request->input('email', 'test@example.com');
    
    try {
        $brevoService = app(\App\Services\BrevoEmailService::class);
        
        $htmlContent = '<html><body><h1>Test Email from Pathfinder</h1><p>This is a test email to verify Brevo API is working correctly.</p></body></html>';
        
        $brevoService->send(
            $testEmail,
            'Test Email - Pathfinder Brevo API',
            $htmlContent
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully via Brevo API',
            'to' => $testEmail
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
});

// Applicant & Organization
Route::post('/applicants', [ApplicantController::class, 'a_register']);
Route::post('/applicants/login', [ApplicantController::class, 'login']);


Route::post('/organization', [OrganizationController::class, 'o_register']);
Route::post('/organizations/login', [OrganizationController::class, 'login']);
Route::get('/organizations', [OrganizationController::class, 'index']);
Route::get('/admin/approved-organizations', [OrganizationController::class, 'index']);
Route::get('/organizations/{organizationID}', [OrganizationController::class, 'show']);
Route::get('/organizations/{organizationID}', [OrganizationController::class, 'getOrgDetails']);
Route::get('/admin/rejected-organizations', [OrganizationController::class, 'rejected']);

// ----------------------
// Resume (authenticated)
// ----------------------
Route::middleware('auth.custom')->group(function () {

    // Resume
    Route::post('/resume', [ResumeController::class, 'store']);
    Route::get('/resume', [ResumeController::class, 'show']);
    Route::delete('/resume', [ResumeController::class, 'destroy']);

    // Public totals inside auth group
    Route::get('/trainings/total', [TrainingController::class, 'total'])
        ->withoutMiddleware('auth.custom');
    Route::get('/trainings/counts-partial', [TrainingController::class, 'countsPartial'])
        ->withoutMiddleware('auth.custom');
    Route::get('/careers/total', [CareerController::class, 'total'])
        ->withoutMiddleware('auth.custom');
     Route::get('/careers/counts-partial', [CareerController::class, 'countsPartial'])
        ->withoutMiddleware('auth.custom');
});

// ----------------------
// Admin routes (Sanctum auth)
// ----------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/details', [AdminController::class, 'AdminInfo']);
    Route::put('/admin/update', [AdminController::class, 'update']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
});

// ----------------------
// Protected routes (auth.custom)
// ----------------------
Route::middleware('auth.custom')->group(function () {
// Certificate issuance (removed duplicate - see below)


    // Trainings
    Route::post('/trainings', [TrainingController::class, 'store'])->middleware('require.verified.org');
    Route::put('/trainings/{id}', [TrainingController::class, 'update'])->middleware('require.verified.org');
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy'])->middleware('require.verified.org');
    
  
    Route::get('/trainings/{trainingID}', [TrainingController::class, 'show']);
    Route::get('/organization/trainings', [TrainingController::class, 'organizationIndex']);

    // Organization-specific listings
    Route::get('/organization/careers', [CareerController::class, 'index']);

    // Careers
    Route::post('/careers', [CareerController::class, 'store'])->middleware('require.verified.org');
    Route::get('/careers/{id}', [CareerController::class, 'show']);
    Route::put('/careers/{id}', [CareerController::class, 'update'])->middleware('require.verified.org');
    Route::delete('/careers/{id}', [CareerController::class, 'destroy'])->middleware('require.verified.org'); 

    // Certificate issuance
    Route::post('/registrations/{registrationID}/certificate', [RegistrationController::class, 'updateCertificate'])->middleware('require.verified.org');
    Route::put('/registrations/{registrationID}/status', [RegistrationController::class, 'updateStatus'])->middleware('require.verified.org');
    Route::post('/trainings/{trainingID}/certificates/bulk', [RegistrationController::class, 'issueBulkCertificates'])->middleware('require.verified.org');

    // Applicant monitoring
    Route::get('/careers/{careerID}/applicants', [ApplicationController::class, 'getApplicantsByCareer']);

    // Career aligned trainings
    Route::get('/careers/{careerID}/aligned-trainings', [CareerController::class, 'getAlignedTrainings']);
    Route::get('/careers/{careerID}/selected-trainings', [CareerController::class, 'getSelectedTrainings']);
    Route::post('/careers/{careerID}/selected-trainings', [CareerController::class, 'saveSelectedTrainings']);
    Route::put('/applications/{applicationID}/status', [ApplicationController::class, 'updateStatus'])->middleware('require.verified.org');
    Route::post('/applications/{applicationID}/send-status-email', [ApplicationController::class, 'sendStatusEmail'])->middleware('require.verified.org');
    Route::get('/applications/interviews', [InterviewController::class, 'index']);
    Route::put('/applications/{applicationID}/interview', [ApplicationController::class, 'updateInterview'])->middleware('require.verified.org');
    Route::get('/applications/{applicationID}/requirements/signed-url', [ApplicationFileController::class, 'generateSignedUrl']);
    Route::get('/applications/{id}/requirements', [ApplicationController::class, 'getRequirements']);
    Route::post('/applications/{id}/upload-requirement', [ApplicationController::class, 'uploadRequirement']);


    // Registrations
    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::post('/registrations', [RegistrationController::class, 'store']);
    Route::delete('/registrations/{id}', [RegistrationController::class, 'destroy']);
    Route::get('/trainings/{trainingID}/registrants', [RegistrationController::class, 'getRegistrantsByTraining']);

    // Applications
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
    Route::delete('/applications/career/{careerID}', [ApplicationController::class, 'destroyByCareer']);
    Route::get('/applications/{id}/requirement', [ApplicationController::class, 'viewRequirement']);
    Route::get('/interviews', [InterviewController::class, 'index']);

    // Certificates
    // ✅ Specific routes first (before parameterized routes)
    Route::post('/certificates/organization/toggle', [CertificateController::class, 'toggleOrganizationCertificate']);
    Route::get('/certificates/{applicantID}', [CertificateController::class, 'index']);
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    Route::patch('/certificates/{id}/toggle', [CertificateController::class, 'toggleSelection']);
    Route::get('/certificates/{applicantID}/selected', [CertificateController::class, 'selectedCertificates']);

    // Professional Experience
    Route::get('/experiences', [ProfessionalExperienceController::class, 'show']);
    Route::post('/experiences', [ProfessionalExperienceController::class, 'store']);
    Route::put('/experiences/{id}', [ProfessionalExperienceController::class, 'update']);
    Route::delete('/experiences/{id}', [ProfessionalExperienceController::class, 'destroy']);

    // Education
    Route::get('/education', [EducationController::class, 'show']);
    Route::post('/education', [EducationController::class, 'store']);
    Route::put('/education/{id}', [EducationController::class, 'update']);
    Route::delete('/education/{id}', [EducationController::class, 'destroy']);

    Route::get('/organization/details', [OrganizationController::class, 'getOrgDetails']);
    Route::put('/organization/update', [OrganizationController::class, 'update']);
    Route::post('/organization/update/request-otp', [OrganizationController::class, 'requestProfileUpdateOTP']);
    Route::post('/organization/change-password/request-otp', [OrganizationController::class, 'requestPasswordChangeOTP']);
    Route::post('/organization/change-password', [OrganizationController::class, 'changePassword']);
});

// ----------------------
// Skills, User Info, Search, etc.
// ----------------------
Route::get('/skills/{resumeID}', [SkillController::class, 'index']);
Route::post('/skills', [SkillController::class, 'store']);
Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

Route::delete('/user', [ApplicantController::class, 'destroy']);
Route::post('/update-password', [ApplicantController::class, 'updatePassword']);
Route::post('/user/change-password/request-otp', [ApplicantController::class, 'requestPasswordChangeOTP']);
Route::put('/user', [ApplicantController::class, 'update']);

Route::get('/search', [SearchController::class, 'search']);
Route::get('/training/{id}', [SearchController::class, 'getTraining']);
Route::get('/career/{id}', [SearchController::class, 'getCareer']);
Route::get('/organization/{id}/search', [SearchController::class, 'getOrganization']);
Route::get('/training/{id}', [TrainingController::class, 'show']);

// Optional: Get authenticated user by token
Route::get('/user', function (Request $request) {
    $token = $request->bearerToken();
    $user = \App\Models\Applicant::where('api_token', $token)->first();
    if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
    return $user;
});

// Activities & Events
Route::get('/my-activities/{applicantID}', [MyActivityController::class, 'getMyActivities']);
Route::get('/calendar/{applicantID}', [EventController::class, 'getUserEvents']);

// Admin routes
Route::get('/admin/search', [AdminSearchController::class, 'search']);
Route::get('/admin/applicants', [ApplicantController::class, 'index']);
Route::delete('/admin/applicants/{id}', [ApplicantController::class, 'destroyById']);
Route::get('/admin/organizations', [OrganizationController::class, 'index']);
Route::delete('/admin/organizations/{id}', [OrganizationController::class, 'destroyById']);

// Admin – Organization approval system
Route::get('/admin/pending-organizations', [OrganizationController::class, 'pending']);
Route::post('/organization/{id}/approve', [OrganizationController::class, 'approve']);
Route::post('/organization/{id}/reject', [OrganizationController::class, 'reject']);
Route::delete('/trainings/{trainingID}', [TrainingController::class, 'destroyById']);

use App\Http\Controllers\FileController;

Route::get('/file-url/{filename}', [ApplicationFileController::class, 'getFileUrl']);
Route::get('/applications/{id}/file', [ApplicationFileController::class, 'show']);

