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
            'requirement_directory' => $app->career->requirement_directory ?? null,
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
        'requirement_directory' => 'required|file|mimes:pdf|max:5120', // PDF required, max 5MB
    ], [
        'requirement_directory.required' => 'PDF file is required',
        'requirement_directory.mimes' => 'Only PDF files are allowed',
        'requirement_directory.max' => 'File size must be less than 5MB',
    ]);
    
    // Log file info for debugging
    if ($request->hasFile('requirement_directory')) {
        $file = $request->file('requirement_directory');
        Log::info('File received in request', [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'is_valid' => $file->isValid(),
        ]);
    } else {
        Log::warning('No file received in request', [
            'has_file' => $request->hasFile('requirement_directory'),
            'all_files' => $request->allFiles(),
        ]);
    }

    // prevent duplicates
    $existing = Application::where('applicantID', $user->applicantID)
        ->where('careerID', (int) $validated['careerID'])
        ->first();

    if ($existing) {
        return response()->json([
            'message' => 'ALREADY APPLIED',
            'applicationID' => $existing->applicationID,
        ], 409);
    }

    // store uploaded file
    $requirementsPath = null;
    if ($request->hasFile('requirement_directory')) {
        $file = $request->file('requirement_directory');
        
        // Ensure the requirements directory exists with proper permissions
        $requirementsDir = storage_path('app/public/requirements');
        if (!file_exists($requirementsDir)) {
            if (!mkdir($requirementsDir, 0755, true)) {
                Log::error('Failed to create requirements directory', ['path' => $requirementsDir]);
                return response()->json(['message' => 'Failed to create storage directory'], 500);
            }
        }
        
        // Check if directory is writable
        if (!is_writable($requirementsDir)) {
            Log::error('Requirements directory is not writable', ['path' => $requirementsDir]);
            return response()->json(['message' => 'Storage directory is not writable'], 500);
        }
        
        try {
            // Store the file - putFile returns the path relative to the disk root
            $requirementsPath = Storage::disk('public')->putFile('requirements', $file);
            
            if (!$requirementsPath) {
                throw new \Exception('Failed to store file - putFile returned null');
            }
            
            // Verify file was actually saved - check both possible locations
            $fullPath = Storage::disk('public')->path($requirementsPath);
            $alternatePath = storage_path('app/public/' . $requirementsPath);
            
            $fileExists = file_exists($fullPath) || file_exists($alternatePath);
            
            if (!$fileExists) {
                throw new \Exception('File was not saved to disk. Expected at: ' . $fullPath . ' or ' . $alternatePath);
            }
            
            // Use the path that actually exists
            $actualPath = file_exists($fullPath) ? $fullPath : $alternatePath;

            Log::info('Requirements file stored successfully', [
                'stored_path' => $requirementsPath,
                'full_path' => $actualPath,
                'file_exists' => $fileExists,
                'file_size' => filesize($actualPath),
                'original_name' => $file->getClientOriginalName(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store requirements file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $file->getClientOriginalName() ?? 'unknown',
                'file_size' => $file->getSize() ?? 0,
            ]);
            // Return error - file is required
            return response()->json([
                'message' => 'Failed to save file: ' . $e->getMessage()
            ], 500);
        }
    }

    try {
        $app = Application::create([
            'requirement_directory' => $requirementsPath,
            'dateSubmitted' => Carbon::now(),
            'applicationStatus' => 'Submitted',

            'interviewSchedule' => null,
            'interviewMode' => null,
            'interviewLocation' => null,
            'interviewLink' => null,

            'careerID' => (int) $validated['careerID'],
            'applicantID' => $user->applicantID,
        ]);

        // Get ID immediately from attributes to avoid property access issues
        $appId = $app->getAttributes()['applicationID'] ?? null;
        // Unset model immediately to prevent any further access
        unset($app);
        
        // Build response from values we already have
        return response()->json([
            'message' => 'APPLICATION SUBMITTED SUCCESSFULLY!!!',
            'data' => [
                'applicationID' => $appId,
                'careerID' => (int) $validated['careerID'],
                'applicantID' => $user->applicantID,
                'requirement_directory' => $requirementsPath,
                'dateSubmitted' => Carbon::now()->toDateTimeString(),
                'applicationStatus' => 'Submitted',
                'interviewSchedule' => null,
                'interviewMode' => null,
                'interviewLocation' => null,
                'interviewLink' => null,
            ],
        ], 201);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Application submission error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'careerID' => $validated['careerID'] ?? null,
            'applicantID' => $user->applicantID ?? null,
        ]);
        
        // Check if application was actually created (might have been saved before error)
        $existing = Application::where('applicantID', $user->applicantID)
            ->where('careerID', (int) ($validated['careerID'] ?? 0))
            ->first();
        
        if ($existing) {
            // Application was saved, return success even though there was an error
            return response()->json([
                'message' => 'APPLICATION SUBMITTED SUCCESSFULLY!!!',
                'data' => [
                    'applicationID' => $existing->applicationID,
                    'careerID' => (int) $validated['careerID'],
                    'applicantID' => $user->applicantID,
                ],
            ], 201);
        }
        
        // If application wasn't saved, re-throw the error
        throw $e;
    }
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

    if (!$application->requirement_directory) {
        return response()->json(['message' => 'No requirement found.'], 404);
    }

    $filePath = storage_path('app/public/' . $application->requirement_directory);

    if (!file_exists($filePath)) {
        return response()->json(['message' => 'File not found'], 404);
    }

    return response()->file($filePath);
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
                    'requirement_directory' => $app->requirement_directory,
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
    $user = $request->user();

    $application = Application::find($applicationID);
    if (!$application) {
        return response()->json(['message' => 'Application not found'], 404);
    }

    if ($application->career->organizationID !== $user->organizationID) {
        return response()->json(['message' => 'Access denied'], 403);
    }

    $path = $application->requirement_directory;

    if (!$path || !Storage::disk('public')->exists($path)) {
        return response()->json(['message' => 'File not found'], 404);
    }

    try {
        return Storage::disk('public')->download($path, basename($path), [
            'Content-Type' => 'application/pdf'
        ]);
    } catch (\Exception $e) {
        \Log::error('Download failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to download file',
            'error' => $e->getMessage()
        ], 500);
    }
}




}
