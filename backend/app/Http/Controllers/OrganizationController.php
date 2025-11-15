<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Training;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
    // ----------------------
    // List approved organizations with careers & trainings
    // ----------------------
    public function index()
    {
        $organizations = Organization::with(['careers', 'trainings'])
            ->where('status', 'approved')
            ->get();

        return response()->json($organizations);
    }

    // ----------------------
    // Show single organization with related careers & trainings
    // ----------------------
    public function show($organizationID)
    {
        $organization = Organization::with(['careers', 'trainings'])
            ->where('organizationID', $organizationID)
            ->where('status', 'approved')
            ->firstOrFail();

        return response()->json($organization);
    }

    // ----------------------
    // Register new organization
    // ----------------------
    public function o_register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'websiteURL'  => 'nullable|string|max:255',
            'emailAddress'=> 'required|email|unique:organization,emailAddress',
            'password'    => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organization = Organization::create([
            'name'        => $request->input('name'),
            'location'    => $request->input('location'),
            'websiteURL'  => $request->input('websiteURL'),
            'emailAddress'=> $request->input('emailAddress'),
            'password'    => Hash::make($request->input('password')),
            'adminID'     => $request->input('adminID'),
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Registration successful',
            'organization' => $organization
        ], 201);
    }

    // ----------------------
    // Login organization
    // ----------------------
    public function login(Request $request)
    {
        $request->validate([
            'emailAddress' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $organization = Organization::where('emailAddress', $request->emailAddress)->first();

        if (!$organization || !Hash::check($request->password, $organization->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($organization->status === 'pending') {
            return response()->json(['message' => 'Your registration is still under review.'], 403);
        }

        if ($organization->status === 'rejected') {
            return response()->json(['message' => 'Your registration was rejected.'], 403);
        }

        if (!$organization->api_token) {
            $organization->api_token = Str::random(60);
            $organization->save();
        }

        return response()->json([
            'message' => 'Login successful',
            'organization' => $organization,
            'token' => $organization->api_token,
        ]);
    }

    // ----------------------
    // Approve organization
    // ----------------------
    public function approve($id)
    {
        $org = Organization::findOrFail($id);
        $org->status = 'approved';
        $org->save();

        return response()->json(['message' => 'Organization approved']);
    }

    // ----------------------
    // Reject organization
    // ----------------------
    public function reject($id)
    {
        $org = Organization::findOrFail($id);
        $org->status = 'rejected';
        $org->save();

        return response()->json(['message' => 'Organization rejected']);
    }

    // ----------------------
    // List pending organizations
    // ----------------------
    public function pending()
    {
        $organizations = Organization::where('status', 'pending')->get();
        return response()->json($organizations);
    }

    // ----------------------
    // Delete organization along with all related data
    // ----------------------
    public function destroyById($id)
    {
        $organization = Organization::with(['trainings', 'careers'])->find($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        DB::transaction(function () use ($organization) {
            // Delete trainings & related data
            foreach ($organization->trainings as $training) {
                $training->trainingbookmarks()->delete();
                $training->registrations()->delete();
                if (method_exists($training, 'attendances')) {
                    $training->attendances()->delete();
                }
                $training->tags()->detach();
                $training->delete();
            }

            // Delete careers
            $organization->careers()->delete();

            // Delete organization
            $organization->delete();
        });

        return response()->json(['message' => 'Organization and all related data deleted successfully']);
    }
}
