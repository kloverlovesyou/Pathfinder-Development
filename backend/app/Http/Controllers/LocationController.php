<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function regions()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/regions.json";

        $res = Http::get($url);

        return $res->json();
    }

    public function provinces()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/provinces.json";

        $res = Http::get($url);

        return $res->json();
    }

    public function cities()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/cities.json";

        $res = Http::get($url);

        return $res->json();
    }

    public function barangays()
    {
        $url = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master/barangays.json";

        $res = Http::get($url);

        return $res->json();
    }
}