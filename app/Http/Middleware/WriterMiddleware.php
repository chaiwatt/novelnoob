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
        // ตรวจสอบว่าผู้ใช้ล็อกอินแล้วและเป็น 'writer' หรือไม่
        if (Auth::check() && Auth::user()->type === 'writer') {
            return $next($request);
        }

        // ถ้าไม่ใช่ writer (หรือไม่ได้ล็อกอิน) ให้ Redirect ไปที่หน้า Home หรือแสดงข้อผิดพลาด
        // *หมายเหตุ: หากคุณมี middleware 'auth' ครอบไว้อยู่แล้ว การตรวจสอบ Auth::check() อาจไม่จำเป็น*
        return redirect('/')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงแดชบอร์ดนี้');
    }
}
