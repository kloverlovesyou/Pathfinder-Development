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
         $tags = Tag::orderBy('TagName', 'asc')->get();
         return response()->json($tags);
     }
 
     //add a new tag
     public function store(Request $request)
     {
         $request->validate([
             'TagName' => 'required|string|max:255|unique:tag,TagName'
         ]);
 
         $tag = Tag::create([
             'TagName' => $request->TagName
         ]);
 
         return response()->json($tag, 201);
     }
}
