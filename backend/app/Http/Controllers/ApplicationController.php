<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    //list applicant's applications
  public function index(Request $request)
{
    $user = $request->user();

    // Fetch applications with career + organization
    $apps = Application::with(['career.organization'])
        ->where('applicantID', $user->applicantID)
        ->get();


    return $apps->map(function ($app) {

        $career = $app->career;

        return [
            // Application table
            'applicationID' => $app->applicationID,
            'careerID' => $app->careerID,
            'interviewSchedule' => $app->interviewSchedule,
            'interviewMode' => $app->interviewMode,
            'interviewLink' => $app->interviewLink,
            'interviewLocation' => $app->interviewLocation,
            'detailsAndInstructions' => $app->career->detailsAndInstructions ?? null,
            'qualifications' => $app->career->qualifications ?? null,
            'requirement_directory' => $app->requirement_directory ?? null, // Fixed: get from application, not career
            'applicationLetterAddress' => $app->career->applicationLetterAddress ?? null,
            'deadlineOfSubmission' => $app->career->deadlineOfSubmission ?? null,
            'status' => $app->applicationStatus,

            // Career table
            'title' => $career->position ?? null,
            'detailsAndInstructions' => $career->detailsAndInstructions ?? null,
            'qualifications' => $career->qualifications ?? null,
            'requirements' => $career->requirements ?? null,
            'applicationLetterAddress' => $career->applicationLetterAddress ?? null,
            'deadlineOfSubmission' => $career->deadlineOfSubmission ?? null,

            // Organization table
            'organizationName' => $career->organization->name ?? null,

            // Type
            'type' => 'career',
        ];
    });
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

        // ✅ RESTORED VALIDATION
        $validated = $request->validate([
            'careerID' => 'required|exists:career,careerID',
            'requirement_directory' => 'nullable', // file or string
        ]);

        $careerID = (int) $validated['careerID'];

        // prevent duplicates
        $existing = Application::where('applicantID', $user->applicantID)
            ->where('careerID', $careerID)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'ALREADY APPLIED',
                'applicationID' => $existing->applicationID,
            ], 409);
        }

        // File handling
        $filePath = $request->input('requirement_directory');
        $hasFileUpload = $request->hasFile('requirement_directory');
        $requirementsPath = null;
        
        if ($hasFileUpload) {
            $file = $request->file('requirement_directory');
            Log::info('File received (direct upload)', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            try {
                $requirementsPath = $this->uploadRequirementToSupabase($file);
            } catch (\Exception $e) {
                Log::error('Failed to upload requirement to Supabase', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'message' => 'Failed to upload requirements.',
                    'error' => 'SUPABASE_UPLOAD_FAILED',
                ], 500);
            }

        } elseif ($filePath) {
            $requirementsPath = $filePath;
            Log::info('Using file path from Supabase upload', [
                'path' => $requirementsPath,
            ]);
        }

        // Save application
        try {
            $app = Application::create([
                'requirement_directory' => $requirementsPath,
                'dateSubmitted' => Carbon::now(),
                'applicationStatus' => 'Submitted',

                'interviewSchedule' => null,
                'interviewMode' => null,
                'interviewLocation' => null,
                'interviewLink' => null,

                'careerID' => $careerID,
                'applicantID' => $user->applicantID,
            ]);

            Log::info('✅ Application created successfully', [
                'applicationID' => $app->applicationID,
                'careerID' => $app->careerID,
                'applicantID' => $app->applicantID,
                'has_requirement' => !empty($requirementsPath),
            ]);

            return response()->json([
                'message' => 'APPLICATION SUBMITTED SUCCESSFULLY!!!',
                'data' => $app,
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('❌ Database error creating application', [
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'careerID' => $careerID,
                'applicantID' => $user->applicantID,
            ]);

            return response()->json([
                'message' => 'Failed to submit application: Database error occurred.',
                'error' => 'DATABASE_ERROR',
            ], 500);
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error', [
            'errors' => $e->errors()
        ]);
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        Log::error('Fatal error', [
            'message' => $e->getMessage(),
        ]);
        return response()->json([
            'message' => 'An error occurred: ' . $e->getMessage(),
            'error' => 'INTERNAL_SERVER_ERROR'
        ], 500);
    }
}
    /**
     * Upload requirement document to Supabase Storage using the service key.
     */
    protected function uploadRequirementToSupabase(UploadedFile $file): string
    {
        $supabaseUrl = env('SUPABASE_URL', env('SUPABASE_ENDPOINT', 'https://hmevengvfponcwslnyye.supabase.co'));
        $supabaseUrl = preg_replace('#/storage/v1/?$#', '', $supabaseUrl);
        $supabaseUrl = rtrim($supabaseUrl, '/');

        $bucket = env('SUPABASE_BUCKET');
        $supabaseKey = env('SUPABASE_SECRET') ?: env('SUPABASE_KEY');

        if (!$bucket || !$supabaseKey) {
            throw new \RuntimeException('Supabase Storage not configured');
        }

        $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $storagePath = 'requirement_directory/' . time() . '_' . $sanitizedName;
        $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$storagePath}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$supabaseKey}",
            'apikey' => $supabaseKey,
            'Content-Type' => $file->getMimeType() ?: 'application/octet-stream',
            'x-upsert' => 'true',
        ])->withBody(
            file_get_contents($file->getRealPath()),
            $file->getMimeType() ?: 'application/octet-stream'
        )->post($uploadUrl);

        if (!$response->successful()) {
            Log::error('Supabase upload error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $storagePath,
            ]);
            throw new \RuntimeException('Supabase upload failed: ' . $response->body());
        }

        Log::info('Requirements file stored in Supabase', [
            'path' => $storagePath,
            'status' => $response->status(),
        ]);

        return $storagePath;
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
public function viewRequirement(Request $request, $id)
{
    $user = $request->user();
    $application = Application::findOrFail($id);

    // Authorization: Check if user is the applicant or the organization that owns the career
    if ($user instanceof \App\Models\Applicant) {
        // Applicants can only view their own requirements
        if ($application->applicantID !== $user->applicantID) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    } elseif ($user instanceof \App\Models\Organization) {
        // Organizations can only view requirements for applications to their careers
        if ($application->career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    } else {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

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
                    'requirement_directory' => $app->getAttribute('requirement_directory'), // Use getAttribute to ensure proper retrieval
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
