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

     // Fetch recommended careers based on career ID
     public function recommendedCareers($careerID)
     {
         // Ensure that careerID is an integer
         $careerID = (int)$careerID;
 
         // Use Laravel's DB facade to call the stored procedure with parameter binding
         $careers = DB::select("CALL sp_GetRecommendedCareers_ByTags(?)", [$careerID]);
 
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
 
         // Return the results as JSON
         return response()->json($trainings);
     }

}
