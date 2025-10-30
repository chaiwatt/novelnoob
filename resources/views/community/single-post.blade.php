<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Post | Community | Novel Noob</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">

    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    {{-- Include styles directly from index.blade.php for consistency --}}
    <style>
        .container { max-width: 800px; }
        header.navbar { position: sticky; padding: 0 5%; }
        nav.navbar { gap: 20px; max-width: 700px; margin: 0 auto; justify-content: center; /* Center logo */}
        .nav-search, .nav-actions { display: none; } /* Hide search/actions on single post */
        main { padding: 40px 0; }
        .single-post-container { max-width: 700px; margin: 0 auto; }
        .post-card { background-color: var(--bg-light); border-radius: 15px; border: 1px solid var(--border-color); margin-bottom: 25px; padding: 20px; }
        .post-header { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 15px; }
        .post-author-info { flex-grow: 1; display: flex; align-items: center; gap: 15px; }
        
        /* *** MODIFIED: Allow SVG (though we now use img) *** */
        .post-author-avatar img,
        .post-author-avatar svg { 
            width: 45px; 
            height: 45px; 
            border-radius: 50%; 
            object-fit: cover; 
        }
        
        .post-author-details .name { font-weight: bold; color: var(--text-primary); }
        .post-author-details .timestamp { font-size: 0.8rem; color: var(--text-secondary); }
        .post-body p { color: var(--text-secondary); line-height: 1.7; white-space: pre-wrap; margin-bottom: 15px; min-height: 24px; }
        .post-body p[contenteditable="true"] { outline: 1px solid var(--primary-accent); background-color: var(--bg-dark); border-radius: 4px; padding: 4px 8px; margin: 0 0 15px 0; cursor: text; }
        .post-options { position: relative; }
        .options-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .options-btn:hover { background-color: var(--secondary-accent); }
        .options-dropdown { position: absolute; top: calc(100% + 5px); right: 0; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 10; overflow: hidden; width: 250px; display: none; padding: 8px; }
        .options-dropdown.show { display: block; }
        .options-dropdown-item { display: flex; align-items: center; gap: 15px; padding: 10px; border-radius: 6px; cursor: pointer; transition: background-color 0.2s; text-decoration: none; }
        .options-dropdown-item:hover { background-color: var(--secondary-accent); }
        .item-icon { width: 36px; height: 36px; border-radius: 50%; background-color: var(--bg-light); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); flex-shrink: 0; }
        .item-icon svg { width: 20px; height: 20px; }
        .item-text { width: 100%; }
        .item-title { font-size: 0.95rem; color: var(--text-primary); font-weight: 500; }
        .item-subtitle { font-size: 0.8rem; color: var(--text-secondary); }
        .dropdown-separator { height: 1px; background-color: var(--border-color); margin: 8px 0; }
        .item-title.delete { color: var(--danger-color); }
        .post-stats { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; font-size: 0.9rem; color: var(--text-secondary); min-height: 20px; }
        .reactions-summary-wrapper, .useful-stats-wrapper { position: relative; }
        .reactions-summary, .useful-stats { display: flex; align-items: center; cursor: pointer; }
        .reactions-summary .reaction-icon { font-size: 1rem; margin-left: -4px; }
        .reactions-summary .total-likes { margin-left: 8px; }
        .stats-tooltip { position: absolute; bottom: 100%; left: 0; margin-bottom: 8px; background-color: rgba(18, 24, 40, 0.95); border-radius: 8px; padding: 5px 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); width: max-content; min-width: 150px; max-width: 250px; z-index: 20; opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.2s ease-in-out; pointer-events: none; }
        .reactions-summary-wrapper:hover .stats-tooltip,
        .useful-stats-wrapper:hover .stats-tooltip { opacity: 0.95; visibility: visible; transform: translateY(0); }
        .stats-tooltip ul { list-style: none; padding: 0; margin: 0; }
        .stats-tooltip li { padding: 1px 0; font-size: 0.75rem; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .useful-stats { gap: 6px; }
        .useful-stats .useful-icon { color: var(--primary-accent); font-size: 0.9rem; }
        .post-actions { display: flex; align-items: center; padding: 10px 0; border-top: 1px solid var(--border-color); }
        .action-btn-wrapper { position: relative; }
        .action-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; gap: 8px; font-family: var(--font-ui); font-size: 0.9rem; font-weight: bold; padding: 8px 12px; border-radius: 8px; transition: background-color 0.3s, color 0.3s; }
        .action-btn:hover { background-color: var(--secondary-accent); }
        .action-btn.liked { font-weight: bold; }
        .action-btn.useful-btn.active { font-weight: bold; color: var(--primary-accent); }
        .action-btn .useful-icon { font-size: 1.1rem; }
        .like-icon-reaction { font-size: 1.2rem; line-height: 1; }
        .reactions-container { position: absolute; bottom: 100%; left: -10px; margin-bottom: 10px; background-color: var(--bg-dark); padding: 8px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: flex; gap: 8px; opacity: 0; visibility: hidden; transform: translateY(10px) scale(0.9); transition: all 0.2s ease-in-out; z-index: 10; }
        .action-btn-wrapper:hover .reactions-container { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        .reaction-emoji { font-size: 1.8rem; cursor: pointer; transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform-origin: bottom; }
        .reaction-emoji:hover { transform: scale(1.3) translateY(-5px); }
        .post-comments { margin-top: 10px; padding-top: 15px; border-top: 1px solid var(--border-color); }
        .comment { display: flex; gap: 10px; margin-bottom: 15px; }
        
        /* *** MODIFIED: Allow SVG *** */
        .comment-avatar img,
        .comment-avatar svg { 
            width: 35px; 
            height: 35px; 
            border-radius: 50%; 
            object-fit: cover;
        }
        
        .comment-content { background-color: var(--bg-dark); padding: 10px 15px; border-radius: 12px; width: 100%; position: relative; }
        .comment-author { font-weight: bold; font-size: 0.9rem; margin-bottom: 5px; }
        .comment-text { font-size: 0.95rem; color: var(--text-secondary); white-space: pre-wrap; word-wrap: break-word; }
        .comment-text[contenteditable="true"] { outline: 1px solid var(--primary-accent); background-color: var(--bg-light); border-radius: 4px; padding: 2px 4px; margin: -3px -5px; cursor: text; }
        .comment-controls { position: absolute; top: 5px; right: 8px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s; background-color: var(--bg-dark); padding: 2px 0 2px 4px; border-radius: 12px; }
        .comment-content:hover .comment-controls { opacity: 0.7; }
        .comment-controls:hover { opacity: 1; }
        .comment-control-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 2px; line-height: 1; display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; }
        .comment-control-btn:hover { background-color: var(--secondary-accent); opacity: 1; color: var(--text-primary); }
        .comment-control-btn.comment-block-btn:hover { color: var(--danger-color); }
        .comment-delete-btn { font-size: 1.4rem; font-weight: bold; }
        .comment-edit-btn svg, .comment-block-btn svg { width: 14px; height: 14px; }
        .add-comment { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        
        /* *** MODIFIED: Allow SVG *** */
        .add-comment img,
        .add-comment svg { 
            width: 35px; 
            height: 35px; 
            border-radius: 50%; 
            object-fit: cover;
        }
        
        .add-comment input { flex-grow: 1; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 20px; padding: 8px 15px; color: var(--text-primary); font-family: var(--font-ui); outline: none; }
        .add-comment input:focus { border-color: var(--primary-accent); }
        .add-comment input:disabled { cursor: not-allowed; opacity: 0.7; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(18, 24, 40, 0.8); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; visibility: hidden; transition: opacity 0.3s, visibility 0.3s; }
        .modal-overlay.show { opacity: 1; visibility: visible; }
        .modal-content { background-color: var(--bg-light); padding: 30px; border-radius: 15px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 90%; max-width: 500px; transform: scale(0.95); transition: transform 0.3s; }
        .modal-overlay.show .modal-content { transform: scale(1); }
        .modal-content h3 { margin-top: 0; color: var(--text-primary); }
        .modal-content p { color: var(--text-secondary); line-height: 1.6; }
        .modal-actions { margin-top: 25px; display: flex; justify-content: flex-end; gap: 15px; }
        @media (max-width: 600px) {
            .nav-search { display: none; }
            nav.navbar { justify-content: center; } /* Center logo */
            .logo { margin-right: 0; }
            .modal-content { width: 95%; }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="container">
            <nav class="navbar">
                <a href="{{url('/')}}" class="logo">NovelNoob</a>
                 {{-- Search and Actions are hidden on single post page --}}
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="single-post-container">
                 {{-- Post Card will be rendered here by JavaScript --}}
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

    {{-- *** Pass PHP data to JS correctly *** --}}
    <script>
        // Use the variables passed from the controller via compact()
        // Ensure $jsPost exists before trying to access it
        const singlePostData = @json($jsPost ?? null);
        const currentUser = @json($currentUserJs ?? null);
        const currentUserId = currentUser ? currentUser.id : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const communityIndexUrl = "{{ route('community.index') }}"; // URL for back button

        // Basic check if post data exists
        if (!singlePostData) {
            console.error("Single post data ($jsPost) is null or undefined!");
            // Display an error message on the page
            const container = document.querySelector('.single-post-container');
            if(container) {
                container.innerHTML = '<p style="color: red; text-align: center; margin-top: 20px;">ไม่พบข้อมูลโพสต์ที่ต้องการ</p>';
            }
        }
    </script>

    <script>
        // --- Reusable Helper Functions (Keep these) ---
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
        function getReactionInfo(emoji) {
            const reactions = {
                '👍': { text: 'ถูกใจ', color: 'var(--primary-accent)' }, '❤️': { text: 'รักเลย', color: '#ef4444' },
                '😂': { text: 'ฮา', color: '#facc15' }, '😮': { text: 'ว้าว', color: '#facc15' },
                '😢': { text: 'เศร้า', color: '#3b82f6' }, '😠': { text: 'โกรธ', color: '#f97316' }
            };
            return reactions[emoji] || { text: 'ถูกใจ', color: 'var(--text-secondary)' };
        }
        
        // *** MODIFIED: Simplified renderAvatar function ***
        // This function now only expects URLs (from storage or placehold.co)
        function renderAvatar(avatarData) {
            // Fallback for null/undefined or empty string
            if (!avatarData) {
                avatarData = 'https://placehold.co/100x100/A9B4D9/121828?text=?';
            }
            return `<img src="${escapeHTML(avatarData)}" alt="Avatar">`;
        }


        document.addEventListener('DOMContentLoaded', () => {
            // Check again if data exists before proceeding
            if (!singlePostData) {
                console.error("Stopping script execution because singlePostData is missing.");
                return; // Stop script if data is missing
            }

            const singlePostContainer = document.querySelector('.single-post-container');

            // Modals
            const blockModal = document.getElementById('block-confirmation-modal');
            const cancelBlockBtn = document.getElementById('cancel-block-btn');
            const confirmBlockBtn = document.getElementById('confirm-block-btn');
            const errorModal = document.getElementById('error-modal');
            const closeErrorModalBtn = document.getElementById('close-error-modal-btn');

            let userToBlock = null; // Store { element, userId }

            // --- Core Functions (Adapted from index.blade.php) ---

            function createCommentElement(commentData, postData) {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.dataset.commentId = commentData.id;

                const escapedText = escapeHTML(commentData.text);

                let controlsHtml = '';
                const isCommentOwner = currentUser && currentUser.id === commentData.author_id;
                const isPostOwner = postData.is_owner; // Use is_owner from the singlePostData

                if (isCommentOwner) {
                    controlsHtml = `
                        <button class="comment-edit-btn comment-control-btn" title="แก้ไขคอมเมนต์">...</button>
                        <button class="comment-delete-btn comment-control-btn" title="ลบคอมเมนต์">&times;</button>
                    `; // SVG icons omitted for brevity, add them back
                     controlsHtml = controlsHtml.replace('...', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/></svg>');

                } else if (isPostOwner && currentUser) {
                    controlsHtml = `
                        <button class="comment-block-btn comment-control-btn" title="บล็อกผู้ใช้" data-user-id="${commentData.author_id}">...</button>
                        <button class="comment-delete-btn comment-control-btn" title="ลบคอมเมนต์">&times;</button>
                    `; // SVG icons omitted for brevity, add them back
                     controlsHtml = controlsHtml.replace('...', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-slash" viewBox="0 0 16 16"><path d="M13.879 10.414a2.502 2.502 0 0 0-3.465 3.465l3.465-3.465Zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465Zm-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z"/></svg>');
                }

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
                const postCard = document.createElement('div');
                postCard.className = 'post-card';
                postCard.dataset.postId = postData.id; // Use the actual post ID
                postCard.dataset.reactions = postData.reactions;
                postCard.dataset.usefulUsers = postData.usefulUsers;
                postCard.dataset.likerNames = JSON.stringify(postData.likerNames);
                postCard.dataset.usefulUserNames = JSON.stringify(postData.usefulUserNames);

                const escapedPostText = escapeHTML(postData.content);

                let optionsDropdownHtml = '';
                if (postData.is_owner) { // Check if the current user owns this post
                     optionsDropdownHtml = `
                        <div class="options-dropdown-item edit-post-btn">...</div>
                        <div class="options-dropdown-item delete-post-btn">...</div>
                    `; // Icons/Text omitted for brevity, add them back
                     optionsDropdownHtml = optionsDropdownHtml.replace('...', '<div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg></div><div class="item-text"><div class="item-title">แก้ไขโพสต์</div><div class="item-subtitle">ปรับแก้เนื้อหาในโพสต์นี้</div></div>')
                                                    .replace('...', '<div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg></div><div class="item-text"><div class="item-title delete">ลบโพสต์</div><div class="item-subtitle">โพสต์นี้จะถูกลบอย่างถาวร</div></div>');
                } else if (currentUser) {
                     optionsDropdownHtml = `
                        <div class="options-dropdown-item report-post-btn">...</div>
                    `; // Icons/Text omitted for brevity, add them back
                     optionsDropdownHtml = optionsDropdownHtml.replace('...', '<div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" /></svg></div><div class="item-text"><div class="item-title">รายงานโพสต์</div><div class="item-subtitle">แจ้งให้เราทราบหากโพสต์นี้มีปัญหา</div></div>');
                }

                // Add "Back to Community" link
                const backLinkHtml = `
                     <a href="${communityIndexUrl}" class="options-dropdown-item">
                         <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg></div>
                         <div class="item-text"><div class="item-title">กลับไปที่ Community</div><div class="item-subtitle">ดูโพสต์ทั้งหมด</div></div>
                     </a>
                 `;
                 const separatorHtml = (optionsDropdownHtml !== '') ? '<div class="dropdown-separator"></div>' : '';

                postCard.innerHTML = `
                    <div class="post-header">
                        <div class="post-author-info">...</div>
                        ${(backLinkHtml + separatorHtml + optionsDropdownHtml) ? `<div class="post-options">...</div>` : ''}
                    </div>
                    <div class="post-body"><p>${escapedPostText}</p></div>
                    <div class="post-stats">...</div>
                    <div class="post-actions">...</div>
                    <div class="post-comments">...</div>
                `; // Content omitted for brevity, add it back

                 postCard.innerHTML = postCard.innerHTML
                    .replace('...', `<div class="post-author-avatar">${renderAvatar(postData.avatar)}</div><div class="post-author-details"><div class="name">${escapeHTML(postData.author)}</div><div class="timestamp">${postData.timestamp}</div></div>`)
                    .replace('...', `<button class="options-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg></button><div class="options-dropdown">${backLinkHtml}${separatorHtml}${optionsDropdownHtml}</div>`)
                    .replace('...', `<div class="reactions-summary-wrapper"><div class="reactions-summary"><span class="total-likes"></span></div><div class="likers-tooltip stats-tooltip"><ul></ul></div></div><div class="useful-stats-wrapper"><div class="useful-stats" style="display: none;"><span class="useful-count"></span><span class="useful-icon">💎</span></div><div class="useful-tooltip stats-tooltip"><ul></ul></div></div>`)
                    .replace('...', `<div class="action-btn-wrapper"><div class="reactions-container"><span class="reaction-emoji" data-reaction="👍">👍</span><span class="reaction-emoji" data-reaction="❤️">❤️</span><span class="reaction-emoji" data-reaction="😂">😂</span><span class="reaction-emoji" data-reaction="😮">😮</span><span class="reaction-emoji" data-reaction="😢">😢</span><span class="reaction-emoji" data-reaction="😠">😠</span></div><button class="action-btn like-btn"><span class="like-icon-reaction"></span><span class="action-text">ถูกใจ</span></button></div><button class="action-btn useful-btn"><span class="useful-icon">💎</span><span>มีประโยชน์</span></button>`)
                     .replace('...', `<div class="add-comment"><div class="comment-avatar" id="add-comment-avatar-wrapper">${currentUser ? renderAvatar(currentUser.avatar) : renderAvatar('https://placehold.co/100x100/A9B4D9/121828?text=G')}</div><input type="text" placeholder="${currentUser ? 'แสดงความคิดเห็น...' : 'กรุณาล็อกอินเพื่อแสดงความคิดเห็น...'}" ${currentUser ? '' : 'disabled'}></div>`);


                const commentsContainer = postCard.querySelector('.post-comments');
                const addCommentDiv = commentsContainer.querySelector('.add-comment');
                if (postData.comments && postData.comments.length > 0) {
                    postData.comments.forEach(commentData => {
                        // Pass the main postData here too for permission checks
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

            // --- Stats Update Functions (Adapted from index.blade.php) ---
             function updatePostStats(postCard) {
                 const statsContainer = postCard.querySelector('.post-stats');
                 if(!statsContainer) return; // Element might not exist if post deleted
                 const totalLikesSpan = statsContainer.querySelector('.total-likes');
                 const summaryContainer = statsContainer.querySelector('.reactions-summary');
                 const tooltipUl = postCard.querySelector('.likers-tooltip ul');

                 const reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                 const likerNames = (postCard.dataset.likerNames && postCard.dataset.likerNames !== '[]') ? JSON.parse(postCard.dataset.likerNames) : [];

                 let totalLikes = likerNames.length;
                 let uniqueReactions = new Set(Object.values(reactions));

                 totalLikesSpan.textContent = totalLikes > 0 ? totalLikes : '';
                 summaryContainer.querySelectorAll('.reaction-icon').forEach(icon => icon.remove());
                 uniqueReactions.forEach(emoji => {
                     const iconSpan = document.createElement('span');
                     iconSpan.className = 'reaction-icon';
                     iconSpan.textContent = emoji;
                     summaryContainer.prepend(iconSpan);
                 });

                 tooltipUl.innerHTML = '';
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
                 if(!usefulStats) return;
                const usefulCountSpan = usefulStats.querySelector('.useful-count');
                const tooltipUl = postCard.querySelector('.useful-tooltip ul');

                const usefulUserNames = (postCard.dataset.usefulUserNames && postCard.dataset.usefulUserNames !== '[]') ? JSON.parse(postCard.dataset.usefulUserNames) : [];
                const count = usefulUserNames.length;

                if (count > 0) {
                    usefulCountSpan.textContent = count;
                    usefulStats.style.display = 'flex';
                } else {
                    usefulCountSpan.textContent = '';
                    usefulStats.style.display = 'none';
                }

                tooltipUl.innerHTML = '';
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
                 if(!likeBtn) return;
                const reactionIconSpan = likeBtn.querySelector('.like-icon-reaction');
                const actionTextSpan = likeBtn.querySelector('.action-text');
                const reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                const myReaction = reactions[currentUserId];

                if (myReaction) {
                    likeBtn.classList.add('liked');
                    reactionIconSpan.textContent = myReaction;
                    const reactionInfo = getReactionInfo(myReaction);
                    actionTextSpan.textContent = 'ถูกใจ';
                    likeBtn.style.color = reactionInfo.color;
                } else {
                    likeBtn.classList.remove('liked');
                    reactionIconSpan.textContent = '';
                    actionTextSpan.textContent = 'ถูกใจ';
                    likeBtn.style.color = 'var(--text-secondary)';
                }
            }

            function updateUsefulButtonState(postCard) {
                if (!currentUser) return;
                const usefulBtn = postCard.querySelector('.useful-btn');
                 if(!usefulBtn) return;
                const usefulUsers = (postCard.dataset.usefulUsers && postCard.dataset.usefulUsers !== '[]') ? JSON.parse(postCard.dataset.usefulUsers) : {};

                if (usefulUsers[currentUserId]) {
                    usefulBtn.classList.add('active');
                } else {
                    usefulBtn.classList.remove('active');
                }
            }

            // --- AJAX Handlers (Almost identical to index.blade.php) ---
            async function sendReaction(postCard, reactionEmoji) {
                 if (!currentUser) { window.location.href = '{{ route("login") }}'; return; }
                 const postId = postCard.dataset.postId;
                 // Optimistic Update (same as index.blade.php)
                 const likeBtn = postCard.querySelector('.like-btn');
                 const reactionIconSpan = likeBtn.querySelector('.like-icon-reaction');
                 const actionTextSpan = likeBtn.querySelector('.action-text');
                 let reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '{}' && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                 let likerNames = (postCard.dataset.likerNames && postCard.dataset.likerNames !== '[]') ? JSON.parse(postCard.dataset.likerNames) : [];
                 const currentReaction = reactions[currentUserId];
                 const currentUserName = currentUser.name;
                 const userIndex = likerNames.indexOf(currentUserName);
                 let nextReaction = (currentReaction === reactionEmoji) ? null : reactionEmoji;

                 if (nextReaction === null) { /* Unlike UI */ delete reactions[currentUserId]; if (userIndex > -1) likerNames.splice(userIndex, 1); likeBtn.classList.remove('liked'); reactionIconSpan.textContent = ''; likeBtn.style.color = 'var(--text-secondary)'; actionTextSpan.textContent = 'ถูกใจ'; }
                 else { /* Like/Change UI */ reactions[currentUserId] = nextReaction; if (userIndex === -1) likerNames.unshift(currentUserName); const reactionInfo = getReactionInfo(nextReaction); likeBtn.classList.add('liked'); reactionIconSpan.textContent = nextReaction; likeBtn.style.color = reactionInfo.color; actionTextSpan.textContent = 'ถูกใจ'; }
                 postCard.dataset.reactions = JSON.stringify(reactions); postCard.dataset.likerNames = JSON.stringify(likerNames); updatePostStats(postCard);

                 // AJAX Call (same as index.blade.php)
                 try {
                     const response = await fetch(`/community/posts/${postId}/react`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ reaction_type: reactionEmoji })
                     });

                    if (response.status === 401) { window.location.href = '{{ route("login") }}'; return; }
                    if (!response.ok) { const errorData = await response.json(); throw new Error(errorData.error || `Reaction failed: ${response.status}`); }
                    const data = await response.json();
                    postCard.dataset.reactions = data.reactions; postCard.dataset.likerNames = JSON.stringify(data.likerNames); updatePostStats(postCard); updateLikeButtonState(postCard);
                 } catch (error) { /* Error handling and UI revert (same as index.blade.php) */
                     console.error('Error sending reaction:', error); showModal('error-modal', 'เกิดข้อผิดพลาด', error.message || 'ไม่สามารถส่ง Reaction ได้');
                     // Revert UI
                     if (nextReaction === null) { reactions[currentUserId] = currentReaction; if (userIndex === -1 && currentReaction) likerNames.unshift(currentUserName); }
                     else { if (currentReaction) reactions[currentUserId] = currentReaction; else delete reactions[currentUserId]; if (userIndex === -1 && !currentReaction) { const revertIndex = likerNames.indexOf(currentUserName); if(revertIndex > -1) likerNames.splice(revertIndex, 1); } }
                     postCard.dataset.reactions = JSON.stringify(reactions); postCard.dataset.likerNames = JSON.stringify(likerNames); updatePostStats(postCard); updateLikeButtonState(postCard);
                 }
            }

            async function sendUseful(postCard) {
                 if (!currentUser) { window.location.href = '{{ route("login") }}'; return; }
                 const postId = postCard.dataset.postId;
                 // Optimistic Update (same as index.blade.php)
                 const usefulBtn = postCard.querySelector('.useful-btn');
                 let usefulUsers = (postCard.dataset.usefulUsers && postCard.dataset.usefulUsers !== '{}' && postCard.dataset.usefulUsers !== '[]') ? JSON.parse(postCard.dataset.usefulUsers) : {};
                 let usefulUserNames = (postCard.dataset.usefulUserNames && postCard.dataset.usefulUserNames !== '[]') ? JSON.parse(postCard.dataset.usefulUserNames) : [];
                 const currentUserName = currentUser.name;
                 const userIndex = usefulUserNames.indexOf(currentUserName);
                 const wasUseful = usefulUsers[currentUserId];

                 if (wasUseful) { /* Unmark UI */ delete usefulUsers[currentUserId]; if (userIndex > -1) usefulUserNames.splice(userIndex, 1); usefulBtn.classList.remove('active'); }
                 else { /* Mark UI */ usefulUsers[currentUserId] = true; if (userIndex === -1) usefulUserNames.unshift(currentUserName); usefulBtn.classList.add('active'); }
                 postCard.dataset.usefulUsers = JSON.stringify(usefulUsers); postCard.dataset.usefulUserNames = JSON.stringify(usefulUserNames); updateUsefulStats(postCard);

                // AJAX Call (same as index.blade.php)
                 try {
                     const response = await fetch(`/community/posts/${postId}/useful`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (response.status === 401) { window.location.href = '{{ route("login") }}'; return; }
                    if (!response.ok) { const errorData = await response.json(); throw new Error(errorData.error || `Useful toggle failed: ${response.status}`); }
                    const data = await response.json();
                    postCard.dataset.usefulUsers = data.usefulUsers; postCard.dataset.usefulUserNames = JSON.stringify(data.usefulUserNames); updateUsefulStats(postCard); updateUsefulButtonState(postCard);
                 } catch (error) { /* Error handling and UI revert (same as index.blade.php) */
                     console.error('Error sending useful:', error); showModal('error-modal', 'เกิดข้อผิดพลาด', error.message || 'ไม่สามารถกดมีประโยชน์ได้');
                     // Revert UI
                     if (wasUseful) { usefulUsers[currentUserId] = true; if(userIndex === -1) usefulUserNames.unshift(currentUserName); usefulBtn.classList.add('active'); }
                     else { delete usefulUsers[currentUserId]; const revertIndex = usefulUserNames.indexOf(currentUserName); if(revertIndex > -1) usefulUserNames.splice(revertIndex, 1); usefulBtn.classList.remove('active'); }
                     postCard.dataset.usefulUsers = JSON.stringify(usefulUsers); postCard.dataset.usefulUserNames = JSON.stringify(usefulUserNames); updateUsefulStats(postCard);
                 }
            }


            // --- Event Listeners (Adapted from index.blade.php) ---

            // Use container for event delegation
            singlePostContainer.addEventListener('click', async (event) => {
                const target = event.target;
                const postCard = target.closest('.post-card'); // Should always be the single post card
                if (!postCard) return;

                const postId = postCard.dataset.postId; // Get the actual post ID

                // --- Dropdown Handling ---
                const optionsBtn = target.closest('.options-btn');
                if (optionsBtn) {
                    const dropdown = optionsBtn.nextElementSibling;
                    if (dropdown) {
                         // Close other dropdowns (though there should only be one)
                         document.querySelectorAll('.options-dropdown.show').forEach(d => {
                            if (d !== dropdown) d.classList.remove('show');
                         });
                         dropdown.classList.toggle('show');
                    }
                    return;
                }

                const dropdownItem = target.closest('.options-dropdown-item');
                if (dropdownItem) {
                    const dropdown = dropdownItem.closest('.options-dropdown');

                    const deletePostBtn = dropdownItem.classList.contains('delete-post-btn');
                    const editPostBtn = dropdownItem.classList.contains('edit-post-btn');
                    const reportPostBtn = dropdownItem.classList.contains('report-post-btn');

                    if (deletePostBtn) {
                         if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโพสต์นี้?')) { if (dropdown) dropdown.classList.remove('show'); return; }
                         try { /* ... delete AJAX ... */
                              const response = await fetch(`/community/posts/${postId}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }});
                              if (response.status === 401 || response.status === 403) { const errorData = await response.json(); showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์ลบโพสต์นี้'); }
                              else if (!response.ok) { throw new Error('Failed to delete'); }
                              else {
                                  // On single post page, maybe redirect back to feed after delete?
                                  alert('โพสต์ถูกลบแล้ว');
                                  window.location.href = communityIndexUrl; // Redirect
                              }
                         } catch (error) { console.error('Error deleting post:', error); showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถลบโพสต์ได้'); }
                         finally { if (dropdown) dropdown.classList.remove('show'); }
                         return;
                    }

                    if (editPostBtn) { /* ... edit post logic ... */
                         const postBodyP = postCard.querySelector('.post-body p');
                         if (postBodyP.isContentEditable) { if (dropdown) dropdown.classList.remove('show'); return; }
                         const originalText = postBodyP.textContent; postBodyP.setAttribute('contenteditable', 'true'); postBodyP.focus();
                         const selection = window.getSelection(); const range = document.createRange(); range.selectNodeContents(postBodyP); range.collapse(false); selection.removeAllRanges(); selection.addRange(range);
                         const finishEditing = async (save = true) => {
                              postBodyP.removeEventListener('blur', handleBlur); postBodyP.removeEventListener('keydown', handleKeydown); postBodyP.setAttribute('contenteditable', 'false');
                              const newContent = postBodyP.textContent.trim();
                              if (!save || newContent === originalText || newContent === '') { postBodyP.textContent = originalText; return; }
                              try {
                                   const response = await fetch(`/community/posts/${postId}`, { method: 'PATCH', headers: {'Content-Type': 'application/json','Accept': 'application/json','X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({ content: newContent }) });
                                   if (response.status === 401 || response.status === 403) { const errorData = await response.json(); showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์แก้ไข'); throw new Error('Unauthorized'); }
                                   if (!response.ok) { const errorData = await response.json(); if(response.status === 422) { const err = Object.values(errorData.errors)[0][0]; showModal('error-modal', 'ข้อมูลไม่ถูกต้อง', err || 'เนื้อหาไม่ถูกต้อง'); } else { showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกได้'); } throw new Error('Save failed'); }
                                   const data = await response.json(); postBodyP.textContent = data.content;
                                   // Update local singlePostData if needed
                                   singlePostData.content = data.content;
                              } catch (error) { console.error('Error updating post:', error); postBodyP.textContent = originalText; }
                         };
                         const handleKeydown = (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); finishEditing(true); } else if (e.key === 'Escape') { finishEditing(false); } };
                         const handleBlur = () => { finishEditing(true); };
                         postBodyP.addEventListener('keydown', handleKeydown); postBodyP.addEventListener('blur', handleBlur);
                         if (dropdown) dropdown.classList.remove('show'); return;
                    }

                    if (reportPostBtn) { /* ... report logic ... */
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
                    // Close dropdown for other items like "Back" link
                     if (dropdown) dropdown.classList.remove('show');
                    // Allow default <a> behavior for back link

                } // End if (dropdownItem)

                 // --- Comment Controls ---
                 const commentElement = target.closest('.comment');
                 if (commentElement) {
                     const commentId = commentElement.dataset.commentId;
                     if(target.closest('.comment-delete-btn')) { /* ... delete comment AJAX ... */
                         if (!commentId || commentId.startsWith('temp-')) { if(commentId && commentId.startsWith('temp-')) commentElement.remove(); return; }
                         if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบคอมเมนต์นี้?')) return;
                         try {
                              const response = await fetch(`/community/comments/${commentId}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }});
                              if (response.status === 401 || response.status === 403) { const errorData = await response.json(); showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์ลบ'); return; }
                              if (!response.ok) throw new Error('Failed delete');
                              commentElement.remove();
                               // Update local singlePostData.comments
                               singlePostData.comments = singlePostData.comments.filter(c => c.id != commentId);
                         } catch(error){ console.error('Del comment err:', error); showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถลบได้'); }
                         return;
                     }
                     if(target.closest('.comment-block-btn')) { /* ... block user logic ... */
                         userToBlock = { element: commentElement, userId: target.closest('.comment-block-btn').dataset.userId }; showModal('block-confirmation-modal'); return;
                     }
                     if(target.closest('.comment-edit-btn')) { /* ... edit comment logic ... */
                         const p = commentElement.querySelector('.comment-text');
                         if (p.isContentEditable || !commentId || commentId.startsWith('temp-')) return;
                         const originalText = p.textContent; p.setAttribute('contenteditable', 'true'); p.focus();
                         const selection = window.getSelection(); const range = document.createRange(); range.selectNodeContents(p); range.collapse(false); selection.removeAllRanges(); selection.addRange(range);
                         const finishEditing = async (save = true) => {
                              p.removeEventListener('blur', handleBlur); p.removeEventListener('keydown', handleKeydown); p.setAttribute('contenteditable', 'false');
                              const newContent = p.textContent.trim();
                              if (!save || newContent === originalText || newContent === "") { p.textContent = originalText; return; }
                              try {
                                   const response = await fetch(`/community/comments/${commentId}`, { method: 'PATCH', headers: {'Content-Type': 'application/json','Accept': 'application/json','X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({ content: newContent }) });
                                   if (response.status === 401 || response.status === 403) { const errorData = await response.json(); showModal('error-modal', 'ไม่ได้รับอนุญาต', errorData.message || 'คุณไม่มีสิทธิ์แก้ไข'); throw new Error('Unauthorized'); }
                                   if (!response.ok) { const errorData = await response.json(); if(response.status === 422) { const err = Object.values(errorData.errors)[0][0]; showModal('error-modal', 'ข้อมูลไม่ถูกต้อง', err || 'เนื้อหาไม่ถูกต้อง'); } else { showModal('error-modal', 'เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกได้'); } throw new Error('Save failed'); }
                                   const data = await response.json(); p.textContent = data.content;
                                    // Update local singlePostData.comments
                                    const commentIndex = singlePostData.comments.findIndex(c => c.id == commentId);
                                    if(commentIndex > -1) singlePostData.comments[commentIndex].text = data.content;
                              } catch (error) { console.error('Err updating comment:', error); p.textContent = originalText; }
                         };
                         const handleKeydown = (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); finishEditing(true); } else if (e.key === 'Escape') { finishEditing(false); } };
                         const handleBlur = () => { finishEditing(true); };
                         p.addEventListener('keydown', handleKeydown); p.addEventListener('blur', handleBlur);
                         return;
                     }
                 } // End if (commentElement)


                 // --- Reaction & Useful ---
                 if (target.closest('.reaction-emoji')) {
                     sendReaction(postCard, target.closest('.reaction-emoji').dataset.reaction); return;
                 }
                 if (target.closest('.like-btn')) {
                     const reactions = (postCard.dataset.reactions && postCard.dataset.reactions !== '[]') ? JSON.parse(postCard.dataset.reactions) : {};
                     sendReaction(postCard, reactions[currentUserId] ? null : '👍'); return;
                 }
                 if (target.closest('.useful-btn')) {
                     sendUseful(postCard); return;
                 }

            }); // End Main Click Event Delegate


            // --- Add Comment Handler ---
             singlePostContainer.addEventListener('keydown', async (event) => {
                 const commentInput = event.target;
                 if (commentInput.matches('.add-comment input') && event.key === 'Enter' && commentInput.value.trim() !== '') {
                      event.preventDefault();
                      if (!currentUser) { window.location.href = '{{ route("login") }}'; return; }

                      const content = commentInput.value.trim();
                      const postCard = commentInput.closest('.post-card'); // The single post card
                      const postId = postCard.dataset.postId;
                      const addCommentContainer = commentInput.parentElement;

                      commentInput.disabled = true;

                      // Optimistic Update
                      const tempCommentId = `temp-${Date.now()}`;
                      const tempCommentData = { id: tempCommentId, author: currentUser, text: content, author_id: currentUserId };
                      // Pass singlePostData for permission check
                      const newCommentElement = createCommentElement(tempCommentData, singlePostData);
                      addCommentContainer.parentElement.insertBefore(newCommentElement, addCommentContainer);
                      commentInput.value = '';

                      try {
                          const response = await fetch(`/community/posts/${postId}/comments`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ content: content }) });
                          if (response.status === 401) { window.location.href = '{{ route("login") }}'; newCommentElement.remove(); return; }
                          if (response.status === 403) { const errorData = await response.json(); throw new Error(errorData.error || 'ไม่มีสิทธิ์คอมเมนต์'); }
                          if (!response.ok) { const errorData = await response.json(); if(response.status === 422){ const err=Object.values(errorData.errors)[0][0]; throw new Error(err||'ข้อมูลไม่ถูกต้อง'); } throw new Error(errorData.message||'Failed post'); }

                          const savedCommentData = await response.json();
                          newCommentElement.dataset.commentId = savedCommentData.id;
                          // Update local singlePostData.comments
                          singlePostData.comments.push(savedCommentData); // Add saved comment data

                      } catch (error) { console.error('Err posting comment:', error); showModal('error-modal', 'ผิดพลาด', error.message||'ส่งไม่ได้'); newCommentElement.remove(); commentInput.value = content; }
                      finally { commentInput.disabled = false; commentInput.focus(); }
                 }
             });


            // --- Modal Event Listeners (Same as index.blade.php) ---
            function hideBlockModal() { hideModal('block-confirmation-modal'); userToBlock = null; }
            closeErrorModalBtn.addEventListener('click', () => hideModal('error-modal'));
            errorModal.addEventListener('click', (e) => { if(e.target === errorModal) hideModal('error-modal'); });
            cancelBlockBtn.addEventListener('click', hideBlockModal);
            confirmBlockBtn.addEventListener('click', async () => { /* ... block AJAX ... */
                 if (!userToBlock || !userToBlock.userId) return;
                 const { element, userId } = userToBlock;
                 confirmBlockBtn.disabled = true; cancelBlockBtn.disabled = true;
                 try {
                     const response = await fetch(`/community/users/${userId}/block`, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
                     if (response.status === 401) { window.location.href = '{{ route("login") }}'; return; }
                     if (!response.ok) { const errorData = await response.json(); throw new Error(errorData.error || 'Failed block'); }
                     // Success: Remove the blocked user's comment from the UI immediately
                     element.remove();
                      // Update local singlePostData.comments
                      singlePostData.comments = singlePostData.comments.filter(c => c.author_id != userId);
                     hideBlockModal(); // Hide modal after success
                 } catch (error) { console.error('Err blocking:', error); showModal('error-modal', 'ผิดพลาด', `บล็อกไม่ได้: ${error.message}`); hideBlockModal(); }
                 finally { confirmBlockBtn.disabled = false; cancelBlockBtn.disabled = false; }
            });
            blockModal.addEventListener('click', (e) => { if(e.target === blockModal) hideBlockModal(); });


            // --- Initial Render ---
            // Use the singlePostData passed from the controller
            const postElement = createPostElement(singlePostData);
            singlePostContainer.appendChild(postElement);

        }); // End DOMContentLoaded
    </script>
</body>
</html>

