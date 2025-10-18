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

    // Get all registrants for a specific training
    public function getRegistrantsByTraining(Request $request, $trainingID)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //ensure only orgs can view registrants for their trainings
        if (!isset($user->organizationID)) {
            return response()->json(['message' => 'Only organizations can view registrants'], 403);
        }

        //verify the that the training belongs to the organization
        $training = \App\Models\Training::where('trainingID', $trainingID)
        ->where('organizationID', $user->organizationID)
        ->first();

        if (!$training) {
             return response()->json(['message' => 'Training not found or access denied'], 404);
        }


        $registrants = Registration::with('applicant')
        ->where('trainingID', $trainingID)
        ->get()
        ->map(function($r) {
            return [
                'id' => $r->registrationID,
                'applicantID' => $r->applicantID,
                'name' => $r->applicant->firstName . ' ' . $r->applicant->lastName,
                'status' => $r->registrationStatus,
                'dateRegistered' => $r->registrationDate ? $r->registrationDate->format('M d, Y') : null,
                'certificateTrackingID' => $r->certTrackingID,
                'certificateGivenDate' => $r->certGivenDate ? $r->certGivenDate->format('M d, Y') : null,
                'hasCertificate' => !is_null($r->certTrackingID) && !is_null($r->certGivenDate),
            ];
        });

        return response()->json($registrants);
    }
}