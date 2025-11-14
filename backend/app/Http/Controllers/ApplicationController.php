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
        try {
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
            
            if (!$application->requirements) {
                Log::warning('Requirements path is null or empty', [
                    'application_id' => $applicationID,
                    'application_id_value' => $application->applicationID,
                    'requirements_path' => $application->requirements,
                ]);
                return response()->json(['message' => 'Requirements not found'], 404);
            }
            
            // Normalize the path: remove leading/trailing slashes and normalize directory separators
            $filePath = trim($application->requirements, '/\\');
            $filePath = str_replace('\\', '/', $filePath);
            
            // Get storage root path with error handling
            try {
                $storageRoot = Storage::disk('public')->path('');
                $expectedFullPath = Storage::disk('public')->path($filePath);
            } catch (\Exception $e) {
                Log::error('Error accessing storage disk', [
                    'application_id' => $applicationID,
                    'error' => $e->getMessage(),
                    'file_path' => $filePath
                ]);
                return response()->json(['message' => 'Error accessing storage'], 500);
            }
            
            // Log the path being checked
            Log::info('Retrieving requirements file', [
                'application_id' => $applicationID,
                'raw_path' => $application->requirements,
                'normalized_path' => $filePath,
                'storage_root' => $storageRoot,
                'expected_full_path' => $expectedFullPath
            ]);
            
            // Check if file exists using Storage facade with error handling
            $fileExists = false;
            try {
                $fileExists = Storage::disk('public')->exists($filePath);
            } catch (\Exception $e) {
                Log::warning('Error checking if file exists', [
                    'application_id' => $applicationID,
                    'file_path' => $filePath,
                    'error' => $e->getMessage()
                ]);
            }
            
            if (!$fileExists) {
                // Try alternative path formats
                $alternativePaths = [
                    $application->requirements, // original path
                    ltrim($filePath, '/'), // without leading slash
                    'requirements/' . basename($filePath), // just filename in requirements folder
                ];
                
                $foundPath = null;
                foreach ($alternativePaths as $altPath) {
                    try {
                        if (Storage::disk('public')->exists($altPath)) {
                            $foundPath = $altPath;
                            Log::info('Found file using alternative path', [
                                'original_path' => $application->requirements,
                                'found_path' => $foundPath
                            ]);
                            break;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error checking alternative path', [
                            'path' => $altPath,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
                
                if (!$foundPath) {
                    // List all files in requirements directory for debugging with error handling
                    $allFiles = [];
                    try {
                        $allFiles = Storage::disk('public')->files('requirements');
                    } catch (\Exception $e) {
                        Log::warning('Cannot list files in requirements directory', [
                            'error' => $e->getMessage()
                        ]);
                        $allFiles = [];
                    }
                    
                    try {
                        $storagePath = Storage::disk('public')->path($filePath);
                        $fileExistsCheck = file_exists($storagePath);
                        $storageExistsCheck = Storage::disk('public')->exists($filePath);
                    } catch (\Exception $e) {
                        $storagePath = 'Error getting path: ' . $e->getMessage();
                        $fileExistsCheck = false;
                        $storageExistsCheck = false;
                    }
                    
                    Log::error('Requirements file not found on disk', [
                        'application_id' => $applicationID,
                        'file_path_from_db' => $application->requirements,
                        'normalized_path' => $filePath,
                        'storage_path' => $storagePath,
                        'file_exists_check' => $fileExistsCheck,
                        'storage_exists_check' => $storageExistsCheck,
                        'available_files' => $allFiles,
                        'storage_root' => $storageRoot
                    ]);
                    
                    $debugInfo = null;
                    if (config('app.debug', false)) {
                        try {
                            $debugInfo = [
                                'stored_path' => $application->requirements,
                                'normalized_path' => $filePath,
                                'expected_path' => Storage::disk('public')->path($filePath),
                                'available_files' => $allFiles
                            ];
                        } catch (\Exception $e) {
                            $debugInfo = ['error' => 'Cannot generate debug info: ' . $e->getMessage()];
                        }
                    }
                    
                    return response()->json([
                        'message' => 'File not found on disk',
                        'debug' => $debugInfo
                    ], 404);
                }
                
                $filePath = $foundPath;
            }
            
            // Get the full path with error handling
            try {
                $path = Storage::disk('public')->path($filePath);
            } catch (\Exception $e) {
                Log::error('Error getting file path', [
                    'application_id' => $applicationID,
                    'file_path' => $filePath,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['message' => 'Error accessing file path'], 500);
            }
            
            // Verify file actually exists at path (extra safety check)
            if (!file_exists($path)) {
                try {
                    $storageRootExists = file_exists(Storage::disk('public')->path(''));
                } catch (\Exception $e) {
                    $storageRootExists = false;
                }
                
                Log::error('File path does not exist on filesystem', [
                    'application_id' => $applicationID,
                    'file_path_from_db' => $application->requirements,
                    'normalized_path' => $filePath,
                    'full_path' => $path,
                    'path_exists' => file_exists($path),
                    'is_readable' => is_readable($path),
                    'storage_root_exists' => $storageRootExists
                ]);
                return response()->json(['message' => 'File not found on disk'], 404);
            }
            
            // Validate file is readable before operations
            if (!is_readable($path)) {
                Log::error('File is not readable', [
                    'application_id' => $applicationID,
                    'path' => $path
                ]);
                return response()->json(['message' => 'File cannot be accessed'], 403);
            }
            
            // Log successful retrieval with error handling for filesize
            $fileSize = null;
            try {
                $fileSize = filesize($path);
            } catch (\Exception $e) {
                Log::warning('Cannot get file size', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Successfully retrieving requirements file', [
                'application_id' => $applicationID,
                'file_path' => $filePath,
                'full_path' => $path,
                'file_size' => $fileSize
            ]);
            
            // Return file with proper headers for PDF viewing
            try {
                return response()->file($path)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="requirements.pdf"');
            } catch (\Exception $e) {
                Log::error('Error serving file', [
                    'application_id' => $applicationID,
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['message' => 'Error serving file'], 500);
            }
            
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            Log::error('File not found exception', [
                'application_id' => $applicationID ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'File not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving requirements', [
                'application_id' => $applicationID ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error retrieving file',
                'error' => config('app.debug', false) ? $e->getMessage() : null
            ], 500);
        }
    }


}
