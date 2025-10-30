<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{

   /**
     * Indicates if the IDs are auto-incrementing.
     * Pivot tables typically don't have auto-incrementing IDs.
     *
     * @var bool
     */
    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     * Usually empty for simple pivot tables, but included for completeness.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

}
