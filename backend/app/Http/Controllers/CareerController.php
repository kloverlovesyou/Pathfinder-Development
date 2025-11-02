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
                    'id' => $career->careerID,
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
        $user = $request->user();

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
            'tags' => $validated['Tags'] ?? []
        ], 201);
    }
}
