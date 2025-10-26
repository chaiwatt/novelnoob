<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    public function redirectTo()
    {
        // dd("ooo");
        $user = Auth::user();

        if ($user->type === 'admin') {
            // ถ้าเป็น admin ให้ไปที่ 'admin.dashboard'
            return route('admin.dashboard.index'); 
        } elseif ($user->type === 'writer') {
            // ถ้าเป็น writer ให้ไปที่ 'dashboard.index'
            return route('dashboard.index'); 
        }

        // ค่า default ถ้า type ไม่ใช่ทั้ง 'admin' และ 'writer'
        return '/home'; 
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
