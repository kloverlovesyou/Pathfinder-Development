<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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
use App\Http\Controllers\TrainingBookmarkController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CareerBookmarkController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CareerRecommendationController;
use App\Http\Controllers\ApplicationFileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MyActivityController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\AdminSearchController;

// ----------------------
// Public routes (no auth)
// ----------------------

// Trainings
Route::get('/trainings', [TrainingController::class, 'index']);

// Tags
Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);

// Careers with recommendations
Route::get('/careers', [CareerRecommendationController::class, 'index']);
Route::get('/careers/{careerID}/recommended', [CareerRecommendationController::class, 'recommendedCareers']);
Route::get('/careers/{careerID}/trainings', [CareerRecommendationController::class, 'recommendedTrainings']);
Route::get('/careers/{careerID}/details', [CareerRecommendationController::class, 'careerDetails']);

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

// Applicant & Organization
Route::post('/applicants', [ApplicantController::class, 'a_register']);
Route::post('/applicants/login', [ApplicantController::class, 'login']);

Route::post('/organization', [OrganizationController::class, 'o_register']);
Route::post('/organizations/login', [OrganizationController::class, 'login']);

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
// Protected routes (auth.custom)
// ----------------------
Route::middleware('auth.custom')->group(function () {
// Certificate issuance
    Route::put('/registrations/{registrationID}/certificate', [RegistrationController::class, 'updateCertificate']);
    Route::post('/trainings/{trainingID}/certificates/bulk', [RegistrationController::class, 'issueBulkCertificates']);


    // Trainings
    Route::post('/trainings', [TrainingController::class, 'store']);
    Route::put('/trainings/{id}', [TrainingController::class, 'update']);
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy']);
    
  
    Route::get('/trainings/{trainingID}', [TrainingController::class, 'show']);

    // Careers
    Route::post('/careers', [CareerController::class, 'store']);
    Route::get('/careers/{id}', [CareerController::class, 'show']);
    Route::put('/careers/{id}', [CareerController::class, 'update']);
    Route::delete('/careers/{id}', [CareerController::class, 'destroy']); 

    // Certificate issuance
    Route::put('/registrations/{registrationID}/certificate', [RegistrationController::class, 'updateCertificate']);
    Route::post('/trainings/{trainingID}/certificates/bulk', [RegistrationController::class, 'issueBulkCertificates']);

    // Applicant monitoring
    Route::get('/careers/{careerID}/applicants', [ApplicationController::class, 'getApplicantsByCareer']);
    Route::put('/applications/{applicationID}/status', [ApplicationController::class, 'updateStatus']);
    Route::put('/applications/{applicationID}/interview', [ApplicationController::class, 'updateInterview']);
    Route::get('/applications/{applicationID}/requirements/signed-url', [ApplicationFileController::class, 'generateSignedUrl']);

    // Registrations
    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::post('/registrations', [RegistrationController::class, 'store']);
    Route::delete('/registrations/{id}', [RegistrationController::class, 'destroy']);
    Route::get('/trainings/{trainingID}/registrants', [RegistrationController::class, 'getRegistrantsByTraining']);

    // Applications
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
    Route::get('/applications/{id}/requirement', [ApplicationController::class, 'viewRequirement']);

    // Certificates
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

    // Bookmarks
    Route::get('/bookmarks', [TrainingBookmarkController::class, 'index']);
    Route::post('/bookmarks', [TrainingBookmarkController::class, 'store']);
    Route::delete('/bookmarks/{trainingID}', [TrainingBookmarkController::class, 'destroy']);

    Route::get('/career-bookmarks', [CareerBookmarkController::class, 'index']);
    Route::post('/career-bookmarks', [CareerBookmarkController::class, 'store']);
    Route::delete('/career-bookmarks/{careerID}', [CareerBookmarkController::class, 'destroy']);

    // Interviews
    Route::get('/interviews', [InterviewController::class, 'index']);
});

// ----------------------
// Skills, User Info, Search, etc.
// ----------------------
Route::get('/skills/{resumeID}', [SkillController::class, 'index']);
Route::post('/skills', [SkillController::class, 'store']);
Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

Route::delete('/user', [ApplicantController::class, 'destroy']);
Route::post('/update-password', [ApplicantController::class, 'updatePassword']);
Route::put('/user', [ApplicantController::class, 'update']);

Route::get('/search', [SearchController::class, 'search']);
Route::get('/training/{id}', [SearchController::class, 'getTraining']);
Route::get('/career/{id}', [SearchController::class, 'getCareer']);
Route::get('/organization/{id}', [SearchController::class, 'getOrganization']);
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