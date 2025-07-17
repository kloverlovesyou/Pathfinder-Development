<?php
use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

public function store(Request $request)
{
    $validated = $request->validate([
        'firstName' => 'required|string|max:255',
        'middleName' => 'nullable|string|max:255',
        'lastName' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'emailAddress' => 'required|email|unique:applicants,emailAddress',
        'phoneNumber' => 'required|string|max:11',
        'password' => 'required|string|min:8',
    ]);

    $applicant = new Applicant($validated);
    $applicant->password = Hash::make($request->password); // securely hash password
    $applicant->save();

    return response()->json(['message' => 'Account created'], 201);
}