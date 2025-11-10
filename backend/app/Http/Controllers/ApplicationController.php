<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    //list applicant's applications
   public function index(Request $request)
{
    $user = $request->user();

    $apps = Application::with('career.organization') // eager load organization
        ->where('applicantID', $user->applicantID)
        ->get();

    return response()->json($apps->map(function($app) {
        return [
            'applicationID' => $app->applicationID,
            'careerID' => $app->careerID,
            'title' => $app->career->position ?? 'Career',
            'organizationName' => $app->career->organization->name ?? 'Unknown Organization',
            'interviewSchedule' => $app->interviewSchedule,
            'interviewMode' => $app->interviewMode,
            'interviewLink' => $app->interviewLink,
            'interviewLocation' => $app->interviewLocation,
            'detailsAndInstructions' => $app->career->detailsAndInstructions ?? null,
            'qualifications' => $app->career->qualifications ?? null,
            'requirements' => $app->career->requirements ?? null,
            'applicationLetterAddress' => $app->career->applicationLetterAddress ?? null,
            'deadlineOfSubmission' => $app->career->deadlineOfSubmission ?? null,
            'status' => $app->applicationStatus,
        ];
    }));
}

 public function career()
    {
        // Eager load the organization as well
        return $this->belongsTo(Career::class, 'careerID', 'careerID')
                    ->with('organization');
    }
    //create application
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'careerID' => 'required|exists:career,careerID',
            'requirements' => 'nullable|file|mimes:pdf|max:5120', //5mb
        ]);

        //prevent duplicates
        $existing = Application::where('applicantID', $user->applicantID)
            ->where('careerID', (int) $validated['careerID'])
            ->first();
        
        if($existing){
            return response()->json([
                'message' => 'ALREADY APPLIED',
                'applicationID' => $existing->applicationID,
            ], 409);
        }

        //store requirements pdf
        $requirementsPath = null;
        if($request->hasFile('requirements')){
            //uses public disk, ensure filesystems.php has public disk configured
            $requirementsPath = $request->file('requirements')->store('requirements', 'public');
        }

        $app = Application::create([
            'requirements' => $requirementsPath,
            'dateSubmitted' => Carbon::now(),
            'applicationStatus' => 'Applied',
    'interviewSchedule' => null,
            'interviewMode' => null,
            'interviewLocation' => null,
            'interviewLink' => null,

            'careerID' => (int) $validated['careerID'],
            'applicantID' => $user->applicantID,
            'organization' => $app->career->organization ?? 'Unknown Organization',
        ]);

        return response()->json([
            'message' => 'APPLICATION SUBMITTED SUCCESSFULLY!!!',
            'data' => $app,
        ], 201);
    }

    //withdraw application
    public function destroy(Request $request, int $id)
    {
        $user = $request->authUser;

        $app = Application::where('applicationID', $id)
            ->where('applicantID', $user->applicantID)
            ->first();

        if(!$app){
            return response()->json(['message' => 'APPLICATION NOT FOUND'], 404);
        }

        $app->delete();

        return response()->json(['message' => 'APPLICATION WITHDRAWN'], 200);
    }
public function viewRequirement($id)
{
    $application = Application::findOrFail($id);

    // Check if a requirement exists
    if (!$application->requirements) {
        return response()->json(['message' => 'No requirement found.'], 404);
    }

    // Convert BLOB to PDF response
    return response($application->requirements)
        ->header('Content-Type', 'application/pdf')
        ->header('Cache-Control', 'no-cache, must-revalidate')
        ->header('Content-Disposition', 'inline; filename="requirement.pdf"');
}



}
