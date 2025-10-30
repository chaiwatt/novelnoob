<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\PostUseful;
use App\Models\PostReaction;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'status',
        'credits',
        'affiliate',
        'pen_name',
        'avatar_url'
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // --- Community Relationships ---

    /**
     * ความสัมพันธ์: ผู้ใช้สร้างได้หลายโพสต์ (One-to-Many)
     */
    // ความสัมพันธ์กับโพสต์ที่ผู้ใช้สร้าง
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // ความสัมพันธ์กับคอมเมนต์ที่ผู้ใช้สร้าง
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // ความสัมพันธ์กับโพสต์ที่ผู้ใช้กด Reaction (ใช้ PostReaction Pivot Model)
    public function reactedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_reactions', 'user_id', 'post_id')
                    ->using(PostReaction::class) // <--- แก้ไข: ระบุโมเดล Pivot
                    ->withPivot('reaction_type')
                    ->withTimestamps();
    }

    // ความสัมพันธ์กับโพสต์ที่ผู้ใช้กด Useful (ใช้ PostUseful Pivot Model)
    public function usefulPosts()
    {
        return $this->belongsToMany(Post::class, 'post_usefuls', 'user_id', 'post_id')
                    ->using(PostUseful::class) // <--- แก้ไข: ระบุโมเดล Pivot
                    ->withTimestamps();
    }

    /**
     * ความสัมพันธ์: ผู้ใช้คนนี้ บล็อก ผู้ใช้อื่นๆ (Many-to-Many)
     * ใช้ UserBlock Pivot Model
     */
    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id')
                ->withTimestamps();
    }
    
    /**
     * *** NEW ***: ความสัมพันธ์: ผู้ใช้คนนี้ ถูกบล็อก โดย ผู้ใช้อื่นๆ (Many-to-Many)
     * ใช้ UserBlock Pivot Model
     */
    public function blockedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocked_id', 'blocker_id')
                ->withTimestamps();
    }
}
