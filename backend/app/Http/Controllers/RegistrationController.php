<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Certification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    // List applicant's registrations
    public function index(Request $request)
    {
        $user = $request->user(); // ✅ Use Laravel's user resolver

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $registrations = Registration::with([
                'training.organization',
                'training.schedules' => function ($query) {
                    $query->orderBy('schedule');
                },
                'scheduleAttendances', // Load schedule attendance records
            ])
            ->where('applicantID', $user->applicantID)
            ->get()
            ->map(function ($registration) {
                $training = $registration->training;
                
                // Check if user has attended at least one schedule
                // First check schedule_attendance table (most reliable)
                $attendanceCount = $registration->scheduleAttendances->count();
                $hasAttended = $attendanceCount > 0;
                
                // Fallback: If no schedule_attendance records but registrationStatus is "Attended",
                // consider them as attended (handles old data or manual status updates)
                if (!$hasAttended && strtolower(trim($registration->registrationStatus ?? '')) === 'attended') {
                    $hasAttended = true;
                }

                return [
                    'registrationID' => $registration->registrationID,
                    'registrationDate' => $registration->registrationDate,
                    'registrationStatus' => $registration->registrationStatus,
                    'checked_in_at' => $registration->checked_in_at,
                    'registeredDate' => $registration->registeredDate,
                    'ongoingDate' => $registration->ongoingDate,
                    'completedDate' => $registration->completedDate,
                    'certifiedDate' => $registration->certifiedDate,
                    'certTrackingID' => $registration->certTrackingID,
                    'certGivenDate' => $registration->certGivenDate,
                    'certificatePath' => $registration->certificatePath,
                    'trainingID' => $registration->trainingID,
                    'applicantID' => $registration->applicantID,
                    'hasAttended' => $hasAttended, // ✅ Add flag indicating actual attendance
                    'attendanceCount' => $registration->scheduleAttendances->count(),
                    'training' => $training ? [
                        'trainingID' => $training->trainingID,
                        'title' => $training->title,
                        'description' => $training->description,
                        'organizationID' => $training->organizationID,
                        'organization' => $training->organization ? [
                            'organizationID' => $training->organization->organizationID,
                            'name' => $training->organization->name,
                        ] : null,
                        'schedules' => $training->schedules->map(function ($schedule) {
                            return [
                                'trainingScheduleID' => $schedule->trainingScheduleID,
                                'schedule' => $schedule->schedule,
                                'start_time' => $schedule->start_time,
                                'end_time' => $schedule->end_time,
                                'mode' => $schedule->mode,
                                'location' => $schedule->location,
                                'trainingLink' => $schedule->trainingLink,
                            ];
                        }),
                    ] : null,
                ];
            });

        return response()->json($registrations);
    }

    // Register applicant
    public function store(Request $request)
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'trainingID' => 'required|exists:training,trainingID',
        ]);

        $trainingID = (int) $validated['trainingID'];

        // Ensure not already registered
        $existing = Registration::where('applicantID', $user->applicantID)
            ->where('trainingID', $trainingID)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'ALREADY REGISTERED FOR THIS TRAINING',
                'registrationID' => $existing->registrationID,
            ], 409);
        }

        $registration = Registration::create([
            'registrationDate' => Carbon::now(),
            'registrationStatus' => 'Registered',
            'certTrackingID' => null,
            'certGivenDate' => null,
            'trainingID' => $trainingID,
            'applicantID' => $user->applicantID,
        ]);

        $registration->recordStage('registered', Carbon::now(), true);
        $registration->save();

        return response()->json([
            'message' => 'REGISTRATION SUCCESSFUL!!!',
            'data' => $registration,
        ], 201);
    }

    // Cancel registration
    public function destroy(Request $request, int $trainingID)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $registration = Registration::where('trainingID', $trainingID)
            ->where('applicantID', $user->applicantID)
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'REGISTRATION NOT FOUND'], 404);
        }

        $registration->delete();

        return response()->json(['message' => 'REGISTRATION CANCELLED'], 200);
    }

    // Get all registrants for a specific training
    public function getRegistrantsByTraining(Request $request, $trainingID)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //ensure only orgs can view registrants for their trainings
        if (!isset($user->organizationID)) {
            return response()->json(['message' => 'Only organizations can view registrants'], 403);
        }

        //verify the that the training belongs to the organization
        $training = \App\Models\Training::where('trainingID', $trainingID)
        ->where('organizationID', $user->organizationID)
        ->first();

        if (!$training) {
             return response()->json(['message' => 'Training not found or access denied'], 404);
        }


        // Load training with schedules to get schedule information
        $training->load('schedules');
        
        $registrants = Registration::with(['applicant', 'scheduleAttendances.schedule'])
        ->where('trainingID', $trainingID)
        ->get()
        ->map(function($r) use ($training) {
            // Get schedule attendance for this registrant
            $attendedScheduleIDs = $r->scheduleAttendances->pluck('trainingScheduleID')->toArray();
            
            // Map schedules with attendance status
            $scheduleAttendance = $training->schedules->map(function($schedule) use ($attendedScheduleIDs, $r) {
                $attendance = $r->scheduleAttendances->firstWhere('trainingScheduleID', $schedule->trainingScheduleID);
                return [
                    'trainingScheduleID' => $schedule->trainingScheduleID,
                    'schedule' => $schedule->schedule?->format('Y-m-d H:i:s'),
                    'end_time' => $schedule->end_time?->format('Y-m-d H:i:s'),
                    'mode' => $schedule->mode,
                    'attended' => in_array($schedule->trainingScheduleID, $attendedScheduleIDs),
                    'attendedAt' => $attendance?->attendedAt?->format('Y-m-d H:i:s'),
                ];
            });

            // Calculate overall status based on schedules
            $totalSchedules = $training->schedules->count();
            $attendedCount = count($attendedScheduleIDs);
            
            // Determine status: if attended at least one schedule, show "Attended"
            // But also include partial attendance info
            $effectiveStatus = $r->registrationStatus;
            if ($attendedCount > 0 && $attendedCount < $totalSchedules) {
                // Partial attendance - still show as "Attended" but with details
                $effectiveStatus = 'Attended (Partial)';
            } elseif ($attendedCount === $totalSchedules && $totalSchedules > 0) {
                $effectiveStatus = 'Attended';
            }

            return [
                'id' => $r->registrationID,
                'applicantID' => $r->applicantID,
                'name' => $r->applicant->firstName . ' ' . $r->applicant->lastName,
                'status' => $effectiveStatus,
                'originalStatus' => $r->registrationStatus,
                'dateRegistered' => $r->registrationDate ? $r->registrationDate->format('M d, Y') : null,
                'certificateTrackingID' => $r->certTrackingID,
                'certificateGivenDate' => $r->certGivenDate ? $r->certGivenDate->format('M d, Y') : null,
                'certificatePath' => $r->certificatePath,
                'hasCertificate' => !is_null($r->certTrackingID) && !is_null($r->certGivenDate),
                'scheduleAttendance' => $scheduleAttendance,
                'attendedSchedulesCount' => $attendedCount,
                'totalSchedulesCount' => $totalSchedules,
            ];
        });

        return response()->json($registrants);
    }


    public function issueBulkCertificates(Request $request, $trainingID)
{
    $user = $request->user();
    
    if (!$user || !isset($user->organizationID)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    // Verify training belongs to organization
    $training = \App\Models\Training::where('trainingID', $trainingID)
        ->where('organizationID', $user->organizationID)
        ->first();
    
    if (!$training) {
        return response()->json(['message' => 'Training not found or access denied'], 404);
    }
    
    $validated = $request->validate([
        'certGivenDate' => 'required|date',
        'certificates' => 'required|array',
        'certificates.*.filePath' => 'required|string', // path from Supabase
    ]);
    
    $registrations = Registration::where('trainingID', $trainingID)
        ->whereNull('certTrackingID') // Only those without certificates
        ->get();
    
    if ($registrations->count() !== count($validated['certificates'])) {
        return response()->json([
            'message' => 'Number of certificates does not match number of registrants without certificates'
        ], 400);
    }
    
    $results = [];
    
    foreach ($registrations as $index => $registration) {
        $filePath = $validated['certificates'][$index]['filePath'];
        
        $registration->update([
            'certTrackingID' => $registration->registrationID, // use registrationID
            'certGivenDate' => $validated['certGivenDate'],
            'certificatePath' => $filePath, // Supabase path
        ]);

            $registration->recordStage('certified', Carbon::parse($validated['certGivenDate']), true);
            $registration->save();
        
        // Automatically create a Certification entry for each applicant
        $existingCertification = Certification::where('applicantID', $registration->applicantID)
            ->where('certificate_path', $filePath)
            ->first();
        
        if (!$existingCertification) {
            // Create Certification entry using certificate_path (stored in Supabase)
            $certificationName = $training->title;
            
            $newCertification = Certification::create([
                'certificationName' => $certificationName,
                'certificate_path' => $filePath, // Store the Supabase path
                'applicantID' => $registration->applicantID,
                'IsSelected' => 0, // Default to not selected for resume
            ]);
            
            Log::info('Certificate automatically added to Certification model (bulk)', [
                'certificationID' => $newCertification->certificationID,
                'registrationID' => $registration->registrationID,
                'applicantID' => $registration->applicantID,
                'certificatePath' => $filePath,
            ]);
        }
        
        $results[] = $registration;
    }
    
    return response()->json([
        'message' => 'Certificates issued successfully',
        'data' => $results,
    ]);
}


    public function updateCertificate(Request $request, $registrationID)
    {
        try {
            $user = $request->user();
            
            if (!$user || !isset($user->organizationID)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            
            $registration = Registration::find($registrationID);
            if (!$registration) {
                return response()->json(['message' => 'Registration not found'], 404);
            }
            
            // Load training relationship
            $registration->load('training');
            
            // Verify training belongs to organization
            $training = \App\Models\Training::where('trainingID', $registration->trainingID)
                ->where('organizationID', $user->organizationID)
                ->first();
            
            if (!$training) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            
            // Ensure we have the training title
            if (!$training->title) {
                $training->refresh();
            }
            
            $validated = $request->validate([
                'certificateTrackingID' => 'required|string',
                'certificateGivenDate' => 'required|date',
                'certificatePath' => 'nullable|string', // Optional if file is uploaded
                'file' => 'nullable|file|mimes:jpeg,png,jpg|max:4096', // Only accept images
            ]);
            
            $certificatePath = $validated['certificatePath'] ?? null;
            
            // If file is uploaded, process it and upload to Supabase
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $mimeType = $file->getMimeType();
                $fileContents = file_get_contents($file->getRealPath());
                $fileExtension = $file->getClientOriginalExtension();
                $contentType = $mimeType;
                
                // Upload to Supabase Storage
                $supabaseUrl = env('SUPABASE_URL', 'https://hmevengvfponcwslnyye.supabase.co');
                $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
                $supabaseUrl = rtrim($supabaseUrl, '/');
                $supabaseKey = env('SUPABASE_SECRET') ?: env('SUPABASE_KEY');
                $bucket = env('SUPABASE_BUCKET', 'Requirements');
                
                if (!$supabaseKey || !$bucket) {
                    return response()->json([
                        'message' => 'Supabase Storage not configured'
                    ], 500);
                }
                
                // Generate storage path
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $registrationID);
                $storagePath = "certificate_directory/{$safeName}_" . time() . ".{$fileExtension}";
                
                $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$storagePath}";
                
                try {
                    // SSL verification: For Windows development, SSL certificate issues are common
                    // Set SUPABASE_VERIFY_SSL=true in .env for production (recommended)
                    // For development on Windows, you may need SUPABASE_VERIFY_SSL=false
                    $verifySSL = filter_var(env('SUPABASE_VERIFY_SSL', 'false'), FILTER_VALIDATE_BOOLEAN);
                    
                    $client = new \GuzzleHttp\Client([
                        'verify' => $verifySSL,
                    ]);
                    $response = $client->request('POST', $uploadUrl, [
                        'headers' => [
                            'Authorization' => "Bearer {$supabaseKey}",
                            'Content-Type' => $contentType,
                            'x-upsert' => 'true',
                        ],
                        'body' => $fileContents,
                    ]);
                    
                    if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                        throw new \Exception('Failed to upload certificate to Supabase');
                    }
                    
                    $certificatePath = $storagePath;
                    
                    Log::info('Certificate uploaded to Supabase successfully', [
                        'registrationID' => $registrationID,
                        'path' => $certificatePath
                    ]);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $statusCode = null;
                    $responseBody = null;
                    $errorMessage = $e->getMessage();
                    
                    if ($e->hasResponse()) {
                        $response = $e->getResponse();
                        $statusCode = $response->getStatusCode();
                        $responseBody = $response->getBody()->getContents();
                        $errorMessage = $responseBody ?: $errorMessage;
                    }
                    
                    Log::error('Failed to upload certificate to Supabase', [
                        'registrationID' => $registrationID,
                        'error' => $e->getMessage(),
                        'status_code' => $statusCode,
                        'response_body' => $responseBody,
                        'upload_url' => $uploadUrl,
                    ]);
                    return response()->json([
                        'message' => 'Failed to upload certificate: ' . $errorMessage
                    ], 500);
                } catch (\Exception $e) {
                    Log::error('Failed to upload certificate to Supabase', [
                        'registrationID' => $registrationID,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Failed to upload certificate: ' . $e->getMessage()
                    ], 500);
                }
            }
            
            if (!$certificatePath) {
                return response()->json([
                    'message' => 'Either certificatePath or file must be provided'
                ], 400);
            }
            
            // Store the Supabase path in the database
            $registration->update([
                'certTrackingID' => $validated['certificateTrackingID'],
                'certGivenDate' => $validated['certificateGivenDate'],
                'certificatePath' => $certificatePath,
            ]);

            $registration->recordStage('certified', Carbon::parse($validated['certificateGivenDate']), true);
            $registration->save();
            
            // Automatically create a Certification entry for the applicant
            // This ensures organization-issued certificates appear in the Certificates page
            if (!empty($certificatePath)) {
                // Check if certification already exists for this certificate path
                $existingCertification = Certification::where('applicantID', $registration->applicantID)
                    ->where('certificate_path', $certificatePath)
                    ->first();
                
                if (!$existingCertification) {
                    Log::info('Creating new Certification entry', [
                        'registrationID' => $registrationID,
                        'applicantID' => $registration->applicantID,
                        'certificatePath' => $certificatePath,
                        'trainingID' => $training->trainingID ?? null,
                        'trainingTitle' => $training->title ?? 'Unknown'
                    ]);
                    
                    // Create Certification entry using certificate_path (stored in Supabase)
                    $trainingTitle = $training->title ?? 'Training';
                    $certificationName = $trainingTitle;
                    
                    try {
                        $newCertification = Certification::create([
                            'certificationName' => $certificationName,
                            'certificate_path' => $certificatePath, // Store the Supabase path
                            'applicantID' => $registration->applicantID,
                            'IsSelected' => 0, // Default to not selected for resume
                            'certificate' => '', // Empty string for organization certificates (field is NOT NULL)
                        ]);
                        
                        Log::info('✅ Certificate automatically added to Certification model', [
                            'certificationID' => $newCertification->certificationID,
                            'registrationID' => $registrationID,
                            'applicantID' => $registration->applicantID,
                            'certificatePath' => $certificatePath,
                            'certificationName' => $certificationName,
                            'trainingTitle' => $trainingTitle
                        ]);
                    } catch (\Exception $e) {
                        Log::error('❌ Failed to create Certification entry', [
                            'registrationID' => $registrationID,
                            'applicantID' => $registration->applicantID,
                            'certificatePath' => $certificatePath,
                            'trainingTitle' => $trainingTitle,
                            'error' => $e->getMessage(),
                            'trace' => substr($e->getTraceAsString(), 0, 500) // Limit trace length
                        ]);
                        // Don't fail the request, just log the error
                    }
                } else {
                    Log::info('ℹ️ Certification entry already exists, skipping creation', [
                        'certificationID' => $existingCertification->certificationID,
                        'registrationID' => $registrationID,
                        'certificatePath' => $certificatePath,
                        'existingPath' => $existingCertification->certificate_path
                    ]);
                }
            } else {
                Log::warning('⚠️ Skipping Certification entry creation - empty certificatePath', [
                    'registrationID' => $registrationID,
                    'certificatePath' => $certificatePath
                ]);
            }
        
            return response()->json([
                'message' => 'Certificate issued successfully',
                'data' => $registration,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in updateCertificate', [
                'registrationID' => $registrationID,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in updateCertificate', [
                'registrationID' => $registrationID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to issue certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $registrationID)
    {
        $user = $request->user();

        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $registration = Registration::with('training')->find($registrationID);
        if (!$registration) {
            return response()->json(['message' => 'Registration not found'], 404);
        }

        if (
            !$registration->training ||
            $registration->training->organizationID !== $user->organizationID
        ) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Registered,Ongoing,Completed,Certified,Attended,Cancelled',
            'date' => 'nullable|date',
            'overwrite' => 'nullable|boolean',
        ]);

        $timestamp = isset($validated['date'])
            ? Carbon::parse($validated['date'])
            : Carbon::now();

        $registration->registrationStatus = $validated['status'];
        $registration->recordStage(
            $validated['status'],
            $timestamp,
            $request->boolean('overwrite', false)
        );

        if (
            strtolower($validated['status']) === 'certified' &&
            !$registration->certGivenDate
        ) {
            $registration->certGivenDate = $timestamp;
        }

        $registration->save();

        return response()->json([
            'message' => 'Registration status updated successfully',
            'data' => $registration,
        ]);
    }
}