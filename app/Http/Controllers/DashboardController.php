<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Novel;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\AffiliateTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        // 1. ดึงข้อมูล User Review (ถ้ามี)
        $userReview = Review::where('user_id', $userId)->first();
        // 2. ดึงข้อมูลนิยาย (เหมือนเดิม)
        $novels = Novel::where('user_id', $userId)
                        ->with('chapters') // Eager load chapters (จำเป็นสำหรับ isFinished)
                        ->latest()
                        ->get();
        
        // 3. ดึงข้อมูลแพ็กเกจเครดิต
        $packages = CreditPackage::orderBy('credits', 'asc')->get();

        // 4. (เพิ่ม) คำนวณ Stats โดยใช้ 'isFinished' accessor
        // Accessor จะทำงานตอน .where() ถูกเรียก
        $finishedCount = $novels->where('isFinished', true)->count();
        $inProgressCount = $novels->where('isFinished', false)->count();
        $transactions = CreditTransaction::where('user_id', $userId)
                                    ->with('creditPackage') // Eager load package info if needed
                                    ->latest() // เรียงตามวันที่ล่าสุด
                                    ->get();

        // *** 5. การดึงข้อมูล Affiliate Transactions ***
        // ดึงรายการ Affiliate Transactions ทั้งหมดที่ผู้ใช้เป็น referrer
        $affiliateTransactions = AffiliateTransaction::where('referrer_user_id', $userId)
                                                    // โหลดข้อมูล Package ที่ถูกซื้อมาใช้ในการคำนวณราคา/เครดิต
                                                    ->with('package') 
                                                    // โหลดข้อมูลผู้ถูกแนะนำ ถ้าต้องการแสดงชื่อ/อีเมล
                                                    ->latest()
                                                    ->get(); 

        // คำนวณสถิติ Affiliate
        $totalReferred = $affiliateTransactions->count();
        
        // คำนวณยอดรวมเครดิตที่ได้รับจาก Affiliate (สมมติอัตรา 20% ของ credits_added ในการซื้อ)
        $totalCreditsEarned = $affiliateTransactions->sum(function ($affTx) {
            // ดึงเครดิตที่ถูกซื้อ และคำนวณ 20%
            return optional($affTx->package)->credits * 0.20;
        });                            
        // dd($userReview);
        // 5. ส่งข้อมูลทั้งหมดไปที่ View
        return view('dashboard.index', [
            'novels' => $novels,
            'packages' => $packages,
            'finishedCount' => $finishedCount,     // <-- ส่งค่าที่คำนวณได้
            'inProgressCount' => $inProgressCount,
            'transactions' => $transactions,
            'userReview' => $userReview,
            'affiliateTransactions' => $affiliateTransactions,
            'totalReferred' => $totalReferred,
            'totalCreditsEarned' => round($totalCreditsEarned),
        ]);
    }
        /**
     * Handle the purchase of credit packages (Simulation).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseCredits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer|exists:credit_packages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid package selected.', 'details' => $validator->errors()], 422);
        }

        $affiliateRefCode = $request->session()->get('affiliate_ref');

        
        $packageId = $request->input('package_id');
        $user = Auth::user();

        // Find the selected package
        $package = CreditPackage::find($packageId);
        if (!$package) {
            return response()->json(['error' => 'Package not found.'], 404);
        }

        // --- Payment Simulation ---
        // In a real application, you would integrate with a payment gateway here.
        // For now, we assume payment is successful.
        $paymentSuccessful = true;
        $transactionDetails = ['gateway' => 'simulation', 'timestamp' => now()];
        // --- End Payment Simulation ---

        $commissionRate = 0.20; // 20%
        $commissionCredits = $package->credits * $commissionRate;
        if ($paymentSuccessful) {
            DB::beginTransaction();
            try {
                // 1. Create Transaction Record
                $transaction = CreditTransaction::create([
                    'user_id' => $user->id,
                    'credit_package_id' => $package->id,
                    'credits_added' => $package->credits,
                    'amount_paid' => $package->price,
                    'status' => 'completed',
                    'transaction_details' => $transactionDetails,
                ]);

                // 2. Update User Credits
                // Use increment for safety against race conditions
                $user->increment('credits', $package->credits);

                if ($affiliateRefCode) {
                    $referrer = User::where('affiliate', $affiliateRefCode)->where('id', '!=', $user->id)->first(); 
                    
                    if ($referrer) {
                        $maskedEmail = $this->maskEmail($user->email);
                        $referrer->increment('credits', $commissionCredits);
                        AffiliateTransaction::create([
                            'referrer_user_id' => $referrer->id, 
                            'credit_package_id' => $package->id,
                            'referrer_masked_email' => $maskedEmail
                        ]);
                    }
                }

                DB::commit();
                // โหลดความสัมพันธ์ CreditPackage เพื่อใช้ในการแสดงผล
                $transaction->load('creditPackage');

                // Get the updated user credits
                $newCreditBalance = $user->fresh()->credits;

                return response()->json([
                    'status' => 'success',
                    'message' => 'ซื้อเครดิตสำเร็จ!',
                    'credits_added' => $package->credits,
                    'new_balance' => $newCreditBalance,
                    'transaction' => $transaction->toArray(),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                report($e); // Log the error
                return response()->json(['error' => 'เกิดข้อผิดพลาดระหว่างการบันทึกข้อมูล', 'details' => $e->getMessage()], 500);
            }
        } else {
            // Handle failed payment simulation (optional for now)
             CreditTransaction::create([
                 'user_id' => $user->id,
                 'credit_package_id' => $package->id,
                 'credits_added' => 0, // No credits added
                 'amount_paid' => $package->price,
                 'status' => 'failed', // Mark as failed
                 'transaction_details' => $transactionDetails + ['failure_reason' => 'Simulation failure'],
             ]);
            return response()->json(['error' => 'การชำระเงินไม่สำเร็จ (จำลอง)'], 400);
        }
    }

    /**
     * ปกปิดอีเมล ยกเว้น 3 ตัวแรกของส่วนชื่อ และ 1 ตัวแรกของโดเมน
     * @param string $email
     * @return string
     */
    function maskEmail($email) {
        list($user, $domain) = explode('@', $email);
        
        // ปกปิดส่วนชื่อ (แสดง 3 ตัวแรก)
        $maskedUser = substr($user, 0, 3) . str_repeat('*', strlen($user) - 3);
        
        // ปกปิดส่วนโดเมน (แสดง 1 ตัวแรก และ * แทนส่วนที่เหลือ)
        $domainParts = explode('.', $domain);
        $maskedDomain = substr($domainParts[0], 0, 1) . str_repeat('*', strlen($domainParts[0]) - 1);
        
        return $maskedUser . '@' . $maskedDomain . '.' . $domainParts[1];
    }
    public function showCheckout(CreditPackage $package)
    {
        // ส่งตัวแปร $package ที่ดึงมาจาก URL ไปยัง View ใหม่
        return view('checkout', [
            'package' => $package
        ]);
    }

    /**
     * อัปเดตข้อมูลส่วนตัว (ชื่อผู้ใช้, นามปากกา)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Update user profile information (Name, Pen Name, and Avatar).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 1. ตรวจสอบความถูกต้องของข้อมูล
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'pen_name' => ['nullable', 'string', 'max:255', 'unique:users,pen_name,' . $user->id],
            // *** NEW: Add avatar validation ***
            'avatar_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // Max 2MB
        ], [
            'avatar_file.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพเท่านั้น',
            'avatar_file.mimes' => 'รองรับเฉพาะไฟล์ .jpg, .jpeg, หรือ .png',
            'avatar_file.max' => 'ขนาดรูปภาพต้องไม่เกิน 2MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput()
                             ->with('profile_error', 'ไม่สามารถบันทึกข้อมูลส่วนตัวได้ โปรดตรวจสอบข้อผิดพลาด');
        }

        // 2. อัปเดตข้อมูล
        try {
            $user->name = $request->input('name');
            $user->pen_name = $request->input('pen_name');

            // *** NEW: Handle File Upload ***
            if ($request->hasFile('avatar_file')) {
                // 1. Get old path (if exists)
                $oldPath = $user->avatar_url;

                // 2. Store new file in 'public/avatars' (storage/app/public/avatars)
                // The 'public' disk ensures it's publicly accessible
                $path = $request->file('avatar_file')->store('avatars', 'public');

                // 3. Update the user's avatar_url
                $user->avatar_url = $path;

                // 4. Delete the old file (if it existed)
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            // *** END NEW File Logic ***

            $user->save();

            return redirect()->back()->with('profile_success', 'อัปเดตข้อมูลส่วนตัวสำเร็จแล้ว');
        } catch (\Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage());
            return redirect()->back()->with('profile_error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่');
        }
    }

    /**
     * อัปเดตรหัสผ่าน
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // 1. ตรวจสอบความถูกต้องของข้อมูล
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with('password_error', 'ไม่สามารถเปลี่ยนรหัสผ่านได้ โปรดตรวจสอบข้อผิดพลาด');
        }

        // 2. ตรวจสอบรหัสผ่านปัจจุบัน
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with('password_error', 'รหัสผ่านปัจจุบันไม่ถูกต้อง');
        }

        // 3. อัปเดตรหัสผ่านใหม่
        try {
            $user->password = Hash::make($request->input('password'));
            $user->save();

            return redirect()->back()->with('password_success', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว');
        } catch (\Exception $e) {
            \Log::error('Password Update Error: ' . $e->getMessage());
            return redirect()->back()->with('password_error', 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน กรุณาลองใหม่');
        }
    }

    /**
     * Submit a new user review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitReview(Request $request)
    {
        // dd($request->all());
        // 1. Validation: ตรวจสอบคะแนนและเนื้อหารีวิว
        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'], // ต้องมีคะแนนระหว่าง 1 ถึง 5
            'content' => ['nullable', 'string', 'max:1000'], // ข้อความไม่จำเป็นต้องมี แต่ถ้ามีต้องไม่เกิน 1000 ตัวอักษร
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput()
                            ->with('review_error', 'โปรดให้คะแนนรีวิว (อย่างน้อย 1 ดาว)');
        }

        try {
            // 2. ตรวจสอบว่า User เคยรีวิวแล้วหรือยัง (หากต้องการให้รีวิวได้แค่ครั้งเดียว)
            $existingReview = Review::where('user_id', Auth::id())->first();

            if ($existingReview) {
                // ถ้ามีอยู่แล้ว ให้อัปเดต
                $existingReview->update([
                    'rating' => $request->input('rating'),
                    'content' => $request->input('content'),
                ]);
            } else {
                // ถ้ายังไม่มี ให้สร้างใหม่
                Review::create([
                    'user_id' => Auth::id(),
                    'rating' => $request->input('rating'),
                    'content' => $request->input('content'),
                ]);
            }

            return redirect()->back()->with('review_success', 'ขอบคุณสำหรับรีวิวของคุณ!');

        } catch (Exception $e) {
            return redirect()->back()->with('review_error', 'เกิดข้อผิดพลาดในการส่งรีวิว กรุณาลองใหม่');
        }
    }
}
