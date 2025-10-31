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

}
