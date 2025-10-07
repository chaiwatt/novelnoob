<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForexPriceAlert;

class ForexPriceAlertController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลทั้งหมดและเรียงตาม pips_away จากมากไปน้อย
        $alerts = ForexPriceAlert::orderBy('pips_away', 'desc')->get();

        // ส่งข้อมูลกลับไปในรูปแบบ JSON
        return response()->json($alerts);
    }
}
