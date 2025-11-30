<?php

namespace App\Http\Controllers;

use App\Models\OrganizationsChoice;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationsChoiceController extends Controller
{
    /**
     * Return all organization choice mappings for the authenticated organization.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $choices = OrganizationsChoice::where('organizationID', $user->organizationID)
            ->get(['organizationsChoiceID', 'trainingID', 'careerID', 'organizationID']);

        return response()->json($choices);
    }

    /**
     * Mark a training as an organization choice (optionally tied to a career).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $validated = $request->validate([
            'trainingID' => 'required|exists:training,trainingID',
            'careerID' => 'nullable|exists:career,careerID',
        ]);

        $training = Training::find($validated['trainingID']);

        if (!$training || $training->organizationID !== $user->organizationID) {
            return response()->json(['message' => 'Training not found or not owned by the organization'], 403);
        }

        $choice = DB::transaction(function () use ($validated, $user) {
            $choice = OrganizationsChoice::where('trainingID', $validated['trainingID'])
                ->where('organizationID', $user->organizationID)
                ->lockForUpdate()
                ->first();

            if (!$choice) {
                $choice = new OrganizationsChoice();
                $choice->organizationsChoiceID = $this->generateChoiceId();
                $choice->trainingID = $validated['trainingID'];
                $choice->organizationID = $user->organizationID;
            }

            $choice->careerID = $validated['careerID'] ?? null;
            $choice->save();

            return $choice;
        });

        return response()->json([
            'message' => 'Training marked as organization choice',
            'data' => $choice,
        ], 201);
    }

    /**
     * Remove a training from the organization's choice list.
     */
    public function destroy(Request $request, $trainingID)
    {
        $user = $request->user();

        if (!$user || !isset($user->organizationID)) {
            return response()->json(['message' => 'Unauthorized - Organization access required'], 401);
        }

        $choice = OrganizationsChoice::where('trainingID', $trainingID)
            ->where('organizationID', $user->organizationID)
            ->first();

        if (!$choice) {
            return response()->json(['message' => 'Organization choice not found'], 404);
        }

        $choice->delete();

        return response()->json(['message' => 'Training removed from organization choices']);
    }

    private function generateChoiceId(): int
    {
        $maxId = OrganizationsChoice::max('organizationsChoiceID');
        return ($maxId ?? 0) + 1;
    }

    /**
     * Get all training IDs that are marked as organization choices.
     * This is a public method that can be used by other controllers.
     * 
     * @return array Array of training IDs (as integers)
     */
    public static function getAllOrganizationChoiceTrainingIds(): array
    {
        $trainingIds = DB::table('organizationschoice')
            ->pluck('trainingID')
            ->toArray();
        
        // Convert to integers for consistent comparison
        return array_map('intval', $trainingIds);
    }

    /**
     * Mark trainings with isOrganizationChoice flag based on whether they exist in organizationschoice table.
     * This is a public static method that can be used by other controllers.
     * 
     * @param array $trainings Array of training objects/arrays
     * @return array Array of trainings with isOrganizationChoice flag added
     */
    public static function markTrainingsWithOrganizationChoiceFlag(array $trainings): array
    {
        // Get all organization choice training IDs
        $organizationChoiceTrainingIds = self::getAllOrganizationChoiceTrainingIds();
        
        // Debug: Log organization choice IDs
        \Log::info('OrganizationsChoiceController::markTrainingsWithOrganizationChoiceFlag - Training IDs in table:', $organizationChoiceTrainingIds);
        \Log::info('OrganizationsChoiceController::markTrainingsWithOrganizationChoiceFlag - Processing ' . count($trainings) . ' trainings');
        
        // Map through trainings and add the flag
        $result = array_map(function ($training) use ($organizationChoiceTrainingIds) {
            // Handle both object and array formats
            $trainingId = is_object($training) 
                ? ($training->trainingID ?? $training->TrainingID ?? null)
                : ($training['trainingID'] ?? $training['TrainingID'] ?? null);
            
            // Convert to int for comparison
            $trainingIdInt = $trainingId ? (int)$trainingId : null;
            
            // Check if training is in organizationschoice table
            $isOrgChoice = $trainingIdInt && in_array($trainingIdInt, $organizationChoiceTrainingIds, true);
            
            // Add the flag to the training
            if (is_object($training)) {
                $training->isOrganizationChoice = $isOrgChoice;
            } else {
                $training['isOrganizationChoice'] = $isOrgChoice;
            }
            
            // Debug: Log first few trainings
            if (count($trainings) <= 3 || (is_object($training) ? $training->trainingID : $training['trainingID']) <= 3) {
                \Log::info('Training marked:', [
                    'trainingID' => $trainingId,
                    'trainingIDInt' => $trainingIdInt,
                    'isOrganizationChoice' => $isOrgChoice,
                    'title' => is_object($training) ? ($training->title ?? $training->Title ?? 'Unknown') : ($training['title'] ?? $training['Title'] ?? 'Unknown')
                ]);
            }
            
            return $training;
        }, $trainings);
        
        return $result;
    }
}

