<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

//no auth required
Route::get('/trainings', [TrainingController::class, 'index']);
Route::get('/careers', [CareerController::class, 'index']);

//auth required
Route::middleware('auth.custom')->group(function () {
    Route::post('/trainings', [TrainingController::class, 'store']);
    Route::post('/careers', [CareerController::class, 'store']);

    //registrations
    Route::get('/registrations', [\App\Http\Controllers\RegistrationController::class, 'index']);
    Route::post('/registrations', [\App\Http\Controllers\RegistrationController::class, 'store']);
    Route::delete('/registrations/{id}', [\App\Http\Controllers\RegistrationController::class, 'destroy']);

    //applications
    // Applications
    Route::get('/applications', [\App\Http\Controllers\ApplicationController::class, 'index']);
    Route::post('/applications', [\App\Http\Controllers\ApplicationController::class, 'store']);
    Route::delete('/applications/{id}', [\App\Http\Controllers\ApplicationController::class, 'destroy']);

});


Route::get('/organization', [OrganizationController::class, 'index']);

Route::post('/a_register', [AuthController::class, 'a_register']);
Route::post('/o_register', [AuthController::class, 'o_register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/applicants', [ApplicantController::class, 'a_register']);
Route::post('applicants/login', [ApplicantController::class, 'login']);

Route::post('/organization', [OrganizationController::class, 'o_register']);
Route::post('/organizations/login', [OrganizationController::class, 'login']);

// ✅ Resume routes
Route::post('/resume', [ResumeController::class, 'store']);
Route::get('/resume', [ResumeController::class, 'show']);
Route::delete('/resume', [ResumeController::class, 'destroy']);

// ✅ Professional Experience routes
Route::middleware('auth.custom')->group(function () {
    Route::get('/experiences', [ProfessionalExperienceController::class, 'show']);
    Route::post('/experiences', [ProfessionalExperienceController::class, 'store']);
    Route::put('/experiences/{id}', [ProfessionalExperienceController::class, 'update']);
    Route::delete('/experiences/{id}', [ProfessionalExperienceController::class, 'destroy']);
});
// ✅ Protected routes (require Sanctum auth)
Route::get('/user', function (Request $request) {
    $token = $request->bearerToken();
    $user = \App\Models\Applicant::where('api_token', $token)->first();
    if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
    return $user;
});

// ✅ Education routes
Route::middleware('auth.custom')->group(function () {
    Route::get('/education', [EducationController::class, 'show']);
    Route::post('/education', [EducationController::class, 'store']);
    Route::put('/education/{id}', [EducationController::class, 'update']);
    Route::delete('/education/{id}', [EducationController::class, 'destroy']);
});

// ✅ Skill routes
Route::get('/skills/{resumeID}', [SkillController::class, 'index']);
Route::post('/skills', [SkillController::class, 'store']);
Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

Route::delete('/user', [ApplicantController::class, 'destroy']);

Route::middleware('auth.custom')->group(function () {
    Route::get('/bookmarks', [TrainingBookmarkController::class, 'index']);
    Route::post('/bookmarks', [TrainingBookmarkController::class, 'store']);
    Route::delete('/bookmarks/{trainingID}', [TrainingBookmarkController::class, 'destroy']);
});

Route::middleware('auth.custom')->group(function () {
    Route::get('/trainings/{trainingID}/registrants', [RegistrationController::class, 'getRegistrantsByTraining']);
});

