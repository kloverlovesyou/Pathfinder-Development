<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    // Show all certificates for an applicant
    public function index($applicantID)
    {
        $certificates = Certification::where('applicantID', $applicantID)->get();

        // Convert binary data to base64
        foreach ($certificates as $cert) {
            if ($cert->certificate) {
                $cert->certificate = 'data:image/png;base64,' . base64_encode($cert->certificate);
            }
        }

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

        // Store the image as BLOB
        $file = $request->file('certificate');
        $binaryData = file_get_contents($file->getRealPath());

        $cert = Certification::create([
            'certificationName' => $request->certificationName,
            'certificate' => $binaryData,
            'applicantID' => $request->applicantID
        ]);

        // ✅ Don’t return the raw binary field
        $safeCert = $cert->toArray();
        unset($safeCert['certificate']);

        return response()->json([
            'message' => 'Certificate uploaded successfully!',
            'data' => $safeCert,
        ]);
    }
}