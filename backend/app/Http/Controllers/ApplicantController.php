<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
class ApplicantController extends Controller
{
    public function a_register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstName'    => 'required|string|max:255',
            'lastName'     => 'required|string|max:255',
            'address'      => 'required|string|max:255',
            'emailAddress' => 'required|email|unique:applicant,emailAddress',
            'phoneNumber'  => 'required|string|max:11',
            'password'     => 'required|string|min:8',
         
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $applicant = Applicant::create([
            'firstName'    => $request->firstName,
            'middleName'   => $request->middleName,
            'lastName'     => $request->lastName,
            'address'      => $request->address,
            'emailAddress' => $request->emailAddress,
            'phoneNumber'  => $request->phoneNumber,
            'password'     => bcrypt($request->password),
            'api_token'    => Str::random(60),
           
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration successful',
            'user'    => $applicant,
        ], 201);
    }

public function login(Request $request)
{
    $request->validate([
        'emailAddress' => 'required|email',
        'password' => 'required|string|min:8',
    ]);

    $applicant = Applicant::where('emailAddress', $request->emailAddress)->first();

    if (!$applicant || !Hash::check($request->password, $applicant->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // ✅ Only generate if missing
    if (!$applicant->api_token) {
        $applicant->api_token = Str::random(60);
        $applicant->save();
    }

    return response()->json([
        'message' => 'Login successful',
        'user' => $applicant,
        'token' => $applicant->api_token,
    ]);
}

  // 🧩 Update applicant profile
    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Validate input
        $validated = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'emailAddress' => 'nullable|email|max:255',
            'phoneNumber' => 'nullable|string|max:20',
        ]);

        // ✅ Update profile fields
        $applicant->fill([
            'firstName' => $request->firstName ?? $applicant->firstName,
            'middleName' => $request->middleName ?? $applicant->middleName,
            'lastName' => $request->lastName ?? $applicant->lastName,
            'address' => $request->address ?? $applicant->address,
            'emailAddress' => $request->emailAddress ?? $applicant->emailAddress,
            'phoneNumber' => $request->phoneNumber ?? $applicant->phoneNumber,
        ]);

        $applicant->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }
// Delete applicant account
   public function destroy(Request $request)
{
    $token = $request->bearerToken();
    $user = Applicant::where('api_token', $token)->first();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Verify password
    if (!Hash::check($request->currentPassword, $user->password)) {
        return response()->json(['message' => 'Incorrect password'], 403);
    }

    $user->delete();

    return response()->json(['message' => 'Account deleted successfully']);
}

   // 🧩 Update password separately
    public function updatePassword(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Validate fields
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        // ✅ Check if current password matches
        if (!Hash::check($validated['currentPassword'], $applicant->password)) {
            return response()->json(['message' => 'The current password is incorrect.'], 403);
        }

        // ✅ Update password
        $applicant->password = Hash::make($validated['newPassword']);
        $applicant->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

      public function index()
    {
        $applicants = Applicant::select(
            'applicantID',
            'firstName',
            'middleName',
            'lastName',
            'emailAddress',
            'address',
            'phoneNumber'
        )->get();

        return response()->json($applicants);
    }

    // 🗑️ 2. Delete applicant by ID (for admin use)
    public function destroyById($id)
    {
        $applicant = Applicant::find($id);

        if (!$applicant) {
            return response()->json(['message' => 'Applicant not found'], 404);
        }

        $applicant->delete();

        return response()->json(['message' => 'Applicant deleted successfully']);
    }

}