<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    //list applicant's applications
    public function index(Request $request)
    {
        $user = $request->user();

        $apps = Application::with('career')
            ->where('applicantID', $user->applicantID)
            ->get();

            return response()->json($apps);
    }

    //create application
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'careerID' => 'required|exists:career,careerID',
            'requirements' => 'nullable|file|mimes:pdf|max:5120', //5mb
        ]);

        //prevent duplicates
        $existing = Application::where('applicantID', $user->applicantID)
            ->where('careerID', (int) $validated['careerID'])
            ->first();
        
        if($existing){
            return response()->json([
                'message' => 'ALREADY APPLIED',
                'applicationID' => $existing->applicationID,
            ], 409);
        }

        //store requirements pdf
        $requirementsPath = null;
        if($request->hasFile('requirements')){
            //uses public disk, ensure filesystems.php has public disk configured
            $requirementsPath = $request->file('requirements')->store('requirements', 'public');
        }

        $app = Application::create([
            'requirements' => $requirementsPath,
            'dateSubmitted' => Carbon::now(),
            'applicationStatus' => 'Applied',
            'interviewSchedule' => null,
            'interviewMode' => null,
            'interviewLocation' => null,
            'interviewLink' => null,
            'careerID' => (int) $validated['careerID'],
            'applicantID' => $user->applicantID,
        ]);

        return response()->json([
            'message' => 'APPLICATION SUBMITTED SUCCESSFULLY!!!',
            'data' => $app,
        ], 201);
    }

    //withdraw application
    public function destroy(Request $request, int $id)
    {
        $user = $request->authUser;

        $app = Application::where('applicationID', $id)
            ->where('applicantID', $user->applicantID)
            ->first();

        if(!$app){
            return response()->json(['message' => 'APPLICATION NOT FOUND'], 404);
        }

        $app->delete();

        return response()->json(['message' => 'APPLICATION WITHDRAWN'], 200);
    }


}
