<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'rating',
        'content',
    ];

    /**
     * ความสัมพันธ์: รีวิวนี้เป็นของ User คนใดคนหนึ่ง
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
