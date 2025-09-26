<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\AuthController;

Route::get('/organization', [OrganizationController::class, 'index']);

Route::post('/a_register', [AuthController::class, 'a_register']);
Route::post('/o_register', [AuthController::class, 'o_register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/applicants', [ApplicantController::class, 'a_register']);
Route::post('applicants/login', [ApplicantController::class, 'login']);

Route::post('/organization', [OrganizationController::class, 'o_register']);
Route::post('/organizations/login', [OrganizationController::class, 'login']);

// Protected routes (require Sanctum auth)
Route::get('/user', function (Request $request) {
    $token = $request->bearerToken();
    $user = \App\Models\Applicant::where('api_token', $token)->first();
    if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
    return $user;
});

Route::delete('/user', [ApplicantController::class, 'destroy']);

