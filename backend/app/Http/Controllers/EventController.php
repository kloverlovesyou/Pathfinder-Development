<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Training;
use App\Models\TrainingSchedule;

class EventController extends Controller
{
    /**
     * Auto-generate QR for a training schedule
     */
    private function autoGenerateQRForSchedule(TrainingSchedule $schedule)
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
            $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $key = '';
            for ($i = 0; $i < 16; $i++) {
                $key .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            $schedule->attendance_key = $key;
            $schedule->qr_generated_at = $now;
            $schedule->attendance_expires_at = $schedule->end_time;
            $schedule->save();
        }

        return $schedule->attendance_key;
    }
  public function getUserEvents($applicantID)
{
$trainings = DB::table('registration')
    ->join('training', 'registration.trainingID', '=', 'training.trainingID')
    ->join('trainingschedule', 'training.trainingID', '=', 'trainingschedule.trainingID')
    ->join('organization', 'training.organizationID', '=', 'organization.organizationID')
    ->where('registration.ApplicantID', $applicantID)
    ->where('registration.registrationStatus', 'Registered')
    ->select(
        'training.trainingID as trainingID',
        'training.title as title',
        'training.description as description',
        DB::raw('DATE(trainingschedule.schedule) as date'),   // Extract date from schedule
        DB::raw('TIME(trainingschedule.schedule) as time'),   // Extract time from schedule
        'trainingschedule.mode as mode',
        'trainingschedule.location as location',
        'trainingschedule.trainingLink as trainingLink',
        'organization.name as organization',

        'trainingschedule.attendance_key as attendance_key',
        'trainingschedule.end_time as end_time',
        'trainingschedule.qr_generated_at as qr_generated_at',
        'trainingschedule.attendance_expires_at as attendance_expires_at',

        DB::raw("'training' as type")
    )
    ->get();




    $careers = DB::table('application')
    ->join('career', 'application.careerID', '=', 'career.careerID')
    ->join('organization', 'career.organizationID', '=', 'organization.organizationID')
    ->where('application.applicantID', $applicantID)
    ->where('application.applicationStatus', 'Scheduled for Interview')
    ->select(
        'career.careerID as careerID',
        'career.position as title',
        'career.detailsAndInstructions as detailsAndInstructions',
        'career.qualificationStandard as qualificationStandard',
        'career.requirements as requirements',
        'career.applicationLetterAddress as applicationLetterAddress',
        DB::raw('DATE(application.interviewSchedule) as date'),
        DB::raw('TIME(application.interviewSchedule) as time'),
        'application.interviewMode as mode',
        'application.interviewLocation as interviewLocation',
        'application.interviewLink as interviewLink',
        'career.deadlineOfSubmission as deadlineOfSubmission',
        'organization.name as organization',
        DB::raw("'career' as type")
    )
    ->get();

    $events = $careers->merge($trainings)->sortBy('date')->values();

    return response()->json(['events' => $events]);
}

public function index(Request $request)
{
    $user = $request->user();

    $query = Training::with(['organization', 'schedules']);

    // Filter by organization if query param exists
    if ($request->has('organizationID')) {
        $query->where('organizationID', $request->organizationID);
    }

    $trainings = $query->get();

    // Ensure QR is generated for each schedule
    foreach ($trainings as $training) {
        foreach ($training->schedules as $schedule) {
            $this->autoGenerateQRForSchedule($schedule);
        }
    }

    return response()->json(
        $trainings->map(function ($training) use ($user) {

            // Check if the user is registered
            $registered = $training->registrations()
                ->where('applicant_id', $user->id)
                ->exists();

            return [
                'trainingID' => $training->trainingID,
                'title' => $training->title,
                'description' => $training->description,
                'schedule' => $training->schedules->first()?->schedule?->format('Y-m-d H:i'),
                'mode' => $training->schedules->first()?->mode,
                'location' => $training->schedules->first()?->location,
                'trainingLink' => $training->schedules->first()?->trainingLink,
                // Include attendance_key only if user is registered
                'attendance_key' => $registered ? $training->schedules->first()?->attendance_key : null,
                'attendance_expires_at' => $registered ? $training->schedules->first()?->attendance_expires_at?->format('Y-m-d H:i:s') : null,
                'organizationID' => $training->organizationID,
                'organization' => [
                    'name' => optional($training->organization)->name ?? 'Unknown',
                ],
            ];
        })
    );
}


}
