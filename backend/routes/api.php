<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    ApplicantController,
    OrganizationController,
    AuthController,
    ResumeController,
    ApplicationController,
    RegistrationController,
    ProfessionalExperienceController,
    EducationController,
    SkillController,
    TrainingController,
    CareerController,
    TrainingBookmarkController,
    CertificateController,
    CareerBookmarkController,
    SearchController,
    MyActivityController,
    EventController
};

// ----------------------
// Public routes
// ----------------------
Route::get('/trainings', [TrainingController::class, 'index']);
Route::get('/careers', [CareerController::class, 'index']);
Route::get('/organization', [OrganizationController::class, 'index']);

// Auth routes
Route::post('/a_register', [AuthController::class, 'a_register']);
Route::post('/o_register', [AuthController::class, 'o_register']);
Route::post('/login', [AuthController::class, 'login']);

// Applicant routes
Route::post('/applicants', [ApplicantController::class, 'a_register']);
Route::post('/applicants/login', [ApplicantController::class, 'login']);

// Organization routes
Route::post('/organization', [OrganizationController::class, 'o_register']);
Route::post('/organizations/login', [OrganizationController::class, 'login']);

// Resume (authenticated)
Route::middleware('auth.custom')->group(function () {
    Route::post('/resume', [ResumeController::class, 'store']);
    Route::get('/resume', [ResumeController::class, 'show']);
    Route::delete('/resume', [ResumeController::class, 'destroy']);
});

// ----------------------
// Protected routes (auth.custom)
// ----------------------
Route::middleware('auth.custom')->group(function () {

    // Trainings
    Route::post('/trainings', [TrainingController::class, 'store']);
    Route::post('/trainings/generate-qr', [TrainingController::class, 'generateQRCode']);
    Route::get('/attendance/checkin', [TrainingController::class, 'attendanceCheckin']);
    
    // Careers
    Route::post('/careers', [CareerController::class, 'store']);

    // Registrations
    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::post('/registrations', [RegistrationController::class, 'store']);
    Route::delete('/registrations/{id}', [RegistrationController::class, 'destroy']);
    Route::get('/trainings/{trainingID}/registrants', [RegistrationController::class, 'getRegistrantsByTraining']);

    // Applications
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);

    // Certificates
    Route::get('/certificates/{applicantID}', [CertificateController::class, 'index']);
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    Route::patch('/certificates/{id}/toggle', [CertificateController::class, 'toggleSelection']); // ✅ toggle select
    Route::get('/certificates/{applicantID}/selected', [CertificateController::class, 'selectedCertificates']); // ✅ get selected only

    // Experience
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
});

// ----------------------
// Resume, skills, user info (can be refined further)
// ----------------------
Route::get('/skills/{resumeID}', [SkillController::class, 'index']);
Route::post('/skills', [SkillController::class, 'store']);
Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

Route::delete('/user', [ApplicantController::class, 'destroy']);

Route::middleware('auth.custom')->group(function () {
    Route::get('/bookmarks', [TrainingBookmarkController::class, 'index']);
    Route::post('/bookmarks', [TrainingBookmarkController::class, 'store']);
    Route::delete('/bookmarks/{trainingID}', [TrainingBookmarkController::class, 'destroy']);
});

Route::get('/search', [SearchController::class, 'search']);
Route::get('/training/{id}', [SearchController::class, 'getTraining']);
Route::get('/career/{id}', [SearchController::class, 'getCareer']);
Route::get('/organization/{id}', [SearchController::class, 'getOrganization']);


// Optional: Get authenticated user by token
Route::get('/user', function (Request $request) {
    $token = $request->bearerToken();
    $user = \App\Models\Applicant::where('api_token', $token)->first();
    if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
    return $user;
});

Route::get('/my-activities/{applicantID}', [MyActivityController::class, 'getMyActivities']);
Route::get('/calendar/{applicantID}', [EventController::class, 'getUserEvents']);

