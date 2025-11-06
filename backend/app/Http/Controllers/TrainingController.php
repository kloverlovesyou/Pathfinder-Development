<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Attendance;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    /**
     * Automatically generate QR for a training if schedule has started.
     * QR stays valid for 30 minutes from creation.
     */

     private function generateSafeKey($length = 16)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $key;
    }

    private function autoGenerateQR(Training $training)
    {
        $now = now();

        // Clear QR if training ended
        if ($training->attendance_key && $now->greaterThanOrEqualTo($training->end_time)) {
            $training->attendance_key = null;
            $training->qr_generated_at = null;
            $training->attendance_expires_at = null;
            $training->save();
            return null;
        }

        // Generate QR key if schedule started
        if ($now->greaterThanOrEqualTo($training->schedule)
            && !$training->attendance_key
            && $now->lessThan($training->end_time)) 
        {
            $training->attendance_key = $this->generateSafeKey(16);
            $training->qr_generated_at = $now;
            $training->attendance_expires_at = $training->end_time;
            $training->save();
        }

        if ($training->attendance_key) {
            // ✅ Return a full link for QR scanning
            return env('FRONTEND_URL') . '/attendance/checkin?trainingID='
                . $training->trainingID . '&key=' . $training->attendance_key;
        }

        return null;
    }
    /**
     * User attendance check-in via QR code
     */
    public function attendanceCheckin(Request $request)
    {
        // ✅ Validate the input
        $request->validate([
            'trainingID' => 'required|integer',
            'key' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        // Find the training with the QR key
        $training = DB::table('training')
            ->where('trainingID', $request->trainingID)
            ->where('attendance_key', $request->key)
            ->first();

        if (!$training) {
            return response()->json(['message' => 'Invalid or fake QR code'], 400);
        }

        if (now()->greaterThanOrEqualTo($training->end_time)) {
            return response()->json(['message' => 'QR Code Expired'], 400);
        }

        // Look up registration by name, email, and phone
        $registration = DB::table('registration')
            ->join('applicant', 'registration.applicantID', '=', 'applicant.applicantID')
            ->where('registration.trainingID', $request->trainingID)
            ->where('applicant.first_name', $request->first_name)
            ->where('applicant.last_name', $request->last_name)
            ->where('applicant.email', $request->email)
            ->where('applicant.phone', $request->phone)
            ->select('registration.*')
            ->first();

        if (!$registration) {
            return response()->json(['message' => '⚠️ No matching registration found'], 404);
        }

        // Update attendance
        DB::table('registration')
            ->where('registrationID', $registration->registrationID)
            ->update([
                'checked_in_at' => now(),
                'registrationStatus' => 'Attended',
                'certTrackingID' => $request->key
            ]);

        return response()->json(['message' => '✅ Attendance Recorded Successfully']);
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

        if (now()->lessThan($training->end_time)) {
            $training->attendance_key = $this->generateSafeKey(16);
            $training->qr_generated_at = now();
            $training->attendance_expires_at = $training->end_time;
            $training->save();

            // ✅ Create full attendance URL
            $attendanceUrl = env('FRONTEND_URL') . '/attendance/checkin?trainingID='
                            . $training->trainingID . '&key=' . $training->attendance_key;

            return response()->json([
                'key' => $training->attendance_key,
                'attendance_link' => $attendanceUrl,
                'expires_at' => $training->end_time,
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

        if ($request->has('organizationID')) {
            $query->where('organizationID', $request->organizationID);
        }

        $trainings = $query->get();

        foreach ($trainings as $training) {
            $this->autoGenerateQR($training);
        }

        return response()->json($trainings->map(function ($training) {
            // ✅ Generate attendance link if key exists
            $attendanceLink = $training->attendance_key
                ? env('FRONTEND_URL') . '/attendance/checkin?trainingID='
                . $training->trainingID . '&key=' . $training->attendance_key
                : null;

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
                'attendance_link' => $attendanceLink, // ✅ here
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