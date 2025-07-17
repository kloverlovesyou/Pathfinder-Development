<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;


class ApplicantController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'emailAddress' => 'required|email|unique:Applicant,emailAddress',
            'phoneNumber' => 'required|digits:11',
            'password' => 'required|string|min:8'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $applicant = Applicant::create($validated);

        return response()->json(['message' => 'Applicant registered successfully']);
    }
}