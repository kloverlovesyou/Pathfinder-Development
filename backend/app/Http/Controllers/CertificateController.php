<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use App\Models\Registration;
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
    Log::info('🔍 Fetching certificates for applicant', ['applicantID' => $applicantID]);
    
    $allCertificates = [];
    
    // Step 1: Fetch all certificates from certifications table that have certificate_path
    // All certificates now use certificate_path (stored in Supabase)
    $certifications = Certification::where('applicantID', $applicantID)
        ->whereNotNull('certificate_path')
        ->get();
    
    foreach ($certifications as $cert) {
        if (empty($cert->certificate_path)) {
            continue;
        }
        
        // Generate Supabase public URL for this certificate
        $publicUrl = $this->generateSupabaseUrl($cert->certificate_path);
        
        // Determine source: if it's from a registration, it's organization-issued, otherwise manual
        $source = 'manual'; // Default to manual for user-uploaded certificates
        
        $allCertificates[] = [
            'certificationID' => $cert->certificationID,
            'certificationName' => $cert->certificationName,
            'certificate' => $publicUrl, // Supabase public URL
            'applicantID' => $cert->applicantID,
            'IsSelected' => (int) $cert->IsSelected,
            'source' => $source,
            'certificate_path' => $cert->certificate_path,
        ];
    }
    
    // Step 2: Fetch organization-issued certificates from registrations (Supabase)
    // Get all registrations with certificatePath for this applicant
    $registrations = Registration::with('training')
        ->where('applicantID', $applicantID)
        ->whereNotNull('certificatePath')
        ->where('certificatePath', '!=', '')
        ->get();
    
    Log::info('📋 Found registrations with certificates', [
        'applicantID' => $applicantID,
        'count' => $registrations->count(),
    ]);
    
    // Step 3: Create certificates from registrations - fetch from Supabase using certificate_path
    $existingPaths = array_column($allCertificates, 'certificate_path');
    
    foreach ($registrations as $registration) {
        $certificatePath = $registration->certificatePath;
        $training = $registration->training;
        
        if (empty($certificatePath)) {
            continue;
        }
        
        // Skip if already added from certifications table
        if (in_array($certificatePath, $existingPaths)) {
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
    
    Log::info('🎯 Returning certificates', [
        'applicantID' => $applicantID,
        'totalCount' => count($allCertificates),
        'organizationCount' => count(array_filter($allCertificates, fn($c) => ($c['source'] ?? '') === 'organization')),
        'manualCount' => count(array_filter($allCertificates, fn($c) => ($c['source'] ?? '') === 'manual')),
    ]);

    return response()->json($allCertificates, 200, [], JSON_UNESCAPED_SLASHES);
}

    // Upload a new certificate
    public function store(Request $request)
    {
        $request->validate([
            'certificationName' => 'required|string|max:255',
            'certificate' => 'required|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'applicantID' => 'required|integer',
            'IsSelected' => 'sometimes|integer|in:0,1',
        ]);

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

        // Handle file upload - upload to Supabase and store path
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            
            // Initialize Supabase client
            $storage = new StorageClient(
                env('SUPABASE_URL'),
                env('SUPABASE_SECRET')
            );
            
            $bucket = env('SUPABASE_BUCKET', 'Requirements');
            
            // Generate unique filename
            $fileName = 'certificate_directory/' . time() . '_' . $request->applicantID . '_' . $file->getClientOriginalName();
            $fileBytes = file_get_contents($file->getRealPath());
            
            // Upload to Supabase
            $result = $storage->from($bucket)->upload($fileName, $fileBytes);
            
            if (!empty($result['error'])) {
                Log::error('❌ Failed to upload certificate to Supabase', [
                    'error' => $result['error'],
                    'fileName' => $fileName,
                ]);
                return response()->json([
                    'message' => 'Failed to upload certificate to storage',
                    'error' => $result['error']
                ], 500);
            }
            
            // Store the path instead of binary data
            $data['certificate_path'] = $fileName;
            
            Log::info('✅ Certificate uploaded to Supabase', [
                'certificate_path' => $fileName,
                'applicantID' => $request->applicantID,
            ]);
        }

        // Ensure certificate_path is set
        if (empty($data['certificate_path'])) {
            return response()->json([
                'message' => 'Certificate upload failed.',
            ], 422);
        }

        $cert = Certification::create($data);

        $safeCert = $cert->toArray();
        unset($safeCert['certificate']);

        return response()->json([
            'message' => 'Certificate saved successfully!',
            'data' => $safeCert,
            'certificationID' => $cert->certificationID,
        ]);
    }

    // ✅ Delete a certificate
       // Delete a certificate
        public function destroy($id)
        {
            $cert = Certification::find($id);

            if (!$cert) {
                return response()->json(['message' => 'Certificate not found'], 404);
            }

            $cert->delete();

            return response()->json(['message' => 'Certificate deleted successfully']);
        }

public function toggleSelection($id)
{
    try {
        $cert = Certification::find($id);

        if (!$cert) {
            return response()->json(['message' => 'Certificate not found'], 404);
        }

        // ✅ Flip the IsSelected value properly (handle null, 0, 1, or string values)
        // Convert to integer first to handle null/string cases
        $currentValue = (int) ($cert->IsSelected ?? 0);
        $cert->IsSelected = $currentValue ? 0 : 1;
        
        if (!$cert->save()) {
            Log::error('Failed to save certificate toggle', [
                'certificationID' => $id,
                'IsSelected' => $cert->IsSelected
            ]);
            return response()->json([
                'message' => 'Failed to update certificate selection'
            ], 500);
        }

        return response()->json([
            'message' => $cert->IsSelected
                ? 'Certificate added to resume'
                : 'Certificate removed from resume',
            'IsSelected' => (int) $cert->IsSelected, // ✅ always return as integer
            'certificationID' => $cert->certificationID,
        ]);
    } catch (\Exception $e) {
        Log::error('Error toggling certificate selection', [
            'certificationID' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'An error occurred while updating certificate selection',
            'error' => $e->getMessage()
        ], 500);
    }
}

// ✅ Toggle or create organization certificate selection (name only for resume)
public function toggleOrganizationCertificate(Request $request)
{
    $request->validate([
        'applicantID' => 'required|integer',
        'certificationName' => 'required|string|max:255',
        'certificate_path' => 'required|string',
    ]);
    
    $applicantID = $request->applicantID;
    $certificatePath = $request->certificate_path;
    
    // Find existing certification by certificate_path
    $existing = Certification::where('applicantID', $applicantID)
        ->where('certificate_path', $certificatePath)
        ->first();
    
    if ($existing) {
        // Toggle existing certification
        $existing->IsSelected = $existing->IsSelected ? 0 : 1;
        $existing->save();
        
        return response()->json([
            'message' => $existing->IsSelected
                ? 'Certificate added to resume'
                : 'Certificate removed from resume',
            'IsSelected' => (int) $existing->IsSelected,
            'certificationID' => $existing->certificationID,
            'certificationName' => $existing->certificationName,
        ]);
    }
    
    // Create new certification entry (name only for resume)
    $cert = Certification::create([
        'certificationName' => $request->certificationName,
        'certificate_path' => $certificatePath,
        'applicantID' => $applicantID,
        'IsSelected' => 1, // Auto-select when adding to resume
    ]);
    
    return response()->json([
        'message' => 'Certificate added to resume',
        'IsSelected' => 1,
        'certificationID' => $cert->certificationID,
        'certificationName' => $cert->certificationName,
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

    return response()->json($formattedCertificates);
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
        return response()->json([
            'message' => 'Failed to upload issued certificate',
            'error' => $result['error']
        ], 500);
    }

    // Save the path to DB
    $cert->certificate_path = $fileName;
    $cert->save();

    // Get a public URL
    $publicUrl = $storage->from($bucket)->getPublicUrl($fileName);

    return response()->json([
        'message' => 'Certificate issued successfully',
        'path' => $fileName,
        'public_url' => $publicUrl
    ]);
}



}