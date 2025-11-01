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
        
        /* ... (โค้ด CSS เดิมของคุณตั้งแต่ .container จนถึง .btn-read-more:hover อยู่ตรงนี้) ... */
        .container {
            max-width: 900px;
        }
        .nav-actions .btn-secondary {
            border-color: var(--text-secondary);
        }
        .nav-actions .btn-secondary:hover { 
            background-color: var(--border-color); 
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        main {
            padding-top: 88px;
        }
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
        .search-container {
            margin-top: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .search-container form {
            display: flex;
            position: relative;
        }
        .search-container input[type="text"] {
            flex-grow: 1;
            width: 100%;
            border: 1px solid var(--border-color);
            background-color: var(--bg-light);
            color: var(--text-primary);
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 1rem;
            font-family: var(--font-ui);
            padding-right: 120px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .search-container input[type="text"]:focus {
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.3);
        }
        .search-container button[type="submit"] {
            position: absolute;
            right: 6px;
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
        @media (max-width: 480px) {
            .search-container input[type="text"] {
                padding-right: 55px;
            }
            .search-container button[type="submit"] {
                padding: 10px;
            }
            .search-container button[type="submit"] span {
                display: none;
            }
            .search-container button[type="submit"] svg {
                margin-right: 0;
            }
        }
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

        /* ⭐️ --- (ปรับปรุง) Pagination Styles (อ้างอิงจากรูปภาพ) --- ⭐️ */
        .pagination-container {
            margin-top: 60px; /* เพิ่มระยะห่างด้านบน */
            display: flex;
            flex-direction: column; /* จัดเรียงแนวตั้ง */
            align-items: center; /* จัดกลาง */
            gap: 20px; /* ระยะห่างระหว่างส่วน */
            width: 100%;
        }

        /* "« Previous Next »" text links */
        .pagination-text-links {
            display: flex;
            gap: 25px;
            font-size: 1rem;
            font-weight: bold;
            font-family: var(--font-ui);
            order: 1; /* สั่งให้อยู่บนสุด */
        }
        .pagination-text-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
        }
        .pagination-text-links a:hover {
            color: var(--primary-accent);
        }
        .pagination-text-links a.disabled {
            color: var(--border-color);
            opacity: 0.7;
            pointer-events: none;
        }

        /* ⭐️ (ลบ) "Showing..." text */
        /* .pagination-summary { ... } */
        
        /* Main controls (Arrows + Numbers) */
        .pagination-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            width: 100%;
            max-width: 300px; /* จำกัดความกว้าง */
            order: 3; /* สั่งให้อยู่ล่างสุด */
            margin-top: 10px;
        }

        /* Big Arrows */
        .pagination-arrow {
            color: white; /* สีลูกศร */
            text-decoration: none;
            transition: color 0.3s, transform 0.3s;
        }
        .pagination-arrow svg {
            width: 40px;
            height: 40px;
            stroke-width: 2.5;
        }
        .pagination-arrow:hover {
            color: var(--primary-accent);
            transform: scale(1.1);
        }
        .pagination-arrow.disabled {
            color: var(--border-color); /* สีจาง */
            opacity: 0.7;
            pointer-events: none;
        }
        
        /* Page numbers container */
        .pagination-pages {
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
            flex-grow: 1; /* ให้มันขยายเต็มพื้นที่ตรงกลาง */
            gap: 10px; /* (ปรับ) ลด gap ให้พอดีกับ ... */
        }
        .pagination-pages .page-link {
            font-family: var(--font-heading);
            font-size: 1.4rem; /* ขนาดใหญ่ */
            font-weight: bold;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 5px;
            transition: color 0.3s;
        }
        .pagination-pages .page-link:hover {
            color: white;
        }
        .pagination-pages .page-link.active {
            color: var(--primary-accent); /* สี active */
            font-size: 1.7rem; /* ใหญ่กว่าเดิมนิดนึง */
        }
        
        /* ⭐️ (เพิ่ม) สไตล์สำหรับ "..." */
        .pagination-dots {
            font-family: var(--font-heading);
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--text-secondary);
            padding: 5px;
            cursor: default;
        }
        
        /* ⭐️ --- (จบ) Pagination Styles --- ⭐️ */

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
                {{-- ⭐️ (แก้) เปลี่ยนเป็น H1 และ P แบบไดนามิก --}}
                <h1>{{ $pageTitle ?? 'บทความและเทคนิคการเขียน' }}</h1>
                <p>{{ $pageSubtitle ?? 'คลังความรู้สำหรับนักเขียนยุคใหม่ ค้นพบเคล็ดลับ เทคนิค และแรงบันดาลใจในการสร้างสรรค์นิยายของคุณ' }}</p>
                
                <!-- ⭐️ --- (โค้ดเดิม) Search Bar HTML --- ⭐️ -->
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
                
                {{-- ⭐️ --- (แก้) เริ่มต้น Loop แสดงบทความ --- ⭐️ --}}
                <div class="articles-list">
                    
                    @forelse ($articles as $article)
                        <div class="article-card">
                            <div class="article-card-header">
                                {{-- ⭐️ (แก้) ใช้ route 'articles.show' และ $article->slug --}}
                                <h2 class="article-card-title"><a href="{{ route('articles.show', ['slug' => $article->slug]) }}">{{ $article->title }}</a></h2>
                                {{-- ⭐️ (แก้) ใช้ $article->thaiDate จาก Model Accessor --}}
                                <div class="article-card-meta">โดย Novel Noob | {{ $article->thaiDate }}</div>
                            </div>

                            {{-- ⭐️ (แก้) ตรวจสอบและ Loop Tags --}}
                            @if (!empty($article->tags) && is_array($article->tags))
                                <div class="article-card-tags">
                                    @foreach ($article->tags as $tag)
                                        {{-- ⭐️ (แก้) ใช้ route 'articles.byTag' --}}
                                        <a href="{{ route('articles.byTag', ['tag_slug' => $tag['tag_slug']]) }}" class="tag">{{ $tag['tag'] }}</a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- ⭐️ (แก้) ใช้ $article->meta_description สำหรับ excerpt --}}
                            <p class="article-card-excerpt">{{ $article->meta_description }}</p>
                            
                            <div class="article-card-footer">
                                {{-- ⭐️ (แก้) ใช้ route 'articles.show' และ $article->slug --}}
                                <a href="{{ route('articles.show', ['slug' => $article->slug]) }}" class="btn-read-more">อ่านต่อ &rarr;</a>
                            </div>
                        </div>
                    @empty
                        {{-- ⭐️ (เพิ่ม) กรณีไม่พบบทความ --}}
                        <div class="article-card" style="text-align: center;">
                            <p style="font-size: 1.1rem; color: var(--text-secondary); margin: 20px 0;">
                                ไม่พบบทความที่ตรงกับการค้นหาของคุณ
                            </p>
                        </div>
                    @endforelse

                </div>
                {{-- ⭐️ --- (จบ) Loop แสดงบทความ --- ⭐️ --}}


                {{-- ⭐️ --- (ปรับปรุง) ส่วนแสดงผล Pagination --- ⭐️ --}}
                @if ($articles->hasPages())
                    <div class="pagination-container">

                        <!-- 1. "« Previous Next »" text links (จากรูป) -->
                        <div class="pagination-text-links">
                            <a href="{{ $articles->previousPageUrl() }}" 
                               class="{{ $articles->onFirstPage() ? 'disabled' : '' }}">
                               « ก่อนหน้า
                            </a>
                            <a href="{{ $articles->nextPageUrl() }}" 
                               class="{{ !$articles->hasMorePages() ? 'disabled' : '' }}">
                               ต่อไป »
                            </a>
                        </div>

                        <!-- ⭐️ (ลบ) "Showing..." text -->
                        {{-- 
                        <div class="pagination-summary">
                            Showing {{ $articles->firstItem() }} to {{ $articles->lastItem() }} of {{ $articles->total() }} results
                        </div>
                        --}}

                        <!-- 3. Main controls: Arrows + Numbers (จากรูป) -->
                        <div class="pagination-controls">
                            
                            <!-- Previous Arrow -->
                            <a href="{{ $articles->previousPageUrl() }}" 
                               class="pagination-arrow {{ $articles->onFirstPage() ? 'disabled' : '' }}"
                               aria-label="Previous Page">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </a>

                            <!-- Page Numbers -->
                            <div class="pagination-pages">
                                {{-- ⭐️ --- (ปรับปรุง) ตรรกะการแสดงผลเลขหน้า --- ⭐️ --}}
                                @php
                                    $currentPage = $articles->currentPage();
                                    $lastPage = $articles->lastPage();
                                    $linksToShow = 6; // 6 ลิงก์ (1 2 3 4 5 6)
                                    $sideLinks = 3; // 3 ลิงก์แรก (1 2 3) และ 3 ลิงก์สุดท้าย (X-2 X-1 X)
                                @endphp

                                @if ($lastPage <= $linksToShow)
                                    {{-- Case 1: 6 หรือน้อยกว่า 6 หน้า. แสดงทั้งหมด --}}
                                    @for ($page = 1; $page <= $lastPage; $page++)
                                        <a href="{{ $articles->url($page) }}" class="page-link {{ ($page == $currentPage) ? 'active' : '' }}">
                                           {{ $page }}
                                        </a>
                                    @endfor
                                @else
                                    {{-- Case 2: 7 หน้าขึ้นไป. แสดงแบบย่อ (1 2 3 ... 5 6 7) --}}
                                    
                                    {{-- แสดง 3 หน้าแรก --}}
                                    @for ($page = 1; $page <= $sideLinks; $page++)
                                        <a href="{{ $articles->url($page) }}" class="page-link {{ ($page == $currentPage) ? 'active' : '' }}">
                                           {{ $page }}
                                        </a>
                                    @endfor

                                    <span class="pagination-dots">...</span>

                                    {{-- แสดง 3 หน้าสุดท้าย --}}
                                    @for ($page = $lastPage - ($sideLinks - 1); $page <= $lastPage; $page++)
                                        <a href="{{ $articles->url($page) }}" class="page-link {{ ($page == $currentPage) ? 'active' : '' }}">
                                           {{ $page }}
                                        </a>
                                    @endfor
                                @endif
                                {{-- ⭐️ --- (จบ) ตรรกะการแสดงผลเลขหน้า --- ⭐️ --}}
                            </div>
                            
                            <!-- Next Arrow -->
                            <a href="{{ $articles->nextPageUrl() }}" 
                               class="pagination-arrow {{ !$articles->hasMorePages() ? 'disabled' : '' }}"
                               aria-label="Next Page">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>

                    </div>
                @endif
                {{-- ⭐️ --- (จบ) ส่วนแสดงผล Pagination --- ⭐️ --}}


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


