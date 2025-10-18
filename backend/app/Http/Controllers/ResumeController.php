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
    $token = $request->bearerToken();
    $applicant = Applicant::where('api_token', $token)->first();

    if (!$applicant) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $resume = Resume::where('applicantID', $applicant->applicantID)->first();

    return response()->json($resume, 200);
}

public function store(Request $request)
{
    $token = $request->bearerToken();
    $applicant = Applicant::where('api_token', $token)->first();

    if (!$applicant) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'summary' => 'nullable|string',
        'professionalLink' => 'nullable|string',
    ]);

    // 👇 update if exists, otherwise create
    $resume = Resume::updateOrCreate(
        ['applicantID' => $applicant->applicantID],
        [
            'summary' => $validated['summary'] ?? '',
            'professionalLink' => $validated['professionalLink'] ?? '',
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