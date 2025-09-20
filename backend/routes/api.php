<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\OrganizationController;

Route::get('/organization', [OrganizationController::class, 'index']);

Route::post('/applicants', [ApplicantController::class, 'a_register']);
Route::post('applicants/login', [ApplicantController::class, 'login']);

Route::post('/organization', [OrganizationController::class, 'o_register']);
Route::post('/organizations/login', [OrganizationController::class, 'login']);

// Protected routes (require Sanctum auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // delete account
    Route::delete('/user', [ApplicantController::class, 'destroy']);
});


