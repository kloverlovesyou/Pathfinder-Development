<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // ✅ Accept input safely from the frontend
        $searchTerm = $request->input('search', '');
        $filterType = trim($request->input('filterType', ''));
        $subFilter  = trim($request->input('subFilter', ''));

        // ✅ Default to "all" when no filter is selected
        if ($filterType === null || $filterType === '') {
            $filterType = 'all';
        }

        try {
            // ✅ Call stored procedure
            $results = DB::select('CALL SearchContent(?, ?, ?)', [
                $searchTerm,
                $filterType,
                $subFilter
            ]);

            // ✅ Return structured JSON response
            return response()->json([
                'success' => true,
                'filterType' => $filterType,
                'count' => count($results),
                'results' => $results,
                'message' => count($results) > 0
                    ? 'Search results retrieved successfully.'
                    : 'No results found.'
            ]);
        } catch (\Exception $e) {
            // ⚠️ Catch SQL or other backend errors
            return response()->json([
                'success' => false,
                'message' => 'Error executing search: ' . $e->getMessage()
            ], 500);
        }
    }
}
