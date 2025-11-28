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

        // Log incoming data for debugging
        \Log::info('Experience store request data', [
            'startYear' => $request->input('startYear'),
            'endYear' => $request->input('endYear'),
            'startYear_type' => gettype($request->input('startYear')),
            'endYear_type' => gettype($request->input('endYear')),
        ]);

        // Manually validate and convert years before Laravel validation
        $startYear = $request->input('startYear');
        $endYear = $request->input('endYear');
        
        // Convert to integer if it's a string number
        if (is_string($startYear) && is_numeric($startYear)) {
            $startYear = (int) $startYear;
        }
        if (is_string($endYear) && is_numeric($endYear)) {
            $endYear = (int) $endYear;
        }
        
        // Validate years are integers in valid range
        if (!is_int($startYear) || $startYear < 1900 || $startYear > 2099) {
            return response()->json([
                'message' => 'Start year must be a valid year between 1900 and 2099.'
            ], 422);
        }
        
        if (!is_int($endYear) || $endYear < 1900 || $endYear > 2099) {
            return response()->json([
                'message' => 'End year must be a valid year between 1900 and 2099.'
            ], 422);
        }
        
        if ($endYear < $startYear) {
            return response()->json([
                'message' => 'End year must be greater than or equal to start year.'
            ], 422);
        }

        // Now validate other fields normally
        $validated = $request->validate([
            'jobTitle' => 'required|string|max:255',
            'companyName' => 'required|string|max:255',
            'companyAddress' => 'required|string|max:255',
        ]);

        // Convert year integers to date format (January 1st of that year)
        $startDate = $startYear . '-01-01';
        $endDate = $endYear . '-01-01';

        $experience = ProfessionalExperience::create([
            'jobTitle' => $validated['jobTitle'],
            'companyName' => $validated['companyName'],
            'companyAddress' => $validated['companyAddress'],
            'startYear' => $startDate,
            'endYear' => $endDate,
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

        // Manually validate and convert years before Laravel validation
        $startYear = $request->input('startYear');
        $endYear = $request->input('endYear');
        
        // Convert to integer if it's a string number
        if ($startYear !== null) {
            if (is_string($startYear) && is_numeric($startYear)) {
                $startYear = (int) $startYear;
            }
            // Validate year is integer in valid range
            if (!is_int($startYear) || $startYear < 1900 || $startYear > 2099) {
                return response()->json([
                    'message' => 'Start year must be a valid year between 1900 and 2099.'
                ], 422);
            }
        }
        
        if ($endYear !== null) {
            if (is_string($endYear) && is_numeric($endYear)) {
                $endYear = (int) $endYear;
            }
            // Validate year is integer in valid range
            if (!is_int($endYear) || $endYear < 1900 || $endYear > 2099) {
                return response()->json([
                    'message' => 'End year must be a valid year between 1900 and 2099.'
                ], 422);
            }
        }
        
        // Validate endYear >= startYear if both are provided
        if ($startYear !== null && $endYear !== null && $endYear < $startYear) {
            return response()->json([
                'message' => 'End year must be greater than or equal to start year.'
            ], 422);
        }

        // Now validate other fields normally
        $validated = $request->validate([
            'jobTitle' => 'nullable|string|max:255',
            'companyName' => 'nullable|string|max:255',
            'companyAddress' => 'nullable|string|max:255',
        ]);

        // Convert year integers to date format if provided
        if ($startYear !== null) {
            $validated['startYear'] = $startYear . '-01-01';
        }
        if ($endYear !== null) {
            $validated['endYear'] = $endYear . '-01-01';
        }

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