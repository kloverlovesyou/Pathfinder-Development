<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CareerBookmark;

class CareerBookmarkController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $user = \App\Models\Applicant::where('api_token', $token)->first();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $bookmarks = CareerBookmark::where('applicantID', $user->applicantID)->get();
        return response()->json($bookmarks);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $user = \App\Models\Applicant::where('api_token', $token)->first();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $careerID = $request->careerID;
        if (!$careerID) return response()->json(['message' => 'careerID is required'], 422);

        $exists = CareerBookmark::where('applicantID', $user->applicantID)
            ->where('careerID', $careerID)
            ->exists();

        if ($exists) return response()->json(['message' => 'Already bookmarked'], 409);

        $bookmark = CareerBookmark::create([
            'applicantID' => $user->applicantID,
            'careerID' => $careerID,
        ]);

        return response()->json($bookmark, 201);
    }

    public function destroy(Request $request, $careerID)
    {
        $token = $request->bearerToken();
        $user = \App\Models\Applicant::where('api_token', $token)->first();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $bookmark = CareerBookmark::where('applicantID', $user->applicantID)
            ->where('careerID', $careerID)
            ->first();

        if (!$bookmark) return response()->json(['message' => 'Bookmark not found'], 404);

        $bookmark->delete();
        return response()->json(['message' => 'Bookmark removed successfully']);
    }
}