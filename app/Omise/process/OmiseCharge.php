<?php

namespace App\Omise\process;

use App\Omise\process\Omise;
use Illuminate\Support\Facades\Http;

class OmiseCharge extends Omise
{
    /**
     * Create a new charge using a source ID
     *
     * @param array $data
     * @return array
     */
    public static function create(array $data)
    {
        static::init();

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(self::$secret_key)
        ])->post(self::$url . '/charges', $data);

        return $response->json();
    }
}
