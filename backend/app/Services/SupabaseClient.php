<?php

namespace App\Services;

use Supabase\Storage\StorageClient;

class SupabaseClient
{
    public static function storage()
    {
        return new StorageClient(
            env('VITE_SUPABASE_URL'),
            env('VITE_SUPABASE_SERVICE_ROLE_KEY') // SERVICE ROLE – NOT the anon key
        );
    }
}
