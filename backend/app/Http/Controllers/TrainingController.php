<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrainingController extends Controller
{
    public function index()
    {
        //fetch all trainings
        $trainings = Training::with('organization')->get();

        $formatted = $trainings->map(function ($training){
            return[
                'trainingID' => $training->trainingID,
                'title' => $training->title,
                'description' => $training->description,
                'schedule' => $training->schedule 
                ? Carbon::parse($training->schedule)->format('Y-m-d H:i')
                : null,
                'mode' => $training->mode,
                'location' => $training->location,
                'trainingLink' => $training->trainingLink,
                'organization' => [
                    'organizationID' => $training->organizationID,
                        'name' => optional($training->organization)->name ?? 'Unknown',
                ],
                                
            ];
        }); 
        
        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - no auth user found'], 401);
        }

        // Ensure only organizations can post trainings
        if (!isset($user->organizationID)) {
            return response()->json(['message' => 'Only organizations can create trainings'], 403);
        }

        //validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'schedule' => 'required',
            'mode' => 'required|string|in:On-Site,Online',
            'location' => 'nullable|string|max:255',
            'training_link' => 'nullable|url',
            'Tags' => 'nullable|array',
            'Tags.*' => 'integer|exists:tag,TagID',
        ]);

        $schedule = Carbon::parse($validated['schedule'])->format('Y-m-d H:i');

        //create the training method
        $training = Training::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'schedule' => $schedule,
            'mode' => $validated['mode'],
            'location' => $validated['location'] ?? null,
            'trainingLink' => $validated['training_link'] ?? null,
            'organizationID' => $user->organizationID,
        ]);

        //handle tags
        if (!empty($validated['Tags'])) {
            $training->tags()->attach($validated['Tags']);
        }
        
        /* if (!empty($validated['Tags'])) {
            foreach ($validated['Tags'] as $tagID) {
                DB::table('TrainingTag')->insert([
                    'TrainingID' => $training->TrainingID,
                    'TagID' => $tagID
                ]);
            }
        } */

        return response()->json([
            'message' => 'TRAINING CREATED SUCCESSFULLY!',
            'data' => $training->load('organization')
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
