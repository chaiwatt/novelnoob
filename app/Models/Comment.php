<?php

namespace App\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'content'];

    // --- Relationships ---

    /**
     * ความสัมพันธ์: คอมเมนต์นี้ถูกสร้างโดยใคร (Inverse One-to-Many)
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ความสัมพันธ์: คอมเมนต์นี้อยู่บนโพสต์ใด (Inverse One-to-Many)
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
