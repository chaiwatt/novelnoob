<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StoreAffiliateRef
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. ดักจับค่า 'ref' จาก URL
        if ($request->has('ref')) {
            $refCode = $request->query('ref');

            // 2. เก็บค่าไว้ใน Session ภายใต้คีย์ 'affiliate_ref'
            // ค่านี้จะถูกเก็บไว้จนกว่า session จะหมดอายุ หรือถูกลบออกด้วยมือ
            $request->session()->put('affiliate_ref', $refCode);
        }
        
        return $next($request);
    }
}
