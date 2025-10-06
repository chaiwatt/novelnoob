<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เขียนนิยายด้วย AI ง่ายๆ | จากไอเดียสู่ Ebook ขายได้จริง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Link to the central stylesheet -->
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    
    <!-- Link to the central stylesheet -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <style>
        /* --- Page-Specific Styles for Create Page --- */

        /* Override body for centered layout */
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .main-container {
            width: 100%;
            max-width: 900px;
            background-color: var(--bg-light);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            transition: all 0.5s ease-in-out;
            position: relative;
        }
        
        /* Page System */
        .page { display: none; }
        .page.active { display: block; }

        /* Shared Header */
        header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }
        header h1 {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            margin-bottom: 5px;
        }
        header p {
            font-size: 1rem;
            color: var(--text-secondary);
        }
        
        /* Home link icon */
        .home-link {
            position: absolute;
            top: 25px;
            right: 30px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
            z-index: 10;
        }
        .home-link:hover { color: var(--text-primary); }
        .home-link svg { width: 28px; height: 28px; }

        /* === Step 1: Blueprint Form === */
        .form-grid { display: grid; gap: 20px; }
        .form-group .label-with-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .form-textarea { min-height: 120px; resize: vertical; }

        .advanced-toggle {
            display: flex; align-items: center; gap: 10px;
            color: var(--text-secondary); cursor: pointer; margin-top: 10px;
        }
        .advanced-toggle input { display: none; }
        .custom-checkbox {
            width: 20px; height: 20px; background-color: var(--bg-dark);
            border: 2px solid var(--border-color); border-radius: 5px;
            display: flex; justify-content: center; align-items: center; transition: all 0.3s;
        }
        .custom-checkbox .checkmark { display: none; }
        .advanced-toggle input:checked + .custom-checkbox {
            background-color: var(--primary-accent); border-color: var(--primary-accent);
        }
        .advanced-toggle input:checked + .custom-checkbox .checkmark {
            display: block; color: white; font-weight: bold;
        }
        .advanced-options {
            max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out;
        }
        .advanced-options.open { max-height: 1000px; margin-top: 20px; }

        .form-actions {
            margin-top: 30px; text-align: center;
            display: flex; gap: 15px; justify-content: center;
        }

        .btn-icon {
            background-color: transparent; border: 1px solid var(--secondary-accent);
            color: var(--text-secondary); padding: 5px 10px; border-radius: 6px;
            font-size: 0.8rem; gap: 5px; height: 30px;
        }
        .btn-icon svg {
            width: 14px; height: 14px; stroke: currentColor;
            stroke-width: 2; fill: none;
        }
        .btn-icon:hover:not(:disabled) {
            background-color: var(--secondary-hover); color: var(--text-primary);
        }
        .btn-icon.loading svg { animation: rotation 0.8s linear infinite; }
        
        .coin-icon {
            color: var(--status-ready); margin-left: 8px; vertical-align: middle;
        }
        
        /* Modal specific styles */
        #read-chapter-modal .modal-content {
            max-width: 800px; height: 90vh; max-height: 800px;
            display: flex; flex-direction: column;
        }
        #read-chapter-modal .modal-body { flex-grow: 1; overflow-y: auto; display: flex; }
        #read-chapter-modal #read-modal-content {
            height: 100%; width: 100%; resize: none;
            line-height: 1.8; padding: 15px;
        }

        /* === Step 2: Outline Review === */
        #outline-reviewer .loading-container { text-align: center; padding: 50px 0; }
        .outline-content h2 {
            font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 10px;
            padding-bottom: 10px; border-bottom: 2px solid var(--primary-accent);
        }
        .outline-section { margin-bottom: 30px; line-height: 1.8; }
        .outline-section h3 { color: var(--text-secondary); margin-bottom: 15px; }
        .character-card, .lore-item {
            background-color: var(--bg-dark); padding: 15px; border-radius: 8px; margin-bottom: 10px;
        }
        .character-card strong { color: var(--primary-accent); }
        .chapter-summary-item { margin: 30px 0; }
        .chapter-summary-item strong { display: block; margin-bottom: 5px; }

        /* === Step 3: Dashboard === */
        #dashboard-header {
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; text-align: left; margin-top: 20px; 
        }
        #dashboard-header h1 { margin-bottom: 0; }
        #dashboard-header p { text-align: left; }
        #dashboard-auto-write-btn {
            padding: 10px 20px; font-size: 0.9rem; flex-shrink: 0;
        }
        #dashboard-auto-write-btn svg { width: 18px; height: 18px; }

        .progress-section { margin-bottom: 30px; }
        .progress-label { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: bold; color: var(--text-secondary); }
        .progress-bar-container { width: 100%; height: 12px; background-color: var(--bg-dark); border-radius: 6px; overflow: hidden; }
        .progress-bar { width: 0%; height: 100%; background: linear-gradient(90deg, var(--primary-accent), var(--primary-hover)); border-radius: 6px; transition: width 0.5s ease-in-out; }
        .chapter-list { display: flex; flex-direction: column; gap: 15px; }
        .act-title { 
            font-family: var(--font-heading); color: var(--text-primary); margin-top: 20px;
            margin-bottom: 5px; padding-bottom: 5px; border-bottom: 2px solid var(--primary-accent);
            font-size: 1.5rem;
        }
        .act-title:first-of-type { margin-top: 0; }
        .chapter-item { 
            display: flex; align-items: center; justify-content: space-between;
            background-color: var(--bg-dark); padding: 15px 20px; border-radius: 12px;
            border: 1px solid transparent; transition: all 0.3s ease;
        }
        .chapter-item[data-status="latest"] { border-color: var(--status-latest); box-shadow: 0 0 15px rgba(96, 165, 250, 0.2); }
        .chapter-item[data-status="ready"] { border-color: var(--status-ready); }
        .chapter-item[data-status="locked"] { opacity: 0.5; }
        .chapter-info { display: flex; align-items: center; gap: 15px; }
        .status-indicator { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
        .chapter-item[data-status="completed"] .status-indicator { background-color: var(--status-completed); }
        .chapter-item[data-status="latest"] .status-indicator { background-color: var(--status-latest); }
        .chapter-item[data-status="ready"] .status-indicator { background-color: var(--status-ready); }
        .chapter-item[data-status="locked"] .status-indicator { background-color: var(--status-locked); }
        .chapter-title { font-family: var(--font-heading); font-size: 1.2rem; font-weight: 500; }
        .chapter-number { font-size: 1rem; font-weight: bold; color: var(--text-secondary); }
        .chapter-actions { display: flex; align-items: center; gap: 10px; }
        .chapter-actions .btn { padding: 8px 18px; font-size: 0.9rem; display: none; }
        
        .btn-neutral { background-color: var(--secondary-accent); color: var(--text-primary); }
        .btn-neutral:hover:not(:disabled) { background-color: var(--secondary-hover); }
        .chapter-actions .loader { width: 24px; height: 24px; display: none; }
    </style>
</head>
<body>
    
    <!-- Plot Helper Modal -->
    <div class="modal-overlay" id="plot-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>ระบุบริบทของพล็อต</h2>
                <button class="modal-close-btn" id="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>โปรดใส่รายละเอียดเพิ่มเติม เพื่อให้เรื่องราวชัดเจนยิ่งขึ้น เช่น “การฆาตกรรมในยุคโชซอน”, “โลกอนาคตอีก 1,000 ปี”, “เหตุการณ์ในสมัยอยุธยาตอนต้น”, “แพทย์หลงยุคไปอดีตไปยุคจีนโบราณ” โดยให้อธิบายโครื่องเรื่องให้ครบ</p>
                <div class="form-group">
                    <textarea id="plot-context-input" class="form-textarea" placeholder="ป้อนบริบทที่นี่..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="modal-cancel-btn">ยกเลิก</button>
                <button class="btn btn-primary" id="modal-gen-btn">
                    <span>สร้างพล็อต
                        <svg class="coin-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" fill="currentColor"></circle>
                            <text x="12" y="12.5" fill="var(--text-primary)" font-size="12" font-weight="bold" text-anchor="middle" dominant-baseline="middle">5</text>
                        </svg>
                    </span>
                    <div class="loader" style="display: none;"></div>
                </button>
            </div>
        </div>
    </div>

    <div class="main-container">

           <a href="{{route('dashboard.index')}}" class="home-link" title="กลับไปหน้าหลัก">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>
            
        <!-- Page 1: Blueprint Creator -->
        <div id="blueprint-creator" class="page active">
            <header>
                <h1>สร้างสรรค์เรื่องราวของคุณ</h1>
                <p>เริ่มต้นด้วยการป้อนไอเดีย แล้วให้ AI ช่วยสร้างโครงเรื่องที่น่าทึ่ง</p>
            </header>
            <main>
                <form id="blueprint-form" class="form-grid">
                    <div class="form-group">
                        <label for="title_prompt">แนวทางชื่อเรื่อง</label>
                        <input type="text" id="title_prompt" class="form-input" placeholder="เช่น ฆาตกรในกระจกเงา" required>
                    </div>
                    <div class="form-group">
                        <label for="character_nationality">สัญชาติตัวละคร</label>
                        <select id="character_nationality" class="form-select">
                            <option value="ไทย">ไทย</option>
                            <option value="ญี่ปุ่น">ญี่ปุ่น</option>
                            <option value="เกาหลี">เกาหลี</option>
                            <option value="จีน">จีน</option>
                            <option value="อเมริกัน">อเมริกัน</option>
                            <option value="อังกฤษ">อังกฤษ</option>
                            <option value="ฝรั่งเศส">ฝรั่งเศส</option>
                            <option value="สเปน">สเปน</option>
                            <option value="อินเดีย">อินเดีย</option>
                            <option value="รัสเซีย">รัสเซีย</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="label-with-action">
                            <label for="setting_prompt">พล็อตเรื่อง / ฉาก</label>
                            <button type="button" class="btn btn-icon" id="gen-plot-btn" title="ให้ AI ช่วยคิดพล็อต">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3v2.35M10.15 6.85L8.74 5.44M18.56 15.26l-1.41-1.41M12 21v-2.35M6.85 13.85l-1.41 1.41M12 8.4a3.6 3.6 0 00-3.6 3.6 3.6 3.6 0 003.6 3.6 3.6 3.6 0 003.6-3.6 3.6 3.6 0 00-3.6-3.6z"></path><path d="M22 12h-2.35M4.35 12H2M15.26 5.44l-1.41 1.41M5.44 18.56l1.41-1.41"></path>
                                </svg>
                                <span>ช่วยคิดพล็อต</span>
                            </button>
                        </div>
                        <textarea id="setting_prompt" class="form-textarea" placeholder="อธิบายพล็อตเรื่องย่อของคุณที่นี่... หรือกดปุ่มด้านบนให้ AI ช่วยคิด" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="style_to_use">เลือกสไตล์การเขียน</label>
                        <select id="style_to_use" class="form-select">
                            <option value="style_detective">แนวสืบสวนสอบสวน</option>
                            <option value="style_erotic">แนวอิโรติก</option>
                            <option value="style_romance">แนวโรแมนติก</option>
                            <option value="style_sci-fi">แนววิทยาศาสตร์</option>
                        </select>
                    </div>
                     <div class="form-group">
                        <label for="act_count">เลือกโครงสร้างเรื่อง (จำนวนองก์)</label>
                        <select id="act_count" class="form-select">
                           <option value="3">3 องก์ (15 บท) - โครงสร้างมาตรฐาน</option>
                           <option value="4">4 องก์ (20 บท)</option>
                           <option value="5">5 องก์ (25 บท) - โครงสร้างแบบยาว</option>
                        </select>
                    </div>
                    
                    <label class="advanced-toggle">
                        <input type="checkbox" id="advanced-toggle-checkbox">
                        <span class="custom-checkbox"><span class="checkmark">✓</span></span>
                        <span>ปรับแต่งสไตล์และกฎขั้นสูง</span>
                    </label>

                    <div id="advanced-options" class="advanced-options">
                        <div class="form-group">
                            <label for="custom_style_guide">Style Guide (แก้ไขได้)</label>
                            <textarea id="custom_style_guide" class="form-textarea"></textarea>
                        </div>
                         <div class="form-group">
                            <label for="custom_genre_rules">Genre Rules (แก้ไขได้)</label>
                            <textarea id="custom_genre_rules" class="form-textarea"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="generate-outline-btn">
                            <span>
                                ให้ AI สร้างโครงเรื่อง
                                <svg class="coin-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" fill="currentColor"></circle>
                                    <text x="12" y="12.5" fill="var(--text-primary)" font-size="12" font-weight="bold" text-anchor="middle" dominant-baseline="middle">25</text>
                                </svg>
                            </span>
                            <div class="loader" style="display: none;"></div>
                        </button>
                    </div>
                </form>
            </main>
        </div>

        <!-- Page 2: Outline Reviewer -->
        <div id="outline-reviewer" class="page">
             <header>
                <h1>ตรวจสอบโครงเรื่อง</h1>
                <p>นี่คือโครงเรื่องที่ AI สร้างขึ้นตามไอเดียของคุณ</p>
            </header>
            <main>
                <div class="loading-container">
                    <div class="loader" style="width: 50px; height: 50px; border-width: 5px; margin: auto;"></div>
                    <p style="margin-top: 15px; color: var(--text-secondary);">AI กำลังใช้จินตนาการ... โปรดรอสักครู่</p>
                </div>
                <div class="outline-content" style="display: none;">
                    <!-- Content will be injected by JavaScript -->
                </div>
                 <div class="form-actions">
                    <button class="btn btn-primary" id="confirm-outline-btn" style="display:none;">
                        <span>เริ่มเขียนนิยาย</span>
                    </button>
                </div>
            </main>
        </div>

        <!-- Page 3: Writing Dashboard -->
        <div id="writing-dashboard" class="page">
            <header id="dashboard-header">
                <div id="dashboard-header-content">
                     <!-- h1 and p tags will be injected here -->
                </div>
                <button class="btn btn-secondary" id="dashboard-auto-write-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                    <span>เขียนอัตโนมัติ</span>
                    <div class="loader" style="display: none;"></div>
                </button>
            </header>
            <section class="progress-section">
                <div class="progress-label">
                    <span>ความคืบหน้า</span>
                    <span id="progress-text">0 / 15 บท</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>
            </section>
            <main class="chapter-list" id="chapter-list">
                <!-- Chapters will be injected by JavaScript -->
            </main>
            <div class="form-actions" id="completion-actions" style="display: none;">
                <a class="btn btn-primary" href="{{route('dashboard.index')}}">🎉 ยินดีด้วย! เขียนนิยายสำเร็จแล้ว</a>
            </div>
        </div>

    </div>

    <!-- Read/Edit Chapter Modal -->
    <div class="modal-overlay" id="read-chapter-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="read-modal-title"></h2>
                <button class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <textarea id="read-modal-content" class="form-textarea"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-action="close-read-modal">ปิด</button>
                <button class="btn btn-primary" data-action="save-read-modal">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </div>
    </div>
    
    <!-- Link to the central script file (optional but good practice) -->
    <script src="{{asset('assets/js/script.js')}}"></script>

    <!-- Page-Specific Script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        // --- Application State ---
        let appState = {
            currentPage: 'blueprint-creator',
            novelBlueprint: null,
            generatedOutline: null, 
            novelData: null
        };
        
        // --- Mock Data for Styles & Rules ---
        const defaultStyles = {
            style_detective: `--- STYLE GUIDE (ต้องปฏิบัติตามอย่างเคร่งครัด) ---\n\nมุมมองการเล่าเรื่อง (POV): ใช้มุมมองบุรุษที่ 3 แบบจำกัด (Third-Person Limited) โดยติดตามการสังเกต, การวิเคราะห์, และตรรกะของ [ตัวละครนักสืบ] เป็นหลัก...`,
            style_erotic: `--- STYLE GUIDE (ต้องปฏิบัติตามอย่างเคร่งครัด) ---\n\nมุมมองการเล่าเรื่อง (POV): ใช้มุมมองบุรุษที่ 3 ที่เน้นความรู้สึกภายในของตัวละคร...`,
            style_romance: `--- STYLE GUIDE (ต้องปฏิบัติตามอย่างเคร่งครัด) ---\n\nมุมมองการเล่าเรื่อง (POV): สลับระหว่างมุมมองของพระเอกและนางเอก เพื่อให้เห็นความคิดและความรู้สึกของทั้งสองฝ่าย...`,
            style_sci_fi: `--- STYLE GUIDE (ต้องปฏิบัติตามอย่างเคร่งครัด) ---\n\nมุมมองการเล่าเรื่อง (POV): ใช้มุมมองบุรุษที่ 3 ที่สามารถเห็นภาพรวมของเหตุการณ์ แต่ยังคงเน้นไปที่ตัวละครหลัก...`
        };
        const defaultRules = {
            style_detective: `{"rule_id": "R01", "name": "ห้ามเปิดเผยตัวคนร้ายก่อนเวลาอันควร"}`,
            style_erotic: `{"rule_id": "R01_SENSATION_OVER_ACTION", "name": "เน้นความรู้สึกเหนือการกระทำ"}`,
        };

        // --- DOM Elements ---
        const pages = document.querySelectorAll('.page');
        const blueprintForm = document.getElementById('blueprint-form');
        const generateBtn = document.getElementById('generate-outline-btn');
        const advancedToggle = document.getElementById('advanced-toggle-checkbox');
        const advancedOptions = document.getElementById('advanced-options');
        const styleSelect = document.getElementById('style_to_use');
        const customStyleTextarea = document.getElementById('custom_style_guide');
        const customRulesTextarea = document.getElementById('custom_genre_rules');
        const confirmOutlineBtn = document.getElementById('confirm-outline-btn');
        
        // Modal elements
        const plotModal = document.getElementById('plot-modal');
        const genPlotBtn = document.getElementById('gen-plot-btn');
        const modalCloseBtn = document.getElementById('modal-close-btn');
        const modalCancelBtn = document.getElementById('modal-cancel-btn');
        const modalGenBtn = document.getElementById('modal-gen-btn');
        const plotContextInput = document.getElementById('plot-context-input');
        const settingPromptTextarea = document.getElementById('setting_prompt');
        const readChapterModal = document.getElementById('read-chapter-modal');


        // --- Page Navigation ---
        function navigateTo(pageId) {
            appState.currentPage = pageId;
            pages.forEach(page => page.classList.remove('active'));
            document.getElementById(pageId).classList.add('active');
            window.scrollTo(0, 0); 
        }

        // --- Advanced Options Logic ---
        advancedToggle.addEventListener('change', () => {
            if (advancedToggle.checked) {
                advancedOptions.classList.add('open');
                updateAdvancedTextareas();
            } else {
                advancedOptions.classList.remove('open');
            }
        });
        styleSelect.addEventListener('change', updateAdvancedTextareas);
        function updateAdvancedTextareas() {
            if (!advancedToggle.checked) return;
            const selectedStyle = styleSelect.value;
            customStyleTextarea.value = defaultStyles[selectedStyle] || 'ไม่พบ Style Guide สำหรับแนวนี้';
            customRulesTextarea.value = defaultRules[selectedStyle] || 'ไม่พบ Genre Rules สำหรับแนวนี้';
        }

        // --- Plot Modal Logic ---
        function openModal(modal) { modal.classList.add('visible'); }
        function closeModal(modal) { modal.classList.remove('visible'); }
        
        genPlotBtn.addEventListener('click', () => openModal(plotModal));
        modalCloseBtn.addEventListener('click', () => closeModal(plotModal));
        modalCancelBtn.addEventListener('click', () => closeModal(plotModal));
        plotModal.addEventListener('click', (e) => { if (e.target === plotModal) closeModal(plotModal); });

        modalGenBtn.addEventListener('click', () => {
            const titlePrompt = document.getElementById('title_prompt').value;
            const styleText = styleSelect.options[styleSelect.selectedIndex].text;
            const plotContext = plotContextInput.value;

            if (!titlePrompt || !plotContext) {
                alert('กรุณากรอก "แนวทางชื่อเรื่อง" และ "บริบท" ก่อนครับ');
                return;
            }

            const loader = modalGenBtn.querySelector('.loader');
            const btnText = modalGenBtn.querySelector('span');
            modalGenBtn.disabled = true;
            loader.style.display = 'block';
            btnText.style.display = 'none';

            setTimeout(() => {
                const mockPlot = `จากแนวทางเรื่อง "${titlePrompt}" ในบริบท "${plotContext}" และสไตล์ "${styleText}", เรื่องราวได้เริ่มต้นขึ้น... (เนื้อหาจำลอง)\n\nตัวเอกได้ค้นพบความลับอันดำมืดที่ถูกซ่อนไว้ใต้ฉากหน้าที่สวยงามของสังคม ทำให้ต้องเข้าไปพัวพันกับเหตุการณ์ที่ไม่คาดฝัน เขา/เธอต้องเผชิญหน้ากับศัตรูที่ทรงอำนาจและไขปริศนาเพื่อเปิดโปงความจริง ก่อนที่ทุกอย่างจะสายเกินไป`;
                settingPromptTextarea.value = mockPlot;

                modalGenBtn.disabled = false;
                loader.style.display = 'none';
                btnText.style.display = 'inline-flex';
                closeModal(plotModal);
            }, 2500);
        });
        
        // --- Read Chapter Modal Logic ---
        const MOCK_CHAPTER_TEXT = `ในยามนี้ เคนจิโร่กำลังนั่งอยู่หลังโต๊ะทำงานที่เต็มไปด้วยเอกสารกระจัดกระจายและแก้วกาแฟเย็นชืด สายตาคมกริบของเขากวาดมองข้อมูลบนจอภาพที่เปล่งแสงสีฟ้าสลัว ใบหน้าของชายวัยกลางคนคนหนึ่งถูกฉายขึ้นมาพร้อมกับรายละเอียดคดีเล็กๆ น้อยๆ—การโกงยักยอกเงินจากการลงทุนที่ดูผิวเผินแล้วแสนจะธรรมดา ทว่าสำหรับเคนจิโร่แล้ว ไม่มีคดีใดที่ธรรมดาอย่างแท้จริง`;
        function openReadModal(chapterId) {
            readChapterModal.querySelector('#read-modal-title').textContent = `เนื้อหาบทที่ ${chapterId}`;
            readChapterModal.querySelector('#read-modal-content').value = `บทที่ ${chapterId}: แสงเงาแห่งนีโอ-โตเกียว (ตัวอย่าง)\n\n${MOCK_CHAPTER_TEXT}`;
            openModal(readChapterModal);
        }
        readChapterModal.addEventListener('click', (e) => {
            if (e.target.matches('.modal-close-btn, [data-action="close-read-modal"]')) closeModal(readChapterModal);
            if (e.target.matches('[data-action="save-read-modal"]')) {
                alert('บันทึกข้อมูลแล้ว (จำลอง)');
                closeModal(readChapterModal);
            }
            if (e.target === readChapterModal) closeModal(readChapterModal);
        });

        // --- Event Listener: Blueprint Form Submission ---
        blueprintForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btnText = generateBtn.querySelector('span');
            const loader = generateBtn.querySelector('.loader');
            generateBtn.disabled = true;
            btnText.style.display = 'none';
            loader.style.display = 'block';
            
            setTimeout(() => {
                appState.novelBlueprint = { title_prompt: document.getElementById('title_prompt').value };
                appState.generatedOutline = getMockOutline();
                navigateTo('outline-reviewer');
                setTimeout(() => renderOutlineReview(), 500);
            }, 3000);
        });
        
        // --- Render Outline Review Page ---
        function renderOutlineReview() {
            const outline = appState.generatedOutline;
            const loadingContainer = document.querySelector('#outline-reviewer .loading-container');
            const contentContainer = document.querySelector('#outline-reviewer .outline-content');
            
            const loreHtml = outline.story_bible.world_and_lore 
                ? `<div class="outline-section"><h3>โลกและข้อมูลเสริม</h3>${outline.story_bible.world_and_lore.map(item => `<div class="lore-item"><p>${item}</p></div>`).join('')}</div>`
                : '';

            contentContainer.innerHTML = `
                <div class="outline-section"><h2>${outline.story.title}</h2><p><strong>ธีมเรื่อง:</strong> ${outline.story.theme}</p></div>
                <div class="outline-section"><h3>ตัวละครหลัก</h3>${outline.story_bible.characters.map(char => `<div class="character-card"><strong>${char.name}:</strong> ${char.role}</div>`).join('')}</div>
                ${loreHtml}
                 <div class="outline-section"><h3>โครงเรื่องและบทต่างๆ</h3>${outline.story.acts.map(act => `<div><h4>${act.summary}</h4>${act.chapters.map(chap => `<div class="chapter-summary-item"><strong>บทที่ ${chap.no}: ${chap.title}</strong><p>${chap.summary}</p></div>`).join('')}</div>`).join('')}</div>`;
            
            loadingContainer.style.display = 'none';
            contentContainer.style.display = 'block';
            confirmOutlineBtn.style.display = 'inline-flex';
        }

        // --- Event Listener: Confirm Outline ---
        confirmOutlineBtn.addEventListener('click', () => {
             appState.novelData = {
                title: appState.generatedOutline.story.title,
                totalChapters: appState.generatedOutline.story.acts.flatMap(a => a.chapters).length,
                acts: appState.generatedOutline.story.acts.map(act => ({
                    title: act.summary,
                    chapters: act.chapters.map(chap => ({ id: chap.no, title: chap.title, status: 'locked' }))
                }))
             };
             if(appState.novelData.acts[0]?.chapters[0]){
                 appState.novelData.acts[0].chapters[0].status = 'ready';
             }
             navigateTo('writing-dashboard');
             initializeDashboard();
        });

        // --- Dashboard Logic ---
        function initializeDashboard() {
            document.getElementById('dashboard-header-content').innerHTML = `<h1>${appState.novelData.title}</h1><p>โดย: AI Assistant</p>`;
            const chapterListContainer = document.getElementById('chapter-list');
            chapterListContainer.innerHTML = ''; // Clear previous content
            
            appState.novelData.acts.forEach(act => {
                const actTitle = document.createElement('h2');
                actTitle.className = 'act-title';
                actTitle.textContent = act.title.length > 100 ? act.title.slice(0, 100) + '...' : act.title;
                chapterListContainer.appendChild(actTitle);
                act.chapters.forEach(chapter => {
                    const chapterElement = document.createElement('div');
                    chapterElement.className = 'chapter-item';
                    chapterElement.dataset.chapterId = chapter.id;
                    chapterElement.innerHTML = `
                        <div class="chapter-info">
                            <span class="status-indicator"></span><span class="chapter-number">บทที่ ${chapter.id}</span>
                            <h3 class="chapter-title">${chapter.title}</h3>
                        </div>
                        <div class="chapter-actions">
                            <div class="loader"></div>
                            <button class="btn btn-neutral" data-action="read">อ่าน</button>
                            <button class="btn btn-secondary" data-action="rewrite">เขียนใหม่ <svg class="coin-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="currentColor"></circle><text x="12" y="12.5" fill="var(--text-primary)" font-size="12" font-weight="bold" text-anchor="middle" dominant-baseline="middle">10</text></svg></button>
                            <button class="btn btn-primary" data-action="write">เขียนบทนี้ <svg class="coin-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="currentColor"></circle><text x="12" y="12.5" fill="var(--text-primary)" font-size="12" font-weight="bold" text-anchor="middle" dominant-baseline="middle">10</text></svg></button>
                        </div>`;
                    chapterListContainer.appendChild(chapterElement);
                });
            });
            renderDashboardUI();
        }

        function renderDashboardUI() {
            if (!appState.novelData) return;
            let completedCount = 0;
            const allChapters = appState.novelData.acts.flatMap(act => act.chapters);
            
            allChapters.forEach(chapter => {
                const element = document.querySelector(`.chapter-item[data-chapter-id='${chapter.id}']`);
                if (!element) return;
                element.dataset.status = chapter.status;
                if (chapter.status === 'completed' || chapter.status === 'latest') completedCount++;
                const actions = element.querySelector('.chapter-actions');
                const [loader, readBtn, rewriteBtn, writeBtn] = actions.children;
                loader.style.display = 'none'; readBtn.style.display = 'none';
                rewriteBtn.style.display = 'none'; writeBtn.style.display = 'none';
                if (chapter.status === 'completed') readBtn.style.display = 'block';
                if (chapter.status === 'latest') { readBtn.style.display = 'block'; rewriteBtn.style.display = 'block'; }
                if (chapter.status === 'ready') writeBtn.style.display = 'block';
            });

            document.getElementById('progress-bar').style.width = `${(completedCount / appState.novelData.totalChapters) * 100}%`;
            document.getElementById('progress-text').textContent = `${completedCount} / ${appState.novelData.totalChapters} บท`;
            
            const autoWriteBtn = document.getElementById('dashboard-auto-write-btn');
            const allCompleted = allChapters.every(ch => ch.status === 'completed');
            document.getElementById('completion-actions').style.display = allCompleted ? 'flex' : 'none';
            autoWriteBtn.style.display = allCompleted ? 'none' : 'inline-flex';
            autoWriteBtn.disabled = false;
            autoWriteBtn.querySelector('span').textContent = 'เขียนอัตโนมัติ';
            autoWriteBtn.querySelector('.loader').style.display = 'none';
        }
        
        function handleChapterGeneration(chapterId, isRewrite = false, callback = null) {
            const element = document.querySelector(`.chapter-item[data-chapter-id='${chapterId}']`);
            const actions = element.querySelector('.chapter-actions');
            actions.querySelector('.loader').style.display = 'block';
            Array.from(actions.querySelectorAll('.btn')).forEach(btn => btn.style.display = 'none');
            
            setTimeout(() => {
                const allChapters = appState.novelData.acts.flatMap(act => act.chapters);
                const currentChapter = allChapters.find(ch => ch.id === chapterId);

                if (!isRewrite && currentChapter) {
                    const currentLatest = allChapters.find(ch => ch.status === 'latest');
                    if (currentLatest) currentLatest.status = 'completed';
                    
                    const isLastChapter = (chapterId === appState.novelData.totalChapters);
                    currentChapter.status = isLastChapter ? 'completed' : 'latest';

                    if (!isLastChapter) {
                        const nextChapter = allChapters.find(ch => ch.id === chapterId + 1);
                        if (nextChapter) nextChapter.status = 'ready';
                    }
                }
                renderDashboardUI();
                if (callback) callback();
            }, callback ? 1000 : 2500); // Shorter delay when auto-writing
        }

        function startAutoWriting() {
            const nextChapterToWrite = appState.novelData.acts.flatMap(act => act.chapters).find(ch => ch.status === 'ready');
            if (nextChapterToWrite) {
                handleChapterGeneration(nextChapterToWrite.id, false, startAutoWriting);
            } else {
                renderDashboardUI();
            }
        }
        
        document.getElementById('dashboard-auto-write-btn').addEventListener('click', (e) => {
            const btn = e.currentTarget;
            btn.disabled = true;
            btn.querySelector('span').textContent = 'กำลังเขียน...';
            btn.querySelector('.loader').style.display = 'inline-block';
            startAutoWriting();
        });

        document.getElementById('chapter-list').addEventListener('click', (e) => {
            const button = e.target.closest('.btn');
            if (!button) return;
            const chapterId = parseInt(button.closest('.chapter-item').dataset.chapterId);
            const action = button.dataset.action;
            if (action === 'write') handleChapterGeneration(chapterId, false);
            else if (action === 'rewrite') handleChapterGeneration(chapterId, true);
            else if (action === 'read') openReadModal(chapterId);
        });
        
        // --- Mock Data for Outline ---
        function getMockOutline() {
            return {
                "story": { "title": "ปริศนาแห่งเซ็นทินัล", "theme": "การแสวงหาความจริงในยุคที่เทคโนโลยีบิดเบือนทุกสิ่ง",
                    "acts": [ { "act": 1, "summary": "องก์ที่ 1 เปิดตัวเคนจิโร่ อาซาฮี และนำเสนอคดีแรกที่ดูเหมือนเป็นเพียงคนหาย...",
                            "chapters": [ { "no": 1, "title": "แสงเงาแห่งนีโอ-โตเกียว", "summary": "เคนจิโร่ อาซาฮี นักสืบเอกชน ได้รับการว่าจ้างจากหญิงชราให้ตามหาหลานชายที่หายตัวไป..." }, { "no": 2, "title": "ร่องรอยในข้อมูลดิจิทัล", "summary": "เคนจิโร่และมิคาโกะพบไฟล์ที่ถูกเข้ารหัสในคอมพิวเตอร์ของบุคคลที่หายไป..." }, { "no": 3, "title": "การเผชิญหน้าในอาณาจักรแห่งเทคโนโลยี", "summary": "เคนจิโร่บุกไปที่ 'เทคคอร์ป' และได้พบกับ CEO ผู้เยือกเย็น..." }, { "no": 4, "title": "คำเตือนจากเงามืด", "summary": "ไฟล์ที่ถอดรหัสได้เผยถึงโปรเจกต์ลับ และเคนจิโร่ถูกสะกดรอยตาม..." }, { "no": 5, "title": "ปมปริศนาที่ซับซ้อน", "summary": "เคนจิโร่เริ่มเชื่อมโยงข้อมูลและพบความไม่ชอบมาพากลของเทคคอร์ป..." } ] },
                        { "act": 2, "summary": "องก์ที่ 2 เคนจิโร่เริ่มเจาะลึกเข้าไปในเครือข่ายของ 'โครงการเซ็นทินัล'...",
                            "chapters": [ { "no": 6, "title": "เงาตามรอย", "summary": "การสืบสวนนำเขาไปสู่กลุ่มนักเคลื่อนไหว 'เดอะเรนเจอร์ส'..." }, { "no": 7, "title": "การถอดรหัสที่ซับซ้อน", "summary": "มิคาโกะถอดรหัสไฟล์ได้สำเร็จ เผยให้เห็นเทคโนโลยีควบคุมพฤติกรรม..." }, { "no": 8, "title": "ความจริงอันบิดเบือน", "summary": "เคนจิโร่ตามรอยไปยังศูนย์ทดลองลับและถูกขัดขวาง..." }, { "no": 9, "title": "เหยื่อที่ถูกลืม", "summary": "เขาพบนักวิจัยอีกคนที่หายไปในสภาพจิตไม่สมประกอบ..." }, { "no": 10, "title": "คำสารภาพของปีศาจ", "summary": "เคนจิโร่เผชิญหน้ากับ CEO อีกครั้ง พร้อมกับหลักฐาน..." } ] },
                        { "act": 3, "summary": "องก์ที่ 3 คือจุดสูงสุดของการต่อสู้ เคนจิโร่ต้องเปิดโปงความจริงทั้งหมด...",
                            "chapters": [ { "no": 11, "title": "กับดักในเขาวงกตข้อมูล", "summary": "เคนจิโร่และมิคาโกะพยายามเจาะระบบหลักของเทคคอร์ป..." }, { "no": 12, "title": "คืนแห่งการตัดสิน", "summary": "เคนจิโร่แทรกซึมเข้าไปในใจกลางเซิร์ฟเวอร์หลัก..." }, { "no": 13, "title": "เปิดโปงความจริง", "summary": "ข้อมูลลับถูกปล่อยสู่สาธารณะ สร้างความโกลาหลในเมือง..." }, { "no": 14, "title": "การเผชิญหน้าครั้งสุดท้าย", "summary": "เคนจิโร่เผชิญหน้ากับ CEO เป็นครั้งสุดท้าย..." }, { "no": 15, "title": "แสงสุดท้ายในมหานคร", "summary": "ยูจิโร่ถูกจับกุม และเคนจิโร่กลับมายังสำนักงานของเขา..." } ] } ] },
                "story_bible": { "characters": [ { "name": "เคนจิโร่ อาซาฮี", "role": "นักสืบเอกชนผู้ฉลาดหลักแหลม ช่างสังเกต..." }, { "name": "มิคาโกะ ทานากะ", "role": "แฮกเกอร์อิสระอัจฉริยะและเป็นผู้ช่วย..." }, { "name": "ยูจิโร่ ฮายาชิ", "role": "CEO ของ 'เทคคอร์ป' ผู้ทรงอิทธิพลและมีวิสัยทัศน์..." } ],
                    "world_and_lore": [ "นีโอ-โตเกียว: มหานครแห่งอนาคตที่เต็มไปด้วยแสงสีนีออน...", "โครงการเซ็นทินัล: โปรเจกต์ลับสุดยอดของเทคคอร์ป..." ] } };
        }
    });
    </script>
</body>
</html>
