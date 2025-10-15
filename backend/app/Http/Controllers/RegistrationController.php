<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

class RegistrationController extends Controller
{
    //list applicants's registrations
    public function index(Request $request)
    {
        $user = $request->authUser;

        $registrations = Registration::with('training')
            ->where('applicantID', $user->applicantID)
            ->get();

        return response()->json($registrations);
    }

    //register applicant
    public function store(Request $request)
    {
        $user = $request->authUser;

        $validated = $request->validate([
            'trainingID' => 'required|exists:training,trainingID', 
        ]);

        $trainingID = (int) $validated['trainingID'];

        //ensure not already registered
        $existing = Registration::where('applicantID', $user->applicantID)
            ->where('trainingID', $trainingID)
            ->first();

        if($existing){
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

    //cancel registration
    public function destroy(Request $request, int $id)
    {
        $user = $request->authUser;

        $registration = Registration::where('registrationID', $id)
            ->where('applicantID', $user->applicantID)
            ->first();
            
        if(!$registration){
            return response()->json(['message' => ' REGISTRATION NOT FOUND'], 404);
        }

        $registration->delete();

        return response()->json(['message' => 'REGISTRATION CANCELLED'], 200);
    }

}
