<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForexPriceAlert extends Model
{
    protected $fillable = [
        'pair',
        'type',
        'target_price',
        'pips_away',
        'reversal'
    ];
}
