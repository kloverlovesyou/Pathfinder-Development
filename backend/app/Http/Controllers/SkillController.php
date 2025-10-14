<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skill;

class SkillController extends Controller
{
    // ✅ Get all skills for a specific resume
    public function index($resumeID)
    {
        $skills = Skill::where('resumeID', $resumeID)->get();
        return response()->json($skills);
    }

    // ✅ Store a new skill for a resume
    public function store(Request $request)
    {
        $validated = $request->validate([
            'skillName' => 'required|string|max:255',
            'resumeID' => 'required|integer|exists:resume,resumeID',
        ]);

        $skill = Skill::create($validated);
        return response()->json($skill, 201);
    }

    // ✅ Delete a skill
    public function destroy($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();

        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
