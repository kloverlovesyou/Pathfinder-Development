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
}
