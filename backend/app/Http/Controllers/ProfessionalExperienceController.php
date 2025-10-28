<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfessionalExperience;
use App\Models\Resume;
use App\Models\Applicant;

class ProfessionalExperienceController extends Controller
{
    // ✅ Show all experiences of the applicant
    public function show(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $resume = Resume::where('applicantID', $applicant->applicantID)->first();

        if (!$resume) {
            return response()->json(['message' => 'Resume not found'], 404);
        }

        $experiences = ProfessionalExperience::where('resumeID', $resume->resumeID)->get();

        return response()->json($experiences, 200);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $resume = Resume::where('applicantID', $applicant->applicantID)->first();

        if (!$resume) {
            return response()->json([
                'error' => 'Failed to add experience. Make sure you have a resume first.'
            ], 400);
        }

        $validated = $request->validate([
            'jobTitle' => 'required|string|max:255',
            'companyName' => 'required|string|max:255',
            'companyAddress' => 'required|string|max:255',
            'startYear' => 'required|date',
            'endYear' => 'required|date|after_or_equal:startYear',
        ]);

        $experience = ProfessionalExperience::create([
            'jobTitle' => $validated['jobTitle'],
            'companyName' => $validated['companyName'],
            'companyAddress' => $validated['companyAddress'],
            'startYear' => $validated['startYear'],
            'endYear' => $validated['endYear'],
            'resumeID' => $resume->resumeID,
        ]);

        return response()->json($experience, 201);
    }

    // ✅ Update specific experience
    public function update(Request $request, $id)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $resume = Resume::where('applicantID', $applicant->applicantID)->first();

        if (!$resume) {
            return response()->json(['message' => 'Resume not found'], 404);
        }

        $experience = ProfessionalExperience::where('resumeID', $resume->resumeID)
            ->where('experienceID', $id)
            ->first();

        if (!$experience) {
            return response()->json(['message' => 'Experience not found'], 404);
        }

        $validated = $request->validate([
            'jobTitle' => 'nullable|string|max:255',
            'companyName' => 'nullable|string|max:255',
            'startYear' => 'nullable|integer',
            'endYear' => 'nullable|integer',
        ]);

        $experience->update($validated);

        return response()->json($experience, 200);
    }

    // ✅ Delete specific experience
    public function destroy(Request $request, $id)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $resume = Resume::where('applicantID', $applicant->applicantID)->first();

        if (!$resume) {
            return response()->json(['message' => 'Resume not found'], 404);
        }

        $experience = ProfessionalExperience::where('resumeID', $resume->resumeID)
            ->where('experienceID', $id)
            ->first();

        if (!$experience) {
            return response()->json(['message' => 'Experience not found'], 404);
        }

        $experience->delete();

        return response()->json(['message' => 'Experience deleted successfully'], 200);
    }
}