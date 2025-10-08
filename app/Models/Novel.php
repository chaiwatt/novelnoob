<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Novel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'status',
        'outline_data',
        'title_prompt',
        'character_nationality',
        'setting_prompt',
        'style',
        'act_count',
        'style_guide',
        'genre_rules',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'outline_data' => 'array',
    ];

    /**
     * Get the user that owns the novel.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chapters for the novel.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(NovelChapter::class)->orderBy('chapter_number');
    }
}

