<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ยืนยันการสั่งซื้อ - Novel Noob</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{asset('assets/css/font.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">

    <style>
        body {
            background-color: var(--bg-dark); 
            padding-top: 100px;
        }
        main {
            padding: 40px 0;
        }
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .checkout-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-primary);
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .summary-item strong {
            color: var(--text-primary);
            font-size: 1.2rem;
        }
        .summary-item span {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }
        .summary-total {
            margin-top: 20px;
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-accent);
        }
        .payment-actions {
            margin-top: 40px;
        }
        .btn-full-width {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            justify-content: center; 
        }
        .back-link {
            display: block; 
            text-align: center; 
            margin-top: 20px; 
            color: var(--text-secondary);
            text-decoration: none;
        }
        .back-link:hover {
            color: var(--text-primary);
        }
        
        /* Dropdown CSS */
        .form-group {
            margin-bottom: 25px; 
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1.1rem;
            font-family: var(--font-body);
            color: var(--text-primary);
            background-color: var(--bg-dark); 
            border: 1px solid var(--border-color);
            border-radius: 8px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%238A94A6"><path d="M7 10l5 5 5-5H7z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 1.2em;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.3);
        }
        
        /* ⭐️ CSS สำหรับช่องแสดง QR Code ⭐️ */
        .qr-code-container {
            display: none; /* ซ่อนไว้ก่อน */
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            background-color: #ffffff; /* QR Code ควรอยู่บนพื้นขาว */
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        .qr-code-container img {
            width: 250px;
            height: 250px;
            margin: 15px auto;
            display: block;
        }
        .qr-code-container p {
            color: #333; /* สีตัวอักษรบนพื้นขาว */
            font-size: 1.1rem;
            font-weight: bold;
        }
        .qr-code-container .qr-wait-text {
            color: var(--primary-accent);
            font-size: 1rem;
            margin-top: 10px;
            font-weight: bold;
        }
        
        /* ⭐️ CSS สำหรับ Status Polling ⭐️ */
        .qr-status-box {
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            color: #333;
        }
        #status-indicator { 
            font-weight: bold; 
            padding: 5px 10px; 
            border-radius: 5px; 
        }
        #status-indicator.pending { 
            background-color: #fff3cd; /* #ffc */
            color: #856404; 
        }
        #status-indicator.successful { 
            background-color: #d4edda; 
            color: #155724; 
        }
        #status-indicator.failed { 
            background-color: #f8d7da; 
            color: #721c24; 
        }
    </style>
</head>
<body>

    <header class="navbar">
        <!-- (Navbar HTML ของคุณ) -->
        <div class="container">
            <nav class="navbar">
                <a href="{{ url('/') }}" class="logo">NovelNoob</a>
                <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="Open menu"></button>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="checkout-container">
                <h1 class="checkout-title">สรุปรายการสั่งซื้อ</h1>
                
                <div id="alert-message" class="alert-box" style="display: none; position: relative; margin-bottom: 20px;"></div>

                <!-- ⭐️ เพิ่มช่องสำหรับแสดง QR Code ⭐️ -->
                <div id="qr-code-container" class="qr-code-container">
                    <!-- JavaScript จะใส่ QR Code ที่นี่ -->
                </div>

                <form action="{{ route('credits.purchase') }}" method="POST" id="checkout-form">
                    @csrf
                    
                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                    
                    <div class="order-summary">
                        <div class="summary-item">
                            <strong>แพ็กเกจ:</strong>
                            <span>{{ number_format($package->credits) }} เครดิต</span>
                        </div>
                        <div class="summary-item summary-total">
                            <strong>ยอดชำระเงิน:</strong>
                            <span class="total">{{ number_format($package->price) }} บาท</span>
                        </div>
                    </div>

                    <div class="payment-actions">
                        <div class="form-group">
                            <label for="payment_method">เลือกช่องทางการชำระเงิน:</label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="" disabled selected>-- กรุณาเลือก --</option>
                                <option value="qr_promptpay">QR พร้อมเพย์</option>
                                {{-- <option value="card">Debit / Credit Card</option> --}}
                                {{-- (ตัวเลือกอื่นที่ comment ไว้) --}}
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full-width" id="checkout-submit-btn">
                            <span>ชำระเงิน ({{ number_format($package->price) }} บาท)</span>
                            <div class="loader" style="display: none; width: 16px; height: 16px; border-width: 2px;"></div>
                        </button>
                    </div>
                </form>
                
                <a href="{{ route('dashboard.index') }}" class="back-link">
                    &larr; กลับไปแดชบอร์ด
                </a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <!-- (Footer HTML ของคุณ) -->
    </footer>

    <script src="{{asset('assets/js/script.js')}}"></script>

  <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. เลือก Elements และประกาศตัวแปร Polling ---
            const submitButton = document.getElementById('checkout-submit-btn');
            const paymentForm = document.getElementById('checkout-form');
            const qrCodeContainer = document.getElementById('qr-code-container');
            const paymentMethodInput = document.getElementById('payment_method');
            
            const alertMessageDiv = document.getElementById('alert-message'); 
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            let currentChargeId = null; // ⭐️ ตัวแปรสำหรับ Polling

            // --- 2. Helper Functions (เหมือนเดิม) ---
            // (showAlert, hideAlert, handleFetchError functions remain here, truncated for brevity)
            function showAlert(message, type = 'info') {
                if (!alertMessageDiv) return;
                alertMessageDiv.textContent = message;
                alertMessageDiv.style.backgroundColor = type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#cce5ff';
                alertMessageDiv.style.color = type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#004085';
                alertMessageDiv.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#b8daff'}`;
                alertMessageDiv.style.position = 'relative'; 
                alertMessageDiv.style.padding = '15px';
                alertMessageDiv.style.borderRadius = '8px';
                alertMessageDiv.style.display = 'block';
                setTimeout(hideAlert, 5000);
            }
            function hideAlert() {
                if (alertMessageDiv) alertMessageDiv.style.display = 'none';
            }
            async function handleFetchError(response, contextMessage = 'เกิดข้อผิดพลาด') {
                // (โค้ด handleFetchError เดิมของคุณ... ทำงานได้ดี)
                const status = response.status;
                let errorData = {};
                let errorMessage = `เกิดข้อผิดพลาด (${status})`;
                try {
                    if (response.headers.get('content-type')?.includes('application/json')) {
                        errorData = await response.json();
                        errorMessage = errorData.message || errorData.error || errorMessage;
                    } else { errorMessage = (await response.text()) || errorMessage; }
                } catch (e) { console.error(`Failed to parse error body for ${contextMessage}:`, e); }
                console.error(`${contextMessage} (${status}):`, errorData.details || errorData.errors || errorData || errorMessage);
                let alertMsg = errorMessage;
                if (errorData.errors) alertMsg += "\n- " + Object.values(errorData.errors).flat().join("\n- ");
                showAlert(alertMsg, 'error'); 
                if ((status === 401 || status === 403) && errorData.redirect_to) {
                    window.location.href = errorData.redirect_to;
                    throw new Error("Redirecting due to authorization error.");
                }
                const error = new Error(errorMessage);
                error.status = status; error.data = errorData;
                throw error;
            }
            // --- End Helpers ---

            
            // ⭐️ 3. [เพิ่ม] ฟังก์ชัน Polling สำหรับตรวจสอบสถานะการชำระเงิน ⭐️
            async function checkPaymentStatus() {
                // ต้องมี chargeId ก่อนเริ่ม Polling
                if (!currentChargeId) return; 

                const statusElement = document.getElementById('status-indicator');
                const qrWaitText = document.getElementById('qr-wait-text'); 

                if (qrWaitText) qrWaitText.textContent = "กำลังตรวจสอบสถานะ...";

                try {
                    // ⭐️ [แก้ไข] ใช้ /check-status/ ที่คุณสั่ง
                    const response = await fetch(`/check-status/${currentChargeId}`); 
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    const data = await response.json();
                    const status = data.status;

                    if (statusElement) {
                        statusElement.innerText = status.toUpperCase();
                        statusElement.className = status; // 'pending', 'successful', 'failed'
                    }

                    if (status === 'successful') {
                        if (qrWaitText) qrWaitText.textContent = "ชำระเงินสำเร็จ! กำลังเปลี่ยนหน้า...";
                        showAlert('ชำระเงินสำเร็จ! กำลังอัปเดตเครดิตของคุณ', 'success');
                        // !!! สำเร็จแล้ว: Redirect ไปหน้า Success !!!
                        // สมมติว่า route('payment.success') คือ /payment/success
                        window.location.href = '/dashboard'; 
                    } else if (status === 'failed') {
                        if (qrWaitText) qrWaitText.textContent = "การชำระเงินล้มเหลว กรุณาลองใหม่";
                        showAlert('การชำระเงินล้มเหลว กรุณาลองเลือกแพ็กเกจใหม่', 'error');
                        // !!! ล้มเหลว: Redirect ไปหน้า Failed !!!
                        // สมมติว่า route('payment.failed') คือ /payment/failed
                        window.location.href = '/dashboard'; 
                    } else {
                        // ถ้ายังไม่สำเร็จ (pending) ให้เรียกตัวเองซ้ำใน 3 วินาที
                        // if (qrWaitText) qrWaitText.textContent = "กำลังรอการยืนยันการชำระเงิน...";
                        setTimeout(checkPaymentStatus, 3000);
                    }
                } catch (error) {
                    console.error('Error checking status:', error);
                    // กรณี Error ให้ลองใหม่
                    setTimeout(checkPaymentStatus, 5000); // Error-retry หน่วงเวลานานขึ้น
                }
            }
            // ⭐️ [จบ] ฟังก์ชัน Polling ⭐️


            // --- 4. ⭐️ [แก้ไข] Logic ใน 'onchange' ของ Select ⭐️ ---
            paymentMethodInput.addEventListener('change', async function(event) {
                // Reset Polling state
                currentChargeId = null; 
                
                const paymentMethod = this.value;

                // ⭐️ 1. ตรวจสอบว่าเลือก QR หรือไม่
                if (paymentMethod === 'qr_promptpay') {
                    
                    // ดึง packageId
                    const packageIdInput = paymentForm.querySelector('input[name="package_id"]');
                    const packageId = packageIdInput ? packageIdInput.value : null;

                    if (!packageId) {
                        showAlert('ไม่พบ ID แพ็กเกจ', 'error');
                        return;
                    }
                    if (this.disabled) return; // กันการกดซ้ำ

                    // --- ⭐️ [แก้ไข] ซ่อนปุ่ม และปิด Dropdown ---
                    this.disabled = true; // ปิด dropdown
                    submitButton.style.display = 'none'; // ⭐️ ซ่อนปุ่มไปเลย
                    hideAlert(); 
                    
                    // ⭐️ แสดง Loader ชั่วคราวในช่อง QR
                    qrCodeContainer.innerHTML = `<div class="loader" style="display: inline-block; width: 30px; height: 30px; border-width: 4px; margin: 20px auto;"></div>`;
                    qrCodeContainer.style.backgroundColor = 'transparent'; // ไม่ต้องพื้นขาว
                    qrCodeContainer.style.border = 'none';
                    qrCodeContainer.style.display = 'block';

                    try {
                        const response = await fetch('{{ route("credits.purchase") }}', { 
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken ?? ''
                            },
                            body: JSON.stringify({ 
                                package_id: packageId,
                                payment_method: paymentMethod 
                            })
                        });

                        if (!response.ok) await handleFetchError(response, 'เกิดข้อผิดพลาดในการสร้างรายการชำระเงิน'); 

                        const data = await response.json();
                        
                        if (data.status === 'qr_created' && data.qr_image_url) {
                            // ⭐️ บันทึก Charge ID ก่อน Polling
                            currentChargeId = data.charge_id;
                            
                            // 1. สร้าง QR Code และแสดง (คืนสไตล์ให้ช่อง QR)
                            qrCodeContainer.style.backgroundColor = '#ffffff';
                            qrCodeContainer.style.border = '1px solid var(--border-color)';
                            qrCodeContainer.innerHTML = `
                                <p>${data.message || 'กรุณาสแกน QR Code เพื่อชำระเงิน'}</p>
                                <img src="${data.qr_image_url}" alt="QR Code PromptPay">
                                <div class="qr-status-box">
                                    สถานะ: <span id="status-indicator" class="pending">PENDING</span>
                                    <p class="qr-wait-text" id="qr-wait-text"></p>
                                </div>
                            `;
                            qrCodeContainer.style.display = 'block';

                            // 2. ซ่อนฟอร์มและปุ่ม (เพราะ QR แสดงแล้ว)
                            paymentForm.querySelector('.order-summary').style.display = 'none';
                            paymentForm.querySelector('.payment-actions').style.display = 'none'; 
                            
                            // ⭐️ 3. เริ่ม Polling ทันทีที่ QR โหลดเสร็จ
                            setTimeout(checkPaymentStatus, 2000); // เริ่ม Polling ใน 2 วินาที
                            

                        } else {
                            // กรณี Backend ส่ง error
                            showAlert(data.error || 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ', 'error');
                            // ⭐️ คืนค่าปุ่ม/select ให้กดใหม่ได้
                            this.disabled = false;
                            submitButton.style.display = 'block'; 
                            qrCodeContainer.style.display = 'none'; 
                        }

                    } catch (error) {
                        if (error.message !== "Redirecting due to authorization error.") {
                            console.error('Purchase Error caught:', error);
                            // ⭐️ คืนค่าปุ่ม/select ให้กดใหม่ได้
                            this.disabled = false;
                            submitButton.style.display = 'block'; 
                            qrCodeContainer.style.display = 'none'; 
                        }
                    } 

                } else {
                    // ⭐️ 2. ถ้าผู้ใช้เลือกตัวเลือกอื่น (เช่น "-- กรุณาเลือก --")
                    qrCodeContainer.style.display = 'none';
                    submitButton.style.display = 'block'; // ⭐️ ตรวจสอบว่าปุ่มแสดงอยู่
                }
            });
        });
    </script>
</body>
</html>
