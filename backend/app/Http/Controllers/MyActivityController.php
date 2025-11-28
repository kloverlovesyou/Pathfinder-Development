<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyActivityController extends Controller
{
    public function getMyActivities($applicantID)
    {
        $applicantID = (int) $applicantID;

        // ---------------------------
        // TRAINING REGISTRATIONS
        // ---------------------------
        $connection = DB::connection('pgsql');

        $registrations = $connection->table('registration')
            ->join('training', 'registration.trainingID', '=', 'training.trainingID')
            ->leftJoin('organization', 'training.organizationID', '=', 'organization.organizationID')
            ->where('registration.applicantID', $applicantID)
            ->select(
                'registration.registrationID',
                'registration.trainingID',
                DB::raw('COALESCE(registration."registrationStatus", registration.registrationStatus) as registrationStatus'),
                DB::raw('registration."certGivenDate" as certGivenDate'),
                DB::raw('COALESCE(registration."certificatePath", registration."Certificate", registration."certificate_directory") as certificatePath'),
                'training.title',
                'training.description',
                DB::raw("COALESCE(organization.name, 'Unknown') as organizationName")
            )
            ->get();

        $trainingIds = $registrations->pluck('trainingID')->filter()->unique()->values();

        $schedulesByTraining = collect();
        if ($trainingIds->isNotEmpty()) {
            $schedulesByTraining = $connection->table('trainingschedule')
                ->whereIn('trainingID', $trainingIds)
                ->orderBy('schedule')
                ->get()
                ->groupBy('trainingID')
                ->map(function ($items) {
                    return $items->map(function ($schedule) {
                        return [
                            'trainingScheduleID' => $schedule->trainingScheduleID,
                            'schedule' => $schedule->schedule,
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                            'mode' => $schedule->mode,
                            'location' => $schedule->location,
                            'trainingLink' => $schedule->trainingLink,
                            'attendance_key' => $schedule->attendance_key,
                            'qr_generated_at' => $schedule->qr_generated_at,
                            'attendance_expires_at' => $schedule->attendance_expires_at,
                        ];
                    });
                });
        }

        $trainingActivities = $registrations->map(function ($reg) use ($schedulesByTraining) {
            $schedules = $schedulesByTraining->get($reg->trainingID, collect());
            $primarySchedule = $schedules->first();

            return [
                'registrationID' => $reg->registrationID,
                'trainingID' => $reg->trainingID,
                'title' => $reg->title,
                'description' => $reg->description,
                'status' => $reg->registrationStatus,
                'certGivenDate' => $reg->certGivenDate,
                'certificate' => $reg->certificatePath,
                'organizationName' => $reg->organizationName,
                'schedule' => $primarySchedule['schedule'] ?? null,
                'start_time' => $primarySchedule['start_time'] ?? null,
                'end_time' => $primarySchedule['end_time'] ?? null,
                'mode' => $primarySchedule['mode'] ?? null,
                'location' => $primarySchedule['location'] ?? null,
                'trainingLink' => $primarySchedule['trainingLink'] ?? null,
                'attendance_key' => $primarySchedule['attendance_key'] ?? null,
                'qr_generated_at' => $primarySchedule['qr_generated_at'] ?? null,
                'attendance_expires_at' => $primarySchedule['attendance_expires_at'] ?? null,
                'schedules' => $schedules,
                'type' => 'training',
            ];
        });

        // ---------------------------
        // CAREER APPLICATIONS
        // ---------------------------
        $careerActivities = $connection->table('application')
            ->join('career', 'application.careerID', '=', 'career.careerID')
            ->leftJoin('organization', 'career.organizationID', '=', 'organization.organizationID')
            ->where('application.applicantID', $applicantID)
            ->select(
                'application.applicationID',
                'application.careerID',
                DB::raw('COALESCE(application."applicationStatus", application.applicationStatus) as applicationStatus'),
                DB::raw('COALESCE(application.requirement_directory, application."Requirements") as requirement_directory'),
                'career.position as title',
                'career.details',
                'career.placeOfAssignment',
                'career.qualificationStandard',
                'career.pdf_directory',
                'career.postingDate',
                'career.closingDate',
                DB::raw("COALESCE(organization.name, 'Unknown') as organizationName")
            )
            ->get()
            ->map(function ($career) {
                return [
                    'applicationID' => $career->applicationID,
                    'careerID' => $career->careerID,
                    'title' => $career->title,
                    'details' => $career->details,
                    'placeOfAssignment' => $career->placeOfAssignment,
                    'qualificationStandard' => $career->qualificationStandard,
                    'pdf_directory' => $career->pdf_directory,
                    'postingDate' => $career->postingDate,
                    'closingDate' => $career->closingDate,
                    'status' => $career->applicationStatus,
                    'requirement_directory' => $career->requirement_directory,
                    'organizationName' => $career->organizationName,
                    'type' => 'career',
                ];
            });

        $activities = $trainingActivities->merge($careerActivities)->values();

        return response()->json(['activities' => $activities]);
    }

    // 🟣 Handle attendance QR submission
    public function submitAttendance(Request $request)
    {
        $registrationID = $request->input('registrationID');
        $qrCode = $request->input('qrCode');

        DB::connection('pgsql')->table('Registration')
            ->where('RegistrationID', $registrationID)
            ->update(['CertTrackingID' => $qrCode]);

        return response()->json(['message' => 'Attendance recorded successfully.']);
    }
}
