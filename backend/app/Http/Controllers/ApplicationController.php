<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            $file = $request->file('requirements');
            // Use Storage facade to ensure correct path format
            $requirementsPath = Storage::disk('public')->putFile('requirements', $file);
            
            // Log the stored path for debugging
            Log::info('Requirements file stored', [
                'stored_path' => $requirementsPath,
                'full_path' => Storage::disk('public')->path($requirementsPath),
                'file_exists' => Storage::disk('public')->exists($requirementsPath)
            ]);
        }

        $app = Application::create([
            'requirements' => $requirementsPath,
            'dateSubmitted' => Carbon::now(),
            'applicationStatus' => 'Submitted',
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



    public function getApplicantsByCareer(Request $request, $careerID)
    {
        $user = $request->user();
        
        // Check if user is an Organization
        if (!$user || !($user instanceof \App\Models\Organization)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
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
                $applicantName = 'Unknown';
                if ($app->applicant) {
                    $applicantName = trim(($app->applicant->firstName ?? '') . ' ' . ($app->applicant->lastName ?? ''));
                    if (empty($applicantName)) {
                        $applicantName = 'Unknown';
                    }
                }
                
                return [
                    'id' => $app->applicationID,
                    'applicantID' => $app->applicantID,
                    'name' => $applicantName,
                    'dateSubmitted' => $app->dateSubmitted ? $app->dateSubmitted->format('M d, Y') : null,
                    'status' => $app->applicationStatus ? strtolower($app->applicationStatus) : 'submitted',
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
        
        // Check if user is an Organization
        if (!$user || !($user instanceof \App\Models\Organization)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
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
        
        // Check if user is an Organization
        if (!$user || !($user instanceof \App\Models\Organization)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
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
            'interviewSchedule' => 'required|date_format:Y-m-d H:i:s',
            'interviewMode' => 'required|in:On-Site,Online',
            'interviewLocation' => 'required_if:interviewMode,On-Site|nullable|string',
            'interviewLink' => 'required_if:interviewMode,Online|nullable|url',
        ]);
        
        try {
            $application->update([
                'interviewSchedule' => $validated['interviewSchedule'],
                'interviewMode' => $validated['interviewMode'],
                'interviewLocation' => $validated['interviewLocation'] ?? null,
                'interviewLink' => $validated['interviewLink'] ?? null,
                'applicationStatus' => 'for interview',
            ]);
            
            // Refresh the model to get updated data
            $application->refresh();
            
            return response()->json([
                'message' => 'Interview scheduled successfully',
                'success' => true,
                'data' => [
                    'applicationID' => $application->applicationID,
                    'applicationStatus' => $application->applicationStatus,
                    'interviewSchedule' => $application->interviewSchedule ? $application->interviewSchedule->format('Y-m-d H:i:s') : null,
                    'interviewMode' => $application->interviewMode,
                    'interviewLocation' => $application->interviewLocation,
                    'interviewLink' => $application->interviewLink,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save interview schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRequirements(Request $request, $applicationID)
    {
        $user = $request->user(); // Must be Organization (handled by middleware or check here)
    
        $application = Application::find($applicationID);
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }
    
        // Ownership check (can be offloaded to middleware)
        if ($application->career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Access denied'], 403);
        }
    
        if (!$application->requirements) {
            Log::warning('Requirements missing', [
                'application_id' => $applicationID,
                'raw_path' => $application->requirements,
            ]);
            return response()->json(['message' => 'Requirements not found'], 404);
        }
    
        $path = $application->requirements;
    
        try {
            if (!Storage::disk('public')->exists($path)) {
                Log::error('Requirements file not found', [
                    'application_id' => $applicationID,
                    'path' => $path,
                ]);
                return response()->json(['message' => 'File not found on disk'], 404);
            }
    
            $fullPath = Storage::disk('public')->path($path);
    
            Log::info('Serving applicant requirements', [
                'application_id' => $applicationID,
                'path' => $path,
            ]);
    
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.basename($path).'"'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error serving requirements', [
                'application_id' => $applicationID,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error serving file'], 500);
        }
    }
    


}
