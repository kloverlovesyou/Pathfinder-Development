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
            'careerID'  => 'required|exists:career,careerID',
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
            'careerID'  => $request->careerID,
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

    // You can also generate a token here if needed (for Sanctum)
    return response()->json([
        'message' => 'Login successful',
        'user' => $applicant,
        'token' => $applicant->api_token, // send token to frontend
    ]);
}

 // Update applicant profile
    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $applicant = Applicant::where('api_token', $token)->first();

        if (!$applicant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Optional: Verify current password before allowing update
        if (!Hash::check($request->currentPassword, $applicant->password)) {
            return response()->json(['message' => 'Incorrect current password'], 403);
        }

        // Update fields
        $applicant->update([
            'firstName' => $request->firstName,
            'middleName' => $request->middleName,
            'lastName' => $request->lastName,
            'address' => $request->address,
            'emailAddress' => $request->emailAddress,
            'phoneNumber' => $request->phoneNumber,
            'password' => $request->newPassword ? Hash::make($request->newPassword) : $applicant->password,
        ]);

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

}