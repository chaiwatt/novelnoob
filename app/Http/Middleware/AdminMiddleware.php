<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจสอบว่าผู้ใช้ล็อกอินแล้วและเป็น 'admin' หรือไม่
        if (Auth::check() && Auth::user()->type === 'admin') {
            return $next($request);
        }

        // ถ้าไม่ใช่ admin ให้ Redirect ไปที่หน้า dashboard ปกติหรือหน้า Home
        return redirect()->route('dashboard.index')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}
