<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function regions(): JsonResponse
    {
        $res = Http::get('https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/regions.json');

        if ($res->failed()) {
            return response()->json(['error' => 'Failed to fetch data', 'status' => $res->status()], 404);
        }

        return response()->json($res->json());
    }

    public function provinces(): JsonResponse
    {
        $res = Http::get('https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/provinces.json');

        if ($res->failed()) {
            return response()->json(['error' => 'Failed to fetch data', 'status' => $res->status()], 404);
        }

        return response()->json($res->json());
    }

    public function cities(): JsonResponse
    {
        $res = Http::get('https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/cities.json');

        if ($res->failed()) {
            return response()->json(['error' => 'Failed to fetch data', 'status' => $res->status()], 404);
        }

        return response()->json($res->json());
    }

    public function barangays(): JsonResponse
    {
        $res = Http::get('https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/barangays.json');

        if ($res->failed()) {
            return response()->json(['error' => 'Failed to fetch data', 'status' => $res->status()], 404);
        }

        return response()->json($res->json());
    }
}