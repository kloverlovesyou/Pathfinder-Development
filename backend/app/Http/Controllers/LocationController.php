<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    protected function fetchJson(string $url): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'MyApp/1.0' // GitHub often requires a user-agent
            ])->get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => 'Failed to fetch data',
                    'status' => $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception fetching data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function regions()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/regions.json";
        return $this->fetchJson($url);
    }

    public function provinces()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/provinces.json";
        return $this->fetchJson($url);
    }

    public function cities()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/cities.json";
        return $this->fetchJson($url);
    }

    public function barangays()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/barangays.json";
        return $this->fetchJson($url);
    }
}