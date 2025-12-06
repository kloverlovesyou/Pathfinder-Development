<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CareerController extends Controller
{
    public function getCareers()
    {
        $careers = Career::select('careerID', 'position')->get();

        return response()->json([
            'status' => 'success',
            'careers' => $careers,
        ]);
    }
    
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $careers = Career::with(['organization', 'tags'])
            ->where('organizationID', $user->organizationID)
            ->orderByDesc('closingDate')
            ->get()
            ->map(function ($career){
                return[
                    'careerID' => $career->careerID,
                    'position' => $career->position,
                    'placeOfAssignment' => $career->placeOfAssignment,
                    'details' => $career->details,
                    'qualificationStandard' => $career->qualificationStandard,
                    'pdf_directory' => $career->pdf_directory,
                    'postingDate' => $career->postingDate,
                    'closingDate' => $career->closingDate,
                    'trainingsAttendedPercentage' => $career->trainingsAttendedPercentage,
                    'organizationID' => $career->organizationID,
                    'organizationName' => $career->organization->name ?? 'Unknown',
                    'Tags' => $career->tags->map(function ($tag) {
                        return [
                            'TagID' => $tag->TagID,
                            'tagName' => $tag->TagName ?? '',
                        ];
                    }),
                ];
            });

        return response()->json($careers);
    }
        public function store(Request $request)
    {
        // ✅ Use authenticated user from middleware
         $token = $request->bearerToken();
         // Try both models since you have separate tables
        $user = \App\Models\Organization::where('api_token', $token)->first()
            ?? \App\Models\Applicant::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate request - using actual database column name
        // Details and qualificationStandard are required only if pdf_directory is not provided or is empty
        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'placeOfAssignment' => 'required|string|max:255',
            'details' => 'nullable|string',
            'qualificationStandard' => 'nullable|string',
            'pdf_directory' => 'nullable|string|max:255',
            'postingDate' => 'required|date',
            'closingDate' => 'required|date',
            'trainingsAttendedPercentage' => 'nullable|integer|min:0|max:100',
            'Tags' => 'nullable|array',
            'Tags.*' => 'integer|exists:tag,TagID',
        ]);

        // Ensure at least one of details/qualificationStandard OR pdf_directory is provided
        $pdfDirectory = !empty($validated['pdf_directory']) ? trim($validated['pdf_directory']) : null;
        $details = !empty($validated['details']) ? trim($validated['details']) : null;
        $qualificationStandard = !empty($validated['qualificationStandard']) ? trim($validated['qualificationStandard']) : null;

        // If no PDF is uploaded, require at least details or qualificationStandard
        if (empty($pdfDirectory) && empty($details) && empty($qualificationStandard)) {
            return response()->json([
                'message' => 'Either details/qualification standard or PDF file must be provided.'
            ], 422);
        }

        $postingDateFormatted = Carbon::parse($validated['postingDate'])->format('Y-m-d');
        $closingDateFormatted = Carbon::parse($validated['closingDate'])->format('Y-m-d');

        // Create career linked to organization - using actual database column names
        $career = Career::create([
            'position' => $validated['position'],
            'placeOfAssignment' => $validated['placeOfAssignment'],
            'details' => $details,
            'qualificationStandard' => $qualificationStandard,
            'pdf_directory' => $pdfDirectory,
            'postingDate' => $postingDateFormatted,
            'closingDate' => $closingDateFormatted,
            'trainingsAttendedPercentage' => $validated['trainingsAttendedPercentage'] ?? null,
            'organizationID' => $user->organizationID ?? $user->id,
        ]);

        // Attach tags if any
        if (!empty($validated['Tags'])) {
            $career->tags()->attach($validated['Tags']);
        }

        return response()->json([
            'message' => 'CAREER POSTED SUCCESSFULLY!!!',
            'data' => $career,
            'tags' => $validated['Tags'] 
        ], 201);
    }


//This is for Total numbers of career
public function total() {
    $totalCareers = \App\Models\Career::count();
    return response()->json(['totalCareers' => $totalCareers]);
}

//This is for numbers of on-going and filled out career
public function countsPartial()
{
    $now = now(); // current date & time

    $ongoing = \App\Models\Career::where('closingDate', '>', $now)->count();
    $filled = \App\Models\Career::where('closingDate', '<=', $now)->count();

    return response()->json([
        'ongoing' => $ongoing,
        'filled' => $filled,
    ]);
}

    public function show($id)
    {
        $career = Career::with('organization')->findOrFail($id);

        return response()->json([
            'careerID' => $career->careerID,
            'position' => $career->position,
            'placeOfAssignment' => $career->placeOfAssignment,
            'applicationLetterAddress' => $career->placeOfAssignment, // Backward compatibility
            'details' => $career->details,
            'qualificationStandard' => $career->qualificationStandard,
            'qualifications' => $career->qualificationStandard, // Backward compatibility
            'pdf_directory' => $career->pdf_directory,
            'postingDate' => $career->postingDate,
            'closingDate' => $career->closingDate,
            'deadlineOfSubmission' => $career->closingDate, // Backward compatibility
            'detailsAndInstructions' => $career->details, // Backward compatibility
            'trainingsAttendedPercentage' => $career->trainingsAttendedPercentage,
            'organizationID' => $career->organizationID,
            'organization' => $career->organization->name ?? 'Unknown',
        ]);
    }
 public function update(Request $request, $id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        $user = $request->user();
        if (!$user || !isset($user->organizationID) || $career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        // Validate request - using actual database column name
        // Details and qualificationStandard are required only if pdf_directory is not provided or is empty
        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'placeOfAssignment' => 'required|string|max:255',
            'details' => 'nullable|string',
            'qualificationStandard' => 'nullable|string',
            'pdf_directory' => 'nullable|string|max:255',
            'postingDate' => 'required|date',
            'closingDate' => 'required|date',
            'trainingsAttendedPercentage' => 'nullable|integer|min:0|max:100',
            'Tags' => 'sometimes|array',
            'Tags.*' => 'integer|exists:tag,TagID',
        ]);

        // Ensure at least one of details/qualificationStandard OR pdf_directory is provided
        $pdfDirectory = !empty($validated['pdf_directory']) ? trim($validated['pdf_directory']) : null;
        $details = !empty($validated['details']) ? trim($validated['details']) : null;
        $qualificationStandard = !empty($validated['qualificationStandard']) ? trim($validated['qualificationStandard']) : null;

        // If no PDF is uploaded, require at least details or qualificationStandard
        if (empty($pdfDirectory) && empty($details) && empty($qualificationStandard)) {
            return response()->json([
                'message' => 'Either details/qualification standard or PDF file must be provided.'
            ], 422);
        }

        $career->position = $validated['position'];
        $career->placeOfAssignment = $validated['placeOfAssignment'];
        $career->details = $details;
        $career->qualificationStandard = $qualificationStandard;
        $career->pdf_directory = $pdfDirectory;
        $career->postingDate = Carbon::parse($validated['postingDate'])->format('Y-m-d');
        $career->closingDate = Carbon::parse($validated['closingDate'])->format('Y-m-d');
        $career->trainingsAttendedPercentage = $validated['trainingsAttendedPercentage'] ?? null;

        $career->save();

        if (isset($validated['Tags'])) {
            $career->tags()->sync($validated['Tags']);
        }

        return response()->json([
            'message' => 'Career updated successfully',
            'data' => $career->load('tags', 'organization')
        ]);
    }

    // Delete career
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Check if user is an Organization
        if (!($user instanceof \App\Models\Organization)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $career = Career::find($id);

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }
        
        // Verify career belongs to the organization
        if ($career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Access denied - You can only delete your own careers'], 403);
        }

        try {
            // Use database transaction to ensure all deletions happen atomically
            DB::beginTransaction();
            
            // Temporarily disable foreign key checks to avoid constraint issues
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Delete related records first to avoid foreign key constraint violations
            // Delete all applications for this career
            $applicationsDeleted = \App\Models\Application::where('careerID', $career->careerID)->delete();
            Log::info('Deleted applications for career', ['careerID' => $career->careerID, 'count' => $applicationsDeleted]);
            
            // Delete from pivot table directly (career_tag)
            $pivotDeleted = DB::table('career_tag')->where('careerID', $career->careerID)->delete();
            Log::info('Deleted career_tag pivot records', ['careerID' => $career->careerID, 'count' => $pivotDeleted]);
            
            // Delete the career itself
            $career->delete();
            Log::info('Career deleted successfully', ['careerID' => $id]);
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            DB::commit();

            return response()->json([
                'message' => 'Career deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Re-enable foreign key checks even if there's an error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $e2) {
                // Ignore errors when re-enabling
            }
            
            DB::rollBack();
            Log::error('Failed to delete career', [
                'careerID' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to delete career: ' . $e->getMessage()
            ], 500);
        }
    }

public function recommend($id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Replace with your logic ↓
        return response()->json([
            'recommendedCareer' => $career
        ]);
    }

    /**
     * Get trainings that share tags with the career (for organization's own trainings)
     */
    public function getAlignedTrainings(Request $request, $careerID)
    {
        $user = $request->user();
        
        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $career = Career::with('tags')->find($careerID);
        
        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Verify career belongs to the organization
        if ($career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Get tag IDs from the career
        $careerTagIDs = $career->tags->pluck('TagID')->toArray();

        if (empty($careerTagIDs)) {
            return response()->json([]);
        }

        // Get trainings from the same organization that share at least one tag
        // Exclude trainings that are completed (all schedules have ended)
        $now = \Carbon\Carbon::now();
        
        $trainings = \App\Models\Training::with(['tags', 'schedules'])
            ->where('training.organizationID', $user->organizationID)
            ->whereHas('tags', function ($query) use ($careerTagIDs) {
                $query->whereIn('training_tag.TagID', $careerTagIDs);
            })
            ->get()
            ->filter(function ($training) use ($now) {
                // Exclude trainings that are completed (all schedules have ended)
                if (!$training->schedules || $training->schedules->isEmpty()) {
                    // If no schedules, exclude it (can't determine if it's completed)
                    return false;
                }
                
                // Check if there are any future schedules
                $hasFutureSchedule = false;
                $latestEndTime = null;
                
                foreach ($training->schedules as $schedule) {
                    // Check if schedule hasn't started yet (future schedule)
                    if ($schedule->schedule && $schedule->schedule->isFuture()) {
                        $hasFutureSchedule = true;
                        break;
                    }
                    
                    // Track latest end time
                    if ($schedule->end_time) {
                        if (!$latestEndTime || $schedule->end_time->gt($latestEndTime)) {
                            $latestEndTime = $schedule->end_time;
                        }
                    }
                }
                
                // If there's a future schedule, training is not completed
                if ($hasFutureSchedule) {
                    return true;
                }
                
                // If no future schedules, check if latest end time has passed
                // If latest end time has passed, training is completed (exclude it)
                if ($latestEndTime && $latestEndTime->lte($now)) {
                    return false; // Exclude completed training
                }
                
                // If no end time or end time hasn't passed, include it
                return true;
            })
            ->map(function ($training) {
                return [
                    'trainingID' => $training->trainingID,
                    'title' => $training->title ?? $training->Title,
                    'description' => $training->description ?? $training->Description,
                    'Tags' => $training->tags->map(function ($tag) {
                        return [
                            'TagID' => $tag->TagID,
                            'TagName' => $tag->TagName ?? $tag->tagName,
                        ];
                    }),
                ];
            })
            ->values(); // Reset array keys after filter

        return response()->json($trainings);
    }

    /**
     * Get selected trainings for a career (Organization's Choice)
     */
    public function getSelectedTrainings(Request $request, $careerID)
    {
        $user = $request->user();
        
        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $career = Career::find($careerID);
        
        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Verify career belongs to the organization
        if ($career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Get selected trainings from organizationschoice table
        // Qualify column names to avoid ambiguity
        $selectedTrainings = DB::table('organizationschoice')
            ->where('organizationschoice.careerID', $careerID)
            ->where('organizationschoice.organizationID', $user->organizationID)
            ->join('training', 'organizationschoice.trainingID', '=', 'training.trainingID')
            ->select('training.trainingID', 'training.title', 'training.description')
            ->get();

        return response()->json($selectedTrainings);
    }

    /**
     * Save selected trainings for a career (Organization's Choice)
     */
    public function saveSelectedTrainings(Request $request, $careerID)
    {
        $user = $request->user();
        
        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $career = Career::find($careerID);
        
        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Verify career belongs to the organization
        if ($career->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Log incoming request data
        Log::info('saveSelectedTrainings called', [
            'careerID' => $careerID,
            'organizationID' => $user->organizationID,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'trainingIDs' => 'required|array',
            'trainingIDs.*' => 'integer|exists:training,trainingID',
        ]);

        // Verify all trainings belong to the organization
        $trainingIDs = $validated['trainingIDs'];
        
        Log::info('Validated trainingIDs', [
            'trainingIDs' => $trainingIDs,
            'count' => count($trainingIDs)
        ]);
        
        if (!empty($trainingIDs)) {
            $invalidTrainings = \App\Models\Training::whereIn('trainingID', $trainingIDs)
                ->where('organizationID', '!=', $user->organizationID)
                ->pluck('trainingID')
                ->toArray();

            if (!empty($invalidTrainings)) {
                Log::warning('Invalid trainings detected', ['invalidTrainings' => $invalidTrainings]);
                return response()->json([
                    'message' => 'Some trainings do not belong to your organization'
                ], 403);
            }
        }

        // Get the max organizationsChoiceID BEFORE starting transaction to avoid transaction abort issues
        $maxID = DB::table('organizationschoice')->max('organizationsChoiceID') ?? 0;
        Log::info('Max organizationsChoiceID before transaction', ['maxID' => $maxID]);

        try {
            DB::beginTransaction();

            // Delete existing selections for this career and organization
            $deletedCount = DB::table('organizationschoice')
                ->where('careerID', $careerID)
                ->where('organizationID', $user->organizationID)
                ->delete();
            
            Log::info('Deleted existing selections', ['deletedCount' => $deletedCount]);

            // Insert new selections
            if (!empty($trainingIDs)) {
                
                $insertData = [];
                $currentID = $maxID;
                foreach ($trainingIDs as $trainingID) {
                    $currentID++;
                    $insertData[] = [
                        'organizationsChoiceID' => $currentID,
                        'careerID' => $careerID,
                        'trainingID' => $trainingID,
                        'organizationID' => $user->organizationID,
                    ];
                }

                Log::info('Preparing to insert', [
                    'insertData' => $insertData,
                    'count' => count($insertData)
                ]);

                // Insert all records - since we deleted existing ones first, there shouldn't be duplicates
                try {
                    // Try using query builder first
                    $inserted = DB::table('organizationschoice')->insert($insertData);
                    Log::info('Bulk insert successful (query builder)', [
                        'inserted' => $inserted,
                        'expectedCount' => count($insertData)
                    ]);
                    
                } catch (\Illuminate\Database\QueryException $insertError) {
                    // Log the ORIGINAL error that caused the transaction abort
                    $originalError = $insertError->getPrevious();
                    Log::error('Bulk insert failed with query builder - ORIGINAL ERROR', [
                        'error' => $insertError->getMessage(),
                        'code' => $insertError->getCode(),
                        'errorCode' => $insertError->getCode(),
                        'sql' => $insertError->getSql(),
                        'bindings' => $insertError->getBindings(),
                        'previous' => $originalError ? $originalError->getMessage() : 'N/A',
                        'fullTrace' => $insertError->getTraceAsString()
                    ]);
                    
                    // Transaction is aborted, need to rollback and retry
                    DB::rollBack();
                    DB::beginTransaction();
                    
                    Log::info('Rolled back and started new transaction for retry');
                    
                    // Re-delete existing selections (rollback undid the delete)
                    $deletedCount = DB::table('organizationschoice')
                        ->where('careerID', $careerID)
                        ->where('organizationID', $user->organizationID)
                        ->delete();
                    Log::info('Re-deleted existing selections after rollback', ['deletedCount' => $deletedCount]);
                    
                    // Try inserting one by one using query builder
                    $successCount = 0;
                    foreach ($insertData as $data) {
                        try {
                            DB::table('organizationschoice')->insert($data);
                            $successCount++;
                            Log::info('Individual insert successful', ['data' => $data, 'successCount' => $successCount]);
                        } catch (\Exception $e) {
                            Log::error('Individual insert failed', [
                                'data' => $data,
                                'error' => $e->getMessage(),
                                'code' => $e->getCode()
                            ]);
                            
                            // If individual insert fails, rollback and try raw SQL
                            DB::rollBack();
                            DB::beginTransaction();
                            
                            // Re-delete again after rollback
                            DB::table('organizationschoice')
                                ->where('careerID', $careerID)
                                ->where('organizationID', $user->organizationID)
                                ->delete();
                            
                            // Try raw SQL as last resort - include organizationsChoiceID
                            try {
                                DB::statement(
                                    'INSERT INTO organizationschoice ("organizationsChoiceID", "careerID", "trainingID", "organizationID") VALUES (?, ?, ?, ?)',
                                    [$data['organizationsChoiceID'], $data['careerID'], $data['trainingID'], $data['organizationID']]
                                );
                                $successCount++;
                                Log::info('Individual raw SQL insert successful', ['data' => $data]);
                            } catch (\Exception $rawError) {
                                Log::error('Individual raw SQL insert also failed', [
                                    'data' => $data,
                                    'error' => $rawError->getMessage()
                                ]);
                                throw $rawError; // Re-throw to trigger final rollback
                            }
                        }
                    }
                    Log::info('Individual inserts completed', ['successCount' => $successCount, 'total' => count($insertData)]);
                }
            } else {
                Log::info('No trainings to insert (empty array)');
            }

            DB::commit();
            
            Log::info('Transaction committed successfully');

            // Verify the insert by querying the table
            $verifyCount = DB::table('organizationschoice')
                ->where('careerID', $careerID)
                ->where('organizationID', $user->organizationID)
                ->count();
            
            Log::info('Verification query', ['verifyCount' => $verifyCount]);

            return response()->json([
                'message' => 'Selected trainings saved successfully',
                'selectedTrainings' => $trainingIDs,
                'savedCount' => $verifyCount
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Failed to save selected trainings - Database error', [
                'careerID' => $careerID,
                'organizationID' => $user->organizationID,
                'trainingIDs' => $trainingIDs,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            // Provide more specific error message
            $errorMessage = 'Failed to save selected trainings';
            if (strpos($e->getMessage(), 'duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
                $errorMessage = 'Some trainings are already selected for this career';
            } elseif (strpos($e->getMessage(), 'foreign key') !== false) {
                $errorMessage = 'Invalid training or career reference';
            }

            return response()->json([
                'message' => $errorMessage,
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save selected trainings', [
                'careerID' => $careerID,
                'organizationID' => $user->organizationID,
                'trainingIDs' => $trainingIDs,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to save selected trainings: ' . $e->getMessage()
            ], 500);
        }
    }
}
