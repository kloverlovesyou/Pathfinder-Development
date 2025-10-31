<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CareerRecommendationController extends Controller
{
    public function getCareerWithRecommendations($careerID)
    {
    try {
    // Log the careerID to verify its value
    Log::info('Career ID: ' . $careerID);
            // Call the stored procedure with the careerID
            $results = DB::select('CALL sp_get_career_with_recommendations(?)', [$careerID]);
            
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
