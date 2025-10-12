<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyFxbookSentiment extends Model
{
     protected $fillable = [
        'symbol',
        'pendingbuy',
        'piptopendingbuy',
        'pendingsell',
        'piptopendingsell',
        'record_time',
        'percentsell',
        'percentbuy',
        'sell_volume',
        'buy_volume'
    ];
}
