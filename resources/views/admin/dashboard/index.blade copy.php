<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Novel Noob</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #121828;
            --bg-light: #1B233F;
            --bg-nav: #161d31;
            --primary-accent: #6C5DD3;
            --primary-hover: #8375e7;
            --secondary-accent: #3A3D68;
            --secondary-hover: #525692;
            --text-primary: #F0F2F5;
            --text-secondary: #A9B4D9;
            --border-color: #2a335e;
            --status-completed: #34D399;
            --status-active: #34D399;
            --status-pending: #facc15;
            --status-banned: #f87171;
            --font-ui: 'Noto Sans Thai', sans-serif;
            --font-heading: 'Noto Serif','Noto Serif Thai', serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-ui);
            background-color: var(--bg-dark);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
        }

        .dashboard-layout {
            display: flex;
            width: 100%;
        }

        .logo {
            font-family: var(--font-heading) !important;
            color: var(--text-primary);
            font-size: 1.7rem;
            font-weight: 700;
            text-decoration: none;
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
            transition: background-color 0.3s, color 0.3s;
        }

        .nav-item:hover {
            background-color: var(--secondary-accent);
            color: var(--text-primary);
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
            stroke-width: 2;
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
        .user-profile .role {
            font-size: 0.85rem;
            color: var(--primary-accent);
        }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            padding: 30px 40px;
            overflow-y: auto;
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

        /* General Components */
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-family: var(--font-ui);
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-primary {
            background-color: var(--primary-accent); color: white;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        .btn-danger { background-color: var(--status-banned); color: white; }
        .btn-warning { background-color: var(--status-pending); color: #422006; }

        .card {
            background-color: var(--bg-light);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }
        .card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .card-header h3 { font-family: var(--font-heading); font-size: 1.5rem; }

        /* --- Dashboard Home Page --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: var(--bg-light);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .stat-card .label { font-size: 1rem; color: var(--text-secondary); margin-bottom: 10px; }
        .stat-card .value { font-size: 2.2rem; font-weight: bold; }
        .stat-card .change, .change {
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .change.positive { color: var(--status-completed); }
        .change.negative { color: var(--status-banned); }
        
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }
        .form-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            color: var(--text-secondary);
        }
        .form-input {
            width: 100%;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 15px;
            color: var(--text-primary);
            font-family: var(--font-ui);
            font-size: 1rem;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }


        /* --- Table styles --- */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-box {
            position: relative;
        }
        .search-box input {
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 15px 10px 40px;
            color: var(--text-primary);
        }
        .search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-secondary);
        }
        .table-wrapper { overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th, .custom-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        .custom-table th {
            background-color: var(--bg-dark);
            font-weight: bold;
            color: var(--text-secondary);
        }
        .status-tag {
            padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;
            font-weight: bold; text-align: center;
            color: white;
        }
        .status-tag.active { background-color: #166534; color: #a7f3d0; }
        .status-tag.banned { background-color: #991b1b; color: #fecaca; }
        .status-tag.completed { background-color: #166534; color: #a7f3d0; }
        .status-tag.failed { background-color: #991b1b; color: #fecaca; }
        
        .action-buttons { display: flex; gap: 8px; justify-content: flex-end; }

        /* --- Custom Radio Button Styles --- */
        .custom-table input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 22px;
            height: 22px;
            border: 2px solid var(--border-color);
            border-radius: 50%;
            background-color: var(--bg-dark);
            cursor: pointer;
            position: relative;
            outline: none;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .custom-table input[type="radio"]:hover {
            border-color: var(--primary-accent);
        }

        .custom-table input[type="radio"]:checked {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
        }

        .custom-table input[type="radio"]:checked::after {
            content: '';
            display: block;
            width: 10px;
            height: 10px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(18, 24, 40, 0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1001;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
        }
        .modal-overlay.visible {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content {
            background-color: var(--bg-light);
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transform: scale(0.95);
            transition: transform 0.3s ease-in-out;
        }
        .modal-overlay.visible .modal-content {
            transform: scale(1);
        }
        .modal-content h3 {
            font-family: var(--font-heading);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        .modal-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
        }

        /* --- Responsive & Mobile Sidebar Styles --- */
        .sidebar-toggle-btn {
            display: none;
            background: none; border: none;
            color: var(--text-primary); cursor: pointer;
            padding: 0;
        }
        .sidebar-toggle-btn svg { width: 28px; height: 28px; }
        .sidebar-close-btn {
            display: none; position: absolute;
            top: 15px; right: 20px;
            background: none; border: none;
            color: var(--text-secondary);
            font-size: 2.5rem; cursor: pointer;
            line-height: 1;
        }
        .overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(18, 24, 40, 0.7);
            z-index: 999; opacity: 0;
            pointer-events: none; transition: opacity 0.3s ease;
        }
        .overlay.active { opacity: 1; pointer-events: auto; }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed; height: 100%;
                z-index: 1000;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0,0,0,0.2);
            }
            .main-content { padding: 20px; }
            .sidebar-toggle-btn { display: block; }
            .sidebar-close-btn { display: block; }
            .main-header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
             <a href="{{url('')}}" class="logo">NovelNoob</a>
            <button class="sidebar-close-btn" id="sidebar-close-btn">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-item active" data-page="dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M16 8l-6 6-4-4"></path></svg>
                <span>ภาพรวม</span>
            </a>
            <a class="nav-item" data-page="users">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a4 4 0 00-4-4h4a4 4 0 004-4v-1.293a1 1 0 00-1-1h-1a1 1 0 00-1 1V15a4 4 0 00-4 4v1zm0 0a9 9 0 006-6h-4a3 3 0 01-3-3V9a3 3 0 01-3-3H9a3 3 0 01-3 3v4.5"></path></svg>
                <span>จัดการผู้ใช้</span>
            </a>
            <a class="nav-item" data-page="transactions">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>ธุรกรรม</span>
            </a>
            <a class="nav-item" data-page="settings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>ตั้งค่า</span>
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
                <span class="username">Admin</span>
                <span class="role">ผู้ดูแลระบบ</span>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="content-wrapper">

            <!-- Dashboard Page -->
            <div id="dashboard" class="page active">
                <header class="main-header">
                    <div class="header-left">
                        <button class="sidebar-toggle-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>ภาพรวมระบบ</h1>
                    </div>
                </header>
                
                <section class="stats-grid">
                    {{-- Card 1: รายได้ทั้งหมด --}}
                    <div class="stat-card">
                        <div class="label">รายได้ทั้งหมด</div>
                        <div class="value">
                            ฿{{ number_format($kpi_stats['total_revenue'], 0) }}
                        </div>
                        @php
                            $revChange = $kpi_stats['revenue_change'];
                            $revClass = $revChange >= 0 ? 'positive' : 'negative';
                            $revSign = $revChange >= 0 ? '+' : '';
                        @endphp
                        <div class="change {{ $revClass }}">
                            {{ $revSign }}{{ number_format(abs($revChange), 1) }}% จากเดือนที่แล้ว
                        </div>
                    </div>

                    {{-- Card 2: ผู้ใช้ทั้งหมด (Writer) --}}
                    <div class="stat-card">
                        <div class="label">ผู้ใช้ทั้งหมด</div>
                        <div class="value">
                            {{ number_format($kpi_stats['total_users']) }}
                        </div>
                        <div class="change positive">
                            +{{ number_format($kpi_stats['new_users']) }} Users 
                            {{-- หมายเหตุ: ใช้ 'positive' เนื่องจากเป็นการแสดงจำนวนผู้ใช้ใหม่ที่เพิ่มขึ้นใน 30 วัน --}}
                        </div>
                    </div>

                    {{-- Card 3: คำสั่งซื้อ (สำเร็จ) --}}
                    <div class="stat-card">
                        <div class="label">คำสั่งซื้อ</div>
                        <div class="value">
                            {{ number_format($kpi_stats['total_orders']) }}
                        </div>
                        <div class="change positive">
                            +{{ number_format($kpi_stats['new_orders']) }} Orders
                            {{-- หมายเหตุ: ใช้ 'positive' เนื่องจากเป็นการแสดงจำนวนคำสั่งซื้อใหม่ที่เพิ่มขึ้นใน 30 วัน --}}
                        </div>
                    </div>

                    {{-- Card 4: นิยายที่สร้าง --}}
                    <div class="stat-card">
                        <div class="label">นิยายที่สร้าง</div>
                        <div class="value">
                            {{ number_format($kpi_stats['total_novels']) }}
                        </div>
                        <div class="change positive">
                            +{{ number_format($kpi_stats['new_novels']) }} เล่ม
                            {{-- หมายเหตุ: ใช้ 'positive' เนื่องจากเป็นการแสดงจำนวนนิยายที่สร้างใหม่ใน 30 วัน --}}
                        </div>
                    </div>
                </section>

                <div class="card">
                    <div class="card-header" style="flex-wrap: wrap; gap: 20px;">
                        <h3>รายงานประจำเดือน</h3>
                         <div class="form-group" style="margin-bottom:0;">
                            <label for="month-selector">เดือน:</label>
                            <select id="month-selector" class="form-input">
                                @forelse ($monthly_novel_reports as $key => $data)
                                    <option value="{{ $key }}">{{ $data['monthName'] }}</option>
                                @empty
                                    <option value="">ไม่มีข้อมูลรายงาน</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="reports-grid">
                         <div class="card" style="margin-bottom: 0;">
                            <div class="card-header">
                                <h3 id="genre-report-title"></h3>
                            </div>
                            <div class="table-wrapper">
                                 <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>อันดับ</th>
                                            <th>ประเภท</th>
                                            <th>จำนวน</th>
                                        </tr>
                                    </thead>
                                    <tbody id="genre-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                         <div class="card" style="margin-bottom: 0;">
                            <div class="card-header">
                                <h3 id="nationality-report-title"></h3>
                            </div>
                            <div class="table-wrapper">
                                 <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>อันดับ</th>
                                            <th>สัญชาติ</th>
                                            <th>จำนวน</th>
                                        </tr>
                                    </thead>
                                    <tbody id="nationality-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>สถิติรายเดือน</h3>
                    </div>
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>เดือน</th>
                                    <th>รายได้</th>
                                    <th>ลูกค้าทำธุรกรรม</th> <th>นิยายที่สร้าง</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($monthly_stats as $stat)
                                    @php
                                        // สำหรับ รายได้
                                        $revChange = $stat['change']['revenue_change'];
                                        $revClass = $revChange > 0 ? 'positive' : ($revChange < 0 ? 'negative' : '');
                                        $revSign = $revChange > 0 ? '+' : '';
                                        $revDisplay = number_format(abs($revChange), 1) . '%';

                                        // สำหรับ ลูกค้า
                                        $custChange = $stat['change']['customers_change'];
                                        $custClass = $custChange > 0 ? 'positive' : ($custChange < 0 ? 'negative' : '');
                                        $custSign = $custChange > 0 ? '+' : '';
                                        $custDisplay = number_format(abs($custChange));

                                        // สำหรับ นิยาย
                                        $novelChange = $stat['change']['novels_change'];
                                        $novelClass = $novelChange > 0 ? 'positive' : ($novelChange < 0 ? 'negative' : '');
                                        $novelSign = $novelChange > 0 ? '+' : '';
                                        $novelDisplay = number_format(abs($novelChange));
                                    @endphp
                                    <tr>
                                        <td>{{ $stat['month_label'] }}</td>
                                        <td>
                                            ฿{{ number_format($stat['revenue'], 2) }} 
                                            @if ($stat['month_label'] !== $monthly_stats->first()['month_label'])
                                                <span class="change {{ $revClass }}">({{ $revSign }}{{ $revDisplay }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($stat['customers']) }} 
                                            @if ($stat['month_label'] !== $monthly_stats->first()['month_label'])
                                                <span class="change {{ $custClass }}">({{ $custSign }}{{ $custDisplay }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($stat['novels']) }} 
                                            @if ($stat['month_label'] !== $monthly_stats->first()['month_label'])
                                                <span class="change {{ $novelClass }}">({{ $novelSign }}{{ $novelDisplay }})</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #999;">
                                            ยังไม่มีข้อมูลสถิติรายเดือน
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- User Management Page -->
            <div id="users" class="page">
                <header class="main-header">
                    <div class="header-left">
                         <button class="sidebar-toggle-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>จัดการผู้ใช้</h1>
                    </div>
                </header>
                <div class="card">
                     <div class="table-controls">
                        <div class="search-box">
                        
                        <input type="text" id="user-search-input" placeholder="ค้นหาด้วยอีเมล...">
                        
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                     </div>
                     <div id="user-alert-message" class="alert-box" style="display: none; position: relative; margin-bottom: 20px;"></div>
                     <div class="table-wrapper">
                         <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>อีเมล</th>
                                    <th>วันที่สมัคร</th>
                                    <th>สถานะ</th>
                                    <th class="action-buttons">การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                
                                @include('admin.dashboard.partials._users_table_body', ['users' => $users])

                            </tbody>
                        </table>
                     </div>
                     @if($users->hasPages())
                        <div class="pagination-links" style="padding: 1.5rem;">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Transactions Page -->
            <div id="transactions" class="page">
                <header class="main-header">
                    <div class="header-left">
                        <button class="sidebar-toggle-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>ธุรกรรม</h1>
                    </div>
                </header>
                
                <div class="card">
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID ธุรกรรม</th>
                                    <th>ผู้ใช้</th>
                                    <th>วันที่</th>
                                    <th>แพ็กเกจ</th>
                                    <th>จำนวนเงิน</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    @php
                                        // เตรียมข้อมูลสำหรับสถานะและสี
                                        $statusClass = 'default';
                                        $statusText = $transaction->status;
                                        if ($transaction->status === 'completed') {
                                            $statusClass = 'completed';
                                            $statusText = 'สำเร็จ';
                                        } elseif ($transaction->status === 'pending') {
                                            $statusClass = 'pending';
                                            $statusText = 'รอตรวจสอบ';
                                        } elseif ($transaction->status === 'failed') {
                                            $statusClass = 'failed';
                                            $statusText = 'ล้มเหลว';
                                        }

                                        // เตรียมข้อมูลสำหรับแพ็กเกจ
                                        $packageName = 'N/A';
                                        if ($transaction->creditPackage) {
                                            $packageName = number_format($transaction->creditPackage->credits) . ' เครดิต';
                                        }
                                    @endphp
                                    <tr>
                                        {{-- ID ธุรกรรม: ใช้ ID ของ Transaction Model --}}
                                        <td>TXN{{ $transaction->id }}</td> 
                                        
                                        {{-- ผู้ใช้: แสดงอีเมล หรือชื่อของผู้ใช้ --}}
                                        <td>{{ $transaction->user ? $transaction->user->email : 'ผู้ใช้ถูกลบ' }}</td>
                                        
                                        {{-- วันที่: จัดรูปแบบวันที่ --}}
                                        <td>{{ $transaction->created_at->locale('th')->isoFormat('D MMM YYYY') }}</td>
                                        
                                        {{-- แพ็กเกจ: แสดงชื่อ/เครดิตของแพ็กเกจ --}}
                                        <td>{{ $packageName }}</td>
                                        
                                        {{-- จำนวนเงิน: แสดงจำนวนเงินที่จ่าย พร้อมจัดรูปแบบทศนิยมและคอมม่า --}}
                                        <td>{{ number_format($transaction->amount_paid, 2) }} บาท</td>
                                        
                                        {{-- สถานะ: แสดงสถานะพร้อม Tag สี --}}
                                        <td><span class="status-tag {{ $statusClass }}">{{ $statusText }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #999;">
                                            ยังไม่มีประวัติการทำรายการ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- เพิ่มลิงก์ Pagination หากใช้ paginate() ใน Controller --}}
                    @if ($transactions instanceof \Illuminate\Contracts\Pagination\Paginator)
                        <div class="pagination-links">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
            
             <!-- Settings Page -->
            <div id="settings" class="page">
                <header class="main-header">
                     <div class="header-left">
                         <button class="sidebar-toggle-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1>ตั้งค่า</h1>
                    </div>
                </header>
                <div class="card">
                    <div class="card-header">
                        <h3>จัดการแพ็กเกจเครดิต</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.packages.update') }}">
                        @csrf
                        @method('PUT') <!-- ใช้ PUT สำหรับการอัปเดต -->
                        <div class="table-wrapper">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>แพ็กเกจ</th>
                                        <th>จำนวนเครดิต</th>
                                        <th>ราคา (บาท)</th>
                                        <th style="text-align: center;">ไฮไลท์ (คุ้มค่า)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($packages as $package)
                                    <tr>
                                        <td>{{ $package->name }}</td>
                                        <td>
                                            <input type="number" class="form-input" 
                                                name="packages[{{ $package->id }}][credits]" 
                                                value="{{ old('packages.' . $package->id . '.credits', $package->credits) }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-input" 
                                                name="packages[{{ $package->id }}][price]" 
                                                value="{{ old('packages.' . $package->id . '.price', $package->price) }}">
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="radio" name="is_highlighted" 
                                                value="{{ $package->id }}" 
                                                @if(old('is_highlighted', $package->is_highlighted ? $package->id : null) == $package->id) checked @endif>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center;">ไม่พบข้อมูลแพ็กเกจ</td>
                                    </tr>
                                    @endforelse
                                    <!-- จบ Loop -->
                                </tbody>
                            </table>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>



            </div>


        </div>
    </main>
</div>

<div class="overlay" id="overlay"></div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const navItems = document.querySelectorAll('.nav-item');
        const pages = document.querySelectorAll('.page');
        const sidebar = document.querySelector('.sidebar');
        const toggleButtons = document.querySelectorAll('.sidebar-toggle-btn');
        const closeBtn = document.getElementById('sidebar-close-btn');
        const overlay = document.getElementById('overlay');

        const monthlyReportData = @json($monthly_novel_reports);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); // ⭐️ 1. ดึง CSRF Token

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

        // --- Monthly Reports Logic ---

        const monthSelector = document.getElementById('month-selector');
        const genreTableBody = document.getElementById('genre-table-body');
        const nationalityTableBody = document.getElementById('nationality-table-body');
        const genreReportTitle = document.getElementById('genre-report-title');
        const nationalityReportTitle = document.getElementById('nationality-report-title');

        function updateMonthlyReports(monthKey) {
            const data = monthlyReportData[monthKey];
            if (!data) {
                genreReportTitle.textContent = 'ไม่พบข้อมูล';
                nationalityReportTitle.textContent = 'ไม่พบข้อมูล';
                genreTableBody.innerHTML = '<tr><td colspan="3">ไม่มีข้อมูลสำหรับเดือนนี้</td></tr>';
                nationalityTableBody.innerHTML = '<tr><td colspan="3">ไม่มีข้อมูลสำหรับเดือนนี้</td></tr>';
                return;
            }

            genreReportTitle.textContent = `5 ประเภทยอดนิยม (${data.monthName})`;
            nationalityReportTitle.textContent = `5 อันดับสัญชาตินิยาย (${data.monthName})`;

            let genreHtml = '';
            if (data.genres.length > 0) {
                data.genres.forEach(item => {
                    genreHtml += `<tr><td>${item.rank}</td><td>${item.type}</td><td>${item.count}</td></tr>`;
                });
            } else {
                genreHtml = '<tr><td colspan="3">ไม่มีข้อมูล</td></tr>'; // ⭐️ กรณีข้อมูลเป็น 0
            }
            genreTableBody.innerHTML = genreHtml;

            let nationalityHtml = '';
            if (data.nationalities.length > 0) {
                data.nationalities.forEach(item => {
                    nationalityHtml += `<tr><td>${item.rank}</td><td>${item.type}</td><td>${item.count}</td></tr>`;
                });
            } else {
                nationalityHtml = '<tr><td colspan="3">ไม่มีข้อมูล</td></tr>'; // ⭐️ กรณีข้อมูลเป็น 0
            }
            nationalityTableBody.innerHTML = nationalityHtml;
        }

        monthSelector.addEventListener('change', (event) => {
            updateMonthlyReports(event.target.value);
        });

        if (monthSelector && monthSelector.value) {
            updateMonthlyReports(monthSelector.value);
        }



        // Initial load for the default selected month
        updateMonthlyReports(monthSelector.value);

        // ==========================================================
    // ⭐️ 2. เพิ่มส่วนจัดการผู้ใช้ (USER MANAGEMENT LOGIC) ⭐️
    // ==========================================================
    
    // 2.1. เลือก DIV Alert สำหรับหน้านี้
    const alertMessageDiv = document.getElementById('user-alert-message'); 

    // 2.2. เพิ่มฟังก์ชัน Alert ที่คุณให้มา
    function showAlert(message, type = 'info') {
        if (!alertMessageDiv) {
            console.error("Missing alert element (user-alert-message)!");
            return;
        }
        alertMessageDiv.textContent = message;
        alertMessageDiv.style.backgroundColor = type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#cce5ff';
        alertMessageDiv.style.color = type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#004085';
        alertMessageDiv.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#b8daff'}`;
        alertMessageDiv.style.position = 'relative'; 
        alertMessageDiv.style.top = '0';
        alertMessageDiv.style.right = '0';
        alertMessageDiv.style.zIndex = '100';
        alertMessageDiv.style.padding = '15px';
        alertMessageDiv.style.borderRadius = '8px';
        alertMessageDiv.style.display = 'block';
        setTimeout(hideAlert, 5000);
    }

    function hideAlert() {
        if (alertMessageDiv) alertMessageDiv.style.display = 'none';
    }

// 2.3. เลือกตารางและใช้ Event Delegation
    const usersTableBody = document.getElementById('users-table-body');

    if (usersTableBody) {
        usersTableBody.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) return; // คลิกโดนส่วนอื่น

            event.preventDefault();
            button.disabled = true; // ป้องกันการคลิกซ้ำ

            const tr = button.closest('tr');
            const userId = tr.dataset.userId;
            const action = button.dataset.action; // 'ban' or 'unban'
            
            // กำหนดสถานะใหม่ที่จะส่งไป
            const newStatus = (action === 'ban') ? 2 : 1;

            try {
                const response = await fetch('{{ route("admin.users.updateStatus") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ?? ''
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        new_status: newStatus
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Server error');
                }

                // --- อัปเดตตารางเมื่อสำเร็จ ---

                // 1. อัปเดต data-status ของแถว
                tr.dataset.status = data.new_status;

                // 2. อัปเดตป้ายสถานะ (Status Tag)
                const statusTag = tr.querySelector('.status-tag');
                
                // (โค้ดนี้จะตรวจสอบ 1 หรือ 2 และไม่มี .charAt)
                if (data.new_status == 1) { 
                    statusTag.textContent = 'Active';
                    statusTag.className = 'status-tag active';
                } else {
                    statusTag.textContent = 'Banned';
                    statusTag.className = 'status-tag banned';

                }

               
                // 3. อัปเดตปุ่ม
                if (data.new_status == 1) {
                    button.textContent = 'แบน';
                    button.className = 'btn btn-sm btn-danger';
                    button.dataset.action = 'ban';
           
                } else {
                    button.textContent = 'ปลดแบน';
                    button.className = 'btn btn-sm btn-warning';
                    button.dataset.action = 'unban';
                }
                
                showAlert('อัปเดตสถานะผู้ใช้สำเร็จ', 'success');

            } catch (error) {
                console.error('Update status error:', error);
                showAlert(error.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            } finally {
                button.disabled = false; // คืนค่าปุ่ม
            }
        });
    }

    // ==========================================================
        // ⭐️ 3. (โค้ดใหม่) ค้นหาผู้ใช้ (AJAX DATABASE SEARCH LOGIC) ⭐️
        // ==========================================================

        const userSearchInput = document.getElementById('user-search-input');
        
        // (เราใช้ตัวแปร usersTableBody ที่มีอยู่แล้วจากโค้ดด้านบน)

        // --- ฟังก์ชัน Debounce (กันยิง Request รัวๆ) ---
        let searchTimer;
        function debounce(func, delay) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(func, delay);
        }

        if (userSearchInput && usersTableBody) {
            
            userSearchInput.addEventListener('input', () => {
                const query = userSearchInput.value.trim();
                
                // หน่วง 300ms ค่อยค้นหา
                debounce(async () => {
                    try {
                        // 1. สร้าง URL พร้อม query string
                        const searchUrl = new URL('{{ route("admin.users.search") }}');
                        searchUrl.searchParams.append('query', query);

                        // 2. ยิง AJAX
                        const response = await fetch(searchUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json', // or 'text/html'
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Search request failed');
                        }

                        // 3. รับ HTML (จาก Partial View)
                        const html = await response.text();
                        
                        // 4. ยัด HTML ใหม่เข้าไปใน <tbody>
                        usersTableBody.innerHTML = html;

                    } catch (error) {
                        console.error('Search error:', error);
                        // (ถ้าอยากให้แจ้งเตือน ก็เรียก showAlert('ค้นหาล้มเหลว', 'error');)
                    }
                }, 300); // หน่วง 300 มิลลิวินาที
            });
        }

    });
</script>
</body>
</html>

