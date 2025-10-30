@php
    // *** MODIFIED: Simplified Avatar Logic ***
    
    // 1. Prepare Current User Data
    $currentUserJs = null;
    if (Auth::check()) {
        $user = Auth::user();
        
        // Determine avatar URL (Check if it's a full URL or a relative path)
        $currentUserAvatar = $user->avatar_url;
        if ($currentUserAvatar && !\Illuminate\Support\Str::startsWith($currentUserAvatar, 'http')) {
            $currentUserAvatar = asset('storage/' . $currentUserAvatar);
        }
        
        $currentUserJs = [
            'id' => $user->id,
            'name' => $user->pen_name ?: $user->name,
            'avatar' => $currentUserAvatar ?: 'https://placehold.co/100x100/A9B4D9/121828?text=U', // Fallback
            'is_writer' => ($user->type === 'writer' || $user->type === 'admin'),
        ];
    }

    // 2. Prepare Posts Data
    $jsPosts = $posts->map(function($post) {
        $author = $post->author;
        if(!$author) return null; // Skip if author deleted

        $authorName = $author->pen_name ?: $author->name;
        
        // Determine author avatar URL
        $authorAvatar = $author->avatar_url;
        if ($authorAvatar && !\Illuminate\Support\Str::startsWith($authorAvatar, 'http')) {
            $authorAvatar = asset('storage/' . $authorAvatar);
        }
        // Use a fallback if author (somehow) has no avatar_url from seeder
        $authorAvatar = $authorAvatar ?: 'https://placehold.co/100x100/A9B4D9/121828?text=A';


        // Reactions
        $reactionsData = [];
        $likerNames = [];
        if($post->relationLoaded('reactions')) {
            foreach ($post->reactions as $user) {
                if (isset($user->pivot)) {
                    $reactionsData[$user->id] = $user->pivot->reaction_type;
                    $likerNames[] = $user->pen_name ?: $user->name;
                }
            }
        }

        // Usefuls
        $usefulData = [];
        $usefulUserNames = [];
        if($post->relationLoaded('usefuls')) {
            foreach ($post->usefuls as $user) {
                $usefulData[$user->id] = true;
                $usefulUserNames[] = $user->pen_name ?: $user->name;
            }
        }

        // Comments
        $commentsData = [];
        if($post->relationLoaded('comments')) {
            foreach ($post->comments as $comment) {
                $commentAuthor = $comment->author;
                if(!$commentAuthor) continue;

                $commentAuthorName = $commentAuthor->pen_name ?: $commentAuthor->name;
                
                // Determine comment author avatar URL
                $commentAuthorAvatar = $commentAuthor->avatar_url;
                if ($commentAuthorAvatar && !\Illuminate\Support\Str::startsWith($commentAuthorAvatar, 'http')) {
                    $commentAuthorAvatar = asset('storage/' . $commentAuthorAvatar);
                }
                $commentAuthorAvatar = $commentAuthorAvatar ?: 'https://placehold.co/100x100/A9B4D9/121828?text=C';


                $commentsData[] = [
                    'id' => $comment->id,
                    'author' => [
                        'name' => $commentAuthorName,
                        'avatar' => $commentAuthorAvatar,
                    ],
                    'text' => $comment->content,
                    'author_id' => $comment->user_id,
                ];
            }
        }

        return [
            'id' => $post->id,
            'author' => $authorName,
            'avatar' => $authorAvatar,
            'timestamp' => $post->created_at->diffForHumans(),
            'content' => $post->content,
            'reactions' => json_encode($reactionsData),
            'usefulUsers' => json_encode($usefulData),
            'likerNames' => $likerNames,
            'usefulUserNames' => $usefulUserNames,
            'comments' => $commentsData,
            'is_owner' => (Auth::check() && $post->user_id === Auth::id()),
            'user_id' => $post->user_id,
        ];
    })->filter(); // Use filter() to remove null posts
@endphp

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- // 1. Add CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Community | Novel Noob</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">

    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">

    <!-- Page-specific styles -->
    <style>
        .container {
            max-width: 800px;
        }

        /* Specific Nav styling for this page */
        header.navbar {
            position: sticky;
            padding: 0 5%;
        }
        nav.navbar {
            gap: 20px;
            max-width: 700px; /* Match community-feed max-width */
            margin: 0 auto;
        }
        .nav-search {
            flex-grow: 1;
            position: relative;
            max-width: 450px;
        }
        .nav-search .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        .nav-search input {
            width: 100%;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 10px 20px 10px 45px;
            color: var(--text-primary);
            font-family: var(--font-ui);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.3s;
        }
        .nav-search input:focus {
            border-color: var(--primary-accent);
        }
        .nav-search input::placeholder {
            color: var(--text-secondary);
        }
        /* Style for the icon button */
        .nav-actions .btn svg {
             width: 1.25rem; /* Adjust size as needed */
             height: 1.25rem;
        }


        /* Main Community Content */
        main {
            padding: 40px 0;
        }
        .community-feed {
            max-width: 700px;
            margin: 0 auto;
        }

        /* Create Post Card */
        .create-post-card {
            background-color: var(--bg-light);
            padding: 15px 20px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .create-post-avatar img,
        .create-post-avatar svg { /* Allow SVG */
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        .create-post-input {
            flex-grow: 1;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 12px 20px;
            color: var(--text-primary);
            font-family: var(--font-ui);
            font-size: 1rem;
            outline: none;
        }
        .create-post-input:focus {
            border-color: var(--primary-accent);
        }
        .create-post-input:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Post Card */
        .post-card {
            background-color: var(--bg-light);
            border-radius: 15px;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            padding: 20px;
        }
        .post-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }
        .post-author-info {
            flex-grow: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .post-author-avatar img,
        .post-author-avatar svg { /* Allow SVG */
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        .post-author-details .name {
            font-weight: bold;
            color: var(--text-primary);
        }
        .post-author-details .timestamp {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .post-body p {
            color: var(--text-secondary);
            line-height: 1.7;
            white-space: pre-wrap;
            margin-bottom: 15px;
            min-height: 24px; /* กัน layout กระตุกตอนแก้ไข */
        }
        .post-body p[contenteditable="true"] {
            outline: 1px solid var(--primary-accent);
            background-color: var(--bg-dark);
            border-radius: 4px;
            padding: 4px 8px;
            margin: 0 0 15px 0;
            cursor: text;
        }

        .post-options {
            position: relative;
        }
        .options-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .options-btn:hover {
            background-color: var(--secondary-accent);
        }
        .options-dropdown {
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 10;
            overflow: hidden;
            width: 250px;
            display: none;
            padding: 8px;
        }
        .options-dropdown.show {
            display: block;
        }
        .options-dropdown-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none; /* สำหรับ <a> tag */
        }
        .options-dropdown-item:hover {
            background-color: var(--secondary-accent);
        }
        .item-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            flex-shrink: 0;
        }
        .item-icon svg {
            width: 20px;
            height: 20px;
        }
        .item-text {
             width: 100%;
        }
        .item-title {
            font-size: 0.95rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        .item-subtitle {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .dropdown-separator {
            height: 1px;
            background-color: var(--border-color);
            margin: 8px 0;
        }
        .item-title.delete {
            color: var(--danger-color);
        }

        .post-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            font-size: 0.9rem;
            color: var(--text-secondary);
            min-height: 20px;
        }
        .reactions-summary-wrapper, .useful-stats-wrapper {
            position: relative;
        }
        .reactions-summary, .useful-stats {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .reactions-summary .reaction-icon {
            font-size: 1rem;
            margin-left: -4px;
        }
        .reactions-summary .total-likes {
            margin-left: 8px;
        }
        .stats-tooltip {
            position: absolute;
            bottom: 100%;
            left: 0;
            margin-bottom: 8px;
            background-color: rgba(18, 24, 40, 0.95);
            border-radius: 8px;
            padding: 5px 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            width: max-content;
            min-width: 150px;
            max-width: 250px;
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s ease-in-out;
            pointer-events: none; /* Prevent tooltip from blocking hover */
        }
        .reactions-summary-wrapper:hover .stats-tooltip,
        .useful-stats-wrapper:hover .stats-tooltip {
            opacity: 0.95;
            visibility: visible;
            transform: translateY(0);
        }
        .stats-tooltip ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .stats-tooltip li {
            padding: 1px 0;
            font-size: 0.75rem;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .useful-stats {
            gap: 6px;
        }
        .useful-stats .useful-icon {
            color: var(--primary-accent);
            font-size: 0.9rem;
        }

        .post-actions {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-top: 1px solid var(--border-color);
        }
        .action-btn-wrapper {
            position: relative;
        }
        .action-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-ui);
            font-size: 0.9rem;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
        }
        .action-btn:hover {
            background-color: var(--secondary-accent);
        }
        .action-btn.liked {
            font-weight: bold;
        }
        .action-btn.useful-btn.active {
            font-weight: bold;
            color: var(--primary-accent);
        }
        .action-btn .useful-icon {
            font-size: 1.1rem;
        }
        .like-icon-reaction {
            font-size: 1.2rem;
            line-height: 1;
        }
        .reactions-container {
            position: absolute;
            bottom: 100%;
            left: -10px;
            margin-bottom: 10px;
            background-color: var(--bg-dark);
            padding: 8px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            gap: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px) scale(0.9);
            transition: all 0.2s ease-in-out;
            z-index: 10;
        }
        .action-btn-wrapper:hover .reactions-container {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
        .reaction-emoji {
            font-size: 1.8rem;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-origin: bottom;
        }
        .reaction-emoji:hover {
            transform: scale(1.3) translateY(-5px);
        }

        .post-comments {
            margin-top: 10px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        .comment {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .comment-avatar img,
        .comment-avatar svg { /* Allow SVG */
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        .comment-content {
            background-color: var(--bg-dark);
            padding: 10px 15px;
            border-radius: 12px;
            width: 100%;
            position: relative;
        }
        .comment-author {
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .comment-text {
            font-size: 0.95rem;
            color: var(--text-secondary);
            white-space: pre-wrap; /* Show newlines */
            word-wrap: break-word; /* Break long words */
        }
        .comment-text[contenteditable="true"] {
            outline: 1px solid var(--primary-accent);
            background-color: var(--bg-light);
            border-radius: 4px;
            padding: 2px 4px;
            margin: -3px -5px;
            cursor: text;
        }
        .comment-controls {
            position: absolute;
            top: 5px;
            right: 8px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
            background-color: var(--bg-dark); /* Add bg to cover text */
            padding: 2px 0 2px 4px;
            border-radius: 12px;
        }
        .comment-content:hover .comment-controls {
            opacity: 0.7;
        }
        .comment-controls:hover {
             opacity: 1; /* Make controls fully visible when hovering them */
        }
        .comment-control-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 2px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }
        .comment-control-btn:hover {
            background-color: var(--secondary-accent);
            opacity: 1;
            color: var(--text-primary);
        }
        .comment-control-btn.comment-block-btn:hover {
            color: var(--danger-color);
        }
        .comment-delete-btn {
            font-size: 1.4rem;
            font-weight: bold;
        }
        .comment-edit-btn svg, .comment-block-btn svg {
            width: 14px;
            height: 14px;
        }

        .add-comment {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .add-comment img,
        .add-comment svg { /* Allow SVG */
             width: 35px;
             height: 35px;
             border-radius: 50%;
             object-fit: cover;
        }
        .add-comment input {
            flex-grow: 1;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 15px;
            color: var(--text-primary);
            font-family: var(--font-ui);
            outline: none;
        }
        .add-comment input:focus {
            border-color: var(--primary-accent);
        }
        .add-comment input:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        #loader {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
            display: none;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(18, 24, 40, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            background-color: var(--bg-light);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 90%;
            max-width: 500px;
            transform: scale(0.95);
            transition: transform 0.3s;
        }
        .modal-overlay.show .modal-content {
            transform: scale(1);
        }
        .modal-content h3 {
            margin-top: 0;
            color: var(--text-primary);
        }
        .modal-content p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        .modal-actions {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        @media (max-width: 600px) {
            .nav-search {
                display: none;
            }
            nav.navbar {
                justify-content: space-between;
            }
            .logo {
                margin-right: auto;
            }
            .modal-content {
                 width: 95%;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="container">
            <nav class="navbar">
                <a href="{{url('/')}}" class="logo">NovelNoob</a>
                <div class="nav-search">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                    {{-- *** NEW: Add ID and prefill value *** --}}
                    <input type="text" id="community-search-input" placeholder="ค้นหาในชุมชน..." value="{{ request('search') }}">
                </div>
                <div class="nav-actions">
                    {{-- *** MODIFIED: Replaced text with SVG icon *** --}}
                    <a href="{{route('dashboard.index')}}" class="btn btn-primary" title="Dashboard">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </a>
                    {{-- Display name if logged in --}}
  
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="community-feed">

                <!-- Create Post Card -->
                <div class="create-post-card">
                    <div class="create-post-avatar">
                        {{-- Use JS prepared avatar or default --}}
                        {{-- *** MODIFIED: Check for SVG data URL *** --}}
                        @if(Str::startsWith($currentUserJs['avatar'] ?? '', 'data:image/svg+xml'))
                            {!! base64_decode(explode(',', $currentUserJs['avatar'])[1] ?? '') !!}
                        @else
                            <img id="create-post-avatar" src="{{ $currentUserJs['avatar'] ?? 'https://placehold.co/100x100/A9B4D9/121828?text=G' }}" alt="Your Avatar">
                        @endif
                    </div>
                    <input type="text" class="create-post-input" id="create-post-input" placeholder="เขียนอะไรสักหน่อย...">
                </div>

                <!-- Post Feed Container -->
                <div id="post-feed-container">
                    <!-- Posts are rendered here by JavaScript -->
                </div>

                <!-- Loader for infinite scroll -->
                <div id="loader">
                    <p>กำลังโหลดโพสต์เพิ่มเติม...</p>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Novel Noob. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Block Confirmation Modal -->
    <div id="block-confirmation-modal" class="modal-overlay">
        <div class="modal-content">
            <h3>ยืนยันการบล็อกผู้ใช้</h3>
            <p>คุณแน่ใจหรือไม่ว่าต้องการบล็อกผู้ใช้นี้? คอมเมนต์ทั้งหมดของผู้ใช้นี้ในโพสต์ *ทั้งหมดของคุณ* จะถูกซ่อน (จากทุกคน) และผู้ใช้นี้จะไม่สามารถแสดงความคิดเห็นในโพสต์ของคุณได้อีก</p>
            <div class="modal-actions">
                <button id="cancel-block-btn" class="btn btn-secondary">ยกเลิก</button>
                <button id="confirm-block-btn" class="btn btn-danger">ยืนยันการบล็อก</button>
            </div>
        </div>
    </div>

    <!-- Generic Error Modal -->
    <div id="error-modal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="error-modal-title">เกิดข้อผิดพลาด</h3>
            <p id="error-modal-message">ขออภัย, เกิดข้อผิดพลาดที่ไม่คาดคิด</p>
            <div class="modal-actions">
                <button id="close-error-modal-btn" class="btn btn-primary">ตกลง</button>
            </div>
        </div>
    </div>

    {{-- Pass PHP data to JS --}}
    <script>
        let allMockPosts = @json($jsPosts);
        const currentUser = @json($currentUserJs);
        const currentUserId = currentUser ? currentUser.id : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>

    <script>
        // --- Helper Function ---
        function showModal(id, title = null, message = null) {
            const modal = document.getElementById(id);
            if (!modal) return;

            if (title) {
                 const modalTitle = modal.querySelector('.modal-content h3') || modal.querySelector('#error-modal-title');
                 if(modalTitle) modalTitle.textContent = title;
            }
            if (message) {
                 const modalMessage = modal.querySelector('.modal-content p') || modal.querySelector('#error-modal-message');
                 if(modalMessage) modalMessage.textContent = message;
            }

            modal.classList.add('show');
        }

        function hideModal(id) {
             const modal = document.getElementById(id);
             if (modal) modal.classList.remove('show');
        }

        function escapeHTML(str) {
             if (typeof str !== 'string') return '';
             return str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }


        document.addEventListener('DOMContentLoaded', () => {
            const postInput = document.getElementById('create-post-input');
            const postAvatar = document.getElementById('create-post-avatar'); // We already set the src in Blade
            const postFeedContainer = document.getElementById('post-feed-container');
            const loader = document.getElementById('loader');
            // *** NEW: Get search input element ***
            const searchInput = document.getElementById('community-search-input');


            // Modals
            const blockModal = document.getElementById('block-confirmation-modal');
            const cancelBlockBtn = document.getElementById('cancel-block-btn');
            const confirmBlockBtn = document.getElementById('confirm-block-btn');
            const errorModal = document.getElementById('error-modal');
            const closeErrorModalBtn = document.getElementById('close-error-modal-btn');

            let userToBlock = null; // Store { element, userId }

            // --- Infinite Scroll State ---
            let currentPostIndex = 0;
            const postsPerLoad = 10; // How many posts to render initially
            let isLoading = false;
            let observedTarget = null;

            // --- Core Functions ---
            function getReactionInfo(emoji) {
                const reactions = {
                    '👍': { text: 'ถูกใจ', color: 'var(--primary-accent)' }, '❤️': { text: 'รักเลย', color: '#ef4444' },
                    '😂': { text: 'ฮา', color: '#facc15' }, '😮': { text: 'ว้าว', color: '#facc15' },
                    '😢': { text: 'เศร้า', color: '#3b82f6' }, '😠': { text: 'โกรธ', color: '#f97316' }
                };
                return reactions[emoji] || { text: 'ถูกใจ', color: 'var(--text-secondary)' };
            }

            // *** NEW: Helper to render avatar (img or svg) ***
            function renderAvatar(avatarData) {
                if (avatarData.startsWith('data:image/svg+xml')) {
                    // It's a Base64 SVG data URL
                    try {
                        const base64Svg = avatarData.split(',')[1];
                        if (base64Svg) {
                            // Decode and return the raw SVG string
                            return atob(base64Svg); 
                        }
                    } catch (e) {
                        console.error("Error decoding SVG data URL", e);
                    }
                }
                // Fallback for regular URLs (placehold.co or uploaded files)
                return `<img src="${escapeHTML(avatarData)}" alt="Avatar">`;
            }


            function createCommentElement(commentData, postData) {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.dataset.commentId = commentData.id; // Store comment ID

                const escapedText = escapeHTML(commentData.text);

                // --- Logic for comment controls ---
                let controlsHtml = '';
                const isCommentOwner = currentUser && currentUser.id === commentData.author_id;
                const isPostOwner = postData.is_owner;

                if (isCommentOwner) {
                    // 1. เจ้าของคอมเมนต์ (ไม่ว่าจะใช่เจ้าของโพสต์หรือไม่)
                    controlsHtml = `
                        <button class="comment-edit-btn comment-control-btn" title="แก้ไขคอมเมนต์">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/></svg>
                        </button>
                        <button class="comment-delete-btn comment-control-btn" title="ลบคอมเมนต์">&times;</button>
                    `;
                } else if (isPostOwner && currentUser) { // Also check if current user exists
                    // 2. เจ้าของโพสต์ (แต่ไม่ใช่เจ้าของคอมเมนต์)
                    controlsHtml = `
                        <button class="comment-block-btn comment-control-btn" title="บล็อกผู้ใช้" data-user-id="${commentData.author_id}">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-slash" viewBox="0 0 16 16"><path d="M13.879 10.414a2.502 2.502 0 0 0-3.465 3.465l3.465-3.465Zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465Zm-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z"/></svg>
                        </button>
                        <button class="comment-delete-btn comment-control-btn" title="ลบคอมเมนต์">&times;</button>
                    `;
                }
                // 3. คนทั่วไป (ไม่ใช่เจ้าของโพสต์ หรือ เจ้าของคอมเมนต์) - controlsHtml = '' (ว่าง)

                commentDiv.innerHTML = `
                    <div class="comment-avatar">${renderAvatar(commentData.author.avatar)}</div>
                    <div class="comment-content">
                        <div class="comment-controls">${controlsHtml}</div>
                        <div class="comment-author">${escapeHTML(commentData.author.name)}</div>
                        <p class="comment-text">${escapedText}</p>
                    </div>`;
                return commentDiv;
            }

            function createPostElement(postData) {
                // console.log("Creating/Replacing element with data:", postData); // Debug line added
                const postCard = document.createElement('div');
                postCard.className = 'post-card';
                // *** Use actual ID if available, otherwise use temp ID ***
                postCard.dataset.postId = postData.id;
                postCard.dataset.reactions = postData.reactions;
                postCard.dataset.usefulUsers = postData.usefulUsers;
                // Store names for tooltips
                postCard.dataset.likerNames = JSON.stringify(postData.likerNames);
                postCard.dataset.usefulUserNames = JSON.stringify(postData.usefulUserNames);

                const escapedPostText = escapeHTML(postData.content);

                // --- Logic for post options ---
                let optionsDropdownHtml = '';
                 // *** DEBUG: Explicitly log the value being checked ***
                // console.log(`Checking is_owner: ${postData.is_owner} (Type: ${typeof postData.is_owner}) for post ID: ${postData.id}`);
                if (postData.is_owner) { // Check if is_owner is truthy
                    // console.log(`-> Rendering OWNER dropdown for post ID: ${postData.id}`); // Debug line
                    // 1. เจ้าของโพสต์
                    optionsDropdownHtml = `
                        <div class="options-dropdown-item edit-post-btn">
                            <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg></div>
                            <div class="item-text"><div class="item-title">แก้ไขโพสต์</div><div class="item-subtitle">ปรับแก้เนื้อหาในโพสต์นี้</div></div>
                        </div>
                        <div class="options-dropdown-item delete-post-btn">
                            <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg></div>
                            <div class="item-text"><div class="item-title delete">ลบโพสต์</div><div class="item-subtitle">โพสต์นี้จะถูกลบอย่างถาวร</div></div>
                        </div>
                    `;
                } else if (currentUser) { // Use 'else if' to ensure this only runs if NOT the owner AND logged in
                    // console.log(`-> Rendering NON-OWNER dropdown for post ID: ${postData.id}`); // Debug line
                    // 2. ไม่ใช่เจ้าของโพสต์ (และ Login อยู่)
                     optionsDropdownHtml = `
                        <div class="options-dropdown-item report-post-btn">
                            <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" /></svg></div>
                            <div class="item-text"><div class="item-title">รายงานโพสต์</div><div class="item-subtitle">แจ้งให้เราทราบหากโพสต์นี้มีปัญหา</div></div>
                        </div>
                    `;
                } else {
                     // console.log(`-> Rendering NO dropdown options (Guest) for post ID: ${postData.id}`); // Debug line
                }
                 // Implicit else: Guest user who is not owner sees nothing in this section

                // 'แสดงโพสต์' มีสำหรับทุกคน (Use actual post ID if available)
                const showPostUrl = postData.id.toString().startsWith('temp-') ? '#' : `/community/single-post/${postData.id}`;
                const showPostHtml = `
                    <a href="${showPostUrl}" class="options-dropdown-item ${postData.id.toString().startsWith('temp-') ? 'disabled' : ''}" ${postData.id.toString().startsWith('temp-') ? 'style="pointer-events: none; opacity: 0.5;"' : ''}>
                        <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5 0V6.375c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v1.5m-4.5 0h4.5" /></svg></div>
                        <div class="item-text"><div class="item-title">แสดงโพสต์</div><div class="item-subtitle">ดูโพสต์นี้ในหน้าของตัวเอง</div></div>
                    </a>
                `;

                const separatorHtml = (optionsDropdownHtml !== '') ? '<div class="dropdown-separator"></div>' : '';


                postCard.innerHTML = `
                    <div class="post-header">
                        <div class="post-author-info">
                            <div class="post-author-avatar">${renderAvatar(postData.avatar)}</div>
                            <div class="post-author-details">
                                <div class="name">${escapeHTML(postData.author)}</div>
                                <div class="timestamp">${postData.timestamp}</div>
                            </div>
                        </div>
                        ${(showPostHtml + separatorHtml + optionsDropdownHtml) ? `
                        <div class="post-options">
                            <button class="options-btn">
                               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </button>
                            <div class="options-dropdown">
                                ${showPostHtml}
                                ${separatorHtml}
                                ${optionsDropdownHtml}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    <div class="post-body"><p>${escapedPostText}</p></div>
                    <div class="post-stats">
                        <div class="reactions-summary-wrapper">
                             <div class="reactions-summary">
                                <!-- Icons added by updatePostStats -->
                                <span class="total-likes"></span>
                            </div>
                            <div class="likers-tooltip stats-tooltip">
                                <ul><!-- Names added by updatePostStats --></ul>
                            </div>
                        </div>
                        <div class="useful-stats-wrapper">
                            <div class="useful-stats" style="display: none;"> <!-- Hidden by default -->
                                <span class="useful-count"></span>
                                <span class="useful-icon">💎</span>
                            </div>
                            <div class="useful-tooltip stats-tooltip">
                                <ul><!-- Names added by updateUsefulStats --></ul>
                            </div>
                        </div>
                    </div>
                    <div class="post-actions">
                        <div class="action-btn-wrapper">
                            <div class="reactions-container">
                                <span class="reaction-emoji" data-reaction="👍">👍</span><span class="reaction-emoji" data-reaction="❤️">❤️</span>
                                <span class="reaction-emoji" data-reaction="😂">😂</span><span class="reaction-emoji" data-reaction="😮">😮</span>
                                <span class="reaction-emoji" data-reaction="😢">😢</span><span class="reaction-emoji" data-reaction="😠">😠</span>
                            </div>
                            <button class="action-btn like-btn"><span class="like-icon-reaction"></span><span class="action-text">ถูกใจ</span></button>
                        </div>
                        <button class="action-btn useful-btn"><span class="useful-icon">💎</span><span>มีประโยชน์</span></button>
                    </div>
                    <div class="post-comments">
                        <div class="add-comment">
                            <div class="comment-avatar" id="add-comment-avatar-wrapper">
                                {{-- Avatar added by JS --}}
                            </div>
                            <input type="text" id="add-comment-input" placeholder="แสดงความคิดเห็น...">
                        </div>
                    </div>`;

                const commentsContainer = postCard.querySelector('.post-comments');
                const addCommentDiv = commentsContainer.querySelector('.add-comment');
                const commentAvatarWrapper = addCommentDiv.querySelector('#add-comment-avatar-wrapper');

                // Add current user avatar to comment input (if logged in)
                const commentInput = addCommentDiv.querySelector('input');
                if (currentUser) {
                     commentAvatarWrapper.innerHTML = renderAvatar(currentUser.avatar);
                } else {
                    commentAvatarWrapper.innerHTML = renderAvatar('https://placehold.co/100x100/A9B4D9/121828?text=G');
                    commentInput.placeholder = "กรุณาล็อกอินเพื่อแสดงความคิดเห็น...";
                    commentInput.disabled = true;
                }

                if (postData.comments && postData.comments.length > 0) {
                    postData.comments.forEach(commentData => {
                        const commentElement = createCommentElement(commentData, postData);
                        commentsContainer.insertBefore(commentElement, addCommentDiv);
                    });
                }

                updatePostStats(postCard);
                updateUsefulStats(postCard);
                updateLikeButtonState(postCard);
                updateUsefulButtonState(postCard);

                return postCard;
            }

            // --- DEPRECATED: Infinite scroll not needed for initial 30 posts ---
            /*
            function loadMorePosts() {
                // ... original loadMorePosts logic ...
            }
            */

            // --- Stats and Actions Update Functions ---
            function updatePostStats(postCard) {
                 const statsContainer = postCard.querySelector('.post-stats');
                 const totalLikesSpan = statsContainer.querySelector('.total-likes');
                 const summaryContainer = statsContainer.querySelector('.reactions-summary');
                 const tooltipUl = postCard.querySelector('.likers-tooltip ul');

                 // Ensure dataset exists before parsing
                 const reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                 const likerNames = (postCard.dataset.likerNames && postCard.dataset.likerNames !== '[]') ? JSON.parse(postCard.dataset.likerNames) : [];


                 let totalLikes = likerNames.length;
                 let uniqueReactions = new Set(Object.values(reactions));

                 totalLikesSpan.textContent = totalLikes > 0 ? totalLikes : '';

                 // Clear existing icons before adding new ones
                 summaryContainer.querySelectorAll('.reaction-icon').forEach(icon => icon.remove());
                 uniqueReactions.forEach(emoji => {
                     const iconSpan = document.createElement('span');
                     iconSpan.className = 'reaction-icon';
                     iconSpan.textContent = emoji;
                     summaryContainer.prepend(iconSpan); // Prepend to show most common first potentially
                 });

                 // Update tooltip
                 tooltipUl.innerHTML = ''; // Clear previous names
                 likerNames.slice(0, 10).forEach(name => {
                     const li = document.createElement('li');
                     li.textContent = escapeHTML(name);
                     tooltipUl.appendChild(li);
                 });

                 if (totalLikes > 10) {
                     const li = document.createElement('li');
                     li.textContent = `และอีก ${totalLikes - 10} คน...`;
                     tooltipUl.appendChild(li);
                 }
             }

            function updateUsefulStats(postCard) {
                const usefulStats = postCard.querySelector('.useful-stats');
                const usefulCountSpan = usefulStats.querySelector('.useful-count');
                const tooltipUl = postCard.querySelector('.useful-tooltip ul');

                 // Ensure dataset exists before parsing
                const usefulUserNames = (postCard.dataset.usefulUserNames && postCard.dataset.usefulUserNames !== '[]') ? JSON.parse(postCard.dataset.usefulUserNames) : [];
                const count = usefulUserNames.length;


                if (count > 0) {
                    usefulCountSpan.textContent = count;
                    usefulStats.style.display = 'flex';
                } else {
                    usefulCountSpan.textContent = '';
                    usefulStats.style.display = 'none';
                }

                // Update tooltip for useful
                 tooltipUl.innerHTML = ''; // Clear previous names
                 usefulUserNames.slice(0, 10).forEach(name => {
                     const li = document.createElement('li');
                     li.textContent = escapeHTML(name);
                     tooltipUl.appendChild(li);
                 });

                 if (count > 10) {
                     const li = document.createElement('li');
                     li.textContent = `และอีก ${count - 10} คน...`;
                     tooltipUl.appendChild(li);
                 }
            }

            function updateLikeButtonState(postCard) {
                if (!currentUser) return;

                const likeBtn = postCard.querySelector('.like-btn');
                const reactionIconSpan = likeBtn.querySelector('.like-icon-reaction');
                const actionTextSpan = likeBtn.querySelector('.action-text');
                 // Ensure dataset exists before parsing
                const reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                const myReaction = reactions[currentUserId];

                if (myReaction) {
                    likeBtn.classList.add('liked');
                    reactionIconSpan.textContent = myReaction;
                    const reactionInfo = getReactionInfo(myReaction);
                    actionTextSpan.textContent = 'ถูกใจ'; // Or reactionInfo.text
                    likeBtn.style.color = reactionInfo.color;
                } else {
                    likeBtn.classList.remove('liked');
                    reactionIconSpan.textContent = ''; // Or a default icon like <i class="far fa-thumbs-up"></i>
                    actionTextSpan.textContent = 'ถูกใจ';
                    likeBtn.style.color = 'var(--text-secondary)';
                }
            }

            function updateUsefulButtonState(postCard) {
                if (!currentUser) return;

                const usefulBtn = postCard.querySelector('.useful-btn');
                // Ensure dataset exists before parsing
                const usefulUsers = (postCard.dataset.usefulUsers && postCard.dataset.usefulUsers !== '[]') ? JSON.parse(postCard.dataset.usefulUsers) : {};


                if (usefulUsers[currentUserId]) {
                    usefulBtn.classList.add('active');
                } else {
                    usefulBtn.classList.remove('active');
                }
            }

            // --- Event Handlers ---

            // *** NEW: Handle Search Input ***
            if(searchInput) {
                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault(); // Prevent form submission if it's in a form
                        const searchTerm = searchInput.value.trim();
                        const currentUrl = new URL(window.location.href);

                        if (searchTerm) {
                            currentUrl.searchParams.set('search', searchTerm);
                        } else {
                            currentUrl.searchParams.delete('search'); // Remove search param if input is empty
                        }
                        // Reload the page with the new search parameter
                        window.location.href = currentUrl.toString();
                    }
                });
            }


            // Handle Create Post
            postInput.addEventListener('keydown', async (event) => {
                if (event.key === 'Enter' && postInput.value.trim() !== '') {
                    event.preventDefault();
                    if (!currentUser) {
                         window.location.href = '{{ route("login") }}';
                         return;
                    }
                    // Use the flag passed from PHP
                    if (!currentUser.is_writer) {
                        showModal('error-modal', 'ไม่ได้รับอนุญาต', 'คุณต้องเป็นนักเขียนจึงจะสามารถสร้างโพสต์ได้');
                        return;
                    }

                    const content = postInput.value.trim();
                    postInput.disabled = true; // Disable input during request

                    // *** Optimistic Update ***
                    const tempId = `temp-${Date.now()}`;
                    const tempPostData = {
                        id: tempId, // Temporary ID
                        author: currentUser.name,
                        avatar: currentUser.avatar,
                        timestamp: 'เมื่อสักครู่',
                        content: content,
                        reactions: JSON.stringify({}),
                        usefulUsers: JSON.stringify({}),
                        comments: [],
                        likerNames: [],
                        usefulUserNames: [],
                        is_owner: true // Explicitly true for optimistic update
                    };
                    const tempPostElement = createPostElement(tempPostData);
                    postFeedContainer.prepend(tempPostElement);
                    allMockPosts.unshift(tempPostData); // Add temp data to local array
                    postInput.value = ''; // Clear input

                    try {
                        const response = await fetch(`{{ route('community.posts.store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ content: content })
                        });

                        if (response.status === 401) { // Unauthorized (not logged in)
                            window.location.href = '{{ route("login") }}'; // Redirect to login
                             tempPostElement.remove(); // Remove temp element
                             allMockPosts.shift(); // Remove temp data
                            return;
                        }

                        if (!response.ok) {
                             const errorData = await response.json();
                             let errorMessage = 'ไม่สามารถสร้างโพสต์ได้';
                             if (response.status === 403) { // Forbidden (e.g., not a writer)
                                  errorMessage = errorData.message || 'คุณไม่มีสิทธิ์สร้างโพสต์';
                             } else if (response.status === 422) { // Validation error
                                 const firstError = Object.values(errorData.errors)[0][0];
                                 errorMessage = firstError || 'กรุณาตรวจสอบข้อมูลที่กรอก';
                             } else {
                                  errorMessage = errorData.message || `Server error: ${response.status}`;
                             }
                             throw new Error(errorMessage); // Throw error to be caught below
                        } else {
                            // Success!
                            const newPostData = await response.json(); // Data from server

                            // *** Replace temp element with new one from server data ***
                            const finalPostElement = createPostElement(newPostData);
                            tempPostElement.replaceWith(finalPostElement); // Replace in DOM

                            // Update the entry in allMockPosts array
                            const tempIndex = allMockPosts.findIndex(p => p.id === tempId);
                            if (tempIndex > -1) {
                                allMockPosts[tempIndex] = newPostData;
                            } else {
                                // Fallback if temp data wasn't found (shouldn't happen)
                                allMockPosts.unshift(newPostData);
                            }
                            // No need to clear input, already done
                        }

                    } catch (error) {
                        console.error('Error creating post:', error);
                        showModal('error-modal', 'เกิดข้อผิดพลาด', error.message || 'ไม่สามารถสร้างโพสต์ได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง');
                        // Remove the optimistic post if server failed
                         // Find the temp element again in case it was replaced somehow (unlikely here)
                        const elementToRemove = postFeedContainer.querySelector(`[data-post-id="${tempId}"]`);
                        if(elementToRemove) elementToRemove.remove();

                        // Remove temp data from array using tempId
                        allMockPosts = allMockPosts.filter(p => p.id !== tempId);
                        postInput.value = content; // Restore content
                    } finally {
                         postInput.disabled = false; // Re-enable input
                         postInput.focus();
                    }
                }
            });

            // Close dropdowns if clicked outside
            window.addEventListener('click', function(e) {
                document.querySelectorAll('.options-dropdown.show').forEach(dropdown => {
                    // Check if the click is outside the dropdown AND outside its button
                    if (!dropdown.contains(e.target) && dropdown.previousElementSibling && !dropdown.previousElementSibling.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });

            // Handle Add Comment
            postFeedContainer.addEventListener('keydown', async (event) => {
                const commentInput = event.target;
                if (commentInput.matches('.add-comment input') && event.key === 'Enter' && commentInput.value.trim() !== '') {
                    event.preventDefault();
                    if (!currentUser) {
                         window.location.href = '{{ route("login") }}';
                         return;
                    }

                    const content = commentInput.value.trim();
                    const postCard = commentInput.closest('.post-card');
                    const postId = postCard.dataset.postId;
                    const addCommentContainer = commentInput.parentElement;

                    // Ensure postId is valid before proceeding
                    if (!postId || postId.startsWith('temp-')) {
                        console.warn("Attempted to comment on a temporary or invalid post ID.");
                        showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถแสดงความคิดเห็นบนโพสต์นี้ได้');
                        return;
                    }

                    commentInput.disabled = true; // Disable input

                    // Optimistic Update
                    const tempCommentId = `temp-${Date.now()}`;
                    const tempCommentData = {
                        id: tempCommentId, // Temporary ID
                        author: currentUser, // Use current user data passed from PHP
                        text: content,
                        author_id: currentUserId,
                    };
                    // Find the correct postData from our local array to pass for permission checks
                    const postDataForComment = allMockPosts.find(p => p.id == postId);
                    const newCommentElement = createCommentElement(tempCommentData, postDataForComment); // Pass postData
                    addCommentContainer.parentElement.insertBefore(newCommentElement, addCommentContainer);
                    commentInput.value = ''; // Clear input immediately

                    try {
                        const response = await fetch(`/community/posts/${postId}/comments`, {
                             method: 'POST',
                             headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                             },
                             body: JSON.stringify({ content: content })
                        });

                        if (response.status === 401) {
                            window.location.href = '{{ route("login") }}';
                             newCommentElement.remove(); // Remove optimistic comment
                            return; // Stop execution
                        }
                         if (response.status === 403) { // Forbidden (e.g., blocked)
                             const errorData = await response.json();
                             throw new Error(errorData.error || 'คุณไม่มีสิทธิ์แสดงความคิดเห็นบนโพสต์นี้');
                         }
                        if (!response.ok) {
                             const errorData = await response.json();
                             if(response.status === 422) {
                                 const firstError = Object.values(errorData.errors)[0][0];
                                 throw new Error(firstError || 'ข้อมูลความคิดเห็นไม่ถูกต้อง');
                             }
                            throw new Error(errorData.message || `Failed to post comment: ${response.status}`);
                        }

                        // Success! Update temp comment with real data
                        const savedCommentData = await response.json();
                        newCommentElement.dataset.commentId = savedCommentData.id;
                        // Update the temp data object in the local array (needed if editing temp comment later)
                        const postIndex = allMockPosts.findIndex(p => p.id == postId);
                        if(postIndex > -1) {
                            // Ensure comments array exists
                            if(!allMockPosts[postIndex].comments) allMockPosts[postIndex].comments = [];
                            const tempCommentIndex = allMockPosts[postIndex].comments.findIndex(c => c.id === tempCommentId);
                            if(tempCommentIndex > -1) {
                                allMockPosts[postIndex].comments[tempCommentIndex] = savedCommentData; // Replace temp comment data
                            } else {
                                // Fallback: Add if not found (shouldn't happen with prepend)
                                allMockPosts[postIndex].comments.push(savedCommentData);
                            }
                        }


                    } catch (error) {
                        console.error('Error posting comment:', error);
                        showModal('error-modal', 'เกิดข้อผิดพลาด', error.message || 'ไม่สามารถส่งความคิดเห็นได้');
                        newCommentElement.remove(); // Remove optimistic comment on failure
                        commentInput.value = content; // Restore input content
                    } finally {
                        commentInput.disabled = false; // Re-enable input
                        commentInput.focus();
                    }
                }
            });

            // --- New AJAX Handlers for Reaction & Useful ---

            async function sendReaction(postCard, reactionEmoji) {
                if (!currentUser) {
                    window.location.href = '{{ route("login") }}';
                    return;
                }

                const postId = postCard.dataset.postId;
                if (!postId || postId.startsWith('temp-')) return; // Cannot react to temp posts

                // --- Optimistic UI Update ---
                const likeBtn = postCard.querySelector('.like-btn');
                const reactionIconSpan = likeBtn.querySelector('.like-icon-reaction');
                const actionTextSpan = likeBtn.querySelector('.action-text');
                let reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '{}' && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                let likerNames = (postCard.dataset.likerNames && postCard.dataset.likerNames !== '[]') ? JSON.parse(postCard.dataset.likerNames) : [];

                const currentReaction = reactions[currentUserId];
                const currentUserName = currentUser.name;
                const userIndex = likerNames.indexOf(currentUserName);

                // Determine the next state optimistically
                let nextReaction = null;
                if (currentReaction === reactionEmoji) { // Toggling off
                    nextReaction = null;
                } else { // Liking or changing
                    nextReaction = reactionEmoji;
                }

                // Update UI based on next state
                if (nextReaction === null) {
                    // --- Unlike ---
                    delete reactions[currentUserId];
                    if (userIndex > -1) likerNames.splice(userIndex, 1);
                    likeBtn.classList.remove('liked');
                    reactionIconSpan.textContent = '';
                    likeBtn.style.color = 'var(--text-secondary)';
                    actionTextSpan.textContent = 'ถูกใจ';
                } else {
                    // --- Like or Change Reaction ---
                    reactions[currentUserId] = nextReaction;
                    if (userIndex === -1) likerNames.unshift(currentUserName); // Add if not already there
                    const reactionInfo = getReactionInfo(nextReaction);
                    likeBtn.classList.add('liked');
                    reactionIconSpan.textContent = nextReaction;
                    likeBtn.style.color = reactionInfo.color;
                    actionTextSpan.textContent = 'ถูกใจ'; // Keep text as 'Like'
                }


                // Update dataset and UI immediately
                postCard.dataset.reactions = JSON.stringify(reactions);
                postCard.dataset.likerNames = JSON.stringify(likerNames);
                updatePostStats(postCard);
                // updateLikeButtonState(postCard); // updatePostStats handles the count/icons, this handles the button color/text

                // --- Send AJAX ---
                try {
                    const response = await fetch(`/community/posts/${postId}/react`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        // Send the emoji that was *clicked*, server handles toggling logic
                        body: JSON.stringify({ reaction_type: reactionEmoji }) // Send the clicked emoji
                    });

                    if (response.status === 401) {
                        window.location.href = '{{ route("login") }}';
                        return;
                    }
                    if (!response.ok) {
                         const errorData = await response.json();
                         throw new Error(errorData.error || `Reaction failed: ${response.status}`);
                    }

                    const data = await response.json();

                    // --- Re-sync with server data (important for consistency) ---
                    postCard.dataset.reactions = data.reactions; // Update with latest from server
                    postCard.dataset.likerNames = JSON.stringify(data.likerNames); // Update names from server
                    updatePostStats(postCard); // Re-render stats based on server response
                    updateLikeButtonState(postCard); // Ensure button state matches server

                } catch (error) {
                    console.error('Error sending reaction:', error);
                    showModal('error-modal', 'เกิดข้อผิดพลาด', error.message || 'ไม่สามารถส่ง Reaction ได้');
                    // --- Revert UI on failure ---
                    // Revert optimistic changes
                    if (nextReaction === null) { // We tried to unlike, but failed, so re-like
                        reactions[currentUserId] = currentReaction; // Put the original back
                         if (userIndex === -1 && currentReaction) likerNames.unshift(currentUserName);
                    } else { // We tried to like/change, but failed
                        if (currentReaction) reactions[currentUserId] = currentReaction; // Revert to old one
                        else delete reactions[currentUserId]; // Remove if it was a new like that failed

                        if (userIndex === -1 && !currentReaction) { // It was a new like that failed
                            const revertIndex = likerNames.indexOf(currentUserName);
                            if(revertIndex > -1) likerNames.splice(revertIndex, 1);
                        }
                    }
                    postCard.dataset.reactions = JSON.stringify(reactions);
                    postCard.dataset.likerNames = JSON.stringify(likerNames);
                    updatePostStats(postCard);
                    updateLikeButtonState(postCard);
                }
            }

            async function sendUseful(postCard) {
                if (!currentUser) {
                    window.location.href = '{{ route("login") }}';
                    return;
                }

                const postId = postCard.dataset.postId;
                if (!postId || postId.startsWith('temp-')) return; // Cannot mark temp posts as useful

                const usefulBtn = postCard.querySelector('.useful-btn');
                let usefulUsers = (postCard.dataset.usefulUsers && postCard.dataset.usefulUsers !== '{}' && postCard.dataset.usefulUsers !== '[]') ? JSON.parse(postCard.dataset.usefulUsers) : {};
                let usefulUserNames = (postCard.dataset.usefulUserNames && postCard.dataset.usefulUserNames !== '[]') ? JSON.parse(postCard.dataset.usefulUserNames) : [];
                const currentUserName = currentUser.name;
                const userIndex = usefulUserNames.indexOf(currentUserName);
                const wasUseful = usefulUsers[currentUserId]; // Check state *before* toggle

                // --- Optimistic UI Update ---
                if (wasUseful) {
                    delete usefulUsers[currentUserId];
                    if (userIndex > -1) usefulUserNames.splice(userIndex, 1);
                    usefulBtn.classList.remove('active');
                } else {
                    usefulUsers[currentUserId] = true;
                    if (userIndex === -1) usefulUserNames.unshift(currentUserName);
                    usefulBtn.classList.add('active');
                }
                postCard.dataset.usefulUsers = JSON.stringify(usefulUsers);
                postCard.dataset.usefulUserNames = JSON.stringify(usefulUserNames);
                updateUsefulStats(postCard);
                // updateUsefulButtonState(postCard); // updateUsefulStats handles count, this handles button color

                // --- Send AJAX ---
                try {
                     const response = await fetch(`/community/posts/${postId}/useful`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                            // No body needed for toggle
                        }
                    });

                    if (response.status === 401) {
                        window.location.href = '{{ route("login") }}';
                        return;
                    }
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || `Useful toggle failed: ${response.status}`);
                    }

                    const data = await response.json();

                    // --- Re-sync with server data ---
                    postCard.dataset.usefulUsers = data.usefulUsers; // Update with latest from server
                    postCard.dataset.usefulUserNames = JSON.stringify(data.usefulUserNames); // Update names
                    updateUsefulStats(postCard); // Re-render stats
                    updateUsefulButtonState(postCard); // Ensure button state matches server

                } catch (error) {
                    console.error('Error sending useful:', error);
                    showModal('error-modal', 'เกิดข้อผิดพลาด', error.message || 'ไม่สามารถกดมีประโยชน์ได้');
                    // --- Revert UI on failure ---
                    if (wasUseful) { // We tried to unmark, but failed
                        usefulUsers[currentUserId] = true; // Mark it again
                        if(userIndex === -1) usefulUserNames.unshift(currentUserName);
                        usefulBtn.classList.add('active');
                    } else { // We tried to mark, but failed
                        delete usefulUsers[currentUserId]; // Unmark it
                        const revertIndex = usefulUserNames.indexOf(currentUserName);
                        if(revertIndex > -1) usefulUserNames.splice(revertIndex, 1);
                         usefulBtn.classList.remove('active');
                    }
                     postCard.dataset.usefulUsers = JSON.stringify(usefulUsers);
                    postCard.dataset.usefulUserNames = JSON.stringify(usefulUserNames);
                    updateUsefulStats(postCard);
                    // updateUsefulButtonState(postCard);
                }
            }


            // Main Click Event Delegate
            postFeedContainer.addEventListener('click', async (event) => {
                const target = event.target;
                const postCard = target.closest('.post-card');
                if (!postCard) return;

                const postId = postCard.dataset.postId;

                // --- Dropdown Handling ---
                const optionsBtn = target.closest('.options-btn');
                if (optionsBtn) {
                    const dropdown = optionsBtn.nextElementSibling;
                    // Close other dropdowns first
                    document.querySelectorAll('.options-dropdown.show').forEach(d => {
                        if (d !== dropdown) d.classList.remove('show');
                    });
                     // Don't toggle if the post is temporary (no real options yet)
                    if (!postId || postId.toString().startsWith('temp-')) return;
                    dropdown.classList.toggle('show');
                    return; // Stop further processing for this click
                }

                // If a dropdown item IS clicked, close its parent dropdown AFTER the action
                const dropdownItem = target.closest('.options-dropdown-item');
                if (dropdownItem) {
                    const dropdown = dropdownItem.closest('.options-dropdown');
                    // We will close it later if needed, after checking the specific action

                    // Handle specific dropdown actions
                    const deletePostBtn = dropdownItem.classList.contains('delete-post-btn');
                    const editPostBtn = dropdownItem.classList.contains('edit-post-btn');
                    const reportPostBtn = dropdownItem.classList.contains('report-post-btn'); // Assuming you add this class


                     if (deletePostBtn) {
                         if (!postId || postId.startsWith('temp-')) return;
                        //  if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโพสต์นี้?')) {
                        //      if (dropdown) dropdown.classList.remove('show'); // Close dropdown if cancelled
                        //      return;
                        //  }

                         try {
                             const response = await fetch(`/community/posts/${postId}`, {
                                  method: 'DELETE',
                                  headers: {
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': csrfToken
                                  }
                             });
                             if (response.status === 401 || response.status === 403) {
                                   const errorData = await response.json();
                                  showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์ลบโพสต์นี้');
                                  // return; // Keep dropdown open potentially? Or close? Let's close.
                             } else if (!response.ok) {
                                 throw new Error('Failed to delete');
                             } else {
                                postCard.remove();
                                // Remove from local array to prevent re-appearance if using pagination later
                                allMockPosts = allMockPosts.filter(post => post.id != postId);
                             }

                         } catch (error) {
                             console.error('Error deleting post:', error);
                             showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถลบโพสต์ได้ในขณะนี้');
                         } finally {
                              if (dropdown) dropdown.classList.remove('show'); // Close dropdown
                         }
                         return; // Action handled
                     }

                    if (editPostBtn) {
                         // --- Edit Post Logic (extracted for clarity) ---
                         const postBodyP = postCard.querySelector('.post-body p');
                         if (postBodyP.isContentEditable) { // Already editing
                              if (dropdown) dropdown.classList.remove('show');
                              return;
                         }

                         const originalText = postBodyP.textContent;
                         postBodyP.setAttribute('contenteditable', 'true');
                         postBodyP.focus();

                         // Move cursor to end
                         const selection = window.getSelection();
                         const range = document.createRange();
                         range.selectNodeContents(postBodyP);
                         range.collapse(false); // false collapses to the end
                         selection.removeAllRanges();
                         selection.addRange(range);

                         const finishEditing = async (saveChanges = true) => {
                              // Remove listeners immediately to prevent multiple saves
                              postBodyP.removeEventListener('blur', handleBlur);
                              postBodyP.removeEventListener('keydown', handleKeydown);
                              postBodyP.setAttribute('contenteditable', 'false');

                              const newContent = postBodyP.textContent.trim();

                              if (!saveChanges || newContent === originalText || newContent === '') {
                                   postBodyP.textContent = originalText; // Revert if cancelled, empty, or unchanged
                                   return; // Don't send AJAX
                              }

                              // --- Send AJAX Update ---
                              try {
                                   const response = await fetch(`/community/posts/${postId}`, {
                                        method: 'PATCH',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: JSON.stringify({ content: newContent })
                                   });

                                   if (response.status === 401 || response.status === 403) {
                                        const errorData = await response.json();
                                        showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์แก้ไขโพสต์นี้');
                                        throw new Error('Unauthorized'); // Throw to trigger catch block for revert
                                   }
                                   if (!response.ok) {
                                       const errorData = await response.json();
                                       if(response.status === 422) {
                                            const firstError = Object.values(errorData.errors)[0][0];
                                            showModal('error-modal', 'ข้อมูลไม่ถูกต้อง', firstError || 'เนื้อหาโพสต์ไม่ถูกต้อง');
                                       } else {
                                           showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกการแก้ไขได้');
                                       }
                                       throw new Error('Failed to save edit'); // Trigger catch block
                                   }

                                   // Success - update text with potentially sanitized version from server
                                   const data = await response.json();
                                   postBodyP.textContent = data.content; // Update with server response
                                   // Update local array too
                                    const postIndex = allMockPosts.findIndex(p => p.id == postId);
                                    if(postIndex > -1) allMockPosts[postIndex].content = data.content;


                              } catch (error) {
                                   console.error('Error updating post:', error);
                                   postBodyP.textContent = originalText; // Revert on failure
                                   // Error modal already shown or handled above
                              }
                         };

                         const handleKeydown = (e) => {
                              if (e.key === 'Enter' && !e.shiftKey) { // Save on Enter (without Shift)
                                   e.preventDefault();
                                   finishEditing(true); // Save changes
                              } else if (e.key === 'Escape') { // Cancel on Escape
                                   finishEditing(false); // Don't save changes
                              }
                         };

                         const handleBlur = () => { // Save on clicking outside
                             finishEditing(true); // Save changes
                         };

                         postBodyP.addEventListener('keydown', handleKeydown);
                         postBodyP.addEventListener('blur', handleBlur);
                         // --- End Edit Post Logic ---

                         if (dropdown) dropdown.classList.remove('show'); // Close dropdown
                         return; // Action handled
                    }

 
                    if (reportPostBtn) {
                         if (!postId || postId.startsWith('temp-')) {
                              if (dropdown) dropdown.classList.remove('show');
                              return;
                         }

                         // *** Logic การรายงานโพสต์ ***
                         try {
                              const response = await fetch(`/community/posts/${postId}/report`, {
                                   method: 'POST',
                                   headers: {
                                      'Content-Type': 'application/json',
                                      'Accept': 'application/json',
                                      'X-CSRF-TOKEN': csrfToken
                                   },
                                   // เนื่องจาก Admin จะไปตรวจเอง จึงไม่จำเป็นต้องส่ง body ข้อมูลเพิ่มเติม
                                   body: JSON.stringify({}) 
                              });

                              if (response.status === 401) {
                                  window.location.href = '{{ route("login") }}';
                                  return;
                              }
                              
                              const responseData = await response.json();

                              if (response.status === 409) { // Conflict: รายงานซ้ำ
                                   showModal('error-modal', 'ไม่จำเป็นต้องรายงานซ้ำ', responseData.message);
                              } else if (!response.ok) {
                                   const message = responseData.message || 'ไม่สามารถส่งรายงานได้';
                                   showModal('error-modal', 'เกิดข้อผิดพลาด', message);
                              } else {
                                   showModal('error-modal', 'ส่งรายงานเรียบร้อย', responseData.message || 'ขอบคุณที่ช่วยให้ชุมชนน่าอยู่ขึ้น');
                              }
                         } catch (error) {
                              console.error('Error reporting post:', error);
                              showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถส่งรายงานได้ในขณะนี้');
                         } finally {
                              if (dropdown) dropdown.classList.remove('show'); // ปิด dropdown ไม่ว่าสำเร็จหรือไม่
                         }
                         return; // Action handled
                    }

                    // If it was another dropdown item (like 'Show Post'), let the default <a> action happen
                    // but still close the dropdown
                    if (dropdown) dropdown.classList.remove('show');
                    // Don't return here, allow other handlers below if needed (though unlikely for dropdown items)

                } // End if (dropdownItem)


                // --- Comment Controls Handling ---
                const commentElement = target.closest('.comment');
                if (commentElement) {
                    const commentId = commentElement.dataset.commentId;

                    const deleteCommentBtn = target.closest('.comment-delete-btn');
                    if (deleteCommentBtn) {
                         if (!commentId || commentId.startsWith('temp-')) {
                              // Silently remove temp comments or ignore if ID missing
                              if(commentId && commentId.startsWith('temp-')) commentElement.remove();
                              return;
                         }

                        //  if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบคอมเมนต์นี้?')) return;

                         try {
                              const response = await fetch(`/community/comments/${commentId}`, {
                                   method: 'DELETE',
                                   headers: {
                                      'Accept': 'application/json',
                                      'X-CSRF-TOKEN': csrfToken
                                   }
                              });

                              if (response.status === 401 || response.status === 403) {
                                   const errorData = await response.json();
                                   showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์ลบคอมเมนต์นี้');
                                   return;
                              }
                              if (!response.ok) throw new Error('Failed to delete comment');

                              commentElement.remove();
                              // Optional: Update local array
                              // const postIndex = allMockPosts.findIndex(p => p.id == postId);
                              // if(postIndex > -1) allMockPosts[postIndex].comments = allMockPosts[postIndex].comments.filter(c => c.id != commentId);


                         } catch (error) {
                              console.error('Error deleting comment:', error);
                              showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถลบคอมเมนต์ได้');
                         }
                         return; // Action handled
                    }

                    const blockCommentBtn = target.closest('.comment-block-btn');
                    if (blockCommentBtn) {
                         const userIdToBlock = blockCommentBtn.dataset.userId;
                         userToBlock = {
                              element: commentElement,
                              userId: userIdToBlock
                         };
                         showModal('block-confirmation-modal');
                         return; // Action handled
                    }

                    const editCommentBtn = target.closest('.comment-edit-btn');
                    if (editCommentBtn) {
                         // --- Edit Comment Logic ---
                         const commentContentDiv = commentElement.querySelector('.comment-content');
                         const commentTextP = commentContentDiv.querySelector('.comment-text');

                         if (commentTextP.isContentEditable || !commentId || commentId.startsWith('temp-')) return; // Prevent editing temp or already editing

                         const originalText = commentTextP.textContent;
                         commentTextP.setAttribute('contenteditable', 'true');
                         commentTextP.focus();

                         // Move cursor to end
                         const selection = window.getSelection();
                         const range = document.createRange();
                         range.selectNodeContents(commentTextP);
                         range.collapse(false);
                         selection.removeAllRanges();
                         selection.addRange(range);

                         const finishEditing = async (saveChanges = true) => {
                              commentTextP.removeEventListener('blur', handleBlur);
                              commentTextP.removeEventListener('keydown', handleKeydown);
                              commentTextP.setAttribute('contenteditable', 'false');

                              const newContent = commentTextP.textContent.trim();

                              if (!saveChanges || newContent === originalText || newContent === "") {
                                   commentTextP.textContent = originalText;
                                   return; // Don't send AJAX
                              }

                              // --- Send AJAX Update ---
                              try {
                                   const response = await fetch(`/community/comments/${commentId}`, {
                                        method: 'PATCH',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: JSON.stringify({ content: newContent })
                                   });

                                   if (response.status === 401 || response.status === 403) {
                                        const errorData = await response.json();
                                        showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์แก้ไขคอมเมนต์นี้');
                                        throw new Error('Unauthorized');
                                   }
                                   if (!response.ok) {
                                       const errorData = await response.json();
                                        if(response.status === 422) {
                                             const firstError = Object.values(errorData.errors)[0][0];
                                             showModal('error-modal', 'ข้อมูลไม่ถูกต้อง', firstError || 'เนื้อหาคอมเมนต์ไม่ถูกต้อง');
                                        } else {
                                            showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกการแก้ไขคอมเมนต์ได้');
                                        }
                                       throw new Error('Failed to save comment edit');
                                   }

                                   // Success
                                   const data = await response.json();
                                   commentTextP.textContent = data.content; // Update with server response
                                    // Update local array
                                    // const postIndex = allMockPosts.findIndex(p => p.id == postId);
                                    // if(postIndex > -1) {
                                    //     const commentIndex = allMockPosts[postIndex].comments.findIndex(c => c.id == commentId);
                                    //     if(commentIndex > -1) allMockPosts[postIndex].comments[commentIndex].text = data.content;
                                    // }


                              } catch (error) {
                                   console.error('Error updating comment:', error);
                                   commentTextP.textContent = originalText; // Revert on failure
                              }
                         };

                        const handleKeydown = (e) => {
                            if (e.key === 'Enter' && !e.shiftKey) { // Save on Enter (without shift)
                                e.preventDefault();
                                finishEditing(true);
                            } else if (e.key === 'Escape') { // Cancel on Escape
                                finishEditing(false);
                            }
                         };

                         const handleBlur = () => { // Save on blur
                             finishEditing(true);
                         };

                         commentTextP.addEventListener('keydown', handleKeydown);
                         commentTextP.addEventListener('blur', handleBlur);
                         // --- End Edit Comment Logic ---

                         return; // Action handled
                    }

                } // End if (commentElement)


                // --- Reaction & Useful Handling (if click wasn't on dropdown or comment controls) ---

                const reactionEmoji = target.closest('.reaction-emoji');
                if (reactionEmoji) {
                    sendReaction(postCard, reactionEmoji.dataset.reaction);
                    return; // Action handled
                }

                const likeBtn = target.closest('.like-btn');
                if (likeBtn) {
                    // Default to '👍' if not already liked, or null to unlike
                    const reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '{}' && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                    const myReaction = reactions[currentUserId];
                    sendReaction(postCard, myReaction ? null : '👍'); // Send null to unlike
                    return; // Action handled
                }

                const usefulBtn = target.closest('.useful-btn');
                if (usefulBtn) {
                    sendUseful(postCard);
                    return; // Action handled
                }

            }); // End Main Click Event Delegate

            // --- Modal Event Listeners ---
            function hideBlockModal() {
                hideModal('block-confirmation-modal');
                userToBlock = null; // Clear the user to block info
            }

            closeErrorModalBtn.addEventListener('click', () => hideModal('error-modal'));
            errorModal.addEventListener('click', (e) => {
                if(e.target === errorModal) hideModal('error-modal');
            });

            cancelBlockBtn.addEventListener('click', hideBlockModal);

            confirmBlockBtn.addEventListener('click', async () => {
                if (!userToBlock || !userToBlock.userId) return;

                const { element, userId } = userToBlock;
                confirmBlockBtn.disabled = true; // Prevent double clicks
                cancelBlockBtn.disabled = true;

                try {
                    const response = await fetch(`/community/users/${userId}/block`, {
                         method: 'POST',
                         headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                         }
                    });

                    if (response.status === 401) {
                         window.location.href = '{{ route("login") }}';
                         return; // Stop processing
                    }
                    if (!response.ok) {
                         const errorData = await response.json();
                         throw new Error(errorData.error || 'Failed to block user');
                    }

                    // Success! Reload the page to apply filtering from server.
                    // alert("บล็อกผู้ใช้สำเร็จ หน้านี้จะทำการโหลดใหม่"); // Optional alert
                    window.location.reload();
                    // No need to hide modal as page reloads

                } catch (error) {
                     console.error('Error blocking user:', error);
                     showModal('error-modal', 'เกิดข้อผิดพลาด', `ไม่สามารถบล็อกผู้ใช้ได้: ${error.message}`);
                     hideBlockModal(); // Hide the block modal on error
                     confirmBlockBtn.disabled = false; // Re-enable buttons
                     cancelBlockBtn.disabled = false;
                }
            });

            blockModal.addEventListener('click', (e) => {
                // Close if clicked on the overlay itself
                if(e.target === blockModal) {
                    hideBlockModal();
                }
            });


            // --- Initial Load & Observer Setup ---

            // 1. Setup Create Post area based on currentUser
            if (currentUser) {
                // Avatar is set by Blade
                if (!currentUser.is_writer) {
                     postInput.placeholder = "เฉพาะนักเขียนเท่านั้นที่สามารถสร้างโพสต์ได้";
                     postInput.disabled = true;
                }
            } else {
                 // Avatar is set by Blade
                 postInput.placeholder = "กรุณาล็อกอินเพื่อสร้างโพสต์...";
                 postInput.disabled = true;
                 // Disable comment inputs for all posts initially if not logged in
                 // (Done inside createPostElement now)
            }


            // 2. Initial Render of posts from server data (allMockPosts)
            // Render only the first `postsPerLoad` initially
            const initialPosts = allMockPosts.slice(0, postsPerLoad);
            initialPosts.forEach(postData => {
                const postElement = createPostElement(postData);
                postFeedContainer.appendChild(postElement);
            });
            currentPostIndex = initialPosts.length; // Update index to reflect rendered posts

            // 3. Setup observer (only if there are more posts to load)
            if (allMockPosts.length > postsPerLoad) {
                const observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting && !isLoading && currentPostIndex < allMockPosts.length) {
                        loadMoreRenderedPosts(); // Call function to render next batch
                    }
                }, {
                    rootMargin: '500px 0px' // Load when 500px away from viewport bottom
                });

                function updateObserverTarget() {
                    if (observedTarget) {
                        observer.unobserve(observedTarget);
                    }
                    // Stop observing if all posts are rendered
                    if (currentPostIndex >= allMockPosts.length) {
                        observer.disconnect();
                        loader.style.display = 'none';
                        return;
                    }
                    const posts = postFeedContainer.querySelectorAll('.post-card');
                    const targetIndex = posts.length - 3; // Observe 3rd from last rendered post
                    if (targetIndex >= 0) {
                        const newTarget = posts[targetIndex];
                        observer.observe(newTarget);
                        observedTarget = newTarget;
                    } else if (posts.length > 0) {
                        // Fallback if less than 3 posts rendered
                        observer.observe(posts[posts.length - 1]);
                        observedTarget = posts[posts.length - 1];
                    }
                }

                // Function to render the next batch of posts
                function loadMoreRenderedPosts() {
                    if (isLoading) return;
                    isLoading = true;
                    loader.style.display = 'block';

                    // Simulate delay if needed, or just render directly since data is local
                     setTimeout(() => { // Keep timeout for visual feedback
                        const postsToRender = allMockPosts.slice(currentPostIndex, currentPostIndex + postsPerLoad);
                        postsToRender.forEach(postData => {
                            const postElement = createPostElement(postData);
                            postFeedContainer.appendChild(postElement);
                        });
                        currentPostIndex += postsToRender.length;
                        isLoading = false;
                        if (currentPostIndex >= allMockPosts.length) {
                             loader.style.display = 'none'; // Hide loader if all loaded
                        }
                        updateObserverTarget(); // Update observer for the next trigger
                    }, 500); // Shorter delay for local rendering
                }

                updateObserverTarget(); // Set the initial observer target

            } else {
                 // If all posts were rendered initially, hide loader
                 loader.style.display = 'none';
            }


        }); // End DOMContentLoaded
    </script>
</body>
</html>
