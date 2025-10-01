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

        // Format graduationYear to just the year (YYYY)
        $education->transform(function ($edu) {
            if (!empty($edu->graduationYear)) {
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
            'graduationYear' => 'nullable|digits:4', // only 4-digit year
            'resumeID' => 'required|exists:resume,resumeID',
        ]);

        if (!empty($validated['graduationYear'])) {
            $validated['graduationYear'] = $validated['graduationYear'] . '-01-01';
        }

        $education = Education::create($validated);

        // Return year only
        if (!empty($education->graduationYear)) {
            $education->graduationYear = date('Y', strtotime($education->graduationYear));
        }

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

        if (!empty($validated['graduationYear'])) {
            $validated['graduationYear'] = $validated['graduationYear'] . '-01-01';
        }

        $education->update($validated);

        // Return year only
        if (!empty($education->graduationYear)) {
            $education->graduationYear = date('Y', strtotime($education->graduationYear));
        }

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