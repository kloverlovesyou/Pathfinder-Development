<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
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

        $registrations = Registration::with('training')
            ->where('applicantID', $user->applicantID)
            ->get();

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
            'certificate' => null,
            'trainingID' => $trainingID,
            'applicantID' => $user->applicantID,
        ]);

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


        $registrants = Registration::with('applicant')
        ->where('trainingID', $trainingID)
        ->get()
        ->map(function($r) {
            return [
                'id' => $r->registrationID,
                'applicantID' => $r->applicantID,
                'name' => $r->applicant->firstName . ' ' . $r->applicant->lastName,
                'status' => $r->registrationStatus,
                'dateRegistered' => $r->registrationDate ? $r->registrationDate->format('M d, Y') : null,
                'certificateTrackingID' => $r->certTrackingID,
                'certificateGivenDate' => $r->certGivenDate ? $r->certGivenDate->format('M d, Y') : null,
                'certificatePath' => $r->certificatePath,
                'hasCertificate' => !is_null($r->certTrackingID) && !is_null($r->certGivenDate),
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
            'certificate' => $filePath, // Supabase path
        ]);
        
        $results[] = $registration;
    }
    
    return response()->json([
        'message' => 'Certificates issued successfully',
        'data' => $results,
    ]);
}


    public function updateCertificate(Request $request, $registrationID)
    {
        $user = $request->user();
        
        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $registration = Registration::find($registrationID);
        if (!$registration) {
            return response()->json(['message' => 'Registration not found'], 404);
        }
        
        // Verify training belongs to organization
        $training = \App\Models\Training::where('trainingID', $registration->trainingID)
            ->where('organizationID', $user->organizationID)
            ->first();
        
        if (!$training) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        $validated = $request->validate([
            'certificateTrackingID' => 'required|string',
            'certificateGivenDate' => 'required|date',
            'certificatePath' => 'nullable|string', // Optional if file is uploaded
            'file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096', // Accept file uploads
        ]);
        
        $certificatePath = $validated['certificatePath'] ?? null;
        
        // If file is uploaded, process it (convert PDF to image if needed) and upload to Supabase
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mimeType = $file->getMimeType();
            $fileContents = null;
            $fileExtension = 'png'; // Default to PNG after conversion
            $contentType = 'image/png'; // Default content type
            
            // If file is PDF, convert to image
            if ($mimeType === 'application/pdf' || $file->getClientOriginalExtension() === 'pdf') {
                try {
                    if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                        return response()->json([
                            'message' => 'PDF conversion requires Imagick PHP extension. Please install it or upload an image instead.',
                            'error' => 'IMAGICK_NOT_AVAILABLE'
                        ], 400);
                    }
                    
                    // Convert PDF first page to image using Imagick
                    $imagick = new \Imagick();
                    $imagick->setResolution(300, 300);
                    $imagick->readImage($file->getRealPath() . '[0]');
                    $imagick->setImageFormat('png');
                    $imagick->setImageCompressionQuality(95);
                    
                    $fileContents = $imagick->getImageBlob();
                    $imagick->clear();
                    $imagick->destroy();
                    
                    Log::info('PDF converted to image for registration certificate', [
                        'registrationID' => $registrationID,
                        'original_size' => $file->getSize(),
                        'converted_size' => strlen($fileContents)
                    ]);
                } catch (\Exception $e) {
                    Log::error('PDF to image conversion failed for registration certificate', [
                        'registrationID' => $registrationID,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'message' => 'Failed to convert PDF to image: ' . $e->getMessage(),
                        'error' => 'PDF_CONVERSION_FAILED'
                    ], 500);
                }
            } else {
                // For images, read binary data directly
                $fileContents = file_get_contents($file->getRealPath());
                $fileExtension = $file->getClientOriginalExtension();
                $contentType = $mimeType;
            }
            
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
                $client = new \GuzzleHttp\Client();
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
            } catch (\Exception $e) {
                Log::error('Failed to upload certificate to Supabase', [
                    'registrationID' => $registrationID,
                    'error' => $e->getMessage()
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
        
        return response()->json([
            'message' => 'Certificate issued successfully',
            'data' => $registration,
        ]);
    }
}