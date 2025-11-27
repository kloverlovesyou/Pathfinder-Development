<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingSchedule;
use App\Models\Registration;
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

    /**
     * Auto-generate QR for a training schedule
     */
    public function autoGenerateQRForSchedule(TrainingSchedule $schedule)
    {
        $now = now();

        // Clear QR if schedule ended
        if ($schedule->attendance_key && $now->greaterThanOrEqualTo($schedule->end_time)) {
            $schedule->attendance_key = null;
            $schedule->qr_generated_at = null;
            $schedule->attendance_expires_at = null;
            $schedule->save();
            return null;
        }

        // Generate QR key if schedule started
        if ($now->greaterThanOrEqualTo($schedule->schedule)
            && !$schedule->attendance_key
            && $now->lessThan($schedule->end_time)) 
        {
            $schedule->attendance_key = $this->generateSafeKey(16);
            $schedule->qr_generated_at = $now;
            $schedule->attendance_expires_at = $schedule->end_time;
            $schedule->save();
        }

        return $schedule->attendance_key;
    }

    /**
     * @deprecated Use autoGenerateQRForSchedule instead
     */
    public function autoGenerateQR(Training $training)
    {
        // For backward compatibility, use first schedule
        $firstSchedule = $training->schedules->first();
        if ($firstSchedule) {
            return $this->autoGenerateQRForSchedule($firstSchedule);
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

        // Check if QR is valid by finding the schedule with this key
        $schedule = $training->schedules()->where('attendance_key', $request->key)->first();
        if (!$schedule) {
            return response()->json(['message' => 'Invalid or fake QR code'], 400);
        }

        if (now()->greaterThanOrEqualTo($schedule->end_time)) {
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
        $registration = Registration::find($applicant->registrationID);
        if ($registration) {
            $registration->checked_in_at = now();
            $registration->registrationStatus = 'Attended';
            $registration->certTrackingID = $request->key;
            $registration->recordStage('attended', now());
            $registration->save();
        }

        return response()->json(['message' => '✅ Attendance Recorded Successfully']);
    }
    /**
     * Manually generate QR (optional)
     */
    public function generateQRCode(Request $request)
    {
        $training = Training::with('schedules')->find($request->trainingID);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        // Get the first schedule (or specify which schedule to generate QR for)
        $schedule = $training->schedules->first();
        if (!$schedule) {
            return response()->json(['message' => 'Training has no schedules'], 400);
        }

        if (now()->lessThan($schedule->end_time)) {
            $this->autoGenerateQRForSchedule($schedule);
            $schedule->refresh();

            // ✅ Create full attendance URL
            $attendanceUrl = env('FRONTEND_URL') . '/attendance/checkin?trainingID='
                            . $training->trainingID . '&key=' . $schedule->attendance_key;

            return response()->json([
                'key' => $schedule->attendance_key,
                'attendance_link' => $attendanceUrl,
                'expires_at' => $schedule->end_time,
            ]);
        }

        return response()->json(['message' => 'Cannot generate QR — training already ended'], 400);
    }

    /**
     * List all trainings (QR auto-generated if schedule started)
     */
    public function index(Request $request)
    {
        $query = Training::with(['organization', 'tags']);

        $user = $request->user();
        if ($user && isset($user->organizationID)) {
            $query->where('organizationID', $user->organizationID);
        } elseif ($request->has('organizationID')) {
            $query->where('organizationID', $request->organizationID);
        }

        $trainings = $query->with('schedules')->get();

        // Auto-generate QR for each schedule
        foreach ($trainings as $training) {
            foreach ($training->schedules as $schedule) {
                $this->autoGenerateQRForSchedule($schedule);
            }
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

        $trainings = Training::with(['organization', 'tags', 'schedules'])
            ->where('organizationID', $user->organizationID)
            ->get();

        // Auto-generate QR for each schedule
        foreach ($trainings as $training) {
            foreach ($training->schedules as $schedule) {
                $this->autoGenerateQRForSchedule($schedule);
            }
        }

        return response()->json($trainings->map(fn ($training) => $this->formatTraining($training)));
    }

    protected function formatTraining(Training $training)
    {
        // Get the first schedule (for backward compatibility) or all schedules
        $firstSchedule = $training->schedules->first();
        
        // Generate attendance link from first schedule if available
        $attendanceLink = null;
        if ($firstSchedule && $firstSchedule->attendance_key) {
            $attendanceLink = env('FRONTEND_URL') . '/attendance/checkin?trainingID='
                . $training->trainingID . '&key=' . $firstSchedule->attendance_key;
        }

        return [
            'trainingID' => $training->trainingID,
            'title' => $training->title ?? $training->Title,
            'description' => $training->description ?? $training->Description,
            // First schedule data (for backward compatibility)
            'schedule' => $firstSchedule?->schedule?->format('Y-m-d H:i'),
            'end_time' => $firstSchedule?->end_time?->format('Y-m-d H:i'),
            'mode' => $firstSchedule?->mode,
            'location' => $firstSchedule?->location,
            'trainingLink' => $firstSchedule?->trainingLink,
            'attendance_key' => $firstSchedule?->attendance_key,
            'attendance_link' => $attendanceLink,
            'attendance_expires_at' => $firstSchedule?->attendance_expires_at?->format('Y-m-d H:i:s'),
            'organizationID' => $training->organizationID,
            'organization' => [
                'name' => optional($training->organization)->name ?? 'Unknown',
            ],
            // All schedules for the training
            'schedules' => $training->schedules->map(function ($schedule) {
                return [
                    'trainingScheduleID' => $schedule->trainingScheduleID,
                    'schedule' => $schedule->schedule?->format('Y-m-d H:i'),
                    'end_time' => $schedule->end_time?->format('Y-m-d H:i'),
                    'mode' => $schedule->mode,
                    'location' => $schedule->location,
                    'trainingLink' => $schedule->trainingLink,
                    'attendance_key' => $schedule->attendance_key,
                    'attendance_expires_at' => $schedule->attendance_expires_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'Tags' => $training->tags->map(function ($tag) {
                return [
                    'TagID' => $tag->TagID,
                    'tagName' => $tag->TagName ?? '',
                ];
            }),
        ];
    }
    /**
     * Store new training with multiple schedules
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

        // Support both old format (single schedule) and new format (schedules array)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            // New format: array of schedules (preferred)
            'schedules' => 'sometimes|array|min:1',
            'schedules.*.schedule' => 'required_with:schedules|date_format:Y-m-d H:i',
            'schedules.*.end_time' => 'required_with:schedules|date_format:Y-m-d H:i',
            'schedules.*.mode' => 'required_with:schedules|string|in:On-Site,Online',
            'schedules.*.location' => 'nullable|string|max:255',
            'schedules.*.training_link' => 'nullable|url',
            // Backward compatibility: single schedule fields
            'schedule' => 'required_without:schedules|date_format:Y-m-d H:i',
            'end_time' => 'required_without:schedules|date_format:Y-m-d H:i',
            'mode' => 'required_without:schedules|string|in:On-Site,Online',
            'location' => 'nullable|string|max:255',
            'training_link' => 'nullable|url',
            'Tags' => 'nullable|array',
            'Tags.*' => 'integer|exists:tag,TagID',
        ]);

        // Create one Training record
        $training = Training::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'organizationID' => $user->organizationID,
        ]);

        // Create multiple TrainingSchedule records
        $schedules = $validated['schedules'] ?? [];
        
        // Backward compatibility: if no schedules array, use single schedule fields
        if (empty($schedules) && isset($validated['schedule'])) {
            $schedules = [[
                'schedule' => $validated['schedule'],
                'end_time' => $validated['end_time'],
                'mode' => $validated['mode'],
                'location' => $validated['location'] ?? null,
                'training_link' => $validated['training_link'] ?? null,
            ]];
        }

        foreach ($schedules as $scheduleData) {
            TrainingSchedule::create([
                'trainingID' => $training->trainingID,
                'schedule' => $scheduleData['schedule'],
                'end_time' => $scheduleData['end_time'],
                'mode' => $scheduleData['mode'],
                'location' => $scheduleData['mode'] === 'On-Site' ? ($scheduleData['location'] ?? null) : null,
                'trainingLink' => $scheduleData['mode'] === 'Online' ? ($scheduleData['training_link'] ?? null) : null,
            ]);
        }

        // Attach tags if provided
        if (!empty($validated['Tags'])) {
            $training->tags()->attach($validated['Tags']);
        }

        return response()->json([
            'message' => 'TRAINING CREATED SUCCESSFULLY!',
            'data' => $training->load(['organization', 'schedules', 'tags'])
        ], 201);
    }

    public function show($id)
    {
        $training = Training::with(['organization', 'schedules', 'tags'])->find($id);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        return response()->json($this->formatTraining($training));
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

        // Count based on schedules, not training directly
        $upcoming = \App\Models\TrainingSchedule::where('schedule', '>', $now)->count();
        $completed = \App\Models\TrainingSchedule::where('schedule', '<=', $now)->count();

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

        // Validate incoming data - support both single schedule and schedules array
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            // New format: array of schedules
            'schedules' => 'sometimes|array|min:1',
            'schedules.*.schedule' => 'required_with:schedules|date_format:Y-m-d H:i',
            'schedules.*.end_time' => 'required_with:schedules|date_format:Y-m-d H:i',
            'schedules.*.mode' => 'required_with:schedules|string|in:On-Site,Online',
            'schedules.*.location' => 'nullable|string|max:255',
            'schedules.*.training_link' => 'nullable|url',
            // Backward compatibility: single schedule fields
            'schedule' => 'required_without:schedules|date_format:Y-m-d H:i',
            'end_time' => 'required_without:schedules|date_format:Y-m-d H:i',
            'mode' => 'required_without:schedules|string|in:On-Site,Online',
            'location' => 'nullable|string|max:255',
            'training_link' => 'nullable|url',
            'Tags' => 'nullable|array',
            'Tags.*' => 'integer|exists:tag,TagID',
        ]);

        // Update Training core fields
        $training->title = $validated['title'];
        $training->description = $validated['description'];
        $training->save();

        // Update schedules
        if (isset($validated['schedules'])) {
            // Delete existing schedules
            $training->schedules()->delete();
            
            // Create new schedules
            foreach ($validated['schedules'] as $scheduleData) {
                TrainingSchedule::create([
                    'trainingID' => $training->trainingID,
                    'schedule' => $scheduleData['schedule'],
                    'end_time' => $scheduleData['end_time'],
                    'mode' => $scheduleData['mode'],
                    'location' => $scheduleData['mode'] === 'On-Site' ? ($scheduleData['location'] ?? null) : null,
                    'trainingLink' => $scheduleData['mode'] === 'Online' ? ($scheduleData['training_link'] ?? null) : null,
                ]);
            }
        } else {
            // Backward compatibility: update first schedule or create one
            $firstSchedule = $training->schedules->first();
            if ($firstSchedule) {
                $firstSchedule->schedule = $validated['schedule'];
                $firstSchedule->end_time = $validated['end_time'];
                $firstSchedule->mode = $validated['mode'];
                if ($validated['mode'] === 'Online') {
                    $firstSchedule->location = null;
                    $firstSchedule->trainingLink = $validated['training_link'] ?? null;
                } else {
                    $firstSchedule->trainingLink = null;
                    $firstSchedule->location = $validated['location'] ?? null;
                }
                $firstSchedule->save();
            } else {
                // Create schedule if none exists
                TrainingSchedule::create([
                    'trainingID' => $training->trainingID,
                    'schedule' => $validated['schedule'],
                    'end_time' => $validated['end_time'],
                    'mode' => $validated['mode'],
                    'location' => $validated['mode'] === 'On-Site' ? ($validated['location'] ?? null) : null,
                    'trainingLink' => $validated['mode'] === 'Online' ? ($validated['training_link'] ?? null) : null,
                ]);
            }
        }

        // Update tags if provided
        if (isset($validated['Tags'])) {
            $training->tags()->sync($validated['Tags']);
        }

        return response()->json([
            'message' => 'Training updated successfully',
            'data' => $this->formatTraining($training->load(['schedules', 'tags', 'organization'])),
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
            $training->registrations()->delete();
            //$training->attendances()->delete();
            $training->tags()->detach();
            $training->delete();
        });

        return response()->json(['message' => 'Training deleted successfully']);
    }

}