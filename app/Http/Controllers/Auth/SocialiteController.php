<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * นำผู้ใช้ไปยังหน้า Login/Consent ของ Google
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * จัดการ Callback จาก Google และทำการ Login หรือ Register ผู้ใช้
     *
     * @return \Illuminate\Http\RedirectResponse
     */
public function handleGoogleCallback()
{
    try {
        // 🔹 ดึงข้อมูล User จาก Google
        $googleUser = Socialite::driver('google')->user();

        if (!$googleUser || !$googleUser->getEmail()) {
            throw new \Exception('ไม่สามารถดึงข้อมูลจาก Google ได้');
        }

        // 🔹 ค้นหา User จาก email
        $user = User::where('email', $googleUser->getEmail())->first();

        try {
            if ($user) {
                // 1️⃣ ถ้ามี User อยู่แล้ว -> Login
                Auth::login($user);
            } else {
                // 2️⃣ ถ้ายังไม่มี -> สร้าง User ใหม่
                $name = $googleUser->getName() ?? explode('@', $googleUser->getEmail())[0];

                $user = User::create([
                    'name'       => $name,
                    'email'      => $googleUser->getEmail(),
                    'password'   => Hash::make(Str::uuid()),
                    'credits'    => 100,
                    'type'       => 'writer',
                    'status'     => 1,
                    'affiliate'  => Str::uuid(),
                ]);

                Auth::login($user);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // 🔸 จัดการ error จากฐานข้อมูล (เช่น email ซ้ำ)
            Log::error('Database error while creating user: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'เกิดข้อผิดพลาดในระบบฐานข้อมูล กรุณาลองใหม่อีกครั้ง');
        } catch (\Exception $e) {
            // 🔸 จัดการ error อื่น ๆ ในส่วนสร้างหรือล็อกอิน
            Log::error('Error while processing user login/registration: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'ไม่สามารถเข้าสู่ระบบได้ กรุณาลองใหม่');
        }

        // 🔹 ตรวจสอบสิทธิ์และ Redirect
        if ($user->type === 'admin') {
            return redirect()->route('admin.dashboard.index');
        } elseif ($user->type === 'writer') {
            return redirect()->route('dashboard.index');
        }

        return redirect('/home');

    } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
        // 🔸 จัดการกรณี token หมดอายุ / state mismatch
        \Log::warning('Google OAuth state mismatch: ' . $e->getMessage());
        return redirect()->route('login')->with('error', 'หมดเวลาการยืนยันจาก Google กรุณาลองใหม่');
    } catch (\Exception $e) {
        // 🔸 จัดการ error ทั่วไป (เช่น network, Google API ล่ม ฯลฯ)
        \Log::error('Google Socialite Error: ' . $e->getMessage());
        return redirect()->route('register')->with('error', 'ไม่สามารถลงทะเบียน/เข้าสู่ระบบด้วย Google ได้ กรุณาลองใหม่');
    }
}

}
