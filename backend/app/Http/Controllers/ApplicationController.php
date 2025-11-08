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

        $apps = Application::with('career')
            ->where('applicantID', $user->applicantID)
            ->get();

            return response()->json($apps);
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


public function getApplicantsByCareer(Request $request, $careerID)
{
    $user = $request->user();
    
    if (!$user || !isset($user->organizationID)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    // Verify career belongs to organization
    $career = \App\Models\Career::where('careerID', $careerID)
        ->where('organizationID', $user->organizationID)
        ->first();
    
    if (!$career) {
        return response()->json(['message' => 'Career not found or access denied'], 404);
    }
    
    $applications = Application::with('applicant')
        ->where('careerID', $careerID)
        ->get()
        ->map(function($app) {
            return [
                'id' => $app->applicationID,
                'applicantID' => $app->applicantID,
                'name' => $app->applicant->firstName . ' ' . $app->applicant->lastName,
                'dateSubmitted' => $app->dateSubmitted ? $app->dateSubmitted->format('M d, Y') : null,
                'status' => $app->applicationStatus,
                'requirements' => $app->requirements,
                'interviewSchedule' => $app->interviewSchedule ? $app->interviewSchedule->format('Y-m-d H:i:s') : null,
                'interviewMode' => $app->interviewMode,
                'interviewLocation' => $app->interviewLocation,
                'interviewLink' => $app->interviewLink,
            ];
        });
    
    return response()->json($applications);
}

public function updateStatus(Request $request, $applicationID)
{
    $user = $request->user();
    
    if (!$user || !isset($user->organizationID)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    $application = Application::find($applicationID);
    if (!$application) {
        return response()->json(['message' => 'Application not found'], 404);
    }
    
    // Verify career belongs to organization
    $career = \App\Models\Career::where('careerID', $application->careerID)
        ->where('organizationID', $user->organizationID)
        ->first();
    
    if (!$career) {
        return response()->json(['message' => 'Access denied'], 403);
    }
    
    $validated = $request->validate([
        'status' => 'required|in:submitted,in review,for interview,accepted,rejected',
    ]);
    
    $application->update([
        'applicationStatus' => $validated['status'],
    ]);
    
    return response()->json([
        'message' => 'Status updated successfully',
        'data' => $application,
    ]);
}

public function updateInterview(Request $request, $applicationID)
{
    $user = $request->user();
    
    if (!$user || !isset($user->organizationID)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    $application = Application::find($applicationID);
    if (!$application) {
        return response()->json(['message' => 'Application not found'], 404);
    }
    
    // Verify career belongs to organization
    $career = \App\Models\Career::where('careerID', $application->careerID)
        ->where('organizationID', $user->organizationID)
        ->first();
    
    if (!$career) {
        return response()->json(['message' => 'Access denied'], 403);
    }
    
    $validated = $request->validate([
        'interviewSchedule' => 'required|date',
        'interviewMode' => 'required|in:On-Site,Online',
        'interviewLocation' => 'required_if:interviewMode,On-Site|nullable|string',
        'interviewLink' => 'required_if:interviewMode,Online|nullable|url',
    ]);
    
    $application->update([
        'interviewSchedule' => $validated['interviewSchedule'],
        'interviewMode' => $validated['interviewMode'],
        'interviewLocation' => $validated['interviewLocation'] ?? null,
        'interviewLink' => $validated['interviewLink'] ?? null,
        'applicationStatus' => 'for interview',
    ]);
    
    return response()->json([
        'message' => 'Interview scheduled successfully',
        'data' => $application,
    ]);
}

public function getRequirements(Request $request, $applicationID)
{
    $user = $request->user();
    
    if (!$user || !isset($user->organizationID)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    $application = Application::find($applicationID);
    if (!$application) {
        return response()->json(['message' => 'Application not found'], 404);
    }
    
    // Verify career belongs to organization
    $career = \App\Models\Career::where('careerID', $application->careerID)
        ->where('organizationID', $user->organizationID)
        ->first();
    
    if (!$career) {
        return response()->json(['message' => 'Access denied'], 403);
    }
    
    if (!$application->requirements) {
        return response()->json(['message' => 'Requirements not found'], 404);
    }
    
    $path = storage_path('app/public/' . $application->requirements);
    
    if (!file_exists($path)) {
        return response()->json(['message' => 'File not found'], 404);
    }
    
    return response()->file($path);
}


}
