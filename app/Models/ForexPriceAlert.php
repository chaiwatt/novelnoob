<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForexPriceAlert extends Model
{
    protected $fillable = [
        'pair',
        'close_price',
        'is_alert',
        'type',
        'target_price',
        'pips_away',
        'reversal',
        'pending_price'
    ];
}
