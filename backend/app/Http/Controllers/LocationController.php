<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function regions()
    {
        return Http::get('https://psgc.thecodebit.digital/api/v1/region')
                   ->json();   // returns array of regions
    }

    public function provinces($regionCode)
    {
        return Http::get("https://psgc.thecodebit.digital/api/v1/region/{$regionCode}/provinces")
                   ->json();
    }

    public function cities($provinceCode)
    {
        return Http::get("https://psgc.thecodebit.digital/api/v1/province/{$provinceCode}/cities")
                   ->json();
    }

    public function barangays($cityCode)
    {
        return Http::get("https://psgc.thecodebit.digital/api/v1/city/{$cityCode}/barangays")
                   ->json();
    }
}
