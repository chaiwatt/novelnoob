<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; // <--- FIX: ต้องใช้คลาส Pivot

class PostUseful extends Pivot // <--- FIX: สืบทอดจาก Pivot
{
    protected $table = 'post_usefuls';

    // ปิดการเพิ่มอัตโนมัติของคอลัมน์ ID
    public $incrementing = false;
    
    // กำหนดคีย์หลักเป็นคีย์ผสม
    protected $primaryKey = ['user_id', 'post_id'];
    
    protected $fillable = [
        'user_id', 
        'post_id'
    ];
}