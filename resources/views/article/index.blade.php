<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บทความ - Novel Noob</title>
    <meta name="description" content="เคล็ดลับ, เทคนิค, และแรงบันดาลใจสำหรับนักเขียนนิยายยุคใหม่ เรียนรู้วิธีการใช้ AI เพื่อสร้างสรรค์ผลงานและต่อยอดสู่การเป็นเจ้าของ Ebook">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    
    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <style>
        /* --- Page-Specific Styles for Articles Page --- */
        
        /* Adjust container for this page */
        .container {
            max-width: 900px;
        }

        /* Override button styles for this page if needed */
        .nav-actions .btn-secondary {
            border-color: var(--text-secondary);
        }
        .nav-actions .btn-secondary:hover { 
            background-color: var(--border-color); 
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        main {
            padding-top: 88px; /* Offset for fixed header */
        }
        
        /* Page Header */
        .page-header {
            padding: 60px 0;
            text-align: center;
        }
        .page-header h1 {
            font-family: var(--font-heading);
            font-size: 2.8rem;
            margin-bottom: 15px;
        }
        .page-header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ⭐️ --- (เพิ่ม) Search Bar Styles --- ⭐️ */
        .search-container {
            margin-top: 30px; /* Space from the <p> tag */
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-container form {
            display: flex;
            position: relative;
        }

        .search-container input[type="text"] {
            flex-grow: 1; /* Take up most of the space */
            width: 100%;
            border: 1px solid var(--border-color);
            background-color: var(--bg-light); /* Darker background */
            color: var(--text-primary);
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 1rem;
            font-family: var(--font-ui);
            padding-right: 120px; /* Make space for the button */
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .search-container input[type="text"]:focus {
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.3);
        }

        .search-container button[type="submit"] {
            position: absolute;
            right: 6px; /* Small gap from the edge */
            top: 50%;
            transform: translateY(-50%);
            
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            background-color: var(--primary-accent);
            color: white;
            font-family: var(--font-ui);
            font-weight: bold;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-container button[type="submit"]:hover {
            background-color: var(--primary-hover);
        }

        .search-container button[type="submit"] svg {
            width: 18px;
            height: 18px;
            stroke-width: 2.5;
        }
        
        /* Responsive adjustment for search button */
        @media (max-width: 480px) {
            .search-container input[type="text"] {
                padding-right: 55px; /* Space for icon-only button */
            }
            .search-container button[type="submit"] {
                padding: 10px;
            }
            .search-container button[type="submit"] span {
                display: none; /* Hide text on small screens */
            }
            .search-container button[type="submit"] svg {
                margin-right: 0; /* Remove gap */
            }
        }
        /* ⭐️ --- (จบ) Search Bar Styles --- ⭐️ */

        
        /* Article Grid */
        .articles-section {
            padding: 60px 0;
        }
        .articles-list {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .article-card {
            background-color: var(--bg-light);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid var(--border-color);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }
        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .article-card-header {
            margin-bottom: 15px;
        }
        .article-card-title {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            margin-bottom: 10px;
        }
        .article-card-meta {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .article-card-tags {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .tag {
            background-color: var(--border-color);
            color: var(--text-secondary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            transition: background-color 0.3s;
        }
        .tag:hover {
            background-color: var(--secondary-hover);
            color: var(--text-primary);
        }
        .article-card-excerpt {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        .article-card-footer {
            margin-top: auto;
            text-align: right;
        }
        .btn-read-more {
            background-color: var(--primary-accent);
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-read-more:hover {
            background-color: var(--primary-hover);
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <nav class="navbar">
                <a href="{{url('/')}}" class="logo">NovelNoob</a>
                <div class="nav-links">
                    <!-- Links can be added here if needed -->
                </div>
                <div class="nav-actions">
                                        {{-- ⭐️ ตรวจสอบ: ถ้าผู้ใช้ยังไม่ได้ล็อกอิน (@guest) ⭐️ --}}
                    @guest
                        <a href="{{route('login')}}" class="btn btn-secondary">เข้าสู่ระบบ</a>
                        <a href="{{route('novel.create')}}" class="btn btn-primary">ฟรี 100 เครดิต</a>
                    @endguest

                    {{-- ⭐️ ตรวจสอบ: ถ้าผู้ใช้ล็อกอินแล้ว (@auth) ⭐️ --}}
                    @auth
                        {{-- กำหนดเส้นทาง Dashboard ตามประเภทผู้ใช้ (Admin หรือ Writer) --}}
                        @if (Auth::user()->type === 'admin')
                            {{-- ถ้าเป็น Admin ให้ไปที่ Admin Dashboard --}}
                            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">แดชบอร์ด</a>
                        @elseif (Auth::user()->type === 'writer')
                            {{-- ถ้าเป็น Writer ให้ไปที่ Writer Dashboard --}}
                            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">แดชบอร์ด</a>
                        @endif
                        
                        {{-- ปุ่ม Logout (ต้องมีฟอร์ม POST ซ่อนอยู่) --}}
                        <button class="btn btn-primary" 
                        onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                            ออกจากระบบ
                        </button>
                        
                        {{-- ฟอร์ม Logout ที่ซ่อนอยู่ --}}
                        <form id="logout-form-nav" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @endauth
                </div>
                 <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="Open menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>
    
    <div class="mobile-menu-overlay" id="mobile-menu">
        <div class="mobile-nav-links">
            <a href="{{route('login')}}" class="btn btn-secondary">เข้าสู่ระบบ</a>
            <a href="{{route('novel.create')}}" class="btn btn-primary">ฟรี 100 เครดิต</a>
        </div>
    </div>

    <main>
        <section class="page-header">
            <div class="container">
                <h1>บทความและเทคนิคการเขียน</h1>
                <p>คลังความรู้สำหรับนักเขียนยุคใหม่ ค้นพบเคล็ดลับ เทคนิค และแรงบันดาลใจในการสร้างสรรค์นิยายของคุณ</p>
                
                <!-- ⭐️ --- (เพิ่ม) Search Bar HTML --- ⭐️ -->
                <div class="search-container">
                    <form action="{{ route('articles.index') }}" method="GET">
                        <input type="text" name="search" placeholder="ค้นหาบทความ..." value="{{ request('search') }}">
                        <button type="submit">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <span>ค้นหา</span>
                        </button>
                    </form>
                </div>
                <!-- ⭐️ --- (จบ) Search Bar HTML --- ⭐️ -->

            </div>
        </section>

        <section class="articles-section">
            <div class="container">
                <div class="articles-list">
                    
                    <div class="article-card">
                        <div class="article-card-header">
                            <h2 class="article-card-title"><a href="{{route('articles.show',['id' => 1])}}">5 วิธีใช้ AI ช่วยคิดพล็อตนิยายที่ไม่ซ้ำใคร</a></h2>
                            <div class="article-card-meta">โดย  Novel Noob | 15 สิงหาคม 2568</div>
                        </div>
                        <div class="article-card-tags">
                            <a href="#" class="tag">เทคนิคการเขียน</a>
                            <a href="#" class="tag">AI Assistant</a>
                            <a href="#" class="tag">การวางพล็อต</a>
                        </div>
                        <p class="article-card-excerpt">หมดปัญหานั่งจ้องหน้ากระดาษเปล่า! เรียนรู้วิธีใช้ AI เป็นผู้ช่วยระดมสมอง สร้างพล็อตที่ซับซ้อนและน่าติดตามสำหรับนิยายเล่มใหม่ของคุณ...</p>
                        <div class="article-card-footer">
                            <a href="{{route('articles.show',['id' => 1])}}" class="btn-read-more">อ่านต่อ &rarr;</a>
                        </div>
                    </div>

                    <div class="article-card">
                        <div class="article-card-header">
                            <h2 class="article-card-title"><a href="{{route('articles.show',['id' => 1])}}">จากต้นฉบับสู่ Ebook: คู่มือสำหรับนักเขียนมือใหม่</a></h2>
                            <div class="article-card-meta">โดย  Novel Noob | 10 สิงหาคม 2568</div>
                        </div>
                        <div class="article-card-tags">
                            <a href="#" class="tag">Ebook</a>
                            <a href="#" class="tag">How-to</a>
                            <a href="#" class="tag">การตลาด</a>
                        </div>
                        <p class="article-card-excerpt">เขียนนิยายจบแล้วทำอย่างไรต่อ? บทความนี้จะแนะนำขั้นตอนการเปลี่ยนต้นฉบับของคุณให้กลายเป็น Ebook พร้อมขายบนแพลตฟอร์มชั้นนำ...</p>
                        <div class="article-card-footer">
                             <a href="{{route('articles.show',['id' => 1])}}" class="btn-read-more">อ่านต่อ &rarr;</a>
                        </div>
                    </div>
                    
                    <div class="article-card">
                        <div class="article-card-header">
                             <h2 class="article-card-title"><a href="{{route('articles.show',['id' => 1])}}">เทคนิคสร้างตัวละครให้น่าจดจำด้วย AI Assistant</a></h2>
                            <div class="article-card-meta">โดย  Novel Noob | 5 สิงหาคม 2568</div>
                        </div>
                        <div class="article-card-tags">
                            <a href="#" class="tag">การสร้างตัวละคร</a>
                            <a href="#" class="tag">เทคนิคการเขียน</a>
                        </div>
                        <p class="article-card-excerpt">ตัวละครคือหัวใจของเรื่องราว ค้นพบวิธีการใช้ AI ช่วยสร้างมิติให้ตัวละครของคุณ ทั้งปูมหลัง, จุดแข็ง, จุดอ่อน, และเป้าหมายในชีวิต...</p>
                        <div class="article-card-footer">
                             <a href="{{route('articles.show',['id' => 1])}}" class="btn-read-more">อ่านต่อ &rarr;</a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Novel Noob. All Rights Reserved.</p>
        </div>
    </footer>
    
    <!-- Link to the central script file -->
    <!-- The script will automatically initialize the mobile menu -->
    <script src="{{asset('assets/js/script.js')}}"></script>

</body>
</html>
