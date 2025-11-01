<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyActivityController extends Controller
{
public function getMyActivities($applicantID)
{
    // ✅ TRAINING REGISTRATIONS
    $trainingActivities = DB::table('registration')
        ->join('training', 'registration.trainingID', '=', 'training.trainingID')
        ->join('organization', 'training.organizationID', '=', 'organization.organizationID')
        ->select(
            'registration.registrationID',
            'training.title',
            'training.description',
            'training.mode',
            'training.schedule',
            'training.end_time',
            'training.location',
            'training.trainingLink',
            'registration.registrationStatus as status',
            'registration.certGivenDate',
            'registration.certificate',
            'organization.name as organizationName',
            DB::raw("'training' as type") // 👈 lowercase for consistency
        )
        ->where('registration.applicantID', $applicantID)
        ->get();

    // ✅ CAREER APPLICATIONS
    $careerActivities = DB::table('application')
        ->join('career', 'application.careerID', '=', 'career.careerID')
        ->join('organization', 'career.organizationID', '=', 'organization.organizationID')
        ->select(
            'application.applicationID',
            'career.position as title',
            'career.detailsAndInstructions',
            'career.qualifications',
            'career.requirements',
            'career.applicationLetterAddress',
            'career.deadlineOfSubmission',
            'application.applicationStatus as status',
            'organization.name as organizationName',
            DB::raw("'career' as type") // 👈 lowercase for consistency
        )
        ->where('application.applicantID', $applicantID)
        ->get();

    // ✅ Combine both results
    $activities = $trainingActivities->merge($careerActivities);

    return response()->json(['activities' => $activities]);
}


    // 🟣 Handle attendance QR submission
    public function submitAttendance(Request $request)
    {
        $registrationID = $request->input('registrationID');
        $qrCode = $request->input('qrCode');

        DB::table('Registration')
            ->where('RegistrationID', $registrationID)
            ->update(['CertTrackingID' => $qrCode]);

        return response()->json(['message' => 'Attendance recorded successfully.']);
    }
}
