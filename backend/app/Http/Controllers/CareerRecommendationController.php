<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CareerRecommendationController extends Controller
{
    // Display list of careers
    public function index()
    {
        $careers = DB::table('career as c')
            ->join('organization as o', 'c.organizationID', '=', 'o.organizationID')
            ->select(
                'c.careerID',
                'c.position',
                'o.name as organization',
                'c.detailsAndInstructions',
                'c.qualifications',
                'c.requirements',
                'c.applicationLetterAddress',
                'c.deadlineOfSubmission'
            )
            ->get();

        return response()->json($careers);
    }

     // Fetch recommended careers based on career ID (includes target career)
     public function recommendedCareers($careerID)
     {
         // Ensure that careerID is an integer
         $careerID = (int)$careerID;
 
         // Use Laravel's DB facade to call the stored procedure with parameter binding
         $careers = DB::select("CALL sp_GetRecommendedCareers_ByTags(?)", [$careerID]);
 
         // ✅ Map the organization field to ensure consistency
         $careers = collect($careers)->map(function ($career) {
             // The stored procedure returns 'organization' as 'name', ensure it's accessible
             if (isset($career->organization)) {
                 // Already has organization name from stored procedure
             } elseif (isset($career->organizationID)) {
                 // Fallback: fetch organization name if not included
                 $org = DB::table('organization')
                     ->where('organizationID', $career->organizationID)
                     ->first();
                 $career->organization = $org->name ?? 'Unknown';
             } else {
                 $career->organization = 'Unknown';
             }
             return $career;
         });
 
         // Return the results as JSON
         return response()->json($careers);
     }

     // Fetch recommended trainings based on career ID
     public function recommendedTrainings($careerID)
     {
         // Ensure that careerID is an integer
         $careerID = (int)$careerID;
 
         // Use Laravel's DB facade to call the stored procedure with parameter binding
         $trainings = DB::select("CALL sp_GetRecommendedTrainings_ByCareer(?)", [$careerID]);
 
         // ✅ Map organization name from stored procedure result
         $trainingsWithOrg = collect($trainings)->map(function ($training) {
             // The stored procedure returns 'organizationName', map it to multiple fields for compatibility
             if (isset($training->organizationName)) {
                 // Stored procedure already returns organizationName
                 $training->provider = $training->organizationName;
             } elseif (isset($training->organization)) {
                 // If it's returned as 'organization', map to organizationName
                 $training->organizationName = $training->organization;
                 $training->provider = $training->organization;
             } elseif (isset($training->organizationID)) {
                 // Fallback: fetch organization name if not included
                 $org = DB::table('organization')
                     ->where('organizationID', $training->organizationID)
                     ->first();
                 $training->organizationName = $org->name ?? 'Unknown';
                 $training->provider = $training->organizationName;
             } else {
                 $training->organizationName = 'Unknown';
                 $training->provider = 'Unknown';
             }
             
             return $training;
         });
 
         // Return the results as JSON
         return response()->json($trainingsWithOrg);
     }

     // Fetch career details and recommended trainings
     public function careerDetails($careerID)
     {
         // Ensure that careerID is an integer
         $careerID = (int)$careerID;
 
         // Fetch career details
         $career = DB::table('career as c')
             ->join('organization as o', 'c.organizationID', '=', 'o.organizationID')
             ->select(
                 'c.careerID',
                 'c.position',
                 'o.name as organizationName',
                 'c.detailsAndInstructions',
                 'c.qualifications',
                 'c.requirements',
                 'c.applicationLetterAddress',
                 'c.deadlineOfSubmission'
             )
             ->where('c.careerID', $careerID)
             ->first();
 
         // Fetch recommended trainings (includes trainings for target career)
         $recommended_trainings = DB::select("CALL sp_GetRecommendedTrainings_ByCareer(?)", [$careerID]);
         
         // ✅ Map organization name from stored procedure result
         $trainingsWithOrg = collect($recommended_trainings)->map(function ($training) {
             // The stored procedure returns 'organizationName', map it to multiple fields for compatibility
             if (isset($training->organizationName)) {
                 // Stored procedure already returns organizationName
                 $training->provider = $training->organizationName;
             } elseif (isset($training->organization)) {
                 // If it's returned as 'organization', map to organizationName
                 $training->organizationName = $training->organization;
                 $training->provider = $training->organization;
             } elseif (isset($training->organizationID)) {
                 // Fallback: fetch organization name if not included
                 $org = DB::table('organization')
                     ->where('organizationID', $training->organizationID)
                     ->first();
                 $training->organizationName = $org->name ?? 'Unknown';
                 $training->provider = $training->organizationName;
             } else {
                 $training->organizationName = 'Unknown';
                 $training->provider = 'Unknown';
             }
             
             return $training;
         });
 
         // Return the results as JSON
         return response()->json([
             'career' => $career,
             'recommended_trainings' => $trainingsWithOrg,
         ]);
     }
}
