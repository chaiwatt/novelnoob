<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แดชบอร์ด | เขียนนิยายด้วย AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    
    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">

    <!-- Page-specific styles -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        .dashboard-layout {
            display: flex;
            width: 100%;
        }

        /* --- Sidebar Navigation --- */
        .sidebar {
            width: 260px;
            background-color: var(--bg-nav);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 20px;
            transition: transform 0.3s ease, width 0.3s ease;
            flex-shrink: 0;
        }
        
        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .sidebar-nav {
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
        }

        .nav-item.active {
            background-color: var(--primary-accent);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 93, 211, 0.3);
        }
        
        .nav-item svg {
            width: 22px;
            height: 22px;
            margin-right: 15px;
        }

        .sidebar-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }
        .user-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .user-profile .username {
            font-weight: bold;
        }
        .user-profile .email {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            padding: 30px 40px;
            overflow-y: auto;
            min-width: 0;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .main-header h1 {
            font-family: var(--font-heading);
            font-size: 2.2rem;
        }
        
        .content-wrapper .page { display: none; }
        .content-wrapper .page.active { display: block; animation: fadeIn 0.5s ease-in-out; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .section-subtitle {
            color: var(--text-secondary);
            margin-bottom: 20px;
            max-width: 800px;
            line-height: 1.7;
        }

        /* --- Dashboard Home Page --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: var(--bg-light);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            background-color: var(--secondary-accent);
        }
        .stat-icon svg { width: 24px; height: 24px; }
        .stat-info .value { font-size: 1.8rem; font-weight: bold; }
        .stat-info .label { font-size: 1rem; color: var(--text-secondary); }

        /* --- Ebook Library Page --- */
        .ebook-list { display: flex; flex-direction: column; gap: 15px; }
        .ebook-item {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            background-color: var(--bg-dark);
            padding: 15px 20px;
            border-radius: 12px;
            border-left: 5px solid var(--status-completed);
        }
        .ebook-details h4 { font-family: var(--font-heading); font-size: 1.2rem; }
        .ebook-details p { color: var(--text-secondary); font-size: 0.9rem; }
        .ebook-actions { display: flex; gap: 10px; }
        .ebook-actions .btn { font-size: 0.9rem; padding: 8px 15px; }

        /* --- Billing Page --- */
        .credit-balance { text-align: center; margin-bottom: 30px; }
        .credit-balance .balance { font-size: 3.5rem; font-weight: bold; color: var(--primary-accent); }
        .credit-balance p { color: var(--text-secondary); }
        .credit-packages {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .credit-packages button {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .package-card {
            background-color: var(--bg-dark);
            padding: 20px; border-radius: 12px; text-align: center;
            border: 1px solid var(--border-color);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .package-card .credits { font-size: 2rem; font-weight: bold; }
        .package-card .price { font-size: 1.5rem; color: var(--status-completed); margin: 10px 0; }
        .package-card .btn { width: 100%; }
        
        .ribbon {
            position: absolute;
            right: -5px; top: -5px;
            z-index: 1;
            overflow: hidden;
            width: 75px; height: 75px;
            text-align: right;
        }
        .ribbon span {
            font-size: 0.8rem;
            font-weight: bold;
            color: #422006;
            text-align: center;
            line-height: 20px;
            transform: rotate(45deg);
            width: 100px;
            display: block;
            background: linear-gradient(#fde047, #facc15);
            box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 0.8);
            position: absolute;
            top: 19px; right: -21px;
        }
        .ribbon span::before, .ribbon span::after {
            content: "";
            position: absolute;
            z-index: -1;
            border-top: 3px solid #f59e0b;
            border-bottom: 3px solid transparent;
        }
        .ribbon span::before {
            left: 0; top: 100%;
            border-left: 3px solid #f59e0b;
            border-right: 3px solid transparent;
        }
        .ribbon span::after {
            right: 0; top: 100%;
            border-left: 3px solid transparent;
            border-right: 3px solid #f59e0b;
        }

        /* --- Table styles --- */
        .table-wrapper { overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th, .custom-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .custom-table th {
            background-color: var(--bg-dark);
            font-weight: bold;
            color: var(--text-secondary);
        }
        .custom-table tbody tr:hover { background-color: var(--secondary-accent); }
        .status-tag {
            padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;
            font-weight: bold; text-align: center;
        }
        .status-tag.success { background-color: #166534; color: #a7f3d0; }

        /* --- Settings/Review Page --- */
        .form-grid {
            display: grid;
            gap: 20px;
        }
        .form-actions { margin-top: 20px; }

        /* --- Review Page Specific Styles --- */
        .review-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
        .rating-stars {
            display: flex;
            gap: 5px;
            cursor: pointer;
        }
        .rating-stars .star {
            width: 32px;
            height: 32px;
            color: var(--border-color);
            transition: color 0.2s, transform 0.2s;
        }
        .rating-stars .star:hover {
            transform: scale(1.1);
        }
        .rating-stars.rating-hover .star {
            color: var(--warning-color);
        }
        .rating-stars.rating-hover .star:hover ~ .star {
            color: var(--border-color);
        }
        .rating-stars .star.selected {
            color: var(--warning-color);
        }
        #review-text {
            min-height: 120px;
            resize: vertical;
        }
        
        /* --- Testimonial Preview Styles --- */
        .testimonial-card {
            background-color: var(--bg-dark);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            text-align: left;
            position: relative;
            min-height: 250px;
        }
        .testimonial-card::before {
            content: '“';
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 5rem;
            color: var(--border-color);
            font-family: var(--font-heading);
            z-index: 0;
            line-height: 1;
        }
        .testimonial-text {
            position: relative;
            font-size: 1.1rem;
            font-style: italic;
            color: var(--text-secondary);
            margin-bottom: 20px;
            margin-top: 20px; 
            z-index: 1;
        }
        .testimonial-author {
            font-weight: bold;
            color: var(--text-primary);
        }
        .testimonial-author span {
            display: block;
            font-weight: normal;
            font-size: 0.9rem;
            color: var(--primary-accent);
        }
        .testimonial-rating {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .testimonial-rating .stars {
            display: flex;
            color: var(--warning-color);
        }
        .testimonial-rating .stars svg {
            width: 20px;
            height: 20px;
            color: var(--border-color);
        }
         .testimonial-rating .stars svg.filled {
            color: var(--warning-color);
         }
        .testimonial-rating .rating-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* --- Affiliate Page Specific Styles --- */
        .affiliate-link-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .affiliate-link-container .form-input {
            flex-grow: 1;
        }
        .affiliate-link-container .btn {
            white-space: nowrap;
        }
        .affiliate-link-container .btn svg {
            width: 18px;
            height: 18px;
        }
        .credit-gain {
            color: var(--status-completed);
            font-weight: bold;
        }
        
        /* --- Notification Styles --- */
        .notification {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--status-completed);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            font-weight: bold;
            z-index: 1001;
            transition: bottom 0.5s ease-in-out;
        }
        .notification.show {
            bottom: 30px;
        }

        /* --- Responsive & Mobile Sidebar Styles --- */
        .sidebar-toggle-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 0;
        }
        .sidebar-toggle-btn svg {
            width: 28px;
            height: 28px;
        }
        .sidebar-close-btn {
            display: none;
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 2.5rem;
            cursor: pointer;
            line-height: 1;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(18, 24, 40, 0.7);
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* *** MODIFIED: Avatar Styles *** */
        .avatar-preview-group {
            display: flex; /* Use flex for centering */
            flex-direction: column; /* Stack label and input */
            align-items: center; /* Center horizontally */
            gap: 10px; /* Space between preview and text */
            margin-bottom: 20px; /* Space below avatar block */
        }
        .avatar-preview {
            width: 120px; /* Larger size */
            height: 120px;
            border-radius: 50%;
            background-color: var(--bg-dark);
            overflow: hidden;
            border: 3px solid var(--border-color);
            cursor: pointer; /* Make it look clickable */
            position: relative; /* For overlay */
            transition: box-shadow 0.3s ease;
        }
         .avatar-preview:hover {
             border-color: var(--primary-accent);
             box-shadow: 0 0 15px rgba(108, 93, 211, 0.3);
         }
        .avatar-preview img,
        .avatar-preview svg {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-preview:hover .avatar-upload-overlay {
            opacity: 1;
        }
        .avatar-upload-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(18, 24, 40, 0.6);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none; /* Allow click to pass through to label */
        }
        .avatar-upload-overlay svg {
            width: 32px;
            height: 32px;
        }
         .avatar-upload-overlay span {
             font-size: 0.8rem;
             font-weight: 500;
             margin-top: 5px;
         }

        /* Style to hide the real file input */
        .form-input-hidden {
            display: none;
        }
        /* *** END MODIFIED Styles *** */

        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                height: 100%;
                z-index: 1000;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0,0,0,0.2);
            }
            .main-content {
                padding: 20px;
            }
            .sidebar-toggle-btn {
                display: block;
            }
            .sidebar-close-btn {
                display: block;
            }
            .main-header h1 {
                font-size: 1.8rem;
            }
            .review-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
             <a href="{{url('/')}}" class="logo">NovelNoob</a>
            <button class="sidebar-close-btn" id="sidebar-close-btn">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-item active" data-page="dashboard-home">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>แดชบอร์ด</span>
            </a>
            {{-- <a class="nav-item" data-page="ebook-library">
                 <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-9-5.747h18"></path><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>
                <span>นิยายของฉัน</span>
            </a> --}}
            <a class="nav-item" data-page="billing">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>เครดิต</span>
            </a>
            <a class="nav-item" data-page="settings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0 3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>ตั้งค่าบัญชี</span>
            </a>
            <a class="nav-item" data-page="review">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>
                <span>รีวิว</span>
            </a>
            <a class="nav-item" data-page="affiliate">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                <span>Affiliate</span>
            </a>
            <a class="nav-item" data-page="logout"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" style="margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3v-3m3-12h10a3 3 0 013 3v3"></path>
                </svg>
                <span>ออกจากระบบ</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <span class="username">{{ Auth::user()->name }}</span>
                <span class="email">{{ Auth::user()->email }}</span>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="content-wrapper">

            <div id="dashboard-home" class="page active">
                <header class="main-header">
                    <div class="header-left">
                        <button class="sidebar-toggle-btn" id="sidebar-toggle-btn-home">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>แดชบอร์ด</h1>
                    </div>
                    <a href="{{route('novel.create')}}" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>สร้างนิยาย</span>
                    </a>
                </header>
                
                <section class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: var(--primary-accent);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></div>
                        <div class="stat-info">
                            <div class="value">{{ number_format(Auth::user()->credits ?? 0) }}</div>
                            <div class="label">เครดิตคงเหลือ</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: var(--status-completed);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-9-5.747h18"></path><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg></div>
                        <div class="stat-info">
                            <div class="value">{{ $finishedCount }}</div>
                            <div class="label">เขียนเสร็จ</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: var(--warning-color);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></div>
                        <div class="stat-info">
                            <div class="value">{{ $inProgressCount }}</div>
                            <div class="label">กำลังเขียน</div>
                        </div>
                    </div>
                </section>

                <div class="card">
                    <div class="card-header">
                        <h3>นิยายล่าสุดของคุณ</h3>
                        <a href="#" class="btn btn-secondary" onclick="document.querySelector('.nav-item[data-page=\'ebook-library\']').click()">ดูทั้งหมด</a>
                    </div>
                    <div class="ebook-list">
                        @forelse ($novels as $novel) <div class="ebook-item">
                            <div class="ebook-details">
                                <h4>{{ $novel->title }}</h4>
                                <p>
                                    {{ $novel->styleName }} • 
                                    สร้างเมื่อ: {{ $novel->created_at->locale('th')->isoFormat('D MMMM Y') }}
                                </p>
                            </div>
                            <div class="ebook-actions">
                                @if (!$novel->isFinished)
                                    <a href="{{ route('novel.edit', $novel) }}" class="btn btn-secondary">เขียนต่อ</a>
                                @endif
                                {{-- <a href="#" class="btn btn-secondary">จัดการนิยาย</a> --}}
                                <button class="btn btn-secondary download-novel-btn" data-novel-id="{{ $novel->id }}" data-novel-title="{{ $novel->title }}">
                                    <span>ดาวน์โหลด</span>
                                    <div class="loader" style="display: none; width: 16px; height: 16px; border-width: 2px; margin-left: 5px; border-color: currentColor; border-right-color: transparent;"></div>
                                </button>
                            </div>
                        </div>
                        @empty
                            <div style="padding: 20px; text-align: center; color: var(--text-secondary);">
                                <p>คุณยังไม่มีนิยาย</p>
                                <a href="{{route('novel.create')}}" class="btn btn-primary" style="margin-top: 10px;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    <span>เริ่มสร้างเรื่องแรกของคุณ</span>
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- <div id="ebook-library" class="page">
                <header class="main-header">
                    <div class="header-left">
                         <button class="sidebar-toggle-btn" id="sidebar-toggle-btn-library">
                             <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                         </button>
                        <h1>นิยายของฉัน</h1>
                    </div>
                </header>
                <div class="card">
                     <div class="ebook-list" id="full-ebook-list">
                         </div>
                </div>
            </div> --}}

            <div id="billing" class="page">
                <header class="main-header">
                     <div class="header-left">
                         <button class="sidebar-toggle-btn" id="sidebar-toggle-btn-billing">
                             <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                         </button>
                        <h1>เครดิต</h1>
                    </div>
                </header>
                <div class="card">
                    <div class="credit-balance">
                        <p>เครดิตคงเหลือ</p>
                        <!-- 💡 คุณต้องส่งตัวแปร $user_credits มาจาก Controller เพื่อแสดงยอดคงเหลือจริง -->
                        <div class="balance" id="credit-balance-display">{{ number_format(Auth::user()->credits ?? 0) }}</div>
                    </div>
                    <div class="card-header" style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                        <h3>ซื้อเครดิตเพิ่ม</h3>
                    </div>
                    <div class="credit-packages">
                        
                        <!-- 1. เริ่ม Loop $packages -->
                        @forelse ($packages as $package)
                            <div class="package-card">
                                
                                <!-- 2. ตรวจสอบว่าต้องแสดงริบบิ้น "คุ้มค่า" หรือไม่ -->
                                @if ($package->is_highlighted)
                                    <div class="ribbon"><span>คุ้มค่า</span></div>
                                @endif
                                
                                <!-- 3. แสดงข้อมูลจาก $package -->
                                <div class="credits">{{ number_format($package->credits) }} เครดิต</div>
                                <p class="price">{{ number_format($package->price) }} บาท</p>
                                

                                 {{-- <button type="button" class="btn btn-primary purchase-btn" data-package-id="{{ $package->id }}">
                                     <span>ซื้อแพ็กเกจ</span>
                                     <div class="loader" style="display: none; width: 16px; height: 16px; border-width: 2px;"></div>
                                 </button> --}}

                                 <a href="{{ route('credits.checkout', $package->id) }}" class="btn btn-primary">
                                     <span>ซื้อแพ็กเกจ</span>
                                 </a>

                            </div>
                        @empty
                            <p style="color: var(--text-secondary); text-align: center; width: 100%;">
                                ขออภัย, ขณะนี้ไม่มีแพ็กเกจเครดิตให้บริการ
                            </p>
                        @endforelse
                        <!-- จบ Loop -->
                        
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>ประวัติการทำรายการ</h3>
                    </div>
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>รายการ</th>
                                    <th>จำนวนเครดิต</th>
                                    <th>จำนวนเงิน</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->locale('th')->isoFormat('D MMM YYYY') }}</td>
                                        <td>
                                            ซื้อแพ็กเกจ
                                            {{-- Assuming you eager loaded creditPackage --}}
                                            @if($transaction->creditPackage)
                                                {{ number_format($transaction->creditPackage->credits) }} เครดิต
                                            @else
                                                (แพ็กเกจถูกลบ)
                                            @endif
                                        </td>
                                        <td>{{ number_format($transaction->credits_added) }}</td>
                                        <td>{{ number_format($transaction->amount_paid, 2) }} บาท</td>
                                        <td>
                                            {{-- Map status to Thai words and CSS classes --}}
                                            @php
                                                $statusClass = 'default'; // Default class
                                                $statusText = $transaction->status; // Default text is the status itself
                                                if ($transaction->status === 'completed') {
                                                    $statusClass = 'success';
                                                    $statusText = 'สำเร็จ';
                                                } elseif ($transaction->status === 'pending') {
                                                    $statusClass = 'warning';
                                                    $statusText = 'รอตรวจสอบ';
                                                } elseif ($transaction->status === 'failed') {
                                                    $statusClass = 'danger';
                                                    $statusText = 'ล้มเหลว';
                                                }
                                            @endphp
                                            <span class="status-tag {{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--text-secondary);">
                                            ยังไม่มีประวัติการทำรายการ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="settings" class="page">
                <header class="main-header">
                    <div class="header-left">
                        <button class="sidebar-toggle-btn" id="sidebar-toggle-btn-settings">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>ตั้งค่าบัญชี</h1>
                    </div>
                </header>
                
                {{-- *** MODIFIED: Fallback to placehold.co *** --}}
                @php
                    // Use plain PHP function to avoid class resolution issues in cached views
                    if (!function_exists('getDashboardUserAvatar')) {
                        function getDashboardUserAvatar() {
                            $user = Auth::user();
                            if ($user->avatar_url) {
                                // 1. ถ้ามี avatar_url (อัปโหลดเอง)
                                return asset('storage/' . $user->avatar_url);
                            } else {
                                // 2. *** FALLBACK: กลับไปใช้ placehold.co (ตัวอักษรย่อ) ***
                                $name = $user->pen_name ?: $user->name;
                                $initial = mb_substr($name, 0, 1) ?: 'U';
                                return "https://placehold.co/120x120/A9B4D9/121828?text=" . urlencode(strtoupper($initial));
                            }
                        }
                    }
                    $avatar_url = getDashboardUserAvatar();
                @endphp

                <!-- ฟอร์มข้อมูลส่วนตัว -->
                <div class="card">
                    <div class="card-header">
                        <h3>ข้อมูลส่วนตัว</h3>
                    </div>
                    {{-- *** MODIFIED: Added enctype for file uploads *** --}}
                    <form class="form-grid" method="POST" action="{{ route('dashboard.settings.update.profile') }}" enctype="multipart/form-data">
                        @csrf
                        
                        @if (session('profile_success'))
                            <div class="alert alert-success">{{ session('profile_success') }}</div>
                        @endif
                        @if (session('profile_error'))
                            <div class="alert alert-danger">{{ session('profile_error') }}</div>
                        @endif

                        <!-- *** MODIFIED: Avatar Upload Field *** -->
                        <div class="form-group avatar-preview-group">
                            <label for="avatar_file" class="avatar-preview" id="avatar-preview-label" title="เปลี่ยนรูปโปรไฟล์">
                                {{-- แสดง Avatar ปัจจุบัน (อัปโหลด หรือ Placehold) --}}
                                <img src="{{ $avatar_url }}" alt="Avatar ปัจจุบัน" id="avatar-preview-img">
                                <div class="avatar-upload-overlay">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>เปลี่ยนรูป</span>
                                </div>
                            </label>
                            {{-- ซ่อน Input file จริง --}}
                            <input type="file" id="avatar_file" name="avatar_file" class="form-input-hidden @error('avatar_file') is-invalid @enderror" accept="image/png, image/jpeg, image/jpg">
                            
                            {{-- แสดงข้อความ Error (ถ้ามี) --}}
                            @error('avatar_file')
                                <span class="invalid-feedback" style="text-align: center; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- *** END MODIFIED Field *** -->


                        <div class="form-group">
                            <label for="name">ชื่อผู้ใช้</label>
                            <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name', Auth::user()->name) }}">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="pen_name">นามปากกา</label>
                            <input type="text" id="pen_name" name="pen_name" class="form-input @error('pen_name') is-invalid @enderror" placeholder="กรอกนามปากกาของคุณ" value="{{ old('pen_name', Auth::user()->pen_name) }}">
                            @error('pen_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">อีเมล</label>
                            <input type="email" id="email" name="email" class="form-input" value="{{Auth::user()->email}}" disabled>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                        </div>
                    </form>
                </div>

                <!-- ฟอร์มเปลี่ยนรหัสผ่าน -->
                <div class="card">
                    <div class="card-header">
                        <h3>เปลี่ยนรหัสผ่าน</h3>
                    </div>
                    <form class="form-grid" method="POST" action="{{ route('dashboard.settings.update.password') }}">
                        @csrf
                        
                        @if (session('password_success'))
                            <div class="alert alert-success">{{ session('password_success') }}</div>
                        @endif
                        @if (session('password_error'))
                            <div class="alert alert-danger">{{ session('password_error') }}</div>
                        @endif

                        <div class="form-group">
                            <label for="current_password">รหัสผ่านปัจจุบัน</label>
                            <input type="password" name="current_password" id="current_password" class="form-input @error('current_password') is-invalid @enderror">
                            @error('current_password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">รหัสผ่านใหม่</label>
                            <input type="password" name="password" id="password" class="form-input @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">เปลี่ยนรหัสผ่าน</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Review Page -->
            <div id="review" class="page">
                <header class="main-header">
                    <div class="header-left">
                        <button class="sidebar-toggle-btn" id="sidebar-toggle-btn-review">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>รีวิวและข้อเสนอแนะ</h1>
                    </div>
                </header>
                <div class="card">
                    <div class="review-layout">
                        <div class="review-form-container">
                            <div class="card-header" style="padding-left: 0; padding-right: 0;">
                                <h3>ส่งความคิดเห็นของคุณ</h3>
                            </div>
                            <form class="form-grid" method="POST" action="{{ route('dashboard.review.submit') }}">
                                @csrf
                                @if (session('review_success'))
                                    <div class="alert alert-success">{{ session('review_success') }}</div>
                                @endif
                                @if (session('review_error'))
                                    <div class="alert alert-danger">{{ session('review_error') }}</div>
                                @endif
                                <div class="form-group">
                                    <label>ให้คะแนนความพึงพอใจ</label>
                                    <div class="rating-stars" id="rating-stars">
                                        <input type="hidden" name="rating" id="rating-value" value="{{ old('rating', $userReview->rating ?? 0) }}">
                                        <svg class="star" data-value="1" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>
                                        <svg class="star" data-value="2" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>
                                        <svg class="star" data-value="3" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>
                                        <svg class="star" data-value="4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>
                                        <svg class="star" data-value="5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>
                                    </div>
                                    @error('rating')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="review-text">ข้อความรีวิว</label>
                                    <textarea id="review-text" class="form-input" name="content" rows="5" placeholder="ตอนแรกก็กลัวว่าจะใช้ยาก แต่ Novel Noob ใช้ง่ายกว่าที่คิดมากค่ะ AI ช่วยวางโครงเรื่องและเขียนร่างแรกได้ดี ทำให้การเขียนนิยายเรื่องแรกของฉันไม่สะดุดเลย">{{ old('content', $userReview->content ?? '') }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">ส่งรีวิว</button>
                                </div>
                            </form>
                        </div>
                        <div class="review-preview-container">
                            <div class="card-header" style="border: none;">
                                <h3>ตัวอย่างรีวิว</h3>
                            </div>
                            <div class="testimonial-card">
                                <p class="testimonial-text" id="preview-text">{{ $userReview->content ?? 'ตอนแรกก็กลัวว่าจะใช้ยาก แต่ Novel Noob ใช้ง่ายกว่าที่คิดมากค่ะ AI ช่วยวางโครงเรื่องและเขียนร่างแรกได้ดี ทำให้การเขียนนิยายเรื่องแรกของฉันไม่สะดุดเลย' }}</p>
                                <p class="testimonial-author" id="preview-author">นามปากกา: <span>{{Auth::user()->pen_name}}</span></p>
                                <div class="testimonial-rating">
                                    <div class="stars" id="preview-stars">
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                    </div>
                                    <span class="rating-text" id="preview-rating-text">{{ $userReview->rating ?? 0 }}/5</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    
            <!-- Affiliate Page -->
            <div id="affiliate" class="page">
                <header class="main-header">
                    <div class="header-left">
                        <button class="sidebar-toggle-btn" id="sidebar-toggle-btn-affiliate">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>Affiliate Program</h1>
                    </div>
                </header>
                <div class="card">
                    <div class="card-header">
                        <h3>ลิงก์ Affiliate ของคุณ</h3>
                    </div>
                    <p class="section-subtitle" style="text-align: left; margin: -10px 0 20px 0;">
                        แชร์ลิงก์นี้เพื่อเชิญเพื่อนมาใช้งาน Novel Noob และรับเครดิต 20% จากทุกยอดการสั่งซื้อของผู้ที่คุณแนะนำ
                    </p>
                    <div class="affiliate-link-container">
                        <input type="text" id="affiliate-link" class="form-input" value="{{url('')}}/?ref={{Auth::user()->affiliate}}" readonly>
                        <button id="copy-affiliate-link-btn" class="btn btn-primary">
                             <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span>คัดลอกลิงก์</span>
                        </button>
                    </div>
                </div>

                <div class="stats-grid">
                     <div class="stat-card">
                        <div class="stat-icon" style="color: var(--primary-accent);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                        <div class="stat-info">
                            <div class="value">{{ $totalReferred }}</div>
                            <div class="label">จำนวนผู้สมัครผ่านลิงก์</div>
                        </div>
                    </div>
                     <div class="stat-card">
                        <div class="stat-icon" style="color: var(--status-completed);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 12v.01M12 12v-1m0 1H9.598M12 12h2.402M12 16v.01M12 16v-1m0 1H9.598M12 16h2.402M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <div class="stat-info">
                            <div class="value">{{ number_format($totalCreditsEarned) }}</div>
                            <div class="label">เครดิตที่ได้รับทั้งหมด</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>ประวัติการรับเครดิต</h3>
                    </div>
                     <div class="table-wrapper">
                         <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>ผู้ใช้งานที่แนะนำ</th>
                                    <th>ยอดซื้อ (บาท)</th>
                                    <th>เครดิตที่คุณได้รับ (20%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($affiliateTransactions as $affTx)
                                  
                                    @php
                                    // dd($affTx);
                                        $packagePrice = optional($affTx->package)->price ?? 0;
                                        $packageCredits = optional($affTx->package)->credits ?? 0;
                                        $earnedCredits = $packageCredits * 0.20;
                                        // เนื่องจาก migration ล่าสุดไม่ได้เก็บ referred_user_id เราอาจต้องหา user จาก transaction หลัก
                                        // แต่ถ้าไม่มีข้อมูลดังกล่าว เราจะแสดงเป็น 'ผู้ใช้งานใหม่' หรือใช้ข้อมูลที่มี
                                        $referredUserEmail = $affTx->referrer_masked_email; // เนื่องจาก migration ไม่ได้เก็บข้อมูลนี้
                                        
                                        // ถ้าคุณได้เพิ่ม referred_user_id ใน migration ก่อนหน้านี้, คุณสามารถใช้ $affTx->referredUser->email
                                    @endphp
                                    <tr>
                                        <td>{{ $affTx->created_at->format('d M Y') }}</td>
                                        {{-- หากไม่มี referred_user_id ใน migration ล่าสุด เราต้องแสดงเป็นค่าทั่วไป --}}
                                        <td>{{ $referredUserEmail }}</td> 
                                        <td>{{ number_format($packagePrice) }}</td>
                                        <td><span class="credit-gain">+{{ number_format($earnedCredits) }} เครดิต</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center;">ยังไม่มีรายการรับเครดิต</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<div class="overlay" id="overlay"></div>
<div class="notification" id="notification">ส่งรีวิวของคุณเรียบร้อยแล้ว!</div>

<div id="alert-message" style="position: fixed; top: 20px; right: 20px; z-index: 1050; display: none; padding: 15px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
</div>

<!-- Central Script -->
<script src="{{asset('assets/js/script.js')}}"></script>

<!-- Page-specific script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const creditBalanceDisplay = document.getElementById('credit-balance-display');
        const alertMessageDiv = document.getElementById('alert-message');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const navItems = document.querySelectorAll('.nav-item');
        const pages = document.querySelectorAll('.page');

        // --- Responsive Sidebar Logic ---
        const sidebar = document.querySelector('.sidebar');
        const toggleButtons = document.querySelectorAll('.sidebar-toggle-btn');
        const closeBtn = document.getElementById('sidebar-close-btn');
        const overlay = document.getElementById('overlay');

        const openSidebar = () => {
            sidebar.classList.add('open');
            overlay.classList.add('active');
        };
    
        const closeSidebar = () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        };

        toggleButtons.forEach(btn => btn.addEventListener('click', openSidebar));
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);



        document.querySelectorAll('.download-novel-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const btn = e.currentTarget;
                const novelId = btn.dataset.novelId;
                const novelTitle = btn.dataset.novelTitle || 'novel';
                const loader = btn.querySelector('.loader');
                const span = btn.querySelector('span');


                btn.disabled = true;
                loader.style.display = 'inline-block';
                span.style.display = 'none';

                try {
                    const response = await fetch(`/novel/${novelId}/download`, {
                        headers: {
                            'Accept': 'text/plain',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        const errorData = await response.json(); // สมมติว่า server ส่ง JSON error
                        throw new Error(errorData.error || `Error ${response.status}`);
                    }

                    // ดึงเนื้อหา Text จาก response
                    const textContent = await response.text();
                    
                    // สร้าง Blob
                    const blob = new Blob([textContent], { type: 'text/plain;charset=utf-8' });

                    // สร้าง Link ชั่วคราวเพื่อดาวน์โหลด
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = `${novelTitle.replace(/ /g, '_')}.txt`; // สร้างชื่อไฟล์
                    document.body.appendChild(a);
                    a.click();
                    
                    // ลบ Link ชั่วคราว
                    document.body.removeChild(a);
                    URL.revokeObjectURL(a.href);

                } catch (error) {
                    console.error('Download failed:', error);
                    alert('ดาวน์โหลดไม่สำเร็จ: ' + error.message);
                } finally {
                    // คืนค่าปุ่มกลับเหมือนเดิม
                    btn.disabled = false;
                    loader.style.display = 'none';
                    span.style.display = 'inline';
                }
            });
        });


    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const pageId = item.getAttribute('data-page');
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            pages.forEach(page => page.classList.remove('active'));
            document.getElementById(pageId).classList.add('active');
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });
    
    const ratingStarsContainer = document.getElementById('rating-stars');
    const stars = document.querySelectorAll('.rating-stars .star');
    const ratingValueInput = document.getElementById('rating-value');
    const reviewForm = document.getElementById('review-form');
    const notification = document.getElementById('notification');
    const reviewTextArea = document.getElementById('review-text');
    const penNameInput = document.getElementById('pen_name');
    const previewText = document.getElementById('preview-text');
    const previewAuthor = document.getElementById('preview-author');
    const previewStars = document.querySelectorAll('#preview-stars svg');
    const previewRatingText = document.getElementById('preview-rating-text');
    // สมมติว่า `.user-profile .username` มีอยู่เพื่อดึงชื่อผู้ใช้
    const defaultUsername = document.querySelector('.user-profile .username') ? document.querySelector('.user-profile .username').textContent : '';

    function updatePreview() {
        // ใช้ค่าจาก textarea หรือ placeholder หากว่างเปล่า
        previewText.textContent = reviewTextArea.value || reviewTextArea.placeholder;
        // penNameInput ไม่ได้อยู่ในโค้ด Blade ที่ให้มา แต่ถ้ามีจะใช้ได้
        const penName = penNameInput ? penNameInput.value.trim() : ''; 
        // อัปเดตผู้เขียนในตัวอย่างรีวิว (ใช้ defaultUsername/pen_name)
        previewAuthor.innerHTML = `นามปากกา: ${penName || defaultUsername}<span>นักเขียนนิยายอิสระ</span>`;
        
        const rating = parseInt(ratingValueInput.value) || 0; // ต้องแน่ใจว่าเป็นตัวเลข
        
        // อัปเดตดาวในส่วนแสดงตัวอย่าง (preview)
        previewStars.forEach((star, index) => {
            // ต้องมั่นใจว่า CSS ของคุณกำหนดสไตล์สำหรับ .filled ไว้
            star.classList.toggle('filled', index < rating); 
        });
        
        // อัปเดตคะแนนตัวเลขในส่วนแสดงตัวอย่าง
        previewRatingText.textContent = `${rating}/5`;
    }

    if (ratingStarsContainer) {
        function setRating(value) {
            ratingValueInput.value = value;
            // อัปเดตดาวในส่วนฟอร์ม
            stars.forEach(star => {
                star.classList.toggle('selected', star.dataset.value <= value);
            });
            updatePreview();
        }

        ratingStarsContainer.addEventListener('mouseover', (e) => {
            if(e.target.closest('.star')) {
                ratingStarsContainer.classList.add('rating-hover');
                const hoverValue = e.target.closest('.star').dataset.value;
                stars.forEach(s => s.classList.toggle('selected', s.dataset.value <= hoverValue));
            }
        });

        ratingStarsContainer.addEventListener('mouseout', () => {
            ratingStarsContainer.classList.remove('rating-hover');
            setRating(ratingValueInput.value);
        });

        ratingStarsContainer.addEventListener('click', (e) => {
            if(e.target.closest('.star')) {
                setRating(e.target.closest('.star').dataset.value);
            }
        });

        // **จุดสำคัญ: เรียก setRating เมื่อโหลดหน้าเพื่อแสดงผลดาวเริ่มต้นจาก $userReview**
        const initialRating = parseInt(ratingValueInput.value);
        if (initialRating > 0) {
            setRating(initialRating);
        }
    }

    if (reviewTextArea) reviewTextArea.addEventListener('input', updatePreview);
    // if (penNameInput) penNameInput.addEventListener('input', updatePreview); // ถูกปิดไว้เพราะ penNameInput ไม่ได้อยู่ใน Blade

    if (reviewForm) {
        reviewForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // โค้ดนี้ถูกแก้ไขเพื่อให้จำลองการส่งรีวิวสำเร็จ
            notification.textContent = 'ส่งรีวิวของคุณเรียบร้อยแล้ว!';
            notification.style.backgroundColor = 'var(--status-completed)';
            notification.classList.add('show');
            setTimeout(() => notification.classList.remove('show'), 3000);
            reviewForm.reset();
            setRating(0);
            updatePreview();
        });
    }

    // --- Affiliate Page Logic (เหมือนเดิม) ---
    const copyBtn = document.getElementById('copy-affiliate-link-btn');
    const affiliateLinkInput = document.getElementById('affiliate-link');

    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            affiliateLinkInput.select();
            document.execCommand('copy');
            notification.textContent = 'คัดลอกลิงก์เรียบร้อยแล้ว!';
            notification.style.backgroundColor = 'var(--primary-accent)';
            notification.classList.add('show');
            setTimeout(() => notification.classList.remove('show'), 2000);
        });
    }

    // *** NEW: Avatar Preview Logic ***
    const avatarFileInput = document.getElementById('avatar_file');
    const avatarPreviewImg = document.getElementById('avatar-preview-img');

    if (avatarFileInput && avatarPreviewImg) {
        avatarFileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // ตรวจสอบว่าเป็นไฟล์รูปภาพหรือไม่
                if (!file.type.startsWith('image/')) {
                    // (Optional) Show error modal
                    // showModal('error-modal', 'ไฟล์ไม่ถูกต้อง', 'กรุณาเลือกไฟล์รูปภาพ .jpg หรือ .png เท่านั้น');
                    alert('กรุณาเลือกไฟล์รูปภาพ .jpg หรือ .png เท่านั้น');
                    // Clear the input
                    avatarFileInput.value = "";
                    return;
                }
                
                // สร้าง URL ชั่วคราวสำหรับไฟล์ที่เลือก
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    // ตั้งค่า src ของ img tag ให้เป็น URL ชั่วคราวเพื่อแสดงผล
                    avatarPreviewImg.src = e.target.result;
                };
                
                // อ่านไฟล์เป็น Data URL
                reader.readAsDataURL(file);
            }
        });
    }
    // *** END NEW Logic ***


    // **5. เรียก updatePreview() ครั้งสุดท้าย**
    // (ถ้า initialRating > 0 มันจะถูกเรียกใน setRating() ไปแล้ว แต่ถ้าเป็น 0 ต้องเรียกเพื่อให้ content/text preview อัปเดต)
    if (!ratingStarsContainer || parseInt(ratingValueInput.value) === 0) {
        updatePreview();
    }
});
</script>
</body>
</html>

