<?php

namespace App\Models;

use App\Models\FeatureArticle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Article extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'meta_description',
        'json_ld_description',
        'body',
        'tags',
        'public_date',
    ];

    protected $casts = [
        'tags' => 'array',// บอกให้ Laravel แปลงคอลัมน์ tags จาก JSON เป็น Array โดยอัตโนมัติ
        'public_date' => 'datetime', // บอกให้แปลงเป็น Carbon date object
    ];

    protected function thaiDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->public_date
                ? $this->public_date->addYears(543)->locale('th')->translatedFormat('j F Y')
                : null,
        );
    }

    public function featureArticle()
    {
        return $this->hasOne(FeatureArticle::class);
    }
}