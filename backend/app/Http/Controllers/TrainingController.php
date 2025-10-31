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

        // Clear QR if training has ended
        if ($training->attendance_key && $now->greaterThanOrEqualTo($training->end_time)) {
            $training->attendance_key = null;
            $training->qr_generated_at = null;
            $training->save();
            return; // stop here
        }

        // Only generate QR if training has started and no QR exists
        if ($now->greaterThanOrEqualTo($training->schedule) && !$training->attendance_key) {
            $training->attendance_key = Str::random(16);
            $training->qr_generated_at = $now;
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

        // Check if QR expired (training ended)
        if (now()->greaterThanOrEqualTo($training->end_time)) {
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

    /**
     * Manually generate QR (optional)
     */
public function generateQRCode(Request $request)
{
    $training = Training::find($request->trainingID);

    if (!$training) {
        return response()->json(['message' => 'Training not found'], 404);
    }

    // Only generate if training hasn't ended
    if (now()->lessThan($training->end_time)) {
        $training->attendance_key = Str::random(16);
        $training->qr_generated_at = now();
        $training->save();

        return response()->json([
            'key' => $training->attendance_key,
            'expires_at' => $training->end_time, // expiration inferred from end_time
        ]);
    }

    return response()->json(['message' => 'Cannot generate QR — training already ended'], 400);
}

    /**
     * List all trainings (QR auto-generated if schedule started)
     */
    public function index(Request $request)
{
    $query = Training::with('organization');

    // Filter by organization if query param exists
    if ($request->has('organizationID')) {
        $query->where('organizationID', $request->organizationID);
    }

    $trainings = $query->get();

    foreach ($trainings as $training) {
        $this->autoGenerateQR($training);
    }

    return response()->json($trainings->map(function ($training) {
        return [
            'trainingID' => $training->trainingID,
            'title' => $training->title,
            'description' => $training->description,
            'schedule' => $training->schedule?->format('Y-m-d H:i'),
            'end_time' => $training->end_time?->format('Y-m-d H:i'),
            'mode' => $training->mode,
            'location' => $training->location,
            'trainingLink' => $training->trainingLink,
            'attendance_key' => $training->attendance_key,
            'attendance_expires_at' => $training->attendance_expires_at,
            'organizationID' => $training->organizationID,
            'organization' => [
                'name' => optional($training->organization)->name ?? 'Unknown',
            ],
        ];
    }));
    }

    /**
     * Store new training
     */
    public function store(Request $request)
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - no auth user found'], 401);
        }

        if (!isset($user->organizationID)) {
            return response()->json(['message' => 'Only organizations can create trainings'], 403);
        }

        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'schedule' => 'required|date_format:Y-m-d H:i',
        'end_time' => 'required|date_format:Y-m-d H:i|after:schedule',
        'mode' => 'required|string|in:On-Site,Online',
        'location' => 'nullable|string|max:255',
        'training_link' => 'nullable|url',
        'Tags' => 'nullable|array',
        'Tags.*' => 'integer|exists:tag,TagID',
    ]);

            $training = Training::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'schedule' => $validated['schedule'],
            'end_time' => $validated['end_time'],
            'mode' => $validated['mode'],
            'location' => $validated['location'] ?? null,
            'trainingLink' => $validated['training_link'] ?? null,
            'organizationID' => $user->organizationID,
        ]);
        if (!empty($validated['Tags'])) {
            $training->tags()->attach($validated['Tags']);
        }

        return response()->json([
            'message' => 'TRAINING CREATED SUCCESSFULLY!',
            'data' => $training->load('organization')
        ], 201);
    }
}