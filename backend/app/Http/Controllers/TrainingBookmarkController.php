<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrainingBookmark;
use App\Models\Training;

class TrainingBookmarkController extends Controller
{
    // ✅ Fetch all bookmarks for current applicant
    public function index(Request $request)
    {
        $user = $request->user(); // from middleware

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $bookmarks = TrainingBookmark::where('applicantID', $user->applicantID)
            ->pluck('trainingID'); // returns just an array of IDs

        return response()->json($bookmarks);
    }

    // ✅ Store a bookmark
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $trainingID = $request->trainingID;

        // Prevent duplicates
        $exists = TrainingBookmark::where('applicantID', $user->applicantID)
            ->where('trainingID', $trainingID)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already bookmarked'], 409);
        }

        $bookmark = new TrainingBookmark();
        $bookmark->applicantID = $user->applicantID;
        $bookmark->trainingID = $trainingID;
        $bookmark->save();

        return response()->json(['message' => 'Bookmarked successfully'], 201);
    }

    // ✅ Remove a bookmark
    public function destroy(Request $request, $trainingID)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $bookmark = TrainingBookmark::where('applicantID', $user->applicantID)
            ->where('trainingID', $trainingID)
            ->first();

        if (!$bookmark) {
            return response()->json(['message' => 'Bookmark not found'], 404);
        }

        $bookmark->delete();

        return response()->json(['message' => 'Bookmark removed successfully']);
    }
}