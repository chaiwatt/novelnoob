<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForexPriceAlert;
use Illuminate\Support\Facades\Validator;

class ForexPriceAlertController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลทั้งหมดและเรียงตาม pips_away จากมากไปน้อย
        $alerts = ForexPriceAlert::orderBy('pips_away', 'desc')->get();

        // ส่งข้อมูลกลับไปในรูปแบบ JSON
        return response()->json($alerts);
    }

      public function store(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $validator = Validator::make($request->all(), [
            'pair' => 'required|string|max:10',
            'type' => 'required|string|in:BUY,SELL',
            'target_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400); // Bad Request
        }

        // 2. สร้างข้อมูลใหม่
        $alert = ForexPriceAlert::create([
            'pair' => $request->pair,
            'type' => $request->type,
            'target_price' => $request->target_price,
            'pips_away' => 0,  // กำหนดค่าเริ่มต้น
            'reversal' => 0,   // กำหนดค่าเริ่มต้น
        ]);

        // 3. ส่งข้อมูลที่สร้างเสร็จกลับไปพร้อม status 201 Created
        return response()->json($alert, 201);
    }
}
