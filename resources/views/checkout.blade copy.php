<!DOCTYPE html>
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
            /* ใช้พื้นหลังสีเข้มจาก style.css */
            background-color: var(--bg-dark); 
            padding-top: 100px; /* เว้นที่สำหรับ Navbar */
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
            color: var(--primary-accent); /* ใช้สีไฮไลท์ */
        }
        .payment-actions {
            margin-top: 40px;
        }
        /* ใช้ .btn จาก style.css ได้เลย */
        .btn-full-width {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            justify-content: center; /* สำหรับ .btn ที่เป็น flex */
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
        /* ... (CSS เดิมของคุณ) ... */

        /* ⭐️ เพิ่ม CSS สำหรับ Dropdown ⭐️ */
        .form-group {
            margin-bottom: 25px; /* เว้นระยะห่างก่อนปุ่ม */
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
            font-family: var(--font-body); /* ใชฟอนต์เดียวกับเว็บ */
            color: var(--text-primary);
            background-color: var(--bg-dark); /* สีพื้นหลังเหมือน input */
            border: 1px solid var(--border-color);
            border-radius: 8px;
            appearance: none; /* ลบสไตล์เริ่มต้นของ OS */
            -webkit-appearance: none;
            -moz-appearance: none;
            
            /* เพิ่มลูกศร (arrow) ให้ dropdown */
            /* (ใช้ SVG สีเดียวกับ --text-secondary) */
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%238A94A6"><path d="M7 10l5 5 5-5H7z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 1.2em;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.3); /* สีเงาตอน focus */
        }
        /* ⭐️ จบส่วน CSS ที่เพิ่ม ⭐️ */
    </style>
</head>
<body>

    <header class="navbar">
        <div class="container">
            <nav class="navbar">
                <a href="{{ url('/') }}" class="logo">NovelNoob</a>
                
                @auth
                    @endauth
                
                <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="Open menu">
                    </button>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="checkout-container">
                <h1 class="checkout-title">สรุปรายการสั่งซื้อ</h1>
                
                <div id="alert-message" class="alert-box" style="display: none; position: relative; margin-bottom: 20px;"></div>

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
                                {{-- <option value="card">Debit / Credit Card</option>
                                <option value="truemoney">True Money</option>
                                <option value="shopeepay">ShopeePay</option>
                                <option value="linepay">LINE Pay</option>
                                <option value="alipay">Alipay</option> --}}
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
        <div class="container">
            <p>&copy; 2025 Novel Noob. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="{{asset('assets/js/script.js')}}"></script>

  <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. เลือก Elements จากหน้า Checkout ---
            const submitButton = document.getElementById('checkout-submit-btn');
            const paymentForm = document.getElementById('checkout-form');
            
            // Elements จากโค้ดเดิมของคุณ
            const buttonSpan = submitButton.querySelector('span');
            const buttonLoader = submitButton.querySelector('.loader');
            const alertMessageDiv = document.getElementById('alert-message'); // ใช้ div ที่เราเพิ่มใน HTML
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // ### คำเตือน: Elements 2 ตัวนี้ ไม่มีอยู่จริงในหน้า Checkout ###
            // โค้ดใน try...catch ของคุณจะพยายามหามัน แต่จะเจอ 'null'
            const creditBalanceDisplay = document.getElementById('credit-balance-display'); 
            const transactionTableBody = document.querySelector('.custom-table tbody'); 
            

            // --- 2. Helper Functions (จากโค้ดเดิมของคุณทั้งหมด) ---
            function showAlert(message, type = 'info') {
                if (!alertMessageDiv) {
                    console.error("Missing alert element!");
                    return;
                }
                alertMessageDiv.textContent = message;
                alertMessageDiv.style.backgroundColor = type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#cce5ff';
                alertMessageDiv.style.color = type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#004085';
                alertMessageDiv.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#b8daff'}`;
                
                // เราจะไม่ใช้ 'fixed' แต่จะให้มันแสดงในฟอร์ม
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

            function formatNumber(num) {
                try { return new Intl.NumberFormat('th-TH').format(num); }
                catch (e) { return String(num); } 
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


            // --- 3. ดัดแปลง Event Listener ---
            // เปลี่ยนจาก .purchase-btn.forEach มาเป็นปุ่มเดียว
            submitButton.addEventListener('click', async function(event) {
                event.preventDefault(); // <-- นี่คือส่วน "ไม่ต้องจัญไร form submit"
                
                // --- ดึงข้อมูลจากฟอร์ม (วิธีใหม่) ---
                const packageIdInput = paymentForm.querySelector('input[name="package_id"]');
                const paymentMethodInput = paymentForm.querySelector('select[name="payment_method"]');
                
                const packageId = packageIdInput ? packageIdInput.value : null;
                const paymentMethod = paymentMethodInput ? paymentMethodInput.value : null;

                // --- ตรวจสอบข้อมูล ---
                if (!packageId) {
                    showAlert('ไม่พบ ID แพ็กเกจ', 'error');
                    return;
                }
                if (!paymentMethod) {
                    showAlert('กรุณาเลือกช่องทางการชำระเงิน', 'error');
                    return;
                }
                if (this.disabled) return;

                // --- ส่วนแสดง Loader (เหมือนโค้ดเดิม) ---
                this.disabled = true;
                if (buttonSpan) buttonSpan.style.display = 'none';
                if (buttonLoader) buttonLoader.style.display = 'inline-block';
                hideAlert(); 

                try {
                    const response = await fetch('{{ route("credits.purchase") }}', { 
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? ''
                        },
                        // *** นี่คือส่วนที่ดัดแปลง: ส่งข้อมูล 2 ตัว ***
                        body: JSON.stringify({ 
                            package_id: packageId,
                            payment_method: paymentMethod // <-- เพิ่มตัวนี้
                        })
                    });

                    if (!response.ok) await handleFetchError(response, 'เกิดข้อผิดพลาดในการซื้อเครดิต'); 

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        console.log(data);
                        
                        // โค้ดเดิม: คาดหวัง new_balance
                        showAlert(`สำเร็จ! ได้รับ ${formatNumber(data.credits_added || 0)} เครดิต ยอดคงเหลือใหม่ ${data.new_balance !== undefined ? formatNumber(data.new_balance) : 'N/A'}`, 'success');
                        
                        if (creditBalanceDisplay && data.new_balance !== undefined) {
                            creditBalanceDisplay.textContent = formatNumber(data.new_balance); 
                        } else {
                            console.warn("Element 'credit-balance-display' not found. Cannot update balance.");
                        }

                    } else {
                        showAlert(data.error || 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ', 'error');
                    }
                    // --- จบโค้ดส่วนเดิม ---

                } catch (error) {
                    if (error.message !== "Redirecting due to authorization error.") {
                        console.error('Purchase Error caught:', error);
                    }
                } finally {
                    this.disabled = false;
                    if (buttonSpan) buttonSpan.style.display = 'inline-block';
                    if (buttonLoader) buttonLoader.style.display = 'none';
                }
                // ==========================================================
            });

        });
        </script>
</body>
</html>