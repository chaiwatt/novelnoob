<?php

namespace App\Models;

use App\Models\User;
use App\Models\Comment;
use App\Models\PostUseful;
use App\Models\PostReaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
        use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
    ];

    // ความสัมพันธ์กับผู้เขียน
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ความสัมพันธ์กับ Comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // ความสัมพันธ์กับ Reactions - ต้องใช้ .using() เพื่อระบุโมเดล Pivot
    public function reactions()
    {
        return $this->belongsToMany(User::class, 'post_reactions', 'post_id', 'user_id')
                    ->using(PostReaction::class) // <--- FIX: ระบุโมเดล Pivot
                    ->withPivot('reaction_type')
                    ->withTimestamps();
    }

    // ความสัมพันธ์กับ Usefuls - ต้องใช้ .using() เพื่อระบุโมเดล Pivot
    public function usefuls()
    {
        return $this->belongsToMany(User::class, 'post_usefuls', 'post_id', 'user_id')
                    ->using(PostUseful::class) // <--- FIX: ระบุโมเดล Pivot
                    ->withTimestamps();
    }
}
