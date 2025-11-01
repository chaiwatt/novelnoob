<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Omise\process\OmiseCharge;
use App\Omise\process\OmiseSource;
use Illuminate\Support\Facades\DB;
use App\Models\OnChargeTransaction;
use Illuminate\Support\Facades\Log;
use App\Models\AffiliateTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function showCheckout(CreditPackage $package)
    {
        // ส่งตัวแปร $package ที่ดึงมาจาก URL ไปยัง View ใหม่
        return view('checkout', [
            'package' => $package
        ]);
    }

        /**
     * ⭐️ [แก้ไขใหม่]
     * เริ่มต้นกระบวนการชำระเงิน และสร้าง Charge
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseCredits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer|exists:credit_packages,id',
            'payment_method' => 'required|string|in:qr_promptpay,card,truemoney', // ⭐️ เพิ่ม payment_method
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'ข้อมูลไม่ถูกต้อง', 'details' => $validator->errors()], 422);
        }

        $packageId = $request->input('package_id');
        $paymentMethod = $request->input('payment_method');
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'ไม่พบผู้ใช้งาน', 'redirect_to' => route('login')], 401);
        }

        $package = CreditPackage::find($packageId);
        if (!$package) {
            return response()->json(['error' => 'ไม่พบแพ็กเกจ'], 404);
        }

        // --- เริ่มต้นกระบวนการตามช่องทางชำระเงิน ---
        if ($paymentMethod === 'qr_promptpay') {
            
            DB::beginTransaction(); // ⭐️ [เพิ่ม] เริ่ม Transaction
            try {
                // ⭐️ 1. เรียกฟังก์ชันสร้าง Charge (ตามที่คุณสั่ง)
                $chargeData = $this->createCharge($user, $package);

                // ⭐️ 2. [เพิ่ม] บันทึก OnChargeTransaction (ตามคำสั่ง)
                OnChargeTransaction::create([
                    'source_id' => $chargeData['source_id'], // (จำเป็นต้องแก้ createCharge)
                    'charge_id' => $chargeData['charge_id'],
                    'status' => 'pending',
                ]);

                DB::commit(); // ⭐️ [เพิ่ม] Commit การบันทึก

                // ⭐️ 3. ส่ง JSON กลับไปให้ Frontend
                return response()->json([
                    'status' => 'qr_created',
                    'message' => 'กรุณาสแกนเพื่อชำระเงิน',
                    'qr_image_url' => $chargeData['qr_image_url'],
                    'charge_id' => $chargeData['charge_id'],
                ]);

            } catch (Exception $e) {
                DB::rollBack(); // ⭐️ [เพิ่ม] Rollback หากเกิดข้อผิดพลาด
                
                // ⭐️ 4. หากเกิดข้อผิดพลาด
                Log::error('Omise Payment Error', [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'message' => $e->getMessage()
                ]);
                return response()->json(['error' => 'เกิดข้อผิดพลาดในการสร้าง QR Code', 'details' => $e->getMessage()], 500);
            }

        } else if ($paymentMethod === 'card') {
            // (สำหรับอนาคต)
            return response()->json(['error' => 'การชำระผ่านบัตรยังไม่เปิดให้บริการ'], 400);
        } else {
            return response()->json(['error' => 'ไม่รองรับช่องทางการชำระเงินนี้'], 400);
        }
    }


    /**
     * ⭐️ [ฟังก์ชันใหม่ - สร้าง Charge เท่านั้น ตามคำสั่ง]
     * สร้าง Omise Charge สำหรับ PromptPay
     * (ฟังก์ชันนี้จะไม่ยุ่งกับฐานข้อมูล)
     */
    private function createCharge(User $user, CreditPackage $package)
    {
        $amountInSatang = $package->price * 100;

        // ตรวจสอบขั้นต่ำ (Omise 20 บาท)
        if ($amountInSatang < 2000) {
            throw new Exception('ยอดชำระเงินขั้นต่ำคือ 20 บาท');
        }

        try {
            // 1. สร้าง Omise Source (ตามตัวอย่างของคุณ)
            $source = OmiseSource::create([
                'amount' => $amountInSatang,
                'store_name' => 'Novel Noob', // (ใช้ชื่อร้านจริง)
                'name' => $user->name,         // (ใช้ข้อมูล User ที่ Login)
                'email' => $user->email,       // (ใช้ข้อมูล User ที่ Login)
                'currency' => 'THB',
                'type' => 'promptpay',
            ]);

            if (!isset($source['object']) || $source['object'] != 'source') {
                 throw new Exception('ไม่สามารถสร้าง Omise Source ได้');
            }

            // ⭐️ [แก้ไข] ดึง affiliate_ref จาก session เพื่อส่งไปกับ metadata
            $affiliateRefCode = session()->get('affiliate_ref');

            // 2. สร้าง Omise Charge จาก Source (ตามตัวอย่างของคุณ)
            $charge = OmiseCharge::create([
                'amount' => $source['amount'],
                'currency' => 'THB',
                'source' => $source['id'],
                'return_uri' => route('dashboard.index'), // (ใส่ return_uri ที่เหมาะสม)
                
                // ⭐️ [แก้ไข] เพิ่ม metadata เพื่อใช้ใน Webhook
                'metadata' => [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'affiliate_ref' => $affiliateRefCode // ส่ง affiliate_ref ไปด้วย
                ]
            ]);

            if (!isset($charge['object']) || $charge['object'] != 'charge') {
                throw new Exception('ไม่สามารถสร้าง Omise Charge ได้');
            }

            // 3. ดึง URL ของ QR Code และ Charge ID (ตามตัวอย่างของคุณ)
            $qrImageUrl = $charge['source']['scannable_code']['image']['download_uri'];
            $chargeId = $charge['id'];

            // 4. คืนค่าเป็น Array (ไม่ใช่ JSON หรือ View)
            return [
                'qr_image_url' => $qrImageUrl,
                'charge_id' => $chargeId,
                'source_id' => $source['id'], // ⭐️ [เพิ่ม] ส่ง Source ID กลับไปด้วย
            ];

        } catch (Exception $e) {
            // ส่งต่อ Exception ให้ฟังก์ชันที่เรียก (purchaseCredits) ไปจัดการ
            throw $e;
        }
    }


    /**
     * ⭐️ [แก้ไข Webhook ใหม่ทั้งหมด]
     * รับ Webhook จาก Omise เพื่อยืนยันการชำระเงิน
     */
    public function webhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        Log::info('Omise Webhook Received:', $payload);

        // ตรวจสอบว่าเป็น Event "charge.complete"
        if ($payload && isset($payload['key']) && $payload['key'] === 'charge.complete') {
            
            $chargeData = $payload['data'];
            $chargeId = $chargeData['id'];

            if (isset($chargeData['status']) && trim($chargeData['status']) == 'successful') {
                
                // --- 1. อัปเดต OnChargeTransaction (ตามที่คุณสั่ง) ---
                $onCharge = OnChargeTransaction::updateOrCreate(
                    ['charge_id' => $chargeId], 
                    [ 
                        'source_id' => $chargeData['source']['id'] ?? null,
                        'status' => 'successful', // ⭐️ บังคับเป็น successful
                        'paid_at' => $chargeData['paid_at'],
                    ]
                );

                // --- 2. [แก้ไข] ตรวจสอบการทำงานซ้ำ (THE FIX) ---
                // ตรวจสอบว่า OnChargeTransaction นี้ มี CreditTransaction ลูกแล้วหรือยัง
                $onCharge->load('creditTransaction'); 
                
                if ($onCharge->creditTransaction) {
                    Log::warning("Webhook for processed charge received (Duplicate): $chargeId");
                    return response('OK - Already Processed', 200);
                }

                // --- 3. ดึง Metadata และข้อมูล User/Package ---
                $metadata = $chargeData['metadata'] ?? [];
                $userId = $metadata['user_id'] ?? null;
                $packageId = $metadata['package_id'] ?? null;
                $affiliateRefCode = $metadata['affiliate_ref'] ?? null;

                $user = User::find($userId);
                $package = CreditPackage::find($packageId);

                if (!$user || !$package) {
                    Log::error("Webhook Error: User ($userId) or Package ($packageId) not found for Charge $chargeId");
                    return response('Error: Invalid Data', 400); // 400
                }

                // --- 4. เริ่ม Logic การเติมเครดิต (จากโค้ดเดิมของคุณ) ---
                $transactionDetails = [
                    'gateway' => 'omise_promptpay',
                    'charge_id' => $chargeId, // (ยังเก็บไว้ใน JSON ได้)
                ];

                $commissionRate = 0.20; // 20%
                $commissionCredits = $package->credits * $commissionRate;
                
                DB::beginTransaction();
                try {
                    // 1. [แก้ไข] สร้าง CreditTransaction (THE FIX)
                    // โดยผูกกับ on_charge_transaction_id
                    $transaction = CreditTransaction::create([
                        'on_charge_transaction_id' => $onCharge->id, // ⭐️ <-- นี่คือจุดที่แก้ไข
                        'user_id' => $user->id,
                        'credit_package_id' => $package->id,
                        'credits_added' => $package->credits,
                        'amount_paid' => $package->price,
                        'status' => 'completed',
                        'transaction_details' => $transactionDetails,
                    ]);

                    // 2. Update User Credits
                    $user->increment('credits', $package->credits);

                    // 3. Affiliate Logic (จากโค้ดของคุณ)
                    if ($affiliateRefCode) {
                        $referrer = User::where('affiliate', $affiliateRefCode)->where('id', '!=', $user->id)->first(); 
                        
                        if ($referrer) {
                            $maskedEmail = $this->maskEmail($user->email); // (เรียกใช้ฟังก์ชัน)
                            $referrer->increment('credits', $commissionCredits);
                            AffiliateTransaction::create([
                                'referrer_user_id' => $referrer->id, 
                                'credit_package_id' => $package->id,
                                'referrer_masked_email' => $maskedEmail
                            ]);
                        }
                    }

                    DB::commit();

                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error('Webhook DB Error:', ['message' => $e->getMessage(), 'charge_id' => $chargeId]);
                    return response('Database Error', 500); // 500 จะทำให้ Omise ส่ง webhook มาใหม่
                }

            } elseif (isset($chargeData['status']) && trim($chargeData['status']) == 'failed') {
                
                // --- 1. อัปเดต OnChargeTransaction (ตามที่คุณสั่ง) ---
                OnChargeTransaction::updateOrCreate(
                    ['charge_id' => $chargeData['id']],
                    [
                        'status' => 'failed',
                        'source_id' => $chargeData['source']['id'] ?? null,
                    ]
                );
                
                Log::warning('Omise Charge Failed:', [
                    'charge_id' => $chargeData['id'], 
                    'failure_message' => $chargeData['failure_message'] ?? 'Unknown'
                ]);
                
                // (ทางเลือก) สร้าง CreditTransaction ที่ failed (ถ้าต้องการ)
                $onCharge = OnChargeTransaction::where('charge_id', $chargeData['id'])->first();
                
                // ตรวจสอบว่ายังไม่มี credit transaction ลูก
                if ($onCharge && !$onCharge->creditTransaction) {
                     CreditTransaction::create([
                        'on_charge_transaction_id' => $onCharge->id, // ⭐️ <-- แก้ไขให้เชื่อมโยงกัน
                        'user_id' => $chargeData['metadata']['user_id'] ?? null,
                        'credit_package_id' => $chargeData['metadata']['package_id'] ?? null,
                        'credits_added' => 0,
                        'amount_paid' => ($chargeData['amount'] ?? 0) / 100,
                        'status' => 'failed',
                        'transaction_details' => [
                            'gateway' => 'omise_promptpay',
                            'charge_id' => $chargeData['id'],
                            'failure_message' => $chargeData['failure_message'] ?? 'Unknown'
                        ],
                    ]);
                }
            }
        }

        // ตอบ OK 200 กลับไปให้ Omise เสมอ (ถ้าไม่เกิด Error 500)
        return response('OK', 200);
    }

    /**
     * ⭐️ [เพิ่มกลับมา]
     * (จำเป็นสำหรับ Logic การอัปเดต Affiliate Transaction ที่คุณให้มา)
     */
    private function maskEmail($email)
    {
        try {
            $parts = explode('@', $email);
            if (count($parts) != 2) return 'e***l@d***n.com'; // Fallback
            
            $name = $parts[0];
            $domain = $parts[1];
            
            if (strlen($name) <= 3) {
                $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 1);
            } else {
                 $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 3)) . substr($name, -1);
            }
           
            return $maskedName . '@' . $domain;
        } catch (Exception $e) {
            return 'e***l@d***n.com'; // Fallback
        }
    }

    
    /**
     * ⭐️ [ฟังก์ชันใหม่]
     * ตรวจสอบสถานะการชำระเงินจาก Charge ID ที่ Frontend เรียกใช้ (Polling)
     */
    public function checkStatus($charge_id)
    {
        $transaction = OnChargeTransaction::where('charge_id', $charge_id)->first();

        if ($transaction && $transaction->status == 'successful') {
            return response()->json(['status' => 'successful']);
        }
        
        if ($transaction && $transaction->status == 'failed') {
            return response()->json(['status' => 'failed']);
        }

        // สถานะเริ่มต้นหรือยังไม่ได้รับการยืนยัน
        return response()->json(['status' => 'pending']);
    }
}
