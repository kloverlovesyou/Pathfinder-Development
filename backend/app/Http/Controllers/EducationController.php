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
            if (!is_null($edu->GWA)) {
                $edu->GWA = (float) $edu->GWA;
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
            'minor' => 'nullable|string|max:255',
            'strand' => 'nullable|string|max:255',
            'GWA' => 'nullable|numeric|min:1|max:5',
            'institutionName' => 'required|string|max:255',
            'institutionAddress' => 'nullable|string|max:255',
            'graduationYear' => 'nullable|digits:4',
            'resumeID' => 'required|exists:resume,resumeID',
        ]);

        // Save year as integer
        if (!empty($validated['graduationYear'])) {
            $validated['graduationYear'] = (int) $validated['graduationYear'];
        }
        if (array_key_exists('GWA', $validated) && $validated['GWA'] !== null && $validated['GWA'] !== '') {
            $validated['GWA'] = (float) $validated['GWA'];
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
            'minor' => 'nullable|string|max:255',
            'strand' => 'nullable|string|max:255',
            'GWA' => 'nullable|numeric|min:1|max:5',
            'institutionName' => 'nullable|string|max:255',
            'institutionAddress' => 'nullable|string|max:255',
            'graduationYear' => 'nullable|digits:4',
        ]);

        if (!empty($validated['graduationYear'])) {
            $validated['graduationYear'] = (int) $validated['graduationYear'];
        }
        if (array_key_exists('GWA', $validated)) {
            $validated['GWA'] = $validated['GWA'] !== null && $validated['GWA'] !== '' ? (float) $validated['GWA'] : null;
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
