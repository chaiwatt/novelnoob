<?php

namespace App\Omise\process;

class Omise
{
    /**
     * Omise API URL
     * @var string
     */
    static protected $url;

    /**
     * Omise Public Key
     * @var string
     */
    static protected $public_key;

    /**
     * Omise Secret Key
     * @var string
     */
    static protected $secret_key;

    /**
     * Initialize keys and URL from .env
     */
    protected static function init()
    {
        self::$url = env('OMISE_URL', 'https://api.omise.co');
        self::$public_key = env('OMISE_PUBLIC_KEY');
        self::$secret_key = env('OMISE_SECRET_KEY');
    }
}
