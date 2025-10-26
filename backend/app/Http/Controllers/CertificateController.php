<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
  
public function index($applicantID)
{
    $certificates = Certification::where('applicantID', $applicantID)->get();

    $certificates = $certificates->map(function ($cert) {
        return [
            'certificationID' => $cert->certificationID,
            'certificationName' => $cert->certificationName,
            'certificate' => $cert->certificate
                ? 'data:image/png;base64,' . base64_encode($cert->certificate)
                : null,
            'applicantID' => $cert->applicantID,
            'IsSelected' => (int) $cert->IsSelected, // ✅ force integer
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
        $binaryData = file_get_contents($file->getRealPath());

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



}