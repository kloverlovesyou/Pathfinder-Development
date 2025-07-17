<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApplicantController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'emailAddress' => 'required|email|unique:Applicant,emailAddress',
            'phoneNumber' => 'required|digits:11',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/'
            ],
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
            'password' => Hash::make($request->password), // ✅ Encrypt the password
        ]);

        return response()->json(['message' => 'Account created successfully', 'data' => $applicant], 201);
    }
}
