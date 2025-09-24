<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
    ]);
}

public function destroy(Request $request)
{
    $user = $request->user(); // get authenticated user

    if ($user) {
        // delete the account
        $user->delete();

        // revoke all tokens (logout everywhere)
        $user->tokens()->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }

    return response()->json(['message' => 'User not found'], 404);
}
}