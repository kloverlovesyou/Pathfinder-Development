<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Services\SupabaseClient;

class ApplicationFileController extends Controller
{
    public function generateSignedUrl(Request $request, $applicationID)
    {
        $user = $request->user();

        $application = Application::find($applicationID);
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        if ($application->career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!$application->requirements) {
            return response()->json(['message' => 'Requirements not found'], 404);
        }

        $signedUrl = URL::temporarySignedRoute(
            'signed.requirements.view',
            now()->addMinutes(5),
            [
                'application' => $application->applicationID,
                'organization' => $user->organizationID, // must match route param
            ]
        );

        return response()->json([
            'url' => $signedUrl,
            'expires_in' => 5,
        ]);
    }

    public function serveSigned(Request $request, Application $application)
    {
        $orgID = $request->route('organization'); // match route param

        if ($application->career->organizationID !== (int)$orgID) {
            abort(403, 'Invalid signer');
        }

        $path = $application->requirements;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        return response()->file(
            Storage::disk('public')->path($path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.basename($path).'"'
            ]
        );
    }

    public function getFileUrl($filename)
    {
        $storage = SupabaseClient::storage();

        $bucket = $storage->from('Requirements');

        $filePath = "requirement_directory/" . $filename;

        $publicUrl = $bucket->getPublicUrl($filePath);

        return response()->json([
            'url' => $publicUrl
        ]);
    }

    public function show($applicationId)
{
    // find the application
    $app = Application::findOrFail($applicationId);

    // check if file exists
    if (!$app->requirement_directory) {
        return response()->json([
            'error' => 'No requirement uploaded'
        ], 404);
    }

    // generate public URL
    $storage = SupabaseClient::storage();
    $bucket = $storage->from('Requirements');

    // NOTE: you stored the full path like "requirement_directory/xyz.pdf"
    $filePath = $app->requirement_directory;

    $publicUrl = $bucket->getPublicUrl($filePath);

    return response()->json([
        'url' => $publicUrl
    ]);
}

}
