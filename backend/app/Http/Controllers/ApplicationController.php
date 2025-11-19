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
    // Log immediately - even before try block
    error_log('=== APPLICATION STORE METHOD CALLED ===');
    Log::info('=== APPLICATION STORE METHOD CALLED ===', [
        'has_file' => $request->hasFile('requirement_directory'),
        'all_files' => array_keys($request->allFiles()),
        'careerID' => $request->input('careerID'),
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'content_type' => $request->header('Content-Type'),
    ]);
    
    try {
        $user = $request->user();
        
        if (!$user) {
            Log::error('User is null in store method');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!isset($user->applicantID)) {
            Log::error('User does not have applicantID', ['user_type' => get_class($user)]);
            return response()->json(['message' => 'User is not an applicant'], 403);
        }

        // Check if file path is provided (from Supabase upload) or file is uploaded directly
        $filePath = $request->input('requirement_directory');
        $hasFileUpload = $request->hasFile('requirement_directory');
        
        if ($hasFileUpload) {
            $file = $request->file('requirement_directory');
            Log::info('File received (direct upload)', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
        } elseif ($filePath) {
            Log::info('File path received (from Supabase)', [
                'path' => $filePath,
            ]);
        } else {
            Log::warning('No file or file path received in request', [
                'all_input_keys' => array_keys($request->all()),
            ]);
        }

        $validated = $request->validate([
            'careerID' => 'required|exists:career,careerID',
            'requirement_directory' => 'nullable', // Can be file path (string) or file upload
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

        // Get file path - either from Supabase upload (string) or handle direct file upload
        $requirementsPath = null;
        
        if ($filePath) {
            // File was already uploaded to Supabase by frontend, just use the path
            $requirementsPath = $filePath;
            Log::info('Using file path from Supabase upload', [
                'path' => $requirementsPath,
            ]);
        } elseif ($hasFileUpload) {
            // Legacy: Direct file upload (fallback for old clients)
            Log::warning('Direct file upload detected - consider using Supabase upload from frontend');
            // For now, skip direct uploads - frontend should handle Supabase upload
            $requirementsPath = null;
        }
        
        // OLD CODE - Keep for reference but not used when frontend uploads to Supabase
        /*
      if ($request->hasFile('requirement_directory')) {
        try {
            $file = $request->file('requirement_directory');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store in Supabase Storage using native API
            $storagePath = 'requirement_directory/' . $filename;
            
            // Upload to Supabase Storage using HTTP client
            $supabaseUrl = env('SUPABASE_URL', 'https://hmevengvfponcwslnyye.supabase.co');
            // Remove any path suffixes that might be in the URL
            $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
            $supabaseUrl = rtrim($supabaseUrl, '/'); // Remove trailing slash
            // Use service_role key for uploads (has full permissions)
            // Fallback to SUPABASE_KEY if SUPABASE_SECRET is not set
            $supabaseKey = env('SUPABASE_SECRET') ?: env('SUPABASE_KEY');
            $bucket = env('SUPABASE_BUCKET');
            
            if (!$supabaseKey || !$bucket) {
                Log::error('Supabase credentials missing', [
                    'has_secret' => !empty(env('SUPABASE_SECRET')),
                    'has_key' => !empty(env('SUPABASE_KEY')),
                    'has_bucket' => !empty($bucket)
                ]);
                throw new \Exception('Supabase Storage not configured - need SUPABASE_SECRET (service_role key) or SUPABASE_KEY');
            }
            
            $fileContents = file_get_contents($file->getPathname());
            $fileMimeType = $file->getMimeType();
            
            // Upload to Supabase Storage
            // Note: Bucket names in Supabase are case-sensitive
            // Keep original bucket name (don't force lowercase)
            $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$storagePath}";
            
            Log::info('Attempting Supabase upload', [
                'upload_url' => $uploadUrl,
                'bucket' => $bucket,
                'storage_path' => $storagePath,
                'file_size' => strlen($fileContents),
                'mime_type' => $fileMimeType,
            ]);
            
            $client = new \GuzzleHttp\Client();
            try {
                $response = $client->request('POST', $uploadUrl, [
                    'headers' => [
                        'Authorization' => "Bearer {$supabaseKey}",
                        'Content-Type' => $fileMimeType,
                        'x-upsert' => 'true', // Overwrite if exists
                    ],
                    'body' => $fileContents,
                ]);
                
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
                
                Log::info('Supabase upload response', [
                    'status' => $statusCode,
                    'response' => $responseBody,
                ]);
                
                if ($statusCode !== 200 && $statusCode !== 201) {
                    Log::error('Failed to upload file to Supabase', [
                        'status' => $statusCode,
                        'response' => $responseBody
                    ]);
                    throw new \Exception('Failed to upload file to Supabase Storage: ' . $responseBody);
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $errorResponse = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                Log::error('Guzzle HTTP error uploading to Supabase', [
                    'message' => $e->getMessage(),
                    'response' => $errorResponse,
                    'upload_url' => $uploadUrl,
                ]);
                throw new \Exception('HTTP error uploading to Supabase: ' . $errorResponse);
            }
            
            // Store the path in DB (this will be the path in Supabase)
            $requirementsPath = $storagePath;

            Log::info('Requirements file stored successfully in Supabase', [
                'stored_path' => $requirementsPath,
                'upload_url' => $uploadUrl,
                'status_code' => $response->getStatusCode()
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing requirement file in Supabase', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the entire application if file upload fails, but log it
            // Continue without the file path
            $requirementsPath = null;
        }
    }
        */


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
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error in store method', [
            'errors' => $e->errors(),
            'message' => $e->getMessage()
        ]);
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Fatal error in store method', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'An error occurred while submitting the application: ' . $e->getMessage(),
            'error' => 'INTERNAL_SERVER_ERROR'
        ], 500);
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

    // Try Supabase Storage first (for new uploads)
    $supabaseUrl = env('SUPABASE_URL', 'https://hmevengvfponcwslnyye.supabase.co');
    $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
    $supabaseUrl = rtrim($supabaseUrl, '/'); // Remove trailing slash
    $bucket = env('SUPABASE_BUCKET');
    
    if ($bucket && $application->requirement_directory) {
        // Generate public URL for Supabase Storage
        $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$application->requirement_directory}";
        return redirect($publicUrl);
    }
    
    // Fallback to public storage (for old uploads)
    if (Storage::disk('public')->exists($application->requirement_directory)) {
        return Storage::disk('public')->response($application->requirement_directory);
    }
    
    // Fallback to public path for backwards compatibility
    $filePath = public_path($application->requirement_directory);
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }

    return response()->json(['message' => 'File not found'], 404);
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

    // Try Supabase Storage first (for new uploads)
    $supabaseUrl = env('SUPABASE_URL', 'https://hmevengvfponcwslnyye.supabase.co');
    $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
    $supabaseUrl = rtrim($supabaseUrl, '/'); // Remove trailing slash
    $bucket = env('SUPABASE_BUCKET');
    
    if ($bucket && $path) {
        try {
            // Generate public URL for Supabase Storage
            $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$path}";
            return redirect($publicUrl);
        } catch (\Exception $e) {
            Log::error('Supabase download failed: ' . $e->getMessage());
        }
    }
    
    // Fallback to public storage (for old uploads)
    if (Storage::disk('public')->exists($path)) {
        try {
            return Storage::disk('public')->download($path, basename($path), [
                'Content-Type' => 'application/pdf'
            ]);
        } catch (\Exception $e) {
            Log::error('Storage download failed: ' . $e->getMessage());
        }
    }
    
    // Fallback to public path
    $fullPath = public_path($path);
    if (file_exists($fullPath)) {
        try {
            return response()->download($fullPath, basename($path), [
                'Content-Type' => 'application/pdf'
            ]);
        } catch (\Exception $e) {
            Log::error('Public path download failed: ' . $e->getMessage());
        }
    }

    return response()->json(['message' => 'File not found'], 404);
}




}
