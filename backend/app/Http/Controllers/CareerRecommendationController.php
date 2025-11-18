<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CareerRecommendationController extends Controller
{
    // Display list of careers
    public function index(Request $request)
    {
        $query = DB::table('career as c')
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
            );

        if ($request->has('organizationID')) {
            $query->where('c.organizationID', $request->organizationID);
        }

        $careers = $query->get();

        return response()->json($careers);
    }

     public function recommendedCareers($careerID)
    {
        try {
            // Ensure careerID is an integer
            $careerID = (int)$careerID;

            // Call the stored procedure
            $careers = DB::select("CALL sp_GetRecommendedCareers_ByTags(?)", [$careerID]);

            // Map organization names if missing
            $careers = collect($careers)->map(function ($career) {
                if (!isset($career->organization) && isset($career->organizationID)) {
                    $org = DB::table('organization')
                        ->where('organizationID', $career->organizationID)
                        ->first();
                    $career->organization = $org->name ?? 'Unknown';
                } elseif (!isset($career->organization)) {
                    $career->organization = 'Unknown';
                }
                return $career;
            });

            // Return as JSON
            return response()->json($careers);

        } catch (\Exception $e) {
            // Return error message if procedure fails
            return response()->json([
                'message' => 'Error fetching recommended careers',
                'error' => $e->getMessage()
            ], 500);
        }
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

      public function getCareerWithRecommendations($careerID)
    {
    try {
    // Log the careerID to verify its value
    Log::info('Career ID: ' . $careerID);
            // Call the stored procedure with the careerID
            $results = DB::select('CALL sp_GetRecommendedCareers_ByTags(?)', [$careerID]);
            
            // Check if results are returned
            if (empty($results)) {
                return response()->json([
                    'career' => null,
                    'recommended_trainings' => []
                ]);
            }

            // First result set for career info
            $careerInfo = $results[0]; 
            $trainings = [];

            // Capture second result set (recommended trainings)
            if (count($results) > 1) {
                $trainings = array_slice($results, 1); // Get all subsequent results
            }

            return response()->json([
                'career' => $careerInfo, // Return the career info directly
                'recommended_trainings' => $trainings // Return trainings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load career recommendations',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
