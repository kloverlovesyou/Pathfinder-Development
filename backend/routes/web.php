<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\TrainingController;


Route::get('/attendance/submit', function (Request $request, TrainingController $controller) {
    // Call the same function your API uses
    $response = $controller->attendanceCheckin($request);

    // Convert JSON to readable message for user
    if ($response->status() === 200) {
        return "<h2 style='font-family: Arial; color: green;'>✅ Attendance Recorded Successfully!</h2>";
    } else {
        return "<h2 style='font-family: Arial; color: red;'>❌ " . $response->getData()->message . "</h2>";
    }
});

Route::get('/', function () {
    return view('welcome');
});
