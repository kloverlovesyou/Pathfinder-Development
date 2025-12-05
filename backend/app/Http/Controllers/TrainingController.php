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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TrainingUpdated;
use App\Services\BrevoEmailService;

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
        // Schedule times are stored as datetime without timezone info
        // They should be interpreted as Asia/Manila time (Philippine Standard Time)
        $timezone = 'Asia/Manila';
        $now = Carbon::now($timezone);
        
        // Parse schedule times: get the raw datetime string and interpret it as Asia/Manila time
        // The datetime format is "Y-m-d H:i" (e.g., "2025-11-29 07:05")
        $scheduleStartStr = $schedule->schedule->format('Y-m-d H:i');
        $scheduleEndStr = $schedule->end_time->format('Y-m-d H:i');
        
        $scheduleStart = Carbon::createFromFormat('Y-m-d H:i', $scheduleStartStr, $timezone);
        $scheduleEnd = Carbon::createFromFormat('Y-m-d H:i', $scheduleEndStr, $timezone);

        // Clear QR if schedule ended
        if ($schedule->attendance_key && $now->greaterThanOrEqualTo($scheduleEnd)) {
            $schedule->attendance_key = null;
            $schedule->qr_generated_at = null;
            $schedule->attendance_expires_at = null;
            $schedule->save();
            return null;
        }

        // Generate QR key if schedule started
        if ($now->greaterThanOrEqualTo($scheduleStart)
            && !$schedule->attendance_key
            && $now->lessThan($scheduleEnd)) 
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
        ]);

        // Find the training by trainingID
        $training = Training::find($request->trainingID);

        if (!$training) {
            return response()->json(['message' => 'Invalid or fake QR code'], 400);
        }

        // Check if QR is valid by finding the schedule with this key
        // attendance_key is stored on the trainingschedule table, not the training table
        $schedule = $training->schedules()->where('attendance_key', $request->key)->first();
        if (!$schedule) {
            return response()->json(['message' => 'Invalid or fake QR code'], 400);
        }

        if (now()->greaterThanOrEqualTo($schedule->end_time)) {
            return response()->json(['message' => 'QR Code Expired'], 400);
        }

        // ✅ Step 1: Find registration for this training and email
        // Only registered applicants can scan QR and have their attendance updated
        $registrationData = DB::table('registration')
            ->join('applicant', 'registration.applicantID', '=', 'applicant.applicantID')
            ->where('registration.trainingID', $request->trainingID)
            ->where('applicant.emailAddress', $request->emailAddress)
            ->select(
                'applicant.*',
                'registration.registrationID',
                DB::raw('registration."registrationStatus" as registrationStatus')
            )
            ->first();

        // Check if applicant is registered for this training
        if (!$registrationData) {
            return response()->json([
                'message' => '⚠️ Access Denied: You are not registered for this training. Only registered applicants can scan the QR code and record attendance. Please register for this training first.'
            ], 403);
        }

        // Check if registration is cancelled
        $registrationStatus = strtolower(trim($registrationData->registrationStatus ?? ''));
        if ($registrationStatus === 'cancelled') {
            return response()->json([
                'message' => '⚠️ Access Denied: Your registration for this training has been cancelled. You cannot record attendance.'
            ], 403);
        }

        // ✅ Step 2: Verify first/last name matches registered applicant
        if ($registrationData->firstName !== $request->firstName || $registrationData->lastName !== $request->lastName) {
            return response()->json([
                'message' => '⚠️ Verification Failed: Name does not match the registered applicant\'s information. Please use the same name you used when registering for this training.'
            ], 400);
        }

        // ✅ Step 4: Update attendance (only for registered applicants)
        $registration = Registration::find($registrationData->registrationID);
        if ($registration) {
            $registration->checked_in_at = now();
            $registration->registrationStatus = 'Attended';
            $registration->certTrackingID = $request->key;
            $registration->recordStage('attended', now());
            $registration->save();
        } else {
            return response()->json([
                'message' => '⚠️ Error: Could not update attendance record. Please contact support.'
            ], 500);
        }

        return response()->json(['message' => '✅ Attendance Recorded Successfully']);
    }
    /**
     * Manually generate QR (optional)
     * Accepts optional trainingScheduleID to generate QR for a specific schedule
     */
    public function generateQRCode(Request $request)
    {
        $training = Training::with('schedules')->find($request->trainingID);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        // Get specific schedule if trainingScheduleID is provided, otherwise get first schedule
        $schedule = null;
        if ($request->has('trainingScheduleID')) {
            $schedule = $training->schedules->firstWhere('trainingScheduleID', $request->trainingScheduleID);
        } else {
            $schedule = $training->schedules->first();
        }

        if (!$schedule) {
            return response()->json(['message' => 'Training schedule not found'], 400);
        }

        // Schedule times are stored as datetime without timezone info
        // They should be interpreted as Asia/Manila time (Philippine Standard Time)
        // Convert both current time and schedule times to Asia/Manila for proper comparison
        $timezone = 'Asia/Manila';
        $now = Carbon::now($timezone);
        
        // Parse schedule times: get the raw datetime string and interpret it as Asia/Manila time
        // The datetime format is "Y-m-d H:i" (e.g., "2025-11-29 07:05")
        $scheduleStartStr = $schedule->schedule->format('Y-m-d H:i');
        $scheduleEndStr = $schedule->end_time->format('Y-m-d H:i');
        
        $scheduleStart = Carbon::createFromFormat('Y-m-d H:i', $scheduleStartStr, $timezone);
        $scheduleEnd = Carbon::createFromFormat('Y-m-d H:i', $scheduleEndStr, $timezone);

        // Check if schedule has started
        if ($now->lessThan($scheduleStart)) {
            return response()->json([
                'message' => 'Cannot generate QR — training not yet started',
                'start_time' => $scheduleStart->format('Y-m-d H:i:s T'),
                'current_time' => $now->format('Y-m-d H:i:s T')
            ], 400);
        }

        // Check if schedule has ended
        if ($now->greaterThanOrEqualTo($scheduleEnd)) {
            return response()->json([
                'message' => 'Cannot generate QR — training already ended',
                'end_time' => $scheduleEnd->format('Y-m-d H:i:s T'),
                'current_time' => $now->format('Y-m-d H:i:s T')
            ], 400);
        }

        // Generate QR if within timeframe
        $this->autoGenerateQRForSchedule($schedule);
        $schedule->refresh();

        // ✅ Create full attendance URL
        $attendanceUrl = env('FRONTEND_URL') . '/attendance/checkin?trainingID='
                        . $training->trainingID . '&key=' . $schedule->attendance_key;

        // Format expiry time in Asia/Manila timezone for frontend
        // Parse the end_time and ensure it's in Asia/Manila timezone
        $expiresAt = Carbon::createFromFormat('Y-m-d H:i', $schedule->end_time->format('Y-m-d H:i'), $timezone);
        
        return response()->json([
            'key' => $schedule->attendance_key,
            'attendance_link' => $attendanceUrl,
            'expires_at' => $expiresAt->format('Y-m-d\TH:i:sP'), // ISO 8601 format: 2025-11-29T12:00:00+08:00
            'trainingScheduleID' => $schedule->trainingScheduleID,
        ]);
    }

    /**
     * List all trainings (QR auto-generated if schedule started)
     */
    public function index(Request $request)
    {
        $query = Training::with(['organization', 'tags', 'schedules']);

        $user = $request->user();
        if ($user && isset($user->organizationID)) {
            $query->where('organizationID', $user->organizationID);
        } elseif ($request->has('organizationID')) {
            $query->where('organizationID', $request->organizationID);
        }

        $trainings = $query->get();

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

        // Reload training with relationships
        $training->load(['schedules', 'tags', 'organization']);

        // Send email notifications to all registered users
        $this->sendUpdateNotifications($training);

        return response()->json([
            'message' => 'Training updated successfully',
            'data' => $this->formatTraining($training),
        ]);
    }

    /**
     * Send email notifications to all registered users when training is updated
     */
    private function sendUpdateNotifications(Training $training)
    {
        try {
            // Get all registrations for this training with applicant details
            $registrations = Registration::with('applicant')
                ->where('trainingID', $training->trainingID)
                ->where('registrationStatus', '!=', 'Cancelled')
                ->get();

            if ($registrations->isEmpty()) {
                Log::info('No registrations found for training update notification', [
                    'training_id' => $training->trainingID,
                ]);
                return;
            }

            // Format schedules for email
            $schedulesData = $training->schedules->map(function ($schedule) {
                return [
                    'start_time' => $schedule->schedule ? $schedule->schedule->format('F d, Y h:i A') : 'N/A',
                    'end_time' => $schedule->end_time ? $schedule->end_time->format('F d, Y h:i A') : 'N/A',
                    'mode' => $schedule->mode ?? 'N/A',
                    'location' => $schedule->location,
                    'training_link' => $schedule->trainingLink,
                ];
            })->toArray();

            $organizationName = $training->organization->name ?? 'Unknown Organization';
            $trainingTitle = $training->title ?? $training->Title ?? 'Training';
            $trainingDescription = $training->description ?? $training->Description ?? '';

            // Get Brevo API key
            $brevoApiKeyFromConfig = config('services.brevo.api_key');
            $brevoApiKeyFromEnv = env('BREVO_API_KEY');
            $brevoApiKey = trim($brevoApiKeyFromConfig ?: $brevoApiKeyFromEnv ?: '');

            // Send email to each registered applicant
            foreach ($registrations as $registration) {
                $applicant = $registration->applicant;
                
                if (!$applicant) {
                    Log::warning('Registration has no applicant', [
                        'registration_id' => $registration->registrationID,
                    ]);
                    continue;
                }

                $applicantEmail = $applicant->emailAddress ?? $applicant->EmailAddress ?? null;
                if (!$applicantEmail) {
                    Log::warning('Applicant has no email address', [
                        'applicant_id' => $applicant->applicantID,
                    ]);
                    continue;
                }

                $applicantName = trim(($applicant->firstName ?? $applicant->FirstName ?? '') . ' ' . ($applicant->lastName ?? $applicant->LastName ?? ''));

                // Create mailable
                $mailable = new TrainingUpdated(
                    $applicantName,
                    $trainingTitle,
                    $trainingDescription,
                    $organizationName,
                    $schedulesData
                );

                $emailSent = false;
                $emailError = null;
                $brevoError = null;

                // Try Brevo API first
                if (!empty($brevoApiKey)) {
                    try {
                        $brevoService = app(BrevoEmailService::class);
                        $htmlContent = view('emails.training-updated', [
                            'applicantName' => $applicantName,
                            'trainingTitle' => $trainingTitle,
                            'trainingDescription' => $trainingDescription,
                            'organizationName' => $organizationName,
                            'schedules' => $schedulesData,
                        ])->render();

                        $brevoService->send(
                            $applicantEmail,
                            $mailable->envelope()->subject,
                            $htmlContent
                        );

                        $emailSent = true;
                        Log::info('Training update email sent via Brevo API', [
                            'applicant_email' => $applicantEmail,
                            'training_id' => $training->trainingID,
                        ]);
                    } catch (\Exception $brevoException) {
                        $brevoError = $brevoException->getMessage();
                        Log::error('Brevo API failed for training update email', [
                            'error' => $brevoError,
                            'applicant_email' => $applicantEmail,
                            'training_id' => $training->trainingID,
                        ]);
                        // Fall through to SMTP
                    }
                }

                // Fallback to SMTP if Brevo failed or not configured
                if (!$emailSent) {
                    try {
                        Mail::to($applicantEmail)->send($mailable);
                        $emailSent = true;
                        Log::info('Training update email sent via SMTP', [
                            'applicant_email' => $applicantEmail,
                            'training_id' => $training->trainingID,
                        ]);
                    } catch (\Exception $smtpException) {
                        $emailError = $smtpException->getMessage();
                        Log::error('Failed to send training update email', [
                            'error' => $emailError,
                            'brevo_error' => $brevoError,
                            'applicant_email' => $applicantEmail,
                            'training_id' => $training->trainingID,
                        ]);
                    }
                }
            }

            Log::info('Training update notification process completed', [
                'training_id' => $training->trainingID,
                'total_registrations' => $registrations->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending training update notifications', [
                'error' => $e->getMessage(),
                'training_id' => $training->trainingID,
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw - we don't want to fail the update if email fails
        }
    }

    public function destroy($id)
    {
        // Delete all registrations tied to this training
        DB::table('registration')->where('trainingID', $id)->delete();
        DB::table('organizationschoice')->where('trainingID', $id)->delete();

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
            DB::table('organizationschoice')->where('trainingID', $training->trainingID)->delete();
            $training->delete();
        });

        return response()->json(['message' => 'Training deleted successfully']);
    }

}