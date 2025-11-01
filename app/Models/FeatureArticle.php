<?php

namespace App\Models;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;

class FeatureArticle extends Model
{
    protected $fillable = [
        'article_id'
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

}
