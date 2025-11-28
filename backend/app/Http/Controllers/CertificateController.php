<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use App\Models\Registration;
use App\Models\Training;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Supabase\Storage\StorageClient;

class CertificateController extends Controller
{
    // Helper method to generate Supabase public URL
    private function generateSupabaseUrl($certificatePath)
    {
        $supabaseUrl = env('SUPABASE_URL', 'https://hmevengvfponcwslnyye.supabase.co');
        $supabaseUrl = preg_replace('#/storage/v1/object/public/?$#', '', $supabaseUrl);
        $supabaseUrl = rtrim($supabaseUrl, '/');
        $bucket = env('SUPABASE_BUCKET', 'Requirements');
        
        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$certificatePath}";
    }
  
public function index($applicantID)
{
    try {
        $applicantID = (int) $applicantID; // Ensure integer type
        
        Log::info('🔍 Fetching certificates for applicant', ['applicantID' => $applicantID]);
        
        $allCertificates = [];
        
        // Step 1: Fetch manually uploaded certificates from certifications table (binary data)
        $manualCertifications = Certification::where('applicantID', $applicantID)
            ->whereNull('certificate_path')
            ->whereNotNull('certificate')
            ->get();
    
    foreach ($manualCertifications as $cert) {
        if (empty($cert->certificate) || strlen($cert->certificate) < 10) {
            continue; // Skip invalid binary data
        }
        
        $mimeType = 'image/png'; // default
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_buffer($finfo, $cert->certificate);
            finfo_close($finfo);
            
            if ($detectedMime && str_starts_with($detectedMime, 'image/')) {
                $mimeType = $detectedMime;
            }
        }
        
        $dataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($cert->certificate);
        
        $allCertificates[] = [
            'certificationID' => $cert->certificationID,
            'certificationName' => $cert->certificationName,
            'certificate' => $dataUrl,
            'applicantID' => $cert->applicantID,
            'IsSelected' => (int) $cert->IsSelected,
            'source' => 'manual',
        ];
    }
    
        // Step 2: Fetch organization-issued certificates from registrations (Supabase)
        // Get all registrations with certificatePath for this applicant
        $registrations = Registration::where('applicantID', $applicantID)
            ->whereNotNull('certificatePath')
            ->where('certificatePath', '!=', '')
            ->get();
        
        Log::info('📋 Found registrations with certificates', [
            'applicantID' => $applicantID,
            'count' => $registrations->count(),
        ]);
        
        // Step 3: Create certificates from registrations - fetch from Supabase using certificate_path
        foreach ($registrations as $registration) {
            $certificatePath = $registration->certificatePath;
            // Load training relationship separately to avoid issues
            $training = $registration->trainingID ? Training::find($registration->trainingID) : null;
        
        if (empty($certificatePath)) {
            continue;
        }
        
        // Generate Supabase public URL for this certificate
        $publicUrl = $this->generateSupabaseUrl($certificatePath);
        
        // Get certificate name from training title
        $trainingTitle = $training ? $training->title : ('Training #' . ($registration->trainingID ?? 'Unknown'));
        $certificationName = $trainingTitle . ' - Certificate of Completion';
        
        // Check if this certificate already exists in certifications table
        $existingCert = Certification::where('applicantID', $applicantID)
            ->where('certificate_path', $certificatePath)
            ->first();
        
        // Use existing certificationID or create a temporary one
        $certificationID = $existingCert ? $existingCert->certificationID : ('REG_' . $registration->registrationID);
        
        $allCertificates[] = [
            'certificationID' => $certificationID,
            'certificationName' => $certificationName,
            'certificate' => $publicUrl, // Supabase public URL
            'applicantID' => $registration->applicantID,
            'IsSelected' => $existingCert ? (int) $existingCert->IsSelected : 0,
            'source' => 'organization',
            'certificate_path' => $certificatePath,
            'registrationID' => $registration->registrationID,
            'certGivenDate' => $registration->certGivenDate ? $registration->certGivenDate->format('Y-m-d') : null,
        ];
        
        // Optionally create Certification entry in background (for future optimization)
        if (!$existingCert) {
            try {
                Certification::create([
                    'certificationName' => $certificationName,
                    'certificate_path' => $certificatePath,
                    'applicantID' => $registration->applicantID,
                    'IsSelected' => 0,
                    'certificate' => '', // Empty string for organization certificates (field is NOT NULL)
                ]);
                Log::info('✅ Created Certification entry', [
                    'certificatePath' => $certificatePath,
                    'applicantID' => $applicantID,
                ]);
            } catch (\Exception $e) {
                // Silent fail - we're still returning the certificate
                Log::debug('Could not create Certification entry', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    // Step 4: Also fetch certificates from certifications table that have certificate_path
    $certificationsWithPath = Certification::where('applicantID', $applicantID)
        ->whereNotNull('certificate_path')
        ->get();
    
    // Add certificates that have certificate_path but might not be in registrations
    $existingPaths = array_column($allCertificates, 'certificate_path');
    
    foreach ($certificationsWithPath as $cert) {
        if (!empty($cert->certificate_path) && !in_array($cert->certificate_path, $existingPaths)) {
            $publicUrl = $this->generateSupabaseUrl($cert->certificate_path);
            
            $allCertificates[] = [
                'certificationID' => $cert->certificationID,
                'certificationName' => $cert->certificationName,
                'certificate' => $publicUrl,
                'applicantID' => $cert->applicantID,
                'IsSelected' => (int) $cert->IsSelected,
                'source' => 'organization',
                'certificate_path' => $cert->certificate_path,
            ];
        }
        }
        
        Log::info('🎯 Returning certificates', [
            'applicantID' => $applicantID,
            'totalCount' => count($allCertificates),
            'organizationCount' => count(array_filter($allCertificates, fn($c) => ($c['source'] ?? '') === 'organization')),
            'manualCount' => count(array_filter($allCertificates, fn($c) => ($c['source'] ?? '') === 'manual')),
        ]);

        return $this->safeJsonResponse($allCertificates);
    } catch (\Exception $e) {
        Log::error('Error fetching certificates: ' . $e->getMessage(), [
            'applicantID' => $applicantID ?? null,
            'trace' => $e->getTraceAsString()
        ]);
        return $this->safeJsonResponse([
            'error' => 'Failed to fetch certificates',
            'message' => $e->getMessage()
        ], 500);
    }
}

    // Upload a new certificate
    public function store(Request $request)
    {
        // Log request details for debugging
        $base64Input = $request->input('certificate_base64');
        $base64Length = $base64Input ? strlen($base64Input) : 0;
        Log::info('📝 Certificate store request', [
            'hasFile' => $request->hasFile('certificate'),
            'hasPath' => $request->has('certificate_path'),
            'certificate_path' => $request->input('certificate_path'),
            'has_certificate_base64' => $request->has('certificate_base64'),
            'certificate_base64_length' => $base64Length,
            'certificate_base64_preview' => $base64Input ? substr($base64Input, 0, 50) . '...' : null,
            'all_input_keys' => array_keys($request->except(['certificate', 'certificate_base64'])), // Don't log binary data
        ]);
        
        // Validate based on whether we have a file, base64 data, or certificate_path
        $hasFile = $request->hasFile('certificate');
        $hasPath = $request->has('certificate_path') && !empty($request->certificate_path);

        // Accept base64 payloads via multiple keys to be robust against frontend variations
        // Check all() first to ensure we get JSON body data
        $allInput = $request->all();
        $base64Input = $request->input('certificate_base64')
            ?? $request->input('certificateData')
            ?? $request->input('certificate')
            ?? ($allInput['certificate_base64'] ?? null)
            ?? ($allInput['certificateData'] ?? null)
            ?? ($allInput['certificate'] ?? null);

        $hasBase64 = !empty($base64Input) && is_string($base64Input);
        
        // Log what we found
        Log::info('🔍 Certificate detection', [
            'hasFile' => $hasFile,
            'hasPath' => $hasPath,
            'hasBase64' => $hasBase64,
            'base64Input_type' => gettype($base64Input),
            'base64Input_length' => $hasBase64 ? strlen($base64Input) : 0,
            'all_input_keys' => array_keys($allInput),
        ]);
        
        // If neither file nor path is provided, that's an error
        if (!$hasFile && !$hasPath && !$hasBase64) {
            return $this->safeJsonResponse([
                'message' => 'Either certificate file or certificate_path must be provided',
                'errors' => ['certificate' => ['Either certificate file, certificate_base64, or certificate_path must be provided']]
            ], 422);
        }
        
        // At least one of certificate (file) or certificate_path must be provided
        // But not both are required - organization certificates have path, manual have file
        $validationRules = [
            'certificationName' => 'required|string|max:255',
            'applicantID' => 'required|integer',
            'IsSelected' => 'sometimes|integer|in:0,1',
        ];
        
        // Only validate certificate as file if it's actually being uploaded
        // Don't include certificate in validation rules at all if not uploading a file
        if ($hasFile) {
            $validationRules['certificate'] = 'required|file|mimes:jpeg,png,jpg|max:4096';
        }
        
        // Validate base64 payload if present
        if ($hasBase64) {
            $validationRules['certificate_base64'] = 'required|string';
        }

        // Validate certificate_path if provided
        if ($hasPath) {
            $validationRules['certificate_path'] = 'required|string';
        }
        
        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Certificate validation failed', [
                'errors' => $e->errors(),
                'rules' => $validationRules,
            ]);
            throw $e;
        }

        // Handle IsSelected - convert string to integer if needed
        $isSelected = $request->IsSelected ?? 1;
        if (is_string($isSelected)) {
            $isSelected = (int) $isSelected;
        }
        
        $data = [
            'certificationName' => $request->certificationName,
            'applicantID' => (int) $request->applicantID,
            'IsSelected' => $isSelected ? 1 : 0,
        ];

        // Handle file upload (manual certificates)
        if ($hasFile) {
            $file = $request->file('certificate');
            $binaryData = file_get_contents($file->getRealPath());
            $data['certificate'] = $binaryData;
        }
        // Handle base64 input (manual certificates fallback)
        elseif ($hasBase64) {
            $base64Data = $base64Input;
            
            // Strip data URL prefix if present (e.g., "data:image/png;base64,")
            if (str_starts_with($base64Data, 'data:')) {
                $commaPos = strpos($base64Data, ',');
                if ($commaPos !== false) {
                    $base64Data = substr($base64Data, $commaPos + 1);
                }
            }
            
            // Decode base64 to binary
            $decoded = base64_decode($base64Data, true); // strict mode

            if ($decoded === false) {
                Log::error('❌ Failed to decode base64', [
                    'base64_length' => strlen($base64Data),
                    'base64_preview' => substr($base64Data, 0, 50),
                ]);
                return $this->safeJsonResponse([
                    'message' => 'Invalid base64 certificate data provided.',
                    'errors' => ['certificate_base64' => ['Unable to decode certificate_base64 payload']]
                ], 422);
            }

            $data['certificate'] = $decoded;
            Log::info('✅ Decoded base64 certificate', [
                'original_length' => strlen($base64Input),
                'decoded_length' => strlen($decoded),
            ]);
        }
        // For organization certificates without file, don't include certificate field at all
        // This matches RegistrationController which creates certificates without certificate field
        // (see RegistrationController line 193-198)

        // Handle certificate_path (organization-issued certificates)
        if ($hasPath) {
            // Check if certification already exists for this path
            $existing = Certification::where('applicantID', $request->applicantID)
                ->where('certificate_path', $request->certificate_path)
                ->first();
            
            if ($existing) {
                // Update existing certification
                $existing->certificationName = $request->certificationName;
                $updateIsSelected = $request->IsSelected ?? $existing->IsSelected;
                if (is_string($updateIsSelected)) {
                    $updateIsSelected = (int) $updateIsSelected;
                }
                $existing->IsSelected = $updateIsSelected ? 1 : 0;
                $existing->save();
                
                // Build safe response without binary data
                $safeCert = [
                    'certificationID' => $existing->certificationID,
                    'certificationName' => $existing->certificationName,
                    'applicantID' => $existing->applicantID,
                    'resumeID' => $existing->resumeID,
                    'IsSelected' => (int) $existing->IsSelected,
                    'certificate_path' => $existing->certificate_path,
                ];
                
                return $this->safeJsonResponse([
                    'message' => 'Certificate updated successfully!',
                    'certificationID' => $existing->certificationID,
                ]);
            }
            
            $data['certificate_path'] = $request->certificate_path;
            // For organization certificates, provide empty string for certificate field
            // since the database column is NOT NULL and doesn't have a default value
            $data['certificate'] = '';
        }

        // Create the certification
        // For organization certificates, don't include certificate field (like RegistrationController does)
        try {
            Log::info('📦 Attempting to create certification', [
                'data_keys' => array_keys($data),
                'has_certificate' => isset($data['certificate']),
                'has_certificate_path' => isset($data['certificate_path']),
            ]);
            
            $cert = Certification::create($data);
            
            Log::info('✅ Certification created successfully', [
                'certificationID' => $cert->certificationID,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to create certification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data_keys' => array_keys($data),
            ]);
            
            return $this->safeJsonResponse([
                'message' => 'Failed to create certificate: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }

        // Build safe response without binary data
        $safeCert = [
            'certificationID' => $cert->certificationID,
            'certificationName' => $cert->certificationName,
            'applicantID' => $cert->applicantID,
            'resumeID' => $cert->resumeID,
            'IsSelected' => (int) $cert->IsSelected,
            'certificate_path' => $cert->certificate_path,
        ];

        return $this->safeJsonResponse([
            'message' => 'Certificate uploaded successfully!',
            'certificationID' => $cert->certificationID,
        ]);
    }


    // ✅ Delete a certificate
       // Delete a certificate
        public function destroy($id)
        {
            $cert = Certification::find($id);

            if (!$cert) {
                return $this->safeJsonResponse(['message' => 'Certificate not found'], 404);
            }

            $cert->delete();

            return $this->safeJsonResponse(['message' => 'Certificate deleted successfully']);
        }

    public function toggleSelection($id)
{
    $cert = Certification::find($id);

    if (!$cert) {
        return $this->safeJsonResponse(['message' => 'Certificate not found'], 404);
    }

    // ✅ Flip the IsSelected value properly (handle null or string values)
    $cert->IsSelected = $cert->IsSelected ? 0 : 1;
    $cert->save();

    return $this->safeJsonResponse([
        'message' => $cert->IsSelected
            ? 'Certificate added to resume'
            : 'Certificate removed from resume',
        'IsSelected' => (int) $cert->IsSelected, // ✅ always return as integer
        'certificationID' => $cert->certificationID,
    ]);
}



// ✅ Get selected certificates for resume
public function selectedCertificates($applicantID)
{
    // Fetch all selected certificates (both manual and organization-issued)
    $certificates = Certification::where('applicantID', $applicantID)
        ->where('IsSelected', 1)
        ->get();

    $formattedCertificates = [];
    
    foreach ($certificates as $cert) {
        $certData = [
            'certificationID' => $cert->certificationID,
            'certificationName' => $cert->certificationName,
            'applicantID' => $cert->applicantID,
            'IsSelected' => (int) $cert->IsSelected,
        ];
        
        // If certificate has certificate_path (organization-issued), use Supabase URL
        if (!empty($cert->certificate_path)) {
            $certData['certificate'] = $this->generateSupabaseUrl($cert->certificate_path);
            $certData['certificate_path'] = $cert->certificate_path;
            $certData['source'] = 'organization';
        } 
        // If certificate has binary data (manual upload), convert to data URL
        elseif ($cert->certificate) {
            $certData['certificate'] = 'data:image/png;base64,' . base64_encode($cert->certificate);
            $certData['source'] = 'manual';
        }
        
        $formattedCertificates[] = $certData;
    }

    return $this->safeJsonResponse($formattedCertificates);
}

public function issueCertificate(Request $request, $certificationID)
{
    $request->validate([
        'issued_certificate' => 'required|file|mimes:pdf|max:4096'
    ]);

    $cert = Certification::findOrFail($certificationID);

    // Initialize Supabase client
    $storage = new StorageClient(
        env('SUPABASE_URL'),
        env('SUPABASE_SECRET')
    );

    $bucket = env('SUPABASE_BUCKET', 'Requirements'); // default bucket

    // Use certificate_directory instead of issued_certificates
    $fileName = 'certificate_directory/' . time() . '_' . $request->file('issued_certificate')->getClientOriginalName();
    $fileBytes = file_get_contents($request->file('issued_certificate'));

    // Upload to Supabase
    $result = $storage->from($bucket)->upload($fileName, $fileBytes);

    if (!empty($result['error'])) {
        return $this->safeJsonResponse([
            'message' => 'Failed to upload issued certificate',
            'error' => $result['error']
        ], 500);
    }

    // Save the path to DB
    $cert->certificate_path = $fileName;
    $cert->save();

    // Get a public URL
    $publicUrl = $storage->from($bucket)->getPublicUrl($fileName);

    return $this->safeJsonResponse([
        'message' => 'Certificate issued successfully',
        'path' => $fileName,
        'public_url' => $publicUrl
    ]);
}

    protected function safeJsonResponse($data, int $status = 200)
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
        try {
            return response()->json($data, $status, [], $options);
        } catch (\JsonException $e) {
            $sanitized = $this->sanitizeUtf8($data);
            return response()->json($sanitized, $status, [], $options);
        }
    }

    protected function sanitizeUtf8($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->sanitizeUtf8($item);
            }
            return $value;
        }

        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $value;
    }



}