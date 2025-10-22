<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $filterType = $request->input('filterType', '');
        $subFilter = $request->input('subFilter', '');

        $results = DB::select('CALL SearchContent(?, ?, ?)', [
            $searchTerm,
            $filterType,
            $subFilter
        ]);

        return response()->json(['results' => $results]);
    }
}
