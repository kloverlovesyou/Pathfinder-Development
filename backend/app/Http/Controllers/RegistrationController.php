<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Carbon;

class RegistrationController extends Controller
{
    // List applicant's registrations
    public function index(Request $request)
    {
        $user = $request->user(); // ✅ Use Laravel's user resolver

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $registrations = Registration::with('training')
            ->where('applicantID', $user->applicantID)
            ->get();

        return response()->json($registrations);
    }

    // Register applicant
    public function store(Request $request)
    {
        $user = $request->user(); // ✅ Use user resolver

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'trainingID' => 'required|exists:training,trainingID',
        ]);

        $trainingID = (int) $validated['trainingID'];

        // Ensure not already registered
        $existing = Registration::where('applicantID', $user->applicantID)
            ->where('trainingID', $trainingID)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'ALREADY REGISTERED FOR THIS TRAINING',
                'registrationID' => $existing->registrationID,
            ], 409);
        }

        $registration = Registration::create([
            'registrationDate' => Carbon::now(),
            'registrationStatus' => 'Registered',
            'certTrackingID' => null,
            'certGivenDate' => null,
            'certificate' => null,
            'trainingID' => $trainingID,
            'applicantID' => $user->applicantID,
        ]);

        return response()->json([
            'message' => 'REGISTRATION SUCCESSFUL!!!',
            'data' => $registration,
        ], 201);
    }

    // Cancel registration
    public function destroy(Request $request, int $id)
    {
        $user = $request->user(); // ✅ Use user resolver

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $registration = Registration::where('registrationID', $id)
            ->where('applicantID', $user->applicantID)
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'REGISTRATION NOT FOUND'], 404);
        }

        $registration->delete();

        return response()->json(['message' => 'REGISTRATION CANCELLED'], 200);
    }
}