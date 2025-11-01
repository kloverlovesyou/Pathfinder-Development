<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
  public function getUserEvents($applicantID)
{
  $trainings = DB::table('Registration')
    ->join('Training', 'Registration.TrainingID', '=', 'Training.TrainingID')
    ->join('Organization', 'Training.OrganizationID', '=', 'Organization.OrganizationID')
    ->where('Registration.ApplicantID', $applicantID)
    ->where('Registration.RegistrationStatus', 'Registered')
    ->select(
        'Training.TrainingID as trainingID',
        'Training.Title as title',
        'Training.Description as description',
        DB::raw('DATE(Training.Schedule) as date'),          // Date part
        DB::raw('TIME(Training.Schedule) as time'),          // Time part
        'Training.Mode',
        'Training.Location as location',
        'Training.TrainingLink as trainingLink',
        'Organization.Name as organization',
        DB::raw("'training' as type")
    )
    ->get();



    $careers = DB::table('Application')
    ->join('Career', 'Application.CareerID', '=', 'Career.CareerID')
    ->join('Organization', 'Career.OrganizationID', '=', 'Organization.OrganizationID')
    ->where('Application.ApplicantID', $applicantID)
    ->where('Application.ApplicationStatus', 'Scheduled for Interview')
    ->select(
        'Career.CareerID as careerID',
        'Career.Position as title',
        'Career.DetailsAndInstructions as detailsAndInstructions',
        'Career.Qualifications as qualifications',
        'Career.Requirements as requirements',
        'Career.ApplicationLetterAddress as applicationLetterAddress',
        DB::raw('DATE(Application.InterviewSchedule) as date'),
        DB::raw('TIME(Application.InterviewSchedule) as time'),
        'Application.InterviewMode as mode',
        'Application.InterviewLocation as interviewLocation',
        'Application.InterviewLink as interviewLink',
        'Career.DeadlineOfSubmission as deadlineOfSubmission',
        'Organization.Name as organization',
        DB::raw("'career' as type")
    )
    ->get();

    $events = $careers->merge($trainings)->sortBy('date')->values();

    return response()->json(['events' => $events]);
}

public function index(Request $request)
{
    $user = $request->user();

    $query = Training::with('organization');

    // Filter by organization if query param exists
    if ($request->has('organizationID')) {
        $query->where('organizationID', $request->organizationID);
    }

    $trainings = $query->get();

    // Ensure QR is generated for each training
    foreach ($trainings as $training) {
        $this->autoGenerateQR($training);
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
                'schedule' => $training->schedule?->format('Y-m-d H:i'),
                'mode' => $training->mode,
                'location' => $training->location,
                'trainingLink' => $training->trainingLink,
                // Include attendance_key only if user is registered
                'attendance_key' => $registered ? $training->attendance_key : null,
                'attendance_expires_at' => $registered ? $training->attendance_expires_at : null,
                'organizationID' => $training->organizationID,
                'organization' => [
                    'name' => optional($training->organization)->name ?? 'Unknown',
                ],
            ];
        })
    );
}


}
