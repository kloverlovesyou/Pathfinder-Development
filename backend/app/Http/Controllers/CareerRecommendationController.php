<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Career;
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
                'c.placeOfAssignment',
                'c.details',
                'c.qualificationStandard',
                'c.pdf_directory',
                'c.postingDate',
                'c.closingDate',
                'c.trainingsAttendedPercentage',
                'c.organizationID',
                'o.name as organization',
                'o.name as organizationName'
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
            $careers = DB::select('SELECT * FROM sp_getrecommendedcareers_bytags(?)', [$careerID]);

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
            Log::error('sp_GetRecommendedCareers_ByTags failed', [
                'careerID' => $careerID ?? null,
                'error' => $e->getMessage(),
            ]);

            $fallbackCareers = DB::table('career as c')
                ->leftJoin('career_tag as ct', 'c.careerID', '=', 'ct.careerID')
                ->leftJoin('career_tag as selected_ct', function ($join) use ($careerID) {
                    $join->on(DB::raw('"selected_ct"."TagID"'), '=', DB::raw('"ct"."TagID"'))
                        ->where('selected_ct.careerID', '=', $careerID);
                })
                ->leftJoin('organization as o', 'c.organizationID', '=', 'o.organizationID')
                ->where('c.careerID', '!=', $careerID)
                ->select(
                    'c.careerID',
                    'c.position',
                    'c.placeOfAssignment',
                    'c.details',
                    'c.qualificationStandard',
                    'c.pdf_directory',
                    'c.postingDate',
                    'c.closingDate',
                    'c.trainingsAttendedPercentage',
                    'c.organizationID',
                    DB::raw('COALESCE(o.name, \'Unknown\') as organization'),
                    DB::raw('COUNT("selected_ct"."TagID") as "sharedTags"')
                )
                ->groupBy(
                    'c.careerID',
                    'c.position',
                    'c.placeOfAssignment',
                    'c.details',
                    'c.qualificationStandard',
                    'c.pdf_directory',
                    'c.postingDate',
                    'c.closingDate',
                    'c.trainingsAttendedPercentage',
                    'c.organizationID',
                    'o.name'
                )
                ->orderByDesc('sharedTags')
                ->orderByDesc('c.postingDate')
                ->limit(10)
                ->get();

            return response()->json($fallbackCareers);
        }
    }

     // Fetch recommended trainings based on career ID
     public function recommendedTrainings($careerID)
     {
         // Ensure that careerID is an integer
         $careerID = (int)$careerID;
 
         // Use Laravel's DB facade to call the stored procedure with parameter binding
         $trainings = DB::select('SELECT * FROM sp_getrecommendedtrainings_bycareer(?)', [$careerID]);
 
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
         })->all(); // Use all() instead of toArray() to preserve objects
         
         // Convert to array for JSON response
         $trainingsWithOrg = array_map(function($training) {
             return (array) $training; // Convert stdClass to array for JSON encoding
         }, $trainingsWithOrg);

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
                'c.placeOfAssignment',
                'c.details',
                'c.qualificationStandard',
                'c.pdf_directory',
                'c.postingDate',
                'c.closingDate',
                'c.trainingsAttendedPercentage',
                'c.organizationID',
                'o.name as organization',
                'o.name as organizationName'
            )
            ->where('c.careerID', $careerID)
            ->first();
 
         // Fetch recommended trainings (includes trainings for target career)
         $recommended_trainings = DB::select('SELECT * FROM sp_getrecommendedtrainings_bycareer(?)', [$careerID]);
         
         // Get list of training IDs that are marked as organization's choice for this career
         $organizationsChoiceTrainingIDs = DB::table('organizationschoice')
             ->where('careerID', $careerID)
             ->pluck('trainingID')
             ->toArray();
         
         // ✅ Map organization name from stored procedure result
         $trainingsWithOrg = collect($recommended_trainings)->map(function ($training) use ($organizationsChoiceTrainingIDs) {
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
             
             // Check if this training is marked as organization's choice
             $trainingID = $training->trainingID ?? $training->TrainingID ?? null;
             $training->isOrganizationsChoice = $trainingID && in_array($trainingID, $organizationsChoiceTrainingIDs);
             
             return $training;
         })->all(); // Use all() instead of toArray() to preserve objects
         
         // Convert to array for JSON response
         $trainingsWithOrg = array_map(function($training) {
             return (array) $training; // Convert stdClass to array for JSON encoding
         }, $trainingsWithOrg);
 
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
            $results = DB::select('SELECT * FROM sp_getrecommendedcareers_bytags(?)', [$careerID]);
            
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

    /**
     * Get organizationschoice trainings for a career (public endpoint for applicants)
     */
    public function getOrganizationsChoiceTrainings($careerID)
    {
        try {
            $careerID = (int)$careerID;

            // Use Eloquent model to handle column mapping automatically
            $career = Career::find($careerID);

            if (!$career) {
                return response()->json([
                    'message' => 'Career not found'
                ], 404);
            }

            // Get organizationschoice trainings using the relationship
            // Filter by organizationID on the pivot table
            $organizationsChoiceTrainings = $career->selectedTrainings()
                ->wherePivot('organizationID', $career->organizationID)
                ->select('training.trainingID', 'training.title', 'training.description')
                ->get()
                ->map(function($training) {
                    return [
                        'trainingID' => $training->trainingID,
                        'title' => $training->title ?? $training->Title ?? null,
                        'description' => $training->description ?? $training->Description ?? null,
                    ];
                });

            return response()->json($organizationsChoiceTrainings);
        } catch (\Exception $e) {
            Log::error('Failed to get organizationschoice trainings', [
                'careerID' => $careerID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load organizationschoice trainings',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended careers based on user skills
     * Skills are matched against career tags (tagName)
     */
    public function recommendCareersBySkills(Request $request)
    {
        try {
            $skills = $request->input('skills', []);
            
            // If no skills provided, return empty array
            if (empty($skills) || !is_array($skills)) {
                return response()->json([]);
            }

            // Normalize skills to lowercase for matching
            $normalizedSkills = array_map('strtolower', array_map('trim', $skills));
            
            // Get all tags that match the skills (case-insensitive match)
            $tags = DB::table('tag')->get();
            $matchingTagIDs = [];
            foreach ($tags as $tag) {
                $tagName = strtolower(trim($tag->tagName ?? $tag->TagName ?? ''));
                if (in_array($tagName, $normalizedSkills)) {
                    $matchingTagIDs[] = $tag->TagID;
                }
            }

            if (empty($matchingTagIDs)) {
                return response()->json([]);
            }

            // Get careers that have at least one matching tag
            // Join with career_tag to find careers with matching tags
            $careers = DB::table('career as c')
                ->join('organization as o', 'c.organizationID', '=', 'o.organizationID')
                ->join('career_tag as ct', 'c.careerID', '=', 'ct.careerID')
                ->whereIn('ct.TagID', $matchingTagIDs)
                ->select(
                    'c.careerID',
                    'c.position',
                    'c.placeOfAssignment',
                    'c.details',
                    'c.qualificationStandard',
                    'c.pdf_directory',
                    'c.postingDate',
                    'c.closingDate',
                    'c.trainingsAttendedPercentage',
                    'c.organizationID',
                    'o.name as organization',
                    'o.name as organizationName',
                    DB::raw('COUNT(DISTINCT ct.TagID) as matchedTagsCount')
                )
                ->groupBy(
                    'c.careerID',
                    'c.position',
                    'c.placeOfAssignment',
                    'c.details',
                    'c.qualificationStandard',
                    'c.pdf_directory',
                    'c.postingDate',
                    'c.closingDate',
                    'c.trainingsAttendedPercentage',
                    'c.organizationID',
                    'o.name'
                )
                ->orderByDesc('matchedTagsCount')
                ->orderByDesc('c.postingDate')
                ->get();

            // Filter out closed careers (where closing date has passed)
            $today = now()->startOfDay();
            $careers = $careers->filter(function ($career) use ($today) {
                if (!$career->closingDate) {
                    return true; // Include careers without closing date
                }
                $closingDate = \Carbon\Carbon::parse($career->closingDate)->startOfDay();
                return $closingDate >= $today;
            });

            return response()->json($careers->values());
        } catch (\Exception $e) {
            Log::error('Failed to get recommended careers by skills', [
                'skills' => $skills ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load recommended careers',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
