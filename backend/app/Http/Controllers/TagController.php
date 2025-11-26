<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
     //fetch all tags
     public function index()
     {
         // Use tagName (camelCase) to match database column, but return TagName (PascalCase) for frontend
         $tags = Tag::orderBy('tagName', 'asc')->get()->map(function ($tag) {
             return [
                 'TagID' => $tag->TagID,
                 'TagName' => $tag->tagName ?? $tag->TagName,
            ];
         });
         return response()->json($tags);
     }
 
    //add a new tag
    public function store(Request $request)
    {
        $request->validate([
            'TagName' => 'required|string|max:255|unique:tag,tagName' // Database column is tagName (camelCase)
        ]);

        // Use tagName (camelCase) to match database column
        $tag = Tag::create([
            'tagName' => $request->TagName
        ]);

        // Return with TagName (PascalCase) for frontend compatibility
        return response()->json([
            'TagID' => $tag->TagID,
            'TagName' => $tag->tagName ?? $tag->TagName,
        ], 201);
    }
}
