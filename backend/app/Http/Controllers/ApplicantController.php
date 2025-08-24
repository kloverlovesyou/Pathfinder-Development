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
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'emailAddress' => 'required|email|unique:Applicant,emailAddress',
            'phoneNumber' => 'required|digits:11',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $applicant = Applicant::create([
            'firstName' => $request->firstName,
            'middleName' => $request->middleName,
            'lastName' => $request->lastName,
            'address' => $request->address,
            'emailAddress' => $request->emailAddress,
            'phoneNumber' => $request->phoneNumber,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Registration successful'], 201);
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
}