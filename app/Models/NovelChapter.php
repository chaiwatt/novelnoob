<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelChapter extends Model
{
        /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'novel_id',
        'chapter_number',
        'title',
        'summary',
        'content',
        'ending_summary',
        'word_count',
        'status',
    ];

    /**
     * Get the novel that owns the chapter.
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }
}
