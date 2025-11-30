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
use Illuminate\Support\Facades\Mail;
use App\Mail\InterviewSchedule;
use App\Mail\ApplicationHired;
use App\Mail\ApplicationDeclined;
use App\Services\BrevoEmailService;

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
            'applicantID' => $app->applicantID,
            'interviewSchedule' => $app->interviewSchedule ? $app->interviewSchedule->format('Y-m-d H:i:s') : null,
            'interviewMode' => $app->interviewMode,
            'interviewLink' => $app->interviewLink,
            'interviewLocation' => $app->interviewLocation,
            'detailsAndInstructions' => $app->career->detailsAndInstructions ?? null,
            'qualificationStandard' => $app->career->qualificationStandard ?? null,
            'requirement_directory' => $app->requirement_directory ?? null, // Fixed: get from application, not career
            'applicationLetterAddress' => $app->career->applicationLetterAddress ?? null,
            'deadlineOfSubmission' => $app->career->deadlineOfSubmission ?? null,
            'status' => $app->applicationStatus,
            'dateSubmitted' => $app->dateSubmitted,
            'appliedDate' => $app->appliedDate,
            'screenDate' => $app->screenDate,
            'pendingDate' => $app->pendingDate,
            'hiredDate' => $app->hiredDate,
            'declinedDate' => $app->declinedDate,

            // Career table
            'title' => $career->position ?? null,
            'details' => $career->details ?? null,
            'detailsAndInstructions' => $career->details ?? null, // Backward compatibility
            'placeOfAssignment' => $career->placeOfAssignment ?? null,
            'qualificationStandard' => $career->qualificationStandard ?? null,
            'pdf_directory' => $career->pdf_directory ?? null,
            'postingDate' => $career->postingDate ?? null,
            'closingDate' => $career->closingDate ?? null,
            'deadlineOfSubmission' => $career->closingDate ?? null, // Backward compatibility
            'trainingsAttendedPercentage' => $career->trainingsAttendedPercentage ?? null,

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
                'appliedDate' => Carbon::now(),
                'applicationStatus' => 'Submitted',

                'interviewSchedule' => null,
                'interviewMode' => null,
                'interviewLocation' => null,
                'interviewLink' => null,

                'careerID' => $careerID,
                'applicantID' => $user->applicantID,
            ]);
            $this->recordApplicationStage($app, 'submitted');

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
        $supabaseUrl = env('SUPABASE_URL', 'https://hmevengvfponcwslnyye.supabase.co');
        $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
        $supabaseUrl = rtrim($supabaseUrl, '/');
        
        $bucket = env('SUPABASE_BUCKET', 'Requirements');
        $supabaseKey = env('SUPABASE_SECRET') ?: env('SUPABASE_KEY');

        if (!$bucket || !$supabaseKey) {
            Log::error('Supabase Storage not configured', [
                'has_bucket' => !empty($bucket),
                'has_key' => !empty($supabaseKey),
            ]);
            throw new \RuntimeException('Supabase Storage not configured. Please check SUPABASE_BUCKET and SUPABASE_SECRET in .env');
        }

        $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $storagePath = 'requirement_directory/' . time() . '_' . $sanitizedName;
        $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$storagePath}";
        
        $fileContents = file_get_contents($file->getRealPath());
        $contentType = $file->getMimeType() ?: 'application/pdf';

        Log::info('Attempting Supabase upload', [
            'url' => $uploadUrl,
            'bucket' => $bucket,
            'path' => $storagePath,
            'size' => strlen($fileContents),
            'content_type' => $contentType,
        ]);

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $uploadUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$supabaseKey}",
                    'Content-Type' => $contentType,
                    'x-upsert' => 'true',
                ],
                'body' => $fileContents,
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200 && $statusCode !== 201) {
                $responseBody = $response->getBody()->getContents();
                Log::error('Supabase upload failed', [
                    'status' => $statusCode,
                    'body' => $responseBody,
                    'path' => $storagePath,
                ]);
                throw new \RuntimeException('Supabase upload failed: ' . $responseBody);
            }

            Log::info('Requirements file stored in Supabase', [
                'path' => $storagePath,
                'status' => $statusCode,
            ]);

            return $storagePath;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = null;
            $responseBody = null;
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
                $errorMessage = $responseBody ?: $errorMessage;
            }
            
            Log::error('Supabase upload exception', [
                'message' => $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'upload_url' => $uploadUrl,
                'bucket' => $bucket,
                'path' => $storagePath,
                'has_key' => !empty($supabaseKey),
                'key_length' => $supabaseKey ? strlen($supabaseKey) : 0,
            ]);
            throw new \RuntimeException('Supabase upload failed: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Supabase upload general exception', [
                'message' => $e->getMessage(),
                'upload_url' => $uploadUrl,
                'bucket' => $bucket,
                'path' => $storagePath,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Supabase upload failed: ' . $e->getMessage());
        }
    }

    protected function deleteApplicationRecord(Application $application): void
    {
        $requirementsPath = $application->requirement_directory;
        $application->delete();
        $this->deleteRequirementAsset($requirementsPath);
    }

    protected function deleteRequirementAsset(?string $path): void
    {
        if (!$path) {
            return;
        }

        $normalizedPath = ltrim($path, '/');
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $urlPath = parse_url($path, PHP_URL_PATH) ?: '';
            $marker = '/storage/v1/object/public/';
            if ($urlPath && str_contains($urlPath, $marker)) {
                $normalizedPath = ltrim(
                    substr($urlPath, strpos($urlPath, $marker) + strlen($marker)),
                    '/'
                );
            } else {
                $normalizedPath = ltrim($urlPath, '/');
            }
        }

        if (!$normalizedPath) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($normalizedPath)) {
                Storage::disk('public')->delete($normalizedPath);
                return;
            }

            $absolutePath = public_path($normalizedPath);
            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
                return;
            }

            $bucket = env('SUPABASE_BUCKET', 'Requirements');
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SECRET') ?: env('SUPABASE_KEY');

            if ($supabaseUrl && $bucket && $supabaseKey) {
                $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
                $supabaseUrl = rtrim($supabaseUrl, '/');
                $deleteUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$normalizedPath}";

                $client = new \GuzzleHttp\Client();
                $client->delete($deleteUrl, [
                    'headers' => [
                        'Authorization' => "Bearer {$supabaseKey}",
                        'Content-Type' => 'application/json',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete requirement asset', [
                'path' => $path,
                'normalized' => $normalizedPath,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveApplicantFromRequest(Request $request)
    {
        $user = $request->user();
        if ($user && isset($user->applicantID)) {
            return $user;
        }

        $authUser = $request->authUser ?? null;
        if ($authUser && isset($authUser->applicantID)) {
            return $authUser;
        }

        return null;
    }


    //withdraw application
    public function destroy(Request $request, int $id)
    {
        $user = $this->resolveApplicantFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $application = Application::where('applicationID', $id)
            ->where('applicantID', $user->applicantID)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'APPLICATION NOT FOUND'], 404);
        }

        $this->deleteApplicationRecord($application);

        return response()->json(['message' => 'APPLICATION WITHDRAWN'], 200);
    }

    public function destroyByCareer(Request $request, int $careerID)
    {
        $user = $this->resolveApplicantFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $application = Application::where('careerID', $careerID)
            ->where('applicantID', $user->applicantID)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'APPLICATION NOT FOUND'], 404);
        }

        $this->deleteApplicationRecord($application);

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
                
                // Helper function to safely format dates
                $formatDate = function($date, $format) use ($app) {
                    if (!$date) return null;
                    // If it's already a Carbon instance, format it
                    if ($date instanceof Carbon || $date instanceof \DateTime) {
                        return $date->format($format);
                    }
                    // If it's a string, try to parse it first
                    if (is_string($date)) {
                        try {
                            $carbon = Carbon::parse($date);
                            return $carbon->format($format);
                        } catch (\Exception $e) {
                            // If parsing fails, log and return the original string
                            Log::warning('Failed to parse date in ApplicationController', [
                                'date' => $date,
                                'applicationID' => $app->applicationID ?? null,
                                'error' => $e->getMessage()
                            ]);
                            return $date;
                        }
                    }
                    return null;
                };
                
                return [
                    'id' => $app->applicationID,
                    'applicantID' => $app->applicantID,
                    'name' => $applicantName,
                    'dateSubmitted' => $formatDate($app->dateSubmitted, 'M d, Y'),
                    'status' => $app->applicationStatus ? strtolower($app->applicationStatus) : 'submitted',
                    'requirement_directory' => $app->getAttribute('requirement_directory'), // Use getAttribute to ensure proper retrieval
                    'interviewSchedule' => $formatDate($app->interviewSchedule, 'Y-m-d H:i:s'),
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
        
        $application = Application::with(['applicant', 'career.organization'])->find($applicationID);
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
            'status' => 'required|in:submitted,in review,for interview,pending,accepted,rejected,hired,declined',
            'date' => 'nullable|date',
            'overwrite' => 'nullable|boolean',
            'body' => 'nullable|string', // Optional custom message for email
        ]);

        $timestamp = isset($validated['date'])
            ? Carbon::parse($validated['date'])
            : null;

        $application->applicationStatus = $validated['status'];
        $this->recordApplicationStage(
            $application,
            $validated['status'],
            $timestamp,
            $request->boolean('overwrite', false)
        );
        $application->save();
        
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
            'cc' => 'nullable|string',
            'body' => 'nullable|string',
            'sendEmail' => 'nullable|boolean', // Flag to indicate if email should be sent
        ]);
        
        try {
            $application->update([
                'interviewSchedule' => $validated['interviewSchedule'],
                'interviewMode' => $validated['interviewMode'],
                'interviewLocation' => $validated['interviewLocation'] ?? null,
                'interviewLink' => $validated['interviewLink'] ?? null,
                'applicationStatus' => 'for interview',
            ]);
            $this->recordApplicationStage($application, 'for interview');
            
            // Refresh the model to get updated data
            $application->refresh();
            
            // Send email if sendEmail flag is true (Schedule and Email option)
            $emailSent = false;
            $emailError = null;
            
            if ($validated['sendEmail'] ?? false) {
                try {
                    // Load applicant relationship to get email
                    $application->load('applicant', 'career.organization');
                    
                    if ($application->applicant && $application->applicant->emailAddress) {
                        $applicantEmail = $application->applicant->emailAddress;
                        // Handle both camelCase and PascalCase field names
                        $firstName = $application->applicant->firstName ?? $application->applicant->FirstName ?? '';
                        $lastName = $application->applicant->lastName ?? $application->applicant->LastName ?? '';
                        $applicantName = trim($firstName . ' ' . $lastName);
                        if (empty($applicantName)) {
                            $applicantName = 'Applicant'; // Fallback if name is not available
                        }
                        $positionTitle = $application->career->position ?? 'Position';
                        $organizationName = $application->career->organization->name ?? 'Organization';
                        
                        // Format interview date
                        $interviewDate = Carbon::parse($validated['interviewSchedule'])
                            ->format('F j, Y \a\t g:i A');
                        
                        // Prepare CC emails
                        $ccEmails = [];
                        if (!empty($validated['cc'])) {
                            $ccList = array_map('trim', explode(',', $validated['cc']));
                            $ccEmails = array_filter($ccList, function($email) {
                                return filter_var($email, FILTER_VALIDATE_EMAIL);
                            });
                        }
                        
                        // Create email mailable
                        $mailable = new InterviewSchedule(
                            $applicantName,
                            $positionTitle,
                            $organizationName,
                            $interviewDate,
                            $validated['interviewMode'],
                            $validated['interviewLocation'] ?? null,
                            $validated['interviewLink'] ?? null,
                            $validated['body'] ?? null
                        );
                        
                        // Try to send via Brevo API first, then fallback to SMTP
                        // Try multiple ways to get the API key
                        $brevoApiKeyFromConfig = config('services.brevo.api_key');
                        $brevoApiKeyFromEnv = env('BREVO_API_KEY');
                        $brevoApiKey = trim($brevoApiKeyFromConfig ?: $brevoApiKeyFromEnv ?: '');
                        
                        Log::info('Checking email sending method', [
                            'brevo_api_key_set' => !empty($brevoApiKey),
                            'brevo_api_key_length' => $brevoApiKey ? strlen($brevoApiKey) : 0,
                            'from_config' => !empty($brevoApiKeyFromConfig),
                            'from_env' => !empty($brevoApiKeyFromEnv),
                            'api_key_preview' => $brevoApiKey ? substr($brevoApiKey, 0, 10) . '...' : 'not set',
                        ]);
                        
                        if (!empty($brevoApiKey)) {
                            try {
                                $brevoService = app(BrevoEmailService::class);
                                $htmlContent = view('emails.interview-schedule', [
                                    'applicantName' => $applicantName,
                                    'positionTitle' => $positionTitle,
                                    'organizationName' => $organizationName,
                                    'interviewDate' => $interviewDate,
                                    'interviewMode' => $validated['interviewMode'],
                                    'interviewLocation' => $validated['interviewLocation'] ?? null,
                                    'interviewLink' => $validated['interviewLink'] ?? null,
                                    'customBody' => $validated['body'] ?? null,
                                ])->render();
                                
                                // Send via Brevo with CC support
                                $brevoService->sendWithCc(
                                    $applicantEmail,
                                    $mailable->envelope()->subject,
                                    $htmlContent,
                                    $ccEmails
                                );
                                
                                $emailSent = true;
                                Log::info('Interview schedule email sent via Brevo API', [
                                    'applicant_email' => $applicantEmail,
                                    'cc_emails' => $ccEmails,
                                ]);
                            } catch (\Exception $brevoException) {
                                $brevoError = $brevoException->getMessage();
                                Log::error('Brevo API failed for interview schedule email', [
                                    'error' => $brevoError,
                                    'error_code' => $brevoException->getCode(),
                                    'trace' => $brevoException->getTraceAsString(),
                                ]);
                                
                                // Store Brevo error for better error message
                                $emailError = 'Brevo API error: ' . $brevoError . '. Falling back to SMTP.';
                                
                                // Fall through to SMTP
                            }
                        } else {
                            Log::warning('Brevo API key not found, will try SMTP fallback');
                        }
                        
                        // Fallback to SMTP if Brevo failed or not configured
                        if (!$emailSent) {
                            try {
                                $mailTo = Mail::to($applicantEmail);
                                
                                // Add CC if provided
                                if (!empty($ccEmails)) {
                                    foreach ($ccEmails as $ccEmail) {
                                        $mailTo->cc($ccEmail);
                                    }
                                }
                                
                                $mailTo->send($mailable);
                                
                                $emailSent = true;
                                Log::info('Interview schedule email sent via SMTP', [
                                    'applicant_email' => $applicantEmail,
                                    'cc_emails' => $ccEmails,
                                ]);
                            } catch (\Exception $smtpException) {
                                // Provide more helpful error message
                                $errorMessage = $smtpException->getMessage();
                                
                                // Check if it's an authentication error
                                if (strpos($errorMessage, 'Authentication failed') !== false || 
                                    strpos($errorMessage, '535') !== false) {
                                    $emailError = 'Both Brevo API and SMTP failed. ' .
                                                  'Brevo API error: ' . (isset($brevoError) ? $brevoError : 'Unknown') . '. ' .
                                                  'SMTP authentication also failed. Please check your Brevo API key at https://app.brevo.com/settings/keys/api or verify SMTP credentials.';
                                } else {
                                    $emailError = 'Both Brevo API and SMTP failed. ' .
                                                  'Brevo API error: ' . (isset($brevoError) ? $brevoError : 'Unknown') . '. ' .
                                                  'SMTP error: ' . $errorMessage;
                                }
                                
                                Log::error('SMTP failed for interview schedule email (after Brevo failed)', [
                                    'applicant_email' => $applicantEmail,
                                    'brevo_error' => isset($brevoError) ? $brevoError : 'Unknown',
                                    'smtp_error' => $smtpException->getMessage(),
                                    'trace' => $smtpException->getTraceAsString(),
                                ]);
                                
                                // Re-throw to be caught by outer catch block
                                throw $smtpException;
                            }
                        }
                    } else {
                        $emailError = 'Applicant email address not found';
                        Log::warning('Cannot send interview schedule email: applicant email not found', [
                            'application_id' => $applicationID,
                        ]);
                    }
                } catch (\Exception $emailException) {
                    // Provide user-friendly error message
                    $errorMessage = $emailException->getMessage();
                    
                    // Check if it's an authentication error and provide helpful guidance
                    if (strpos($errorMessage, 'Authentication failed') !== false || 
                        strpos($errorMessage, '535') !== false) {
                        $emailError = 'Email authentication failed. Please check your SMTP credentials or configure Brevo API. ' .
                                      'The interview was scheduled successfully, but the email notification could not be sent.';
                    } elseif (strpos($errorMessage, 'Connection') !== false || 
                              strpos($errorMessage, 'timeout') !== false) {
                        $emailError = 'Email connection failed. Please check your SMTP settings or network connection. ' .
                                      'The interview was scheduled successfully, but the email notification could not be sent.';
                    } else {
                        $emailError = 'Email sending failed: ' . $errorMessage . '. ' .
                                      'The interview was scheduled successfully, but the email notification could not be sent.';
                    }
                    
                    Log::error('Failed to send interview schedule email', [
                        'application_id' => $applicationID,
                        'error' => $emailException->getMessage(),
                        'trace' => $emailException->getTraceAsString(),
                    ]);
                    // Don't fail the entire request if email fails
                }
            }
            
            $response = [
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
            ];
            
            if ($emailSent) {
                $response['email_sent'] = true;
                $response['message'] = 'Interview scheduled and email sent successfully';
            } elseif ($emailError) {
                $response['email_sent'] = false;
                $response['email_error'] = $emailError;
                $response['message'] = 'Interview scheduled successfully, but email could not be sent';
                
                // Add helpful debugging info
                $brevoApiKeyFromConfig = config('services.brevo.api_key');
                $brevoApiKeyFromEnv = env('BREVO_API_KEY');
                $brevoApiKey = trim($brevoApiKeyFromConfig ?: $brevoApiKeyFromEnv ?: '');
                
                if (empty($brevoApiKey)) {
                    $response['email_debug'] = 'Brevo API key not found. Please check your .env file - ensure BREVO_API_KEY has no spaces around the equals sign (BREVO_API_KEY=value, not BREVO_API_KEY = value). Then run: php artisan config:clear. You can check your config at /api/check-email-config';
                } else {
                    $response['email_debug'] = 'Brevo API key found but email still failed. Check logs for details. You can check your config at /api/check-email-config';
                }
            }
            
            return response()->json($response, 200);
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




    private function recordApplicationStage(Application $application, string $status, ?Carbon $timestamp = null, bool $force = false): void
    {
        $map = [
            'submitted' => 'appliedDate',
            'in review' => 'appliedDate',
            'for interview' => 'screenDate',
            'pending' => 'pendingDate',
            'accepted' => 'hiredDate',
            'hired' => 'hiredDate',
            'declined' => 'declinedDate',
            'rejected' => 'declinedDate',
        ];

        $key = strtolower(trim($status));
        if (!isset($map[$key])) {
            return;
        }

        $column = $map[$key];
        if (!$force && !empty($application->$column)) {
            return;
        }

        $now = $timestamp ?? Carbon::now();
        $application->$column = $now;

        if ($key === 'submitted' && ($force || empty($application->dateSubmitted))) {
            $application->dateSubmitted = $now;
        }
        $application->save();
    }

    /**
     * Send email notification when application status changes to 'hired', 'declined', or 'rejected'
     */
    private function sendStatusChangeEmail(Application $application, string $status, ?string $customBody = null): void
    {
        Log::info('sendStatusChangeEmail called', [
            'application_id' => $application->applicationID,
            'status' => $status,
            'has_applicant_relation' => $application->relationLoaded('applicant'),
            'has_career_relation' => $application->relationLoaded('career'),
        ]);
        
        try {
            // Reload relationships if not already loaded
            if (!$application->relationLoaded('applicant')) {
                $application->load('applicant');
            }
            if (!$application->relationLoaded('career')) {
                $application->load('career.organization');
            }

            $applicant = $application->applicant;
            $career = $application->career;
            
            Log::info('Relationships loaded', [
                'application_id' => $application->applicationID,
                'applicant_id' => $applicant ? $applicant->applicantID : null,
                'career_id' => $career ? $career->careerID : null,
                'organization_id' => $career && $career->organization ? $career->organization->organizationID : null,
            ]);

            if (!$applicant || !$career) {
                Log::warning('Cannot send status change email: missing applicant or career', [
                    'application_id' => $application->applicationID,
                    'has_applicant' => !is_null($applicant),
                    'has_career' => !is_null($career),
                ]);
                return;
            }

            // Get applicant email (handle both camelCase and PascalCase)
            // Try direct access first (as used in interview schedule)
            $applicantEmail = null;
            if ($applicant->emailAddress) {
                $applicantEmail = $applicant->emailAddress;
            } elseif ($applicant->EmailAddress) {
                $applicantEmail = $applicant->EmailAddress;
            } elseif (isset($applicant->attributes['emailAddress'])) {
                $applicantEmail = $applicant->attributes['emailAddress'];
            } elseif (isset($applicant->attributes['EmailAddress'])) {
                $applicantEmail = $applicant->attributes['EmailAddress'];
            }
            
            Log::info('Applicant email lookup', [
                'application_id' => $application->applicationID,
                'applicant_id' => $applicant->applicantID,
                'email_found' => !empty($applicantEmail),
                'email_value' => $applicantEmail ? substr($applicantEmail, 0, 5) . '...' : null,
                'has_emailAddress' => isset($applicant->emailAddress),
                'has_EmailAddress' => isset($applicant->EmailAddress),
                'attributes_keys' => array_keys($applicant->attributes ?? []),
            ]);
            
            if (!$applicantEmail) {
                Log::warning('Cannot send status change email: applicant email not found', [
                    'application_id' => $application->applicationID,
                    'applicant_id' => $applicant->applicantID,
                    'applicant_attributes' => array_keys($applicant->attributes ?? []),
                ]);
                return;
            }

            // Get applicant name
            $firstName = $applicant->firstName ?? $applicant->FirstName ?? '';
            $lastName = $applicant->lastName ?? $applicant->LastName ?? '';
            $applicantName = trim($firstName . ' ' . $lastName) ?: 'Applicant';

            // Get position and organization info
            $positionTitle = $career->position ?? 'Position';
            $organizationName = $career->organization->name ?? 'Organization';

            $emailSent = false;
            $statusLower = strtolower(trim($status));

            if ($statusLower === 'hired') {
                $mailable = new ApplicationHired(
                    $applicantName,
                    $positionTitle,
                    $organizationName,
                    $customBody
                );
            } elseif ($statusLower === 'declined' || $statusLower === 'rejected') {
                // Treat 'rejected' the same as 'declined' for email purposes
                $mailable = new ApplicationDeclined(
                    $applicantName,
                    $positionTitle,
                    $organizationName,
                    $customBody
                );
            } else {
                Log::warning('sendStatusChangeEmail called with invalid status', [
                    'status' => $status,
                    'application_id' => $application->applicationID,
                ]);
                return;
            }

            // Try to send via Brevo API first, then fallback to SMTP
            $brevoApiKeyFromConfig = config('services.brevo.api_key');
            $brevoApiKeyFromEnv = env('BREVO_API_KEY');
            $brevoApiKey = trim($brevoApiKeyFromConfig ?: $brevoApiKeyFromEnv ?: '');

            if (!empty($brevoApiKey)) {
                try {
                    $brevoService = app(BrevoEmailService::class);
                    // Use declined template for both 'declined' and 'rejected' statuses
                    $viewName = $statusLower === 'hired' ? 'emails.application-hired' : 'emails.application-declined';
                    $htmlContent = view($viewName, [
                        'applicantName' => $applicantName,
                        'positionTitle' => $positionTitle,
                        'organizationName' => $organizationName,
                        'customBody' => $customBody,
                    ])->render();

                    $brevoService->send(
                        $applicantEmail,
                        $mailable->envelope()->subject,
                        $htmlContent
                    );

                    $emailSent = true;
                    Log::info('Status change email sent via Brevo API', [
                        'status' => $status,
                        'applicant_email' => $applicantEmail,
                        'application_id' => $application->applicationID,
                    ]);
                } catch (\Exception $brevoException) {
                    Log::error('Brevo API failed for status change email', [
                        'status' => $status,
                        'error' => $brevoException->getMessage(),
                        'error_code' => $brevoException->getCode(),
                        'application_id' => $application->applicationID,
                    ]);
                    // Fall through to SMTP
                }
            } else {
                Log::warning('Brevo API key not found, will try SMTP fallback for status change email');
            }

            // Fallback to SMTP if Brevo failed or not configured
            if (!$emailSent) {
                try {
                    Mail::to($applicantEmail)->send($mailable);
                    $emailSent = true;
                    Log::info('Status change email sent via SMTP', [
                        'status' => $status,
                        'applicant_email' => $applicantEmail,
                        'application_id' => $application->applicationID,
                    ]);
                } catch (\Exception $smtpException) {
                    Log::error('SMTP failed for status change email', [
                        'status' => $status,
                        'error' => $smtpException->getMessage(),
                        'error_code' => $smtpException->getCode(),
                        'application_id' => $application->applicationID,
                        'trace' => $smtpException->getTraceAsString(),
                    ]);
                }
            }
            
            if (!$emailSent) {
                Log::error('Status change email failed to send via both Brevo and SMTP', [
                    'status' => $status,
                    'application_id' => $application->applicationID,
                    'applicant_email' => $applicantEmail,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send status change email - exception caught', [
                'status' => $status,
                'application_id' => $application->applicationID,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Manually send status change email for an application
     */
    public function sendStatusEmail(Request $request, $applicationID)
    {
        $user = $request->user();
        
        // Check if user is an Organization
        if (!$user || !($user instanceof \App\Models\Organization)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }
        
        $application = Application::with(['applicant', 'career.organization'])->find($applicationID);
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
            'body' => 'nullable|string', // Optional custom message for email
        ]);

        $status = strtolower(trim($application->applicationStatus ?? ''));
        
        // Only allow sending emails for hired, declined, or rejected statuses
        if ($status !== 'hired' && $status !== 'declined' && $status !== 'rejected') {
            return response()->json([
                'message' => 'Email can only be sent for hired, declined, or rejected statuses',
                'current_status' => $application->applicationStatus,
            ], 400);
        }

        try {
            $this->sendStatusChangeEmail($application, $status, $validated['body'] ?? null);
            
            return response()->json([
                'message' => 'Status change email sent successfully',
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to manually send status change email', [
                'application_id' => $applicationID,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
