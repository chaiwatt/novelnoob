<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- ⭐️ (แก้) เปลี่ยนเป็น Title และ Meta แบบไดนามิก --}}
    <title>{{ $article->title }} - Novel Noob</title>
    <meta name="description" content="{{ $article->meta_description }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    
    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <style>
        /* --- Page-Specific Styles for Article Detail Page --- */
        .container {
            max-width: 800px; /* Narrower for better reading */
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
            padding-top: 88px; /* Offset for fixed header */
        }
        
        /* Article Content */
        .article-container {
            padding: 60px 0;
        }
        .article-header {
            text-align: center;
            padding-top: 20px;
            margin-bottom: 40px;
        }
        .article-tags {
            display: flex;
            gap: 10px;
            justify-content: center;
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
        .article-header h1 {
            font-family: var(--font-heading);
            font-size: 2.8rem;
            line-height: 1.3;
            margin-bottom: 20px;
        }
        .article-meta {
            color: var(--text-secondary);
        }
        
        .article-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-secondary);
        }
        .article-body h2, .article-body h3, .article-body h4 {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            margin-top: 40px;
            margin-bottom: 20px;
            color: var(--text-primary);
        }
        .article-body p {
            margin-bottom: 20px;
        }
        .article-body a {
            color: var(--primary-accent);
            text-decoration: underline;
        }
        .article-body blockquote {
            border-left: 4px solid var(--primary-accent);
            padding-left: 20px;
            margin: 30px 0;
            font-style: italic;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="container">
            <nav class="navbar">
                <a href="{{url('/')}}" class="logo">NovelNoob</a>
                <div class="nav-links">
                    <a href="{{route('articles.index')}}">บทความ</a>
                </div>
                
                {{-- ⭐️ (แก้) เพิ่ม Auth-Aware Navigation --}}
                <div class="nav-actions">
                    @guest
                        <a href="{{route('login')}}" class="btn btn-secondary">เข้าสู่ระบบ</a>
                        <a href="{{route('novel.create')}}" class="btn btn-primary">ฟรี 100 เครดิต</a>
                    @endguest
                    @auth
                        @if (Auth::user()->type === 'admin')
                            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">แดชบอร์ด</a>
                        @elseif (Auth::user()->type === 'writer')
                            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">แดชบอร์ด</a>
                        @endif
                        <button class="btn btn-primary" 
                        onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                            ออกจากระบบ
                        </button>
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
    
    {{-- ⭐️ (แก้) เพิ่ม Auth-Aware Mobile Menu --}}
    <div class="mobile-menu-overlay" id="mobile-menu">
        <div class="mobile-nav-links">
            <a href="{{route('articles.index')}}">บทความ</a>
            @guest
                <a href="{{route('login')}}" class="btn btn-secondary">เข้าสู่ระบบ</a>
                <a href="{{route('novel.create')}}" class="btn btn-primary">ฟรี 100 เครดิต</a>
            @endguest
            @auth
                @if (Auth::user()->type === 'admin')
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">แดชบอร์ด</a>
                @elseif (Auth::user()->type === 'writer')
                    <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">แดชบอร์ด</a>
                @endif
                <button class="btn btn-primary" 
                onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                    ออกจากระบบ
                </button>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endauth
        </div>
    </div>

    <main>
        <article class="article-container">
            <div class="container">
                <header class="article-header">
                    
                    {{-- ⭐️ (แก้) วน Loop แสดง Tags --}}
                    @if (!empty($article->tags) && is_array($article->tags))
                        <div class="article-tags">
                            @foreach ($article->tags as $tag)
                                {{-- ⭐️ (แก้) ใช้ route 'articles.byTag' ตามที่คุณต้องการ --}}
                                <a href="{{ route('articles.byTag', ['tag_slug' => $tag['tag_slug']]) }}" class="tag">{{ $tag['tag'] }}</a>
                            @endforeach
                        </div>
                    @endif

                    {{-- ⭐️ (แก้) แสดง Title --}}
                    <h1>{{ $article->title }}</h1>
                    
                    <div class="article-meta">
                        <span>โดย  Novel Noob</span> &bull;
                        {{-- ⭐️ (แก้) แสดง วันที่ --}}
                        <span>{{ $article->thaiDate }}</span>
                    </div>
                </header>

                {{-- ⭐️ (แก้) แสดง Body (ต้องใช้ {!! ... !!} เพื่อ render HTML) --}}
                <div class="article-body">
                    {!! $article->body !!}
                </div>
            </div>
        </article>
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
