<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; // <--- FIX: ต้องใช้คลาส Pivot

class PostReaction extends Pivot // <--- FIX: สืบทอดจาก Pivot
{
    // กำหนดชื่อตารางที่ชัดเจน
    protected $table = 'post_reactions';

    // ปิดการเพิ่มอัตโนมัติของคอลัมน์ ID (เพราะใช้ Composite Key)
    public $incrementing = false;
    
    // กำหนดคีย์หลักเป็นคีย์ผสม (user_id, post_id)
    protected $primaryKey = ['user_id', 'post_id'];
    
    protected $fillable = [
        'user_id', 
        'post_id', 
        'reaction_type'
    ];
}