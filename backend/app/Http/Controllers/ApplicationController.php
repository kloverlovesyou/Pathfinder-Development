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
    // Log immediately to verify method is called
    \Log::info('=== APPLICATION STORE METHOD CALLED ===', [
        'timestamp' => now()->toDateTimeString(),
        'method' => $request->method(),
        'url' => $request->fullUrl(),
    ]);
    
    $user = $request->user();

    // Log all request data for debugging
    \Log::info('Application submission request', [
        'has_file' => $request->hasFile('requirement_directory'),
        'all_files' => array_keys($request->allFiles()),
        'careerID' => $request->input('careerID'),
        'content_type' => $request->header('Content-Type'),
        'request_keys' => array_keys($request->all()),
        'all_input' => $request->all(),
        'files_count' => count($request->allFiles())
    ]);

  if ($request->hasFile('requirement_directory')) {
        $file = $request->file('requirement_directory');
        Log::info('File received', [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'is_valid' => $file->isValid(),
            'error' => $file->getError()
        ]);
    } else {
        Log::warning('No file received in request', [
            'all_input_keys' => array_keys($request->all()),
            'files_in_request' => $request->allFiles()
        ]);
    }

    $validated = $request->validate([
        'careerID' => 'required|exists:career,careerID',
        'requirement_directory' => 'nullable|file|mimes:pdf|max:5120',
    ]);

    // Log after validation to see if file is still present
    Log::info('After validation check', [
        'has_file' => $request->hasFile('requirement_directory'),
        'validated_keys' => array_keys($validated)
    ]);

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
    
    \Log::info('About to check for file', [
        'has_file_check' => $request->hasFile('requirement_directory'),
        'file_method_result' => $request->file('requirement_directory') ? 'exists' : 'null',
        'all_files_debug' => array_keys($request->allFiles())
    ]);
    
  if ($request->hasFile('requirement_directory')) {
    \Log::info('File check passed, entering file upload block');
    try {
        $file = $request->file('requirement_directory');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Ensure the requirement_directory exists in public/storage
        $destinationPath = public_path('storage/requirement_directory');
        
        Log::info('Attempting to store file', [
            'destination' => $destinationPath,
            'filename' => $filename,
            'file_size' => $file->getSize(),
            'file_valid' => $file->isValid(),
            'public_path' => public_path(),
        ]);

        if (!file_exists($destinationPath)) {
            $created = mkdir($destinationPath, 0755, true);
            Log::info('Directory creation attempt', [
                'path' => $destinationPath,
                'created' => $created,
                'exists' => file_exists($destinationPath),
                'writable' => is_writable(dirname($destinationPath))
            ]);
        }

        // Move the file to public/storage/requirement_directory
        $fullDestinationPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
        
        // Ensure destination directory is writable
        if (!is_writable($destinationPath)) {
            Log::error('Destination directory is not writable', [
                'destination' => $destinationPath,
                'permissions' => substr(sprintf('%o', fileperms($destinationPath)), -4)
            ]);
            throw new \Exception('Destination directory is not writable');
        }
        
        // Try using Laravel's move method first
        try {
            $movedFile = $file->move($destinationPath, $filename);
        } catch (\Exception $moveException) {
            // Fallback to PHP's native move_uploaded_file
            Log::warning('Laravel move failed, trying native move_uploaded_file', [
                'error' => $moveException->getMessage()
            ]);
            
            $tempPath = $file->getPathname();
            if (!move_uploaded_file($tempPath, $fullDestinationPath)) {
                Log::error('Native move_uploaded_file also failed', [
                    'temp_path' => $tempPath,
                    'destination' => $fullDestinationPath,
                    'error' => error_get_last()
                ]);
                throw new \Exception('Failed to move uploaded file: ' . (error_get_last()['message'] ?? 'Unknown error'));
            }
        }
        
        // Verify file was actually moved
        if (file_exists($fullDestinationPath)) {
            // Store relative path in DB (storage/requirement_directory/filename)
            $requirementsPath = 'storage/requirement_directory/' . $filename;
            $fullPath = public_path($requirementsPath);

            Log::info('Requirements file stored successfully', [
                'stored_path' => $requirementsPath,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
            ]);
        } else {
            Log::error('File move failed - file does not exist at destination', [
                'destination' => $destinationPath,
                'full_destination' => $fullDestinationPath,
                'filename' => $filename,
                'temp_path' => $file->getPathname(),
                'error' => error_get_last()
            ]);
            throw new \Exception('Failed to move uploaded file to destination directory');
        }
    } catch (\Exception $e) {
        Log::error('Error storing requirement file', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        // Re-throw exception to prevent application creation if file upload fails
        return response()->json([
            'message' => 'Failed to upload requirement file: ' . $e->getMessage(),
            'error' => 'FILE_UPLOAD_FAILED'
        ], 500);
    }
}


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

    if (!$application->requirement_directory) {
        return response()->json(['message' => 'No requirement found.'], 404);
    }

    // Build full path to public/storage/requirement_directory
    $filePath = public_path($application->requirement_directory);

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

    if (!$path) {
        return response()->json(['message' => 'File not found'], 404);
    }

    // Build full path to public/storage/requirement_directory
    $fullPath = public_path($path);

    if (!file_exists($fullPath)) {
        return response()->json(['message' => 'File not found'], 404);
    }

    try {
        return response()->download($fullPath, basename($path), [
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
