<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Supabase\Storage\StorageClient;

class CertificateController extends Controller
{
  
public function index($applicantID)
{
    $certificates = Certification::where('applicantID', $applicantID)->get();

    $certificates = $certificates->map(function ($cert) {
        if (!$cert->certificate) {
            return [
                'certificationID' => $cert->certificationID,
                'certificationName' => $cert->certificationName,
                'certificate' => null,
                'fileType' => null,
                'applicantID' => $cert->applicantID,
                'IsSelected' => (int) $cert->IsSelected,
            ];
        }

        // All certificates are now images (PDFs are converted to PNG during upload)
        $fileContent = $cert->certificate;
        
        // Validate that we have actual binary data
        if (empty($fileContent) || strlen($fileContent) < 10) {
            Log::warning('Certificate has invalid or empty data', [
                'certificationID' => $cert->certificationID,
                'name' => $cert->certificationName,
                'dataSize' => strlen($fileContent)
            ]);
            return [
                'certificationID' => $cert->certificationID,
                'certificationName' => $cert->certificationName,
                'certificate' => null,
                'fileType' => null,
                'mimeType' => null,
                'applicantID' => $cert->applicantID,
                'IsSelected' => (int) $cert->IsSelected,
            ];
        }
        
        $mimeType = 'image/png'; // default (PDFs converted to PNG)
        
        // Use finfo to detect MIME type if available
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_buffer($finfo, $fileContent);
            finfo_close($finfo);
            
            if ($detectedMime) {
                // If it's still a PDF (legacy data), we can't display it as image
                if ($detectedMime === 'application/pdf') {
                    Log::warning('Legacy PDF certificate found (needs re-upload)', [
                        'certificationID' => $cert->certificationID,
                        'name' => $cert->certificationName
                    ]);
                    // Return null so frontend can handle it gracefully
                    return [
                        'certificationID' => $cert->certificationID,
                        'certificationName' => $cert->certificationName,
                        'certificate' => null,
                        'fileType' => null,
                        'mimeType' => 'application/pdf',
                        'applicantID' => $cert->applicantID,
                        'IsSelected' => (int) $cert->IsSelected,
                    ];
                }
                $mimeType = $detectedMime;
            }
        } else {
            // Fallback to magic bytes
            $firstBytes = substr($fileContent, 0, 8);
            // Check for JPEG
            if (substr($firstBytes, 0, 2) === "\xFF\xD8") {
                $mimeType = 'image/jpeg';
            }
            // Check for PNG (most common after PDF conversion)
            elseif (substr($firstBytes, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
                $mimeType = 'image/png';
            }
            // Check for PDF (legacy)
            elseif (substr($firstBytes, 0, 4) === "%PDF") {
                Log::warning('Legacy PDF certificate found (needs re-upload)', [
                    'certificationID' => $cert->certificationID,
                    'name' => $cert->certificationName
                ]);
                return [
                    'certificationID' => $cert->certificationID,
                    'certificationName' => $cert->certificationName,
                    'certificate' => null,
                    'fileType' => null,
                    'mimeType' => 'application/pdf',
                    'applicantID' => $cert->applicantID,
                    'IsSelected' => (int) $cert->IsSelected,
                ];
            }
        }

        // Validate base64 encoding will work
        try {
            $base64Data = base64_encode($fileContent);
            if (empty($base64Data)) {
                throw new \Exception('Base64 encoding failed');
            }
            $dataUrl = 'data:' . $mimeType . ';base64,' . $base64Data;
        } catch (\Exception $e) {
            Log::error('Failed to create data URL for certificate', [
                'certificationID' => $cert->certificationID,
                'name' => $cert->certificationName,
                'error' => $e->getMessage()
            ]);
            return [
                'certificationID' => $cert->certificationID,
                'certificationName' => $cert->certificationName,
                'certificate' => null,
                'fileType' => null,
                'mimeType' => null,
                'applicantID' => $cert->applicantID,
                'IsSelected' => (int) $cert->IsSelected,
            ];
        }

        Log::info('Certificate retrieved', [
            'certificationID' => $cert->certificationID,
            'name' => $cert->certificationName,
            'mimeType' => $mimeType,
            'dataSize' => strlen($fileContent),
            'base64Size' => strlen($base64Data),
            'dataUrlPrefix' => substr($dataUrl, 0, 50)
        ]);

        return [
            'certificationID' => $cert->certificationID,
            'certificationName' => $cert->certificationName,
            'certificate' => $dataUrl,
            'fileType' => 'image', // All are images now (PDFs converted)
            'mimeType' => $mimeType,
            'applicantID' => $cert->applicantID,
            'IsSelected' => (int) $cert->IsSelected,
        ];
    });

    return response()->json($certificates);
}

    // Upload a new certificate
    public function store(Request $request)
    {
        $request->validate([
            'certificationName' => 'required|string|max:255',
            'certificate' => 'required|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'applicantID' => 'required|integer'
        ]);

        $file = $request->file('certificate');
        $mimeType = $file->getMimeType();
        $binaryData = null;

        // If file is PDF, convert to image
        if ($mimeType === 'application/pdf' || $file->getClientOriginalExtension() === 'pdf') {
            try {
                // Check if Imagick is available (required for PDF conversion)
                if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                    return response()->json([
                        'message' => 'PDF conversion requires Imagick PHP extension. Please install it or upload an image instead.',
                        'error' => 'IMAGICK_NOT_AVAILABLE'
                    ], 400);
                }

                // Convert PDF first page to image using Imagick
                $imagick = new \Imagick();
                $imagick->setResolution(300, 300); // High resolution for better quality
                $imagick->readImage($file->getRealPath() . '[0]'); // [0] means first page only
                $imagick->setImageFormat('png');
                $imagick->setImageCompressionQuality(95);
                
                // Get image binary data
                $binaryData = $imagick->getImageBlob();
                $imagick->clear();
                $imagick->destroy();
                
                Log::info('PDF converted to image successfully', [
                    'original_size' => $file->getSize(),
                    'converted_size' => strlen($binaryData)
                ]);
            } catch (\Exception $e) {
                Log::error('PDF to image conversion failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'message' => 'Failed to convert PDF to image: ' . $e->getMessage(),
                    'error' => 'PDF_CONVERSION_FAILED'
                ], 500);
            }
        } else {
            // For images, just get the binary data
            $binaryData = file_get_contents($file->getRealPath());
        }

        $cert = Certification::create([
            'certificationName' => $request->certificationName,
            'certificate' => $binaryData,
            'applicantID' => $request->applicantID,
            'IsSelected' => 1,
            
        ]);

        $safeCert = $cert->toArray();
        unset($safeCert['certificate']);

        return response()->json([
            'message' => 'Certificate uploaded successfully!',
            'data' => $safeCert,
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
    $cert = Certification::find($id);

    if (!$cert) {
        return response()->json(['message' => 'Certificate not found'], 404);
    }

    // ✅ Flip the IsSelected value properly (handle null or string values)
    $cert->IsSelected = $cert->IsSelected ? 0 : 1;
    $cert->save();

    return response()->json([
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
    $certificates = Certification::where('applicantID', $applicantID)
        ->where('IsSelected', 1)
        ->get();

    // Convert to base64 for display
    foreach ($certificates as $cert) {
        if ($cert->certificate) {
            $cert->certificate = 'data:image/png;base64,' . base64_encode($cert->certificate);
        }
    }

    return response()->json($certificates);
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