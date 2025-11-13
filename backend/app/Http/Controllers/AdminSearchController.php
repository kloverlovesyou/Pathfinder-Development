<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('query', '');

        if (empty($query)) {
            return response()->json([]);
        }

        try {
            // Call stored procedure
            $results = DB::select('CALL AdminSearch(?)', [$query]);
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
