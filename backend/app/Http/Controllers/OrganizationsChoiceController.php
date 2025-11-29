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
}

