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

    // ✅ Get popular skills based on tags used in careers and trainings
    public function getPopularSkills()
    {
        // Get most used tags from careers
        $careerTags = \DB::table('career_tag')
            ->select('TagID', \DB::raw('COUNT(*) as count'))
            ->groupBy('TagID')
            ->orderBy('count', 'desc')
            ->get();

        // Get most used tags from trainings
        $trainingTags = \DB::table('training_tag')
            ->select('TagID', \DB::raw('COUNT(*) as count'))
            ->groupBy('TagID')
            ->orderBy('count', 'desc')
            ->get();

        // Combine and count total usage
        $tagCounts = [];

        // Add career tag counts
        foreach ($careerTags as $tag) {
            $tagCounts[$tag->TagID] = ($tagCounts[$tag->TagID] ?? 0) + $tag->count;
        }

        // Add training tag counts
        foreach ($trainingTags as $tag) {
            $tagCounts[$tag->TagID] = ($tagCounts[$tag->TagID] ?? 0) + $tag->count;
        }

        // Sort by total count descending
        arsort($tagCounts);

        // Get top 20 most used tags
        $topTagIds = array_slice(array_keys($tagCounts), 0, 20);

        // Fetch tag details and sort by the order in $topTagIds
        $popularSkills = \App\Models\Tag::whereIn('TagID', $topTagIds)
            ->get(['TagID', 'tagName'])
            ->sortBy(function ($tag) use ($topTagIds) {
                return array_search($tag->TagID, $topTagIds);
            })
            ->values();

        return response()->json($popularSkills);
    }
}
