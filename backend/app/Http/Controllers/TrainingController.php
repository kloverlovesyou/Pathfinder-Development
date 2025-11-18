<?php

namespace App\Http\Controllers;

use App\Models\Training;
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

    public function autoGenerateQR(Training $training)
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
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'emailAddress' => 'required|email',
            'phoneNumber' => 'required|string',
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

        // ✅ Step 1: find applicant by email and training
        $applicant = DB::table('applicant')
            ->join('registration', 'registration.applicantID', '=', 'applicant.applicantID')
            ->where('registration.trainingID', $request->trainingID)
            ->where('applicant.emailAddress', $request->emailAddress)
            ->select('applicant.*', 'registration.registrationID')
            ->first();

        if (!$applicant) {
            return response()->json(['message' => '⚠️ No registration found with this email.'], 404);
        }

        // ✅ Step 2: check first/last name
        if ($applicant->firstName !== $request->firstName || $applicant->lastName !== $request->lastName) {
            return response()->json(['message' => '⚠️ Name does not match our records.'], 400);
        }

        // ✅ Step 3: check phone number
        if ($applicant->phoneNumber !== $request->phoneNumber) {
            return response()->json(['message' => '⚠️ Phone number does not match our records.'], 400);
        }

        // ✅ Step 4: update attendance
        DB::table('registration')
            ->where('registrationID', $applicant->registrationID)
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

        $user = $request->user();
        if ($user && isset($user->organizationID)) {
            $query->where('organizationID', $user->organizationID);
        } elseif ($request->has('organizationID')) {
            $query->where('organizationID', $request->organizationID);
        }

        $trainings = $query->get();

        foreach ($trainings as $training) {
            $this->autoGenerateQR($training);
        }

        return response()->json($trainings->map(fn ($training) => $this->formatTraining($training)));
    }

    /**
     * Trainings for authenticated organization
     */
    public function organizationIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $trainings = Training::with('organization')
            ->where('organizationID', $user->organizationID)
            ->get();

        foreach ($trainings as $training) {
            $this->autoGenerateQR($training);
        }

        return response()->json($trainings->map(fn ($training) => $this->formatTraining($training)));
    }

    protected function formatTraining(Training $training)
    {
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
            'attendance_link' => $attendanceLink,
            'attendance_expires_at' => $training->attendance_expires_at,
            'organizationID' => $training->organizationID,
            'organization' => [
                'name' => optional($training->organization)->name ?? 'Unknown',
            ],
        ];
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

    public function show($id)
    {
        $training = Training::with('organization')->find($id);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        // Optional: generate attendance link if key exists
        $attendanceLink = $training->attendance_key
            ? env('FRONTEND_URL') . '/attendance/checkin?trainingID='
            . $training->trainingID . '&key=' . $training->attendance_key
            : null;

        return response()->json([
            'trainingID' => $training->trainingID,
            'title' => $training->title,
            'description' => $training->description,
            'schedule' => $training->schedule?->format('Y-m-d H:i'),
            'end_time' => $training->end_time?->format('Y-m-d H:i'),
            'mode' => $training->mode,
            'location' => $training->location,
            'trainingLink' => $training->trainingLink,
            'attendance_key' => $training->attendance_key,
            'attendance_link' => $attendanceLink,
            'organizationID' => $training->organizationID,
            'organization' => [
                'name' => optional($training->organization)->name ?? 'Unknown',
            ],
        ]);
    }
//This is for Total numbers of trainings
public function total() {
    $totalTrainings = \App\Models\Training::count();
    return response()->json(['totalTrainings' => $totalTrainings]);
}

    //This is for numbers of upcoming and completed trainings
    public function countsPartial()
    {
        // Ensure timezone matches your data
        $now = now('Asia/Manila'); // Philippine Standard Time

        $upcoming = \App\Models\Training::where('schedule', '>', $now)->count();
        $completed = \App\Models\Training::where('schedule', '<=', $now)->count();

        return response()->json([
            'upcoming' => $upcoming,
            'completed' => $completed,
        ]);
    }

    //This is for Updating Trainings
    public function update(Request $request, $id)
    {
        $training = Training::find($id);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        // SECURITY: Only the owning organization can update
        $user = $request->user();
        if (!$user || $user->organizationID !== $training->organizationID) {
            return response()->json(['message' => 'Unauthorized to update this training'], 403);
        }

        // Validate incoming data
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

        // Update core fields
        $training->title = $validated['title'];
        $training->description = $validated['description'];
        $training->schedule = $validated['schedule'];
        $training->end_time = $validated['end_time'];
        $training->mode = $validated['mode'];

        // Update mode-specific fields
        if ($validated['mode'] === 'Online') {
            $training->location = null;
            $training->trainingLink = $validated['training_link'] ?? null;
        } else { // On-Site
            $training->trainingLink = null;
            $training->location = $validated['location'] ?? null;
        }

        $training->save();

        // Update tags if provided
        if (isset($validated['Tags'])) {
            $training->tags()->sync($validated['Tags']);
        }

        return response()->json([
            'message' => 'Training updated successfully',
            'data' => $training->load('tags', 'organization'),
        ]);
    }

    public function destroy($id)
    {
        // Delete all registrations tied to this training
        DB::table('registration')->where('trainingID', $id)->delete();

        // Delete the training itself
        DB::table('training')->where('trainingID', $id)->delete();

        return response()->json(['message' => 'Training deleted successfully']);
    }

    public function destroyById($trainingID)
    {
        $training = Training::find($trainingID);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        DB::transaction(function () use ($training) {
            $training->trainingbookmarks()->delete();
            $training->registrations()->delete();
            //$training->attendances()->delete();
            $training->tags()->detach();
            $training->delete();
        });

        return response()->json(['message' => 'Training deleted successfully']);
    }

}