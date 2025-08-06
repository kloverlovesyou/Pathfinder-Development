<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\OrganizationController;
Route::middleware('auth:sanctum')->get('/organizations', [OrganizationController::class, 'index']);
Route::post('/applicants', [ApplicantController::class, 'store']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [ApplicantController::class, 'login']);
