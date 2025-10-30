<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novel Noob | เปลี่ยนไอเดียเป็นนิยาย สร้างรายได้ด้วย AI</title>
    <meta name="description" content="เปลี่ยนไอเดียให้เป็นนิยายที่สมบูรณ์ด้วย Novel Noob เครื่องมือช่วยเขียนนิยายอัจฉริยะ ตั้งแต่สร้างพล็อตเรื่อง ตัวละคร ไปจนถึงการเขียนแต่ละบท พร้อมสำหรับสร้าง Ebook เพื่อขาย">
    <meta name="keywords" content="เขียนนิยาย, AI ช่วยเขียนนิยาย, สร้างนิยาย, เขียน ebook, ขาย ebook, เครื่องมือสำหรับนักเขียน, ผู้ช่วยนักเขียน AI">
    
    <!-- === SEO & Social Sharing Tags (Updated) === -->

    <!-- Open Graph / Facebook / LINE -->
    <meta property="og:type" content="website">
    <!--  TODO: Replace with your actual website URL -->
    <meta property="og:url" content="https://www.your-novel-noob-url.com/">
    <meta property="og:title" content="Novel Noob | เปลี่ยนไอเดียเป็นนิยาย สร้างรายได้ด้วย AI">
    <meta property="og:description" content="เครื่องมือที่ใคร ๆ ก็เป็นนักเขียนได้ เปลี่ยนไอเดียของคุณให้กลายเป็นนิยาย แล้วต่อยอดสร้างรายได้จาก Ebook ได้ไม่จำกัด">
    <!-- TODO: Create and replace with your actual Open Graph image URL (Recommended size: 1200x630px) -->
    <meta property="og:image" content="https://www.your-novel-noob-url.com/images/social-share-cover.png">

    <!-- Twitter / X Card -->
    <meta property="twitter:card" content="summary_large_image">
    <!-- TODO: Replace with your actual website URL -->
    <meta property="twitter:url" content="https://www.your-novel-noob-url.com/">
    <meta property="twitter:title" content="Novel Noob | เปลี่ยนไอเดียเป็นนิยาย สร้างรายได้ด้วย AI">
    <meta property="twitter:description" content="เครื่องมือที่ใคร ๆ ก็เป็นนักเขียนได้ เปลี่ยนไอเดียของคุณให้กลายเป็นนิยาย แล้วต่อยอดสร้างรายได้จาก Ebook ได้ไม่จำกัด">
    <!-- TODO: Create and replace with your actual Twitter card image URL -->
    <meta property="twitter:image" content="https://www.your-novel-noob-url.com/images/social-share-cover.png">

    <!-- Structured Data (Schema.org for Software Application) -->
    <!-- The aggregateRating data below is now visible in the testimonial section -->
    {{-- <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "Novel Noob",
      "applicationCategory": "ProductivityApplication",
      "operatingSystem": "Web",
      "description": "เปลี่ยนไอเดียให้เป็นนิยายที่สมบูรณ์ด้วย Novel Noob เครื่องมือช่วยเขียนนิยายอัจฉริยะ ตั้งแต่สร้างพล็อตเรื่อง ตัวละคร ไปจนถึงการเขียนแต่ละบท พร้อมสำหรับสร้าง Ebook เพื่อขาย",
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "reviewCount": "25"
      },
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "THB"
      }
    }
    </script> --}}
    <!-- === End of Added Tags === -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    
    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">

    <!-- Page-specific styles -->
    <style>
        /* Hero Section */
        .hero {
            padding: 80px 0;
            padding-top: 150px;
            background: radial-gradient(circle, rgba(27,35,63,0.8) 0%, rgba(18,24,40,1) 80%);
        }
        .hero-content {
            display: flex;
            align-items: center;
            gap: 60px;
        }
        .hero-text {
            flex: 1;
            text-align: left;
        }
        .hero h1 {
            font-family: var(--font-heading);
            font-size: 3.2rem;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        .hero .highlight { color: var(--primary-accent); }
        .hero p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 30px;
            line-height: 1.7;
        }
        .hero-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }
        .hero .btn { 
            padding: 15px 30px; 
            font-size: 1.1rem; 
            border-radius: 8px;
        }
        .hero-image {
            flex: 1.2;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .hero-image img {
            max-width: 100%;
        }

        /* Shared Section Styles */
        .section {
            padding: 80px 0;
            text-align: center;
        }
        .section-light { background-color: var(--bg-light); }
        .section-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .section-subtitle {
            color: var(--text-secondary);
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }

        /* Stats Section */
        .stats-grid {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-top: 50px;
        }
        .stat-item {
            text-align: center;
            min-width: 280px;
        }
        .stat-icon {
            color: var(--primary-accent);
            margin-bottom: 15px;
        }
        .stat-icon svg {
            width: 50px;
            height: 50px;
        }
        .stat-number {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .stat-label {
            font-size: 1.1rem;
            color: var(--text-secondary);
        }

        /* Features Section */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .feature-card {
            background-color: var(--bg-dark);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            text-align: left;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            background-color: var(--bg-light);
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            color: var(--primary-accent);
        }
        .feature-icon svg { width: 28px; height: 28px; }
        .feature-card h3 { font-family: var(--font-heading); margin-bottom: 10px; }
        .feature-card p { color: var(--text-secondary); line-height: 1.7; }
        
        /* Ebook Section */
        .ebook-section-content {
            display: flex;
            align-items: center;
            gap: 50px;
            text-align: left;
        }
        .ebook-section-content .text-content { flex: 1; }
        .ebook-section-content .image-content { flex: 1; max-width: 450px; }
        .ebook-section-content img { width: 100%; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .ebook-section-content ul {
            list-style: none;
            margin-top: 20px;
        }
        .ebook-section-content ul li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 15px;
            color: var(--text-secondary);
        }
        .ebook-section-content ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary-accent);
            font-weight: bold;
        }

        /* Testimonial & Showcase Section */
        .showcase-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        .book-slider {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        .slider-viewport {
            overflow: hidden;
            border-radius: 15px;
        }
        .slider-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        .slider-track img {
            width: 100%;
            flex-shrink: 0;
            display: block;
        }
        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(18, 24, 40, 0.7);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s;
        }
        .slider-btn:hover { background-color: var(--bg-light); }
        #prev-btn { left: -20px; }
        #next-btn { right: -20px; }
        .testimonial-card {
            background-color: var(--bg-dark);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            text-align: left;
            position: relative;
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
            color: #FFC107; /* Gold color for stars */
        }
        .testimonial-rating .stars svg {
            width: 20px;
            height: 20px;
        }
        .testimonial-rating .rating-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Video Modal Specific Styles */
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
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
        }
        .modal-overlay.visible {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content {
            /* ---ปรับแก้ที่นี่--- */
            background-color: transparent; /* ทำให้พื้นหลังโปร่งใส */
            padding: 0; /* ลบ padding ออก */
            box-shadow: none; /* ลบเงาออก */
            /* ---ของเดิม--- */
            border-radius: 15px;
            width: 90%;
            max-width: 900px;
            position: relative;
            transform: scale(0.95);
            transition: transform 0.3s ease-in-out;
        }
        .modal-overlay.visible .modal-content {
            transform: scale(1);
        }
        .modal-close-btn {
            position: absolute;
            /* ---ปรับแก้ที่นี่--- */
            top: -37px;  /* เปลี่ยนเป็นค่าติดลบเพื่อให้อยู่เหนือกรอบ */
            right: -37px; /* เปลี่ยนเป็นค่าติดลบเพื่อให้อยู่นอกกรอบด้านขวา */
            
            /* ปรับสไตล์กลับมาให้เด่นชัดขึ้นเมื่ออยู่นอกวิดีโอ */
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
            z-index: 10;
            
            /* ---ของเดิม--- */
            color: var(--text-primary);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1;
        }
        #video-modal .modal-content {
            background-color: transparent;
            padding: 0;
            box-shadow: none;
            max-width: 900px;
        }
        #video-modal .modal-close-btn {
            position: absolute;
            top: -37px;
            right: -37px;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
            z-index: 10;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            border-radius: 8px;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .hero-content, .ebook-section-content, .showcase-grid {
                flex-direction: column;
                grid-template-columns: 1fr;
            }
            .hero-text {
                text-align: center;
            }
             .hero-actions {
                justify-content: center;
                flex-direction: column;
             }
             .hero .btn {
                width: 100%;
                text-align: center;
                justify-content: center;
             }
            .hero-image {
                margin-top: 40px;
                width: 320px;
            }
            .ebook-section-content .image-content { order: -1; margin-bottom: 30px; }
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="container">
            <nav class="navbar">
                <a href="{{ url('/') }}" class="logo">NovelNoob</a>
                
                <div class="nav-links">
                    <a href="#features">คุณสมบัติ</a>
                    <a href="#ebook">สร้างนิยาย</a>
                    <a href="#showcase">เสียงตอบรับ</a>
                    <a href="{{route('articles.index')}}">บทความ</a>
                    <a href="{{route('community.index')}}">ชุมชน</a>
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
            <a href="#features">คุณสมบัติ</a>
            <a href="#ebook">สร้างนิยาย</a>
            <a href="#showcase">เสียงตอบรับ</a>
            <a href="{{route('articles.index')}}">บทความ</a>
            <a href="{{route('community.index')}}">ชุมชน</a>
            <a href="{{route('login')}}" class="btn btn-secondary">เข้าสู่ระบบ</a>
            <a href="{{route('novel.create')}}" class="btn btn-primary">ฟรี 100 เครดิต</a>
        </div>
    </div>


    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1>เขียนนิยายเรื่องแรก<br>ด้วยผู้ช่วย <span class="highlight">AI</span> อัจฉริยะ</h1>
                        <p>เครื่องมือที่ใคร ๆ ก็เป็นนักเขียนได้ เปลี่ยนไอเดียของคุณให้กลายเป็นนิยาย แล้วต่อยอดสร้างรายได้จาก Ebook ได้ไม่จำกัด</p>
                        <div class="hero-actions">
                             <a href="{{route('novel.create')}}" class="btn btn-primary">เริ่มต้นเขียนนิยายของคุณ</a>
                             <button id="show-video-btn" class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                  <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445"/>
                                </svg>
                                วิดีโอการใช้งาน
                            </button>
                        </div>
                    </div>
                    <div class="hero-image">
                        <img src="{{asset('assets/images/hero-img-1-1.png')}}" alt="ตัวอย่างปกนิยายที่สร้างด้วย AI">
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="section section-light">
            <div class="container">
                <h2 class="section-title">คุณสมบัติเด่น</h2>
                <p class="section-subtitle">เรามีเครื่องมือที่ทรงพลังเพื่อช่วยให้นักเขียนทุกคนทำงานได้ง่ายและเร็วขึ้น</p>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13V7m0 13a2 2 0 002-2V9a2 2 0 00-2-2m0 13h.01M15 20l5.447-2.724A1 1 0 0021 16.382V5.618a1 1 0 00-1.447-.894L15 7m0 13V7m0 13a2 2 0 01-2-2V9a2 2 0 012-2m0 13h-.01"></path></svg>
                        </div>
                        <h3>สร้างโครงเรื่องอัจฉริยะ</h3>
                        <p>เปลี่ยนไอเดียเล็กๆ ให้เป็นโครงเรื่องนิยายที่สมบูรณ์แบบ พร้อมตัวละครและฉากหลังที่มีชีวิตชีวาพร้อมให้คุณเริ่มเขียนต่อได้ทันที</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </div>
                        <h3>ผู้ช่วยเขียนนิยายส่วนตัว</h3>
                        <p>ให้ AI ช่วยลงมือเขียนเนื้อหาตามโครงเรื่องของคุณ คุณจึงคุมทิศทางของเรื่องราวได้ทั้งหมดเพื่อให้ได้ผลลัพธ์ที่สมบูรณ์แบบที่สุด</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                           <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0 3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <h3>ปรับแต่งสไตล์ได้ดั่งใจ</h3>
                        <p>ไม่ว่านิยายของคุณจะเป็นแนวไหน ก็สามารถเลือกสไตล์การเขียนที่ใช่ หรือปรับแต่งให้ AI เขียนด้วยสำนวนที่เป็นเอกลักษณ์ของคุณเอง</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="ebook" class="section section-light">
            <div class="container">
                <div class="ebook-section-content">
                    <div class="text-content">
                        <h2 class="section-title" style="text-align: left;">จากไอเดีย...สู่นิยายพร้อมขาย</h2>
                        <p class="section-subtitle" style="text-align: left; margin-left: 0;">Novel Noob ไม่ใช่แค่เครื่องมือช่วยเขียน แต่เป็นพาร์ทเนอร์ที่ช่วยให้คุณสร้างผลงานนิยายของตัวเองได้สำเร็จ เพื่อจัดทำ Ebook สร้างรายได้ไม่จำกัด</p>
                        <ul>
                            <li><strong>ประหยัดเวลา:</strong> ลดเวลาในการวางพล็อตและเขียนร่างแรก ให้คุณโฟกัสกับการขัดเกลาเนื้อหาได้มากขึ้น</li>
                            <li><strong>เอาชนะ Writer's Block:</strong> หมดปัญหาคิดไม่ออก ให้ AI ช่วยจุดประกายไอเดียและเขียนต่อไปได้อย่างลื่นไหล</li>
                            <li><strong>สร้างรายได้:</strong> เขียนนิยายจบเรื่องได้อย่างรวดเร็ว พร้อมนำต้นฉบับไปจัดทำเป็น Ebook เพื่อวางขายบนแพลตฟอร์มต่างๆ</li>
                        </ul>
                    </div>
                    <div class="image-content">
                        <img src="https://placehold.co/600x400/1B233F/F0F2F5?text=Your+Ebook+Cover" alt="ปก Ebook ที่สร้างจากนิยาย">
                    </div>
                </div>
            </div>
        </section>

        <section id="stats" class="section">
            <div class="container">
                 <h2 class="section-title">สถิติการสร้างสรรค์</h2>
                 <p class="section-subtitle">เข้าร่วมชุมชนนักเขียนที่เติบโตของเรา</p>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <div class="stat-number" data-target="752">0</div>
                        <div class="stat-label">เล่มนิยายที่ถูกสร้าง</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <div class="stat-number" data-target="12757">0</div>
                        <div class="stat-label">บทนิยายที่ถูกเขียน</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="showcase" class="section section-light">
            <div class="container">
                <h2 class="section-title">ผลงานและเสียงตอบรับจากนักเขียน</h2>
                <p class="section-subtitle">ดูตัวอย่างผลงานและฟังเสียงจากผู้ที่เปลี่ยนไอเดียให้เป็น Ebook ที่ขายได้จริง</p>
                <div class="showcase-grid">
                    <div class="book-slider">
                        <div class="slider-viewport">
                            <div class="slider-track">
                                <img src="https://placehold.co/400x600/6C5DD3/FFFFFF?text=เงาในมหานคร" alt="ปกนิยายตัวอย่าง 1">
                                <img src="https://placehold.co/400x600/D35D5D/FFFFFF?text=เพลิงรักซ่อนใจ" alt="ปกนิยายตัวอย่าง 2">
                                <img src="https://placehold.co/400x600/5DD39E/FFFFFF?text=รหัสลับจักรวาล" alt="ปกนิยายตัวอย่าง 3">
                            </div>
                        </div>
                        <button id="prev-btn" class="slider-btn">❮</button>
                        <button id="next-btn" class="slider-btn">❯</button>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"จากคนที่เขียนนิยายไม่จบเรื่องมาตลอด NovelNoob ช่วยให้วางโครงเรื่องได้ง่ายขึ้นมาก และฟีเจอร์ช่วยเขียนทีละบทก็ทำให้เอาชนะ Writer's Block ได้จริงๆ ตอนนี้ทำนิยาย Ebook ขายได้จริงแล้วค่ะ กว่า 50 เล่ม"</p>
                        <p class="testimonial-author">นามปากกา: ม่านมุก<span>นักเขียนนิยายอิสระ</span></p>
                        <!-- === Added Rating Display === -->
                        <div class="testimonial-rating">
                            <div class="stars">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
                            </div>
                            <span class="rating-text">4.8 จาก 455 รีวิว</span>
                        </div>
                        <!-- === End of Added Rating Display === -->
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

    <div class="modal-overlay" id="video-modal">
        <div class="modal-content">
            <button class="modal-close-btn" id="modal-close-btn">&times;</button>
            <div class="video-container">
                 <iframe id="youtube-video" width="560" height="315" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <!-- Central Script -->
    <script src="{{asset('assets/js/script.js')}}"></script>
    
    <!-- Page-specific script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Slider functionality
            const track = document.querySelector('.slider-track');
            const slides = Array.from(track.children);
            const nextButton = document.getElementById('next-btn');
            const prevButton = document.getElementById('prev-btn');

            let slideIndex = 0;
            
            nextButton.addEventListener('click', e => {
                slideIndex++;
                if (slideIndex >= slides.length) {
                    slideIndex = 0;
                }
                track.style.transform = `translateX(-${slideIndex * 100}%)`;
            });

            prevButton.addEventListener('click', e => {
                slideIndex--;
                if (slideIndex < 0) {
                    slideIndex = slides.length - 1;
                }
                track.style.transform = `translateX(-${slideIndex * 100}%)`;
            });

            // Animated Stats Counter
            const statsSection = document.getElementById('stats');
            const counters = document.querySelectorAll('.stat-number');
            let hasAnimated = false;

            const startCounter = (counter) => {
                const target = +counter.getAttribute('data-target');
                let count = 0;
                const duration = 2000;
                const increment = target / (duration / 16); 

                const updateCount = () => {
                    count += increment;
                    if (count < target) {
                        counter.innerText = Math.ceil(count).toLocaleString('th-TH');
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = target.toLocaleString('th-TH');
                    }
                };
                requestAnimationFrame(updateCount);
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !hasAnimated) {
                        counters.forEach(startCounter);
                        hasAnimated = true;
                        observer.unobserve(statsSection);
                    }
                });
            }, {
                threshold: 0.5
            });

            if (statsSection) {
                observer.observe(statsSection);
            }

            // --- Video Modal Logic ---
            const showVideoBtn = document.getElementById('show-video-btn');
            const videoModal = document.getElementById('video-modal');
            const closeVideoBtn = document.getElementById('modal-close-btn');
            const youtubeIframe = document.getElementById('youtube-video');
            const youtubeSrc = "https://www.youtube.com/embed/09tVXLTCYeI?si=GBRRQ7lzidUDjaao&autoplay=0";

            showVideoBtn.addEventListener('click', () => {
                if (youtubeIframe.src !== youtubeSrc) {
                    youtubeIframe.src = youtubeSrc;
                }
                videoModal.classList.add('visible');
            });

            const closeModal = () => {
                videoModal.classList.remove('visible');
                youtubeIframe.src = ""; // Stop the video
            };

            closeVideoBtn.addEventListener('click', closeModal);
            videoModal.addEventListener('click', (e) => {
                if (e.target === videoModal) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
