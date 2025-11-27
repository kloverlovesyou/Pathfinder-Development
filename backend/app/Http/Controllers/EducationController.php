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

        $education->transform(function ($edu) {
            if (!empty($edu->graduationYear)) {
                $edu->graduationYear = (int) $edu->graduationYear;
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
            'program' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'strand' => 'nullable|string|max:255',
            'institutionName' => 'required|string|max:255',
            'institutionAddress' => 'nullable|string|max:1000',
            'graduationYear' => 'nullable|digits:4',
            'resumeID' => 'required|exists:resume,resumeID',
        ]);

        // Remove GWA and minor if they exist in the request (backward compatibility)
        unset($validated['GWA'], $validated['minor']);

        // Save year as integer
        if (!empty($validated['graduationYear'])) {
            $validated['graduationYear'] = (int) $validated['graduationYear'];
        }

        $education = Education::create($validated);

        return response()->json($education, 201);
    }

    // ✅ Update education
    public function update(Request $request, $id)
    {
        $education = Education::findOrFail($id);

        $validated = $request->validate([
            'educationLevel' => 'nullable|string|max:255',
            'program' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'strand' => 'nullable|string|max:255',
            'institutionName' => 'nullable|string|max:255',
            'institutionAddress' => 'nullable|string|max:1000',
            'graduationYear' => 'nullable|digits:4',
        ]);

        // Remove GWA and minor if they exist in the request (backward compatibility)
        unset($validated['GWA'], $validated['minor']);

        if (!empty($validated['graduationYear'])) {
            $validated['graduationYear'] = (int) $validated['graduationYear'];
        }

        $education->update($validated);

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
