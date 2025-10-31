<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Attendance;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrainingController extends Controller
{
    /**
     * Automatically generate QR for a training if schedule has started.
     * QR stays valid for 30 minutes from creation.
     */
    public function autoGenerateQR(Training $training)
    {
        $now = now();

        // Generate QR if schedule has started and no key exists
        if ($now->greaterThanOrEqualTo($training->schedule) && !$training->attendance_key) {
            $training->attendance_key = Str::random(16);
            $training->attendance_expires_at = $now->addMinutes(30);
            $training->save();
        }

        // Hide QR if 30 minutes have passed since generation
        if ($training->attendance_expires_at && $now->greaterThan($training->attendance_expires_at)) {
            $training->attendance_key = null;
            $training->attendance_expires_at = null;
            $training->save();
        }
    }

    /**
     * User attendance check-in via QR code
     */
    public function attendanceCheckin(Request $request)
    {
        $training = Training::where('trainingID', $request->trainingID)
            ->where('attendance_key', $request->key)
            ->first();

        if (!$training) {
            return response()->json(['message' => 'Invalid or Fake QR Code'], 400);
        }

        // Check if QR expired
        if (now()->greaterThan($training->attendance_expires_at)) {
            return response()->json(['message' => 'QR Code Expired'], 400);
        }

        // Record attendance if user is logged in
        if ($request->user()) {
            Attendance::firstOrCreate([
                'user_id' => $request->user()->id,
                'trainingID' => $training->trainingID,
            ], [
                'time_in' => now(),
                'status' => 'present',
            ]);

            return response()->json(['message' => '✅ Attendance Recorded']);
        }

        return response()->json(['message' => 'QR Valid — Please Login to Record Attendance']);
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
}