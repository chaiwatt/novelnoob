<?php

namespace App\Omise\process;

use App\Omise\process\Omise;
use Illuminate\Support\Facades\Http;

class OmiseSource extends Omise
{
    /**
     * Create a new source (e.g., PromptPay QR)
     *
     * @param array $data
     * @return array
     */
    public static function create(array $data)
    {
        self::init();

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(self::$public_key)
        ])->post(self::$url . '/sources', $data);

        return $response->json();
    }
}
