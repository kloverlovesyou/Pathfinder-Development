<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resume;
use App\Models\Applicant;

class ResumeController extends Controller
{
    // Save or Update Resume
public function show(Request $request)
{
    try {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }
        
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $resume = Resume::where('applicantID', $applicant->applicantID)->first();

        return response()->json($resume ?? null, 200);
    } catch (\Exception $e) {
        \Log::error('Error fetching resume: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => 'Failed to fetch resume',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function store(Request $request)
{
    $token = $request->bearerToken();
    $applicant = Applicant::where('api_token', $token)->first();

    if (!$applicant) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'summary' => 'nullable|string|max:10000',
        'professionalLink' => 'nullable|string|max:1000',
    ]);

    // Truncate professionalLink if it's too long (safety check)
    $professionalLink = $validated['professionalLink'] ?? '';
    if (strlen($professionalLink) > 1000) {
        $professionalLink = substr($professionalLink, 0, 1000);
    }

    // 👇 update if exists, otherwise create
    $resume = Resume::updateOrCreate(
        ['applicantID' => $applicant->applicantID],
        [
            'summary' => $validated['summary'] ?? '',
            'professionalLink' => $professionalLink,
        ]
    );

    return response()->json($resume, 200);
}
    // Delete Resume
    public function destroy(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Resume::where('applicantID', $applicant->applicantID)->delete();

        return response()->json(['message' => 'Resume deleted successfully'], 200);
    }
}