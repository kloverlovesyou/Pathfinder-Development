<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CareerBookmark;

class CareerBookmarkController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only allow applicants to fetch bookmarks
        if (!($user instanceof \App\Models\Applicant)) {
            return response()->json(['message' => 'Only applicants can have bookmarks'], 403);
        }

        $bookmarks = CareerBookmark::where('applicantID', $user->applicantID)->get();
        return response()->json($bookmarks);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Only applicants can bookmark careers
        if (!($user instanceof \App\Models\Applicant)) {
            return response()->json(['message' => 'Only applicants can bookmark careers'], 403);
        }

        $validated = $request->validate([
            'careerID' => 'required|integer|exists:careers,careerID',
        ]);

        $careerID = $validated['careerID'];

        $exists = CareerBookmark::where('applicantID', $user->applicantID)
            ->where('careerID', $careerID)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already bookmarked'], 409);
        }

        $bookmark = CareerBookmark::create([
            'applicantID' => $user->applicantID,
            'careerID' => $careerID,
        ]);

        return response()->json($bookmark, 201);
    }

    public function destroy(Request $request, $careerID)
    {
        $user = $request->user();

        if (!($user instanceof \App\Models\Applicant)) {
            return response()->json(['message' => 'Only applicants can remove bookmarks'], 403);
        }

        $bookmark = CareerBookmark::where('applicantID', $user->applicantID)
            ->where('careerID', $careerID)
            ->first();

        if (!$bookmark) return response()->json(['message' => 'Bookmark not found'], 404);

        $bookmark->delete();
        return response()->json(['message' => 'Bookmark removed successfully']);
    }
}