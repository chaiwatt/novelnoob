<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WriterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจสอบว่าผู้ใช้ล็อกอิน, เป็น 'writer', และมี status เป็น 1 (Active)
        if (Auth::check() &&
            Auth::user()->type === 'writer' &&
            Auth::user()->status == 1) { // ⭐️ (เพิ่มเงื่อนไขนี้)
            
            return $next($request);
        }

        // --- (แนะนำ) เพิ่มการตรวจสอบสำหรับ Writer ที่ถูกแบน (status != 1) ---
        // ถ้าเป็น Writer แต่ status ไม่ใช่ 1 (เช่น โดนแบนเป็น 2)
        // ให้ Logout ออกจากระบบและแจ้งเตือน
        if (Auth::check() && Auth::user()->type === 'writer' && Auth::user()->status != 1) {
            Auth::logout();
            return redirect('/login')->with('error', 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแล');
        }

        // ถ้าไม่ใช่ writer (หรือไม่ได้ล็อกอิน) ให้ Redirect ไปที่หน้า Home
        return redirect('/')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงแดชบอร์ดนี้');
    }
}
