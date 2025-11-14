<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{

    public function index()
    {
        // Include careers and trainings
        $organizations = Organization::with(['careers', 'trainings'])->get();

        return response()->json($organizations);
    }
    // Register Organization

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

    // Login Organization
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

        // BLOCK PENDING / REJECTED
        if ($organization->status === 'pending') {
            return response()->json(['message' => 'Your registration is still under review.'], 403);
        }

        if ($organization->status === 'rejected') {
            return response()->json(['message' => 'Your registration was rejected.'], 403);
        }

        // Generate or reuse token
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

    public function approve($id)
    {
        $org = Organization::findOrFail($id);
        $org->status = 'approved';
        $org->save();

        return response()->json(['message' => 'Organization approved']);
    }

    public function reject($id)
    {
        $org = Organization::findOrFail($id);
        $org->status = 'rejected';
        $org->save();

        return response()->json(['message' => 'Organization rejected']);
    }

    // List pending organizations
    public function pending()
    {
        $organizations = Organization::where('status', 'pending')->get();
        return response()->json($organizations);
    }
    
    // Delete organization by ID along with related data
    
    public function destroyById($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        foreach ($organization->trainings as $training) {
            $training->trainingbookmarks()->delete();
            $training->registrations()->delete();
            $training->attendances()->delete();
            $training->tags()->detach(); // clear pivot table
        }

        $organization->trainings()->delete();
        $organization->careers()->delete();
        $organization->delete();

        return response()->json(['message' => 'Organization and all related data deleted successfully']);
    }


}