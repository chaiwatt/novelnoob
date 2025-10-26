<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NovelController extends Controller
{
    public function create()
    {
        return view('novel.create', ['novel' => null]);
    }

    public function edit(Novel $novel)
    {
        // 1. ตรวจสอบสิทธิ์ (สำคัญมาก!)
        if ($novel->user_id !== Auth::id()) {
            abort(403, 'You do not own this novel.');
        }

        // 2. โหลดข้อมูลบทต่างๆ ที่เกี่ยวข้องกับนิยายนี้
        $novel->load('chapters');

        // 3. ส่ง view 'create' เดิม แต่ "แนบข้อมูลนิยาย" ($novel) ไปด้วย
        return view('novel.create', ['novel' => $novel]);
    }
}
