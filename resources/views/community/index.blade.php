<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .create-post-avatar img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
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
        .post-author-avatar img {
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
        }
        .item-icon svg {
            width: 20px;
            height: 20px;
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
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s ease-in-out;
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
        .comment-avatar img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
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
        }
        .comment-content:hover .comment-controls {
            opacity: 0.7;
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
        
        #loader {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
            display: none;
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
                    <input type="text" placeholder="ค้นหาในชุมชน...">
                </div>
                <div class="nav-actions">
                    <a href="{{route('dashboard.index')}}" class="btn btn-primary">ไปที่แดชบอร์ด</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="community-feed">

                <div class="create-post-card">
                    <div class="create-post-avatar">
                        <img src="https://placehold.co/100x100/A9B4D9/121828?text=S" alt="Your Avatar">
                    </div>
                    <input type="text" class="create-post-input" id="create-post-input" placeholder="เขียนอะไรสักหน่อย...">
                </div>

                <div id="post-feed-container">
                    <!-- Posts will be dynamically inserted here -->
                </div>

                <div id="loader">
                    <p>กำลังโหลดโพสต์เพิ่มเติม...</p>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Novel Noob. All Rights Reserved.</p>
        </div>
    </footer>
    
    <div id="block-confirmation-modal" class="modal-overlay">
        <div class="modal-content">
            <h3>ยืนยันการบล็อกผู้ใช้</h3>
            <p>คุณแน่ใจหรือไม่ว่าต้องการบล็อกผู้ใช้นี้? คอมเมนต์ทั้งหมดของผู้ใช้นี้ทุกโพสต์จะถูกลบออก และผู้ใช้นี้จะไม่สามารถแสดงความคิดเห็นในโพสต์ของคุณได้อีก</p>
            <div class="modal-actions">
                <button id="cancel-block-btn" class="btn btn-secondary">ยกเลิก</button>
                <button id="confirm-block-btn" class="btn btn-danger">ยืนยันการบล็อก</button>
            </div>
        </div>
    </div>

    <!-- Central Script -->
    <script src="{{asset('assets/js/script.js')}}"></script>
    
    <!-- Page-specific script -->
    <script>
        
        document.addEventListener('DOMContentLoaded', () => {
            
            const postInput = document.getElementById('create-post-input');
            const postFeedContainer = document.getElementById('post-feed-container');
            const loader = document.getElementById('loader');
            const blockModal = document.getElementById('block-confirmation-modal');
            const cancelBlockBtn = document.getElementById('cancel-block-btn');
            const confirmBlockBtn = document.getElementById('confirm-block-btn');
            let commentToBlock = null;

            // Mock Data Generation
             const mockLikerNames = ["คิตติกร จันทร์ลาออ", "มูนาวาร์ ฮุสเซน", "อาห์ติชาม อาลี", "นาซิม บิลลาห์", "คาบินายา มูธูลิงกัม", "สุรภี เวอร์มา", "อัญญญา อัญญมณี", "นาดีร์ เชาดรี", "ฮุย ดวง", "กฤต จิรรุ่งเรืองกิจ", "โอมิด กราฟฟิก", "ปัน ดีดีเอ", "คาร์โล คิมาโด", "อาลี ไบ", "อับดุล มุยซ์", "แองเจลีน เบลันโด", "โบนี่ สโนว์ดอน", "เจ สุภารัตน์", "วาเลอรี่ กอนซาเลซ"];
            const mockAuthors = [
                { name: 'ม่านมุก', avatar: 'https://placehold.co/100x100/6C5DD3/FFFFFF?text=M' },
                { name: 'นักเดินทาง', avatar: 'https://placehold.co/100x100/5DD39E/FFFFFF?text=N' },
                { name: 'เพียงฝัน', avatar: 'https://placehold.co/100x100/8375e7/FFFFFF?text=P' },
                { name: 'เงาจันทร์', avatar: 'https://placehold.co/100x100/D35D5D/FFFFFF?text=NG' },
                { name: 'สายลม', avatar: 'https://placehold.co/100x100/3b82f6/FFFFFF?text=S' }
            ];
            const mockContents = [
                "เพิ่งใช้ฟีเจอร์สร้างโครงเรื่องอัจฉริยะไปค่ะ ประทับใจมาก แค่ใส่ไอเดียไปไม่กี่ประโยคก็ได้พล็อตที่น่าสนใจกลับมาเลย",
                "กำลังเขียนนิยายแนวแฟนตาซีอยู่ครับ มีเคล็ดลับในการสร้างโลกยังไงให้น่าสนใจกันบ้างครับ มาแชร์กันหน่อย",
                "Writer's block เป็นอะไรที่ทรมานมาก แต่พอได้ใช้ฟีเจอร์ช่วยเขียนต่อแล้วรู้สึกไปต่อได้ง่ายขึ้นเยอะเลยค่ะ",
                "มีใครลองใช้ AI ช่วยสร้างตัวละครบ้างไหมครับ ผลลัพธ์ที่ได้น่าทึ่งมาก ได้ตัวละครที่มีมิติน่าสนใจสุดๆ",
                "เพิ่งเขียนนิยายเรื่องแรกจบด้วย Novel Noob ดีใจมากครับ! เป็นเครื่องมือที่เหมาะสำหรับมือใหม่จริงๆ",
                "เคล็ดลับการเขียน: ลองอ่านงานของนักเขียนหลายๆ ท่าน จะช่วยเปิดมุมมองและพัฒนาสำนวนของเราได้ดีมากครับ",
                "ตอนนี้กำลังติดนิยายแนวสืบสวนสอบสวน มีใครแนะนำเรื่องไหนเป็นพิเศษไหมครับ?",
                "การสร้าง Ebook ขายเองครั้งแรกตื่นเต้นมากเลยค่ะ มีใครมีประสบการณ์อยากแชร์ไหมคะ"
            ];
             const mockComments = [
                { author: mockAuthors[2], text: "ลองแล้วเหมือนกันค่ะ ช่วยตอนคิดพล็อตไม่ออกได้ดีมากเลย!" },
                { author: mockAuthors[3], text: "น่าสนใจมากครับ เดี๋ยวต้องไปลองใช้ดูบ้างแล้ว" },
                { author: mockAuthors[4], text: "ของผมเน้นสร้างแผนที่กับประวัติศาสตร์ของโลกก่อนเลยครับ จะทำให้เขียนง่ายขึ้นเยอะ" },
                { author: mockAuthors[0], text: "จริงค่ะ พอมีไทม์ไลน์แล้วเขียนสนุกขึ้นเยอะ" },
            ];

            let allMockPosts = [];
            for (let i = 0; i < 30; i++) {
                const author = mockAuthors[i % mockAuthors.length];
                const reactions = {};
                const usefulUsers = {};
                const likeCount = Math.floor(Math.random() * 50);
                const usefulCount = Math.floor(Math.random() * 10);
                const emojis = ['👍', '❤️', '😮', '😂'];

                for(let j=0; j < likeCount; j++) {
                    reactions[`user${j}`] = emojis[j % emojis.length];
                }
                 for(let k=0; k < usefulCount; k++) {
                    usefulUsers[`user${k}`] = true;
                }
                
                let postComments = [];
                if (Math.random() > 0.4) {
                    const commentCount = Math.random() > 0.3 ? 1 : 2;
                    for (let c = 0; c < commentCount; c++) {
                        postComments.push(mockComments[(i + c) % mockComments.length]);
                    }
                }

                allMockPosts.push({
                    id: i,
                    author: author.name,
                    avatar: author.avatar,
                    timestamp: `${Math.floor(Math.random() * 58) + 2} นาทีที่แล้ว`,
                    content: mockContents[i % mockContents.length] + ` (โพสต์ที่ ${i + 1})`,
                    reactions: JSON.stringify(reactions),
                    usefulUsers: JSON.stringify(usefulUsers),
                    comments: postComments
                });
            }

            let currentPostIndex = 0;
            const postsPerLoad = 10;
            let isLoading = false;
            let observedTarget = null;

            // --- Core Functions ---
            function createCommentElement(commentData) {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                const escapedText = commentData.text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                commentDiv.innerHTML = `
                    <div class="comment-avatar"><img src="${commentData.author.avatar}" alt="Avatar"></div>
                    <div class="comment-content">
                        <div class="comment-controls">
                             <button class="comment-edit-btn comment-control-btn" title="แก้ไขคอมเมนต์">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/></svg>
                            </button>
                            <button class="comment-block-btn comment-control-btn" title="บล็อกผู้ใช้">
                               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-slash" viewBox="0 0 16 16"><path d="M13.879 10.414a2.502 2.502 0 0 0-3.465 3.465l3.465-3.465Zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465Zm-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z"/></svg>
                            </button>
                            <button class="comment-delete-btn comment-control-btn" title="ลบคอมเมนต์">&times;</button>
                        </div>
                        <div class="comment-author">${commentData.author.name}</div>
                        <p class="comment-text">${escapedText}</p>
                    </div>`;
                return commentDiv;
            }

            function createPostElement(postData) {
                const postCard = document.createElement('div');
                postCard.className = 'post-card';
                postCard.dataset.postId = postData.id;
                postCard.dataset.reactions = postData.reactions;
                postCard.dataset.usefulUsers = postData.usefulUsers;

                const escapedPostText = postData.content.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                
                postCard.innerHTML = `
                    <div class="post-header">
                        <div class="post-author-info">
                            <div class="post-author-avatar"><img src="${postData.avatar}" alt="Avatar"></div>
                            <div class="post-author-details">
                                <div class="name">${postData.author}</div>
                                <div class="timestamp">${postData.timestamp}</div>
                            </div>
                        </div>
                        <div class="post-options">
                            <button class="options-btn">
                               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </button>
                            <div class="options-dropdown">
                                <a href="community/single-post/${postData.id}" class="options-dropdown-item">
                                    <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5 0V6.375c0-.621.504-1.125 1.125-1.125h1.5c.621 0 1.125.504 1.125 1.125v1.5m-4.5 0h4.5" /></svg></div>
                                    <div class="item-text"><div class="item-title">แสดงโพสต์</div><div class="item-subtitle">ดูโพสต์นี้ในหน้าของตัวเอง</div></div>
                                </a>
                                <div class="dropdown-separator"></div>
                                <div class="options-dropdown-item edit-post-btn">
                                    <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg></div>
                                    <div class="item-text"><div class="item-title">แก้ไขโพสต์</div><div class="item-subtitle">ปรับแก้เนื้อหาในโพสต์นี้</div></div>
                                </div>
                                <div class="options-dropdown-item">
                                    <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg></div>
                                    <div class="item-text"><div class="item-title delete">ลบโพสต์</div><div class="item-subtitle">โพสต์นี้จะถูกลบอย่างถาวร</div></div>
                                </div>
                                <div class="dropdown-separator"></div>
                                <div class="options-dropdown-item">
                                    <div class="item-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" /></svg></div>
                                    <div class="item-text"><div class="item-title">รายงานโพสต์</div><div class="item-subtitle">แจ้งให้เราทราบหากโพสต์นี้มีปัญหา</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="post-body"><p>${escapedPostText}</p></div>
                    <div class="post-stats">
                        <div class="reactions-summary-wrapper">
                             <div class="reactions-summary">
                                <!-- Reaction icons will be prepended here -->
                                <span class="total-likes"></span>
                            </div>
                            <div class="likers-tooltip stats-tooltip">
                                <ul></ul>
                            </div>
                        </div>
                        <div class="useful-stats-wrapper">
                            <div class="useful-stats">
                                <span class="useful-count"></span>
                                <span class="useful-icon">💎</span>
                            </div>
                            <div class="useful-tooltip stats-tooltip">
                                <ul></ul>
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
                            <div class="comment-avatar"><img src="https://placehold.co/100x100/A9B4D9/121828?text=S" alt="Avatar"></div>
                            <input type="text" placeholder="แสดงความคิดเห็น...">
                        </div>
                    </div>`;

                const commentsContainer = postCard.querySelector('.post-comments');
                const addCommentDiv = commentsContainer.querySelector('.add-comment');
                if (postData.comments && postData.comments.length > 0) {
                    postData.comments.forEach(commentData => {
                        const commentElement = createCommentElement(commentData);
                        commentsContainer.insertBefore(commentElement, addCommentDiv);
                    });
                }

                // Call the global update function
                window.updatePostStats(postCard);
                return postCard;
            }
            
            function loadMorePosts() {
                if (isLoading || currentPostIndex >= allMockPosts.length) return;
                isLoading = true;
                loader.style.display = 'block';

                setTimeout(() => { // Simulate network delay
                    const postsToLoad = allMockPosts.slice(currentPostIndex, currentPostIndex + postsPerLoad);
                    postsToLoad.forEach(postData => {
                        const postElement = createPostElement(postData);
                        postFeedContainer.appendChild(postElement);
                    });
                    currentPostIndex += postsToLoad.length;
                    isLoading = false;
                    loader.style.display = 'none';
                    updateObserverTarget();
                }, 1000);
            }

            function handleReaction(button, reactionEmoji) {
                const postCard = button.closest('.post-card');
                const reactionIconSpan = button.querySelector('.like-icon-reaction');
                const actionTextSpan = button.querySelector('.action-text');
                const mockUserId = 'currentUser';
                
                if (!postCard.dataset.reactions) postCard.dataset.reactions = JSON.stringify({});
                let reactions = JSON.parse(postCard.dataset.reactions);
                const currentReaction = reactions[mockUserId];

                if (reactionEmoji === null || currentReaction === reactionEmoji) {
                    delete reactions[mockUserId];
                    button.classList.remove('liked');
                    reactionIconSpan.textContent = '';
                    actionTextSpan.textContent = 'ถูกใจ';
                    button.style.color = 'var(--text-secondary)';
                } else {
                    reactions[mockUserId] = reactionEmoji;
                    button.classList.add('liked');
                    reactionIconSpan.textContent = reactionEmoji;
                    const reactionInfo = window.getReactionInfo(reactionEmoji);
                    actionTextSpan.textContent = 'ถูกใจ';
                    button.style.color = reactionInfo.color;
                }
                postCard.dataset.reactions = JSON.stringify(reactions);
                window.updatePostStats(postCard);
            }

            postInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && postInput.value.trim() !== '') {
                    event.preventDefault();
                    const newPostData = {
                        id: allMockPosts.length,
                        author: 'สมชาย ใจดี',
                        avatar: 'https://placehold.co/100x100/A9B4D9/121828?text=S',
                        timestamp: 'เมื่อสักครู่',
                        content: postInput.value.trim(),
                        reactions: JSON.stringify({}),
                        usefulUsers: JSON.stringify({}),
                        comments: []
                    };
                    const newPostElement = createPostElement(newPostData);
                    postFeedContainer.prepend(newPostElement);
                    allMockPosts.unshift(newPostData);
                    postInput.value = '';
                }
            });
            
            window.addEventListener('click', function(e) {
                document.querySelectorAll('.options-dropdown.show').forEach(dropdown => {
                    if (!dropdown.parentElement.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });

            postFeedContainer.addEventListener('keydown', (event) => {
                const commentInput = event.target;
                if (commentInput.matches('.add-comment input') && event.key === 'Enter' && commentInput.value.trim() !== '') {
                    event.preventDefault();
                    const newCommentData = {
                        author: { 
                            name: 'สมชาย ใจดี',
                            avatar: 'https://placehold.co/100x100/A9B4D9/121828?text=S'
                        },
                        text: commentInput.value.trim()
                    };
                    const newCommentElement = createCommentElement(newCommentData);
                    const addCommentContainer = commentInput.parentElement;
                    addCommentContainer.parentElement.insertBefore(newCommentElement, addCommentContainer);
                    commentInput.value = '';
                }
            });

            postFeedContainer.addEventListener('click', (event) => {
                const target = event.target;
                const postCard = target.closest('.post-card');
                if (!postCard) return;

                const dropdownItem = target.closest('.options-dropdown-item');
                if (dropdownItem) {
                    const dropdown = dropdownItem.closest('.options-dropdown');
                    if (dropdown) dropdown.classList.remove('show');
                }

                const optionsBtn = target.closest('.options-btn');
                if(optionsBtn){
                    const dropdown = optionsBtn.nextElementSibling;
                    document.querySelectorAll('.options-dropdown.show').forEach(d => {
                        if (d !== dropdown) d.classList.remove('show');
                    });
                    dropdown.classList.toggle('show');
                    return;
                }

                const deletePostBtn = target.closest('.item-title.delete');
                if (deletePostBtn) {
                    postCard.remove();
                    const postId = parseInt(postCard.dataset.postId, 10);
                    allMockPosts = allMockPosts.filter(post => post.id !== postId);
                    return;
                }
                
                const deleteCommentBtn = target.closest('.comment-delete-btn');
                if (deleteCommentBtn) {
                    deleteCommentBtn.closest('.comment')?.remove();
                    return;
                }
                
                const blockCommentBtn = target.closest('.comment-block-btn');
                if(blockCommentBtn) {
                    commentToBlock = blockCommentBtn.closest('.comment');
                    blockModal.classList.add('show');
                    return;
                }
                
                const editPostBtn = target.closest('.edit-post-btn');
                if(editPostBtn) {
                    const postBodyP = postCard.querySelector('.post-body p');
                    if (postBodyP.isContentEditable) return;
                    const originalText = postBodyP.textContent;
                    postBodyP.setAttribute('contenteditable', 'true');
                    postBodyP.focus();
                    
                    const selection = window.getSelection();
                    const range = document.createRange();
                    range.selectNodeContents(postBodyP);
                    range.collapse(false);
                    selection.removeAllRanges();
                    selection.addRange(range);

                    const finishEditing = () => {
                        postBodyP.removeEventListener('blur', finishEditing);
                        postBodyP.removeEventListener('keydown', handleKeydown);
                        postBodyP.setAttribute('contenteditable', 'false');
                    };
                    const handleKeydown = (e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            finishEditing();
                        } else if (e.key === 'Escape') {
                            postBodyP.textContent = originalText;
                            finishEditing();
                        }
                    };
                    postBodyP.addEventListener('blur', finishEditing);
                    postBodyP.addEventListener('keydown', handleKeydown);
                    return;
                }

                const editCommentBtn = target.closest('.comment-edit-btn');
                if (editCommentBtn) {
                    const commentText = editCommentBtn.closest('.comment-content').querySelector('.comment-text');
                    if (commentText.isContentEditable) return;
                    const originalText = commentText.textContent;
                    commentText.setAttribute('contenteditable', 'true');
                    commentText.focus();

                    const selection = window.getSelection();
                    const range = document.createRange();
                    range.selectNodeContents(commentText);
                    range.collapse(false);
                    selection.removeAllRanges();
                    selection.addRange(range);

                    const finishEditing = () => {
                        commentText.removeEventListener('blur', finishEditing);
                        commentText.removeEventListener('keydown', handleKeydown);
                        commentText.setAttribute('contenteditable', 'false');
                    };
                    const handleKeydown = (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            finishEditing();
                        } else if (e.key === 'Escape') {
                            commentText.textContent = originalText;
                            finishEditing();
                        }
                    };
                    commentText.addEventListener('blur', finishEditing);
                    commentText.addEventListener('keydown', handleKeydown);
                    return;
                }

                const reaction = target.closest('.reaction-emoji');
                if (reaction) {
                    handleReaction(postCard.querySelector('.like-btn'), reaction.dataset.reaction);
                    return;
                }

                const likeBtn = target.closest('.like-btn');
                if (likeBtn) {
                    handleReaction(likeBtn, likeBtn.classList.contains('liked') ? null : '👍');
                    return;
                }

                const usefulBtn = target.closest('.useful-btn');
                if (usefulBtn) {
                    const mockUserId = 'currentUser';
                    if (!postCard.dataset.usefulUsers) postCard.dataset.usefulUsers = JSON.stringify({});
                    let usefulUsers = JSON.parse(postCard.dataset.usefulUsers);
                    if (usefulUsers[mockUserId]) {
                        delete usefulUsers[mockUserId];
                        usefulBtn.classList.remove('active');
                    } else {
                        usefulUsers[mockUserId] = true;
                        usefulBtn.classList.add('active');
                    }
                    postCard.dataset.usefulUsers = JSON.stringify(usefulUsers);
                    window.updatePostStats(postCard);
                }
            });

            function hideBlockModal() {
                blockModal.classList.remove('show');
                commentToBlock = null;
            }

            cancelBlockBtn.addEventListener('click', hideBlockModal);
            confirmBlockBtn.addEventListener('click', () => {
                if (commentToBlock) {
                    commentToBlock.remove();
                }
                hideBlockModal();
            });
            blockModal.addEventListener('click', (e) => {
                if(e.target === blockModal) hideBlockModal();
            });

            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !isLoading) {
                    loadMorePosts();
                }
            }, { rootMargin: '500px 0px' });

            function updateObserverTarget() {
                if (observedTarget) observer.unobserve(observedTarget);
                if (currentPostIndex >= allMockPosts.length) {
                    observer.disconnect();
                    loader.style.display = 'none';
                    return;
                }
                const posts = postFeedContainer.querySelectorAll('.post-card');
                const targetIndex = posts.length - 5; 
                if (targetIndex >= 0) {
                    const newTarget = posts[targetIndex];
                    observer.observe(newTarget);
                    observedTarget = newTarget;
                }
            }

            const initialPosts = allMockPosts.slice(0, postsPerLoad);
            initialPosts.forEach(postData => {
                const postElement = createPostElement(postData);
                postFeedContainer.appendChild(postElement);
            });
            currentPostIndex = postsPerLoad;
            updateObserverTarget();
        });
    </script>
</body>
</html>
