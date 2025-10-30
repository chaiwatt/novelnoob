<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Novel Noob</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    
    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <style>
        /* * CSS ที่เหลือไว้เฉพาะส่วนที่จำเป็นต่อ Layout เฉพาะหน้านี้ 
         * และการจัดการ Error/Toggle เท่านั้น 
         */

        /* Base layout for center alignment */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        /* Container styling (using variables from external style.css) */
        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: var(--bg-light);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-family: var(--font-heading);
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .login-header p {
            color: var(--text-secondary);
        }
        
        /* Password Toggle Specific Styles */
        .password-input {
            padding-right: 45px; /* Make space for icon */
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(25%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 0;
            z-index: 10;
        }
        .toggle-password:hover {
            color: var(--text-primary);
        }
        .toggle-password svg {
            width: 20px;
            height: 20px;
        }
        .toggle-password .hide {
            display: none;
        }

        /* Full width button override (assuming .btn is styled externally) */
        .btn {
            width: 100%;
        }
        
        /* Divider and Social Button Styles (as provided in the original code block) */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-secondary);
            margin: 25px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        .btn-google {
            background-color: #FFFFFF;
            color: #333333;
            border: 1px solid #DDDDDD;
        }
        .btn-google:hover {
            background-color: #f7f7f7;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .google-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .signin-link {
            text-align: center;
            margin-top: 25px;
            color: var(--text-secondary);
        }
        .signin-link a {
            color: var(--primary-accent);
            font-weight: bold;
            text-decoration: none;
        }
        .signin-link a:hover {
            text-decoration: underline;
        }
        
        /* ⭐️ CSS สำหรับจัดการ Error (จำเป็นต่อการแสดงผล Validation) ⭐️ */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 0.95rem;
            text-align: center;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .form-input.is-invalid {
            border-color: #dc3545; /* สีแดงสำหรับ input ที่มี error */
        }
        .invalid-feedback {
            display: block; /* ทำให้แสดงผล */
            width: 100%;
            margin-top: 5px;
            font-size: 0.875rem;
            color: #dc3545; /* สีแดงสำหรับข้อความ error */
        }
        /* ⭐️ สิ้นสุด CSS ⭐️ */
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h1>สมัครสมาชิก</h1>
            <p>เริ่มต้นการเดินทางเป็นนักเขียนของคุณ</p>
        </div>
        <!-- ⭐️ การตั้งค่า Form สำหรับ Laravel Register (POST, route, CSRF) ⭐️ -->
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- แสดง Error จาก Session กรณีที่อาจมีข้อความแจ้งเตือนทั่วไป -->
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. Name Field (ชื่อผู้ใช้) -->
            <div class="form-group">
                <label for="name">ชื่อผู้ใช้</label>
                <input type="text" id="name" name="name" 
                       class="form-input @error('name') is-invalid @enderror" 
                       value="{{ old('name') }}" required autofocus>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- 2. Email Field -->
            <div class="form-group">
                <label for="email">อีเมล</label>
                <input type="email" id="email" name="email" 
                       class="form-input @error('email') is-invalid @enderror" 
                       value="{{ old('email') }}" required>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            
            <!-- 3. Password Field -->
            <div class="form-group">
                <label for="password">รหัสผ่าน</label>
                <input type="password" id="password" name="password" 
                       class="form-input password-input @error('password') is-invalid @enderror" 
                       required autocomplete="new-password">
                <button type="button" class="toggle-password" id="toggle-password">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg id="eye-slash-icon" class="hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L6.228 6.228" />
                    </svg>
                </button>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            
            <!-- 4. Confirm Password Field (ใช้ name="password_confirmation" ใน Laravel) -->
            <div class="form-group">
                <label for="password-confirm">ยืนยันรหัสผ่าน</label>
                <input type="password" id="password-confirm" name="password_confirmation" 
                       class="form-input password-input" required autocomplete="new-password">
                <button type="button" class="toggle-password" id="toggle-confirm-password">
                    <svg id="confirm-eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg id="confirm-eye-slash-icon" class="hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L6.228 6.228" />
                    </svg>
                </button>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">สมัครสมาชิก</button>

            <div class="divider">
                <span>หรือ</span>
            </div>

            <a href="{{ route('auth.google') }}"  class="btn btn-google">
                <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span>สมัครสมาชิกด้วย Google</span>
            </a>

            <div class="signin-link">
                มีบัญชีอยู่แล้ว? <a href="{{route('login')}}">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>

    <!-- The script will automatically find and set up the password toggles -->
    <script src="{{asset('assets/js/script.js')}}"></script>

</body>
</html>
