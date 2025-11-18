<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function getCareers()
    {
        $careers = Career::select('careerID', 'position')->get();

        return response()->json([
            'status' => 'success',
            'careers' => $careers,
        ]);
    }
    
    public function index()
    {
        //fetch all careers
        $careers = Career::with('organization')
           ->orderByDesc('deadlineOfSubmission')
           ->get()
           ->map(function ($career){
                  return[
                    'careerID' => $career->careerID,
                    'position' => $career->position,
                    'deadlineOfSubmission' => $career->deadlineOfSubmission,
                    'detailsAndInstructions' => $career->detailsAndInstructions,
                    'qualifications' => $career->qualifications,
                    'requirements' => $career->requirements,
                    'applicationLetterAddress' => $career->applicationLetterAddress,
                    'organizationID' => $career->organizationID,
                    'organizationName' => $career->organization->name ?? 'Unknown'
                  ];

           });

        return response()->json($careers);
    }
        public function store(Request $request)
    {
        // ✅ Use authenticated user from middleware
         $token = $request->bearerToken();
         // Try both models since you have separate tables
        $user = \App\Models\Organization::where('api_token', $token)->first()
            ?? \App\Models\Applicant::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate request
        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'details' => 'required|string|max:255',
            'qualifications' => 'required|string|max:255',
            'requirements' => 'required|string|max:255',
            'letterAddress' => 'required|string|max:255',
            'deadline' => 'required|date',
            'Tags' => 'nullable|array',
            'Tags.*' => 'integer|exists:tag,TagID',
        ]);

        $deadline = Carbon::parse($validated['deadline'])->format('Y-m-d');

        // Create career linked to organization
        $career = Career::create([
            'position' => $validated['position'],
            'detailsAndInstructions' => $validated['details'],
            'qualifications' => $validated['qualifications'],
            'requirements' => $validated['requirements'],
            'applicationLetterAddress' => $validated['letterAddress'],
            'deadlineOfSubmission' => $deadline,
            'organizationID' => $user->organizationID ?? $user->id,
        ]);

        // Attach tags if any
        if (!empty($validated['Tags'])) {
            $career->tags()->attach($validated['Tags']);
        }

        return response()->json([
            'message' => 'CAREER POSTED SUCCESSFULLY!!!',
            'data' => $career,
            'tags' => $validated['Tags'] 
        ], 201);
    }


//This is for Total numbers of career
public function total() {
    $totalCareers = \App\Models\Career::count();
    return response()->json(['totalCareers' => $totalCareers]);
}

//This is for numbers of on-going and filled out career
public function countsPartial()
{
    $now = now(); // current date & time

    $ongoing = \App\Models\Career::where('deadlineOfSubmission', '>', $now)->count();
    $filled = \App\Models\Career::where('deadlineOfSubmission', '<=', $now)->count();

    return response()->json([
        'ongoing' => $ongoing,
        'filled' => $filled,
    ]);
}

    public function show($id)
    {
        $career = Career::with('organization')->findOrFail($id);

        return response()->json([
            'careerID' => $career->careerID,
            'title' => $career->position,
            'detailsAndInstructions' => $career->detailsAndInstructions,
            'qualifications' => $career->qualifications,
            'requirements' => $career->requirements,
            'applicationLetterAddress' => $career->applicationLetterAddress,
            'deadlineOfSubmission' => $career->deadlineOfSubmission,
            'organization' => $career->organization->name ?? 'Unknown',
            'link' => $career->link ?? null,
            'mode' => $career->mode ?? null,
            'location' => $career->location ?? null,
        ]);
}
 public function update(Request $request, $id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Validate request (optional but recommended)
        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'detailsAndInstructions' => 'required|string',
            'qualifications' => 'required|string',
            'requirements' => 'required|string',
            'applicationLetterAddress' => 'required|string',
            'deadlineOfSubmission' => 'required|date',
        ]);

        $career->position = $validated['position'];
        $career->detailsAndInstructions = $validated['detailsAndInstructions'];
        $career->qualifications = $validated['qualifications'];
        $career->requirements = $validated['requirements'];
        $career->applicationLetterAddress = $validated['applicationLetterAddress'];
        $career->deadlineOfSubmission = $validated['deadlineOfSubmission'];

        $career->save();

        return response()->json([
            'message' => 'Career updated successfully',
            'data' => $career
        ]);
    }

    // Delete career
    public function destroy($id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        $career->delete();

        return response()->json([
            'message' => 'Career deleted successfully'
        ]);
    }

public function recommend($id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Replace with your logic ↓
        return response()->json([
            'recommendedCareer' => $career
        ]);
    }
}
