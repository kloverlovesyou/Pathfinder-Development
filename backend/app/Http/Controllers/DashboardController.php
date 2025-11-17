<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
   public function getChartData()
{
    // 1️⃣ Trainings per month
    $months = collect(range(1, 12))->map(function ($m) {
        return date("M", mktime(0, 0, 0, $m, 1));
    });

    $trainingRegistrants = collect(range(1, 12))->map(function ($month) {
        return DB::table('registrations')
            ->whereMonth('registrationDate', $month)
            ->count();
    });

    $careerApplicants = collect(range(1, 12))->map(function ($month) {
        return DB::table('applications')
            ->whereMonth('dateSubmitted', $month)
            ->count();
    });

    // 2️⃣ Training types (onsite/online/hybrid)
    $trainingTypes = [
        'onsite' => DB::table('trainings')->where('mode', 'onsite')->count(),
        'online' => DB::table('trainings')->where('mode', 'online')->count(),
        'hybrid' => DB::table('trainings')->where('mode', 'hybrid')->count()
    ];

    // 3️⃣ Career applications statuses
    $careerOutcomes = [
        'accepted' => DB::table('applications')->where('applciationStatus', 'accepted')->count(),
        'pending' => DB::table('applications')->where('applciationStatus', 'pending')->count(),
        'rejected' => DB::table('applications')->where('applciationStatus', 'rejected')->count(),
    ];

    return response()->json([
        'months' => $months,
        'trainingRegistrants' => $trainingRegistrants,
        'careerApplicants' => $careerApplicants,
        'trainingTypes' => $trainingTypes,
        'careerOutcomes' => $careerOutcomes,
    ]);
}

}
