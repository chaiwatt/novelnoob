<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute; // 1. (เพิ่ม) Import Attribute class

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
     * (เพิ่ม) สร้างค่าคงที่สำหรับ map style key
     * เพื่อให้จัดการได้ง่ายในที่เดียว
     */
    public const STYLE_MAP = [
        'style_detective' => 'แนวสืบสวนสอบสวน',
        'style_erotic'    => 'แนวอิโรติก',
        'style_romance'   => 'แนวโรแมนติก',
        'style_sci-fi'    => 'แนววิทยาศาสตร์',
        'style_drama'    => 'แนวดราม่า',
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

    /**
     * 2. (เพิ่ม) Accessor ใหม่สำหรับดึงชื่อ Style ที่เป็นภาษาไทย
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function styleName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => 
                static::STYLE_MAP[$attributes['style']] ?? 'ไม่ระบุแนว'
        );
    }

        /**
     * (เพิ่ม) Accessor ใหม่สำหรับตรวจสอบว่านิยายเสร็จสมบูรณ์หรือไม่
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isFinished(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Logic: ต้องมีอย่างน้อย 1 บท และทุกบทต้อง 'completed'
                // หมายเหตุ: ส่วนนี้ทำงานได้ดีที่สุดเมื่อ 'chapters' ถูก eager load
                // ใน Controller (เช่น ->with('chapters'))

                $totalChapters = $this->chapters->count();

                if ($totalChapters === 0) {
                    return false; // นิยายที่ไม่มีบทเลย = ยังไม่จบ
                }

                // นับจำนวนบทที่เสร็จแล้ว
                $completedChapters = $this->chapters->where('status', 'completed')->count();

                // คืนค่า true ถ้าจำนวนบททั้งหมด == จำนวนบทที่เสร็จแล้ว
                return $totalChapters === $completedChapters;
            }
        );
    }
}
