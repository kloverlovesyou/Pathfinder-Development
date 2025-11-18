<?php

namespace App\Http\Controllers;

use App\Models\Education;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    // ✅ Show all education for a given resume
    public function show(Request $request)
    {
        $resumeID = $request->query('resumeID');
        $education = Education::where('resumeID', $resumeID)->get();

        // The column is an INT, so it already holds a 4-digit year.
        // We will keep the following transformation logic in case the data
        // was originally stored as a full date string (legacy data) or if
        // the front-end strictly expects a string representation of the year.
        $education->transform(function ($edu) {
            if (!empty($edu->graduationYear)) {
                // If it's a number (2024), strtotime() is safe. If it's a date string, it converts to 'YYYY'.
                $edu->graduationYear = date('Y', strtotime($edu->graduationYear));
            }
            return $edu;
        });

        return response()->json($education);
    }

    // ✅ Store new education
    public function store(Request $request)
    {
        $validated = $request->validate([
            'educationLevel' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',
            'institutionName' => 'required|string|max:255',
            'institutionAddress' => 'nullable|string|max:255',
            // Validation ensures it is a 4-digit year, which is correct for an INT column.
            'graduationYear' => 'nullable|digits:4', 
            'resumeID' => 'required|exists:resume,resumeID',
        ]);

        // ❌ FIX: Removed the concatenation to '-01-01'. 
        // Since the DB column is INT, we must only pass the 4-digit year number.
        // The `$validated['graduationYear']` already holds the correct integer year.
        
        $education = Education::create($validated);

        // We return the year directly as it was saved as an INT.
        return response()->json($education, 201);
    }

    // ✅ Update education
    public function update(Request $request, $id)
    {
        $education = Education::findOrFail($id);

        $validated = $request->validate([
            'educationLevel' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'institutionName' => 'nullable|string|max:255',
            'institutionAddress' => 'nullable|string|max:255',
            'graduationYear' => 'nullable|digits:4',
        ]);

        // ❌ FIX: Removed the concatenation to '-01-01'. 
        // Since the DB column is INT, we must only pass the 4-digit year number.
        // The `$validated['graduationYear']` already holds the correct integer year.

        $education->update($validated);

        // We return the year directly as it was updated as an INT.
        return response()->json($education);
    }

    // ✅ Delete education
    public function destroy($id)
    {
        $education = Education::findOrFail($id);
        $education->delete();

        return response()->json(['message' => 'Education deleted']);
    }
}