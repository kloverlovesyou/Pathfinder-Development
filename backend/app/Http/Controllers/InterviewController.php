<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Carbon;

class InterviewController extends Controller
{
    // Schedule or update an interview for a given application
    public function schedule(Request $request, int $applicationID)
    {
        $validated = $request->validate([
            'interviewSchedule' => 'required|date',
            'interviewMode' => 'required|string|in:Online,On-site',
            'interviewLocation' => 'nullable|string',
            'interviewLink' => 'nullable|url',
        ]);

        $app = Application::find($applicationID);

        if (!$app) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Update the interview info
        $app->update([
            'interviewSchedule' => $validated['interviewSchedule'],
            'interviewMode' => $validated['interviewMode'],
            'interviewLocation' => $validated['interviewLocation'] ?? null,
            'interviewLink' => $validated['interviewLink'] ?? null,
        ]);

        return response()->json([
            'message' => 'Interview scheduled successfully',
            'data' => $app
        ]);
    }

    public function index(Request $request)
{
    $user = $request->user(); // get logged-in applicant

    $apps = Application::with('career')
        ->where('applicantID', $user->applicantID)
        ->get();

    return response()->json($apps->map(function ($app) {
        return [
            'id' => $app->applicationID,
            'careerID' => $app->careerID,
            'title' => $app->career->position ?? 'Career',
            'organization' => $app->career->organization ?? 'Unknown Organization',
            'interviewSchedule' => $app->interviewSchedule,
            'interviewMode' => $app->interviewMode,
            'interviewLink' => $app->interviewLink,
            'interviewLocation' => $app->interviewLocation,
            'detailsAndInstructions' => $app->career->detailsAndInstructions ?? null,
            'qualifications' => $app->career->qualifications ?? null,
            'requirements' => $app->career->requirements ?? null,
            'applicationLetterAddress' => $app->career->applicationLetterAddress ?? null,
            'deadlineOfSubmission' => $app->career->deadlineOfSubmission ?? null,
        ];
    }));
}

    
}
