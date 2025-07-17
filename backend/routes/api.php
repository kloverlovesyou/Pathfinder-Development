<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;

Route::post('/applicants', [ApplicantController::class, 'store']);