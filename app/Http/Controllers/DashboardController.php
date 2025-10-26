<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use Illuminate\Http\Request;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index()
    {
        // 2. ดึงข้อมูลนิยาย (เหมือนเดิม)
        $novels = Novel::where('user_id', Auth::id())
                        ->with('chapters') // Eager load chapters (จำเป็นสำหรับ isFinished)
                        ->latest()
                        ->get();
        
        // 3. ดึงข้อมูลแพ็กเกจเครดิต
        $packages = CreditPackage::orderBy('credits', 'asc')->get();

        // 4. (เพิ่ม) คำนวณ Stats โดยใช้ 'isFinished' accessor
        // Accessor จะทำงานตอน .where() ถูกเรียก
        $finishedCount = $novels->where('isFinished', true)->count();
        $inProgressCount = $novels->where('isFinished', false)->count();
        $transactions = CreditTransaction::where('user_id', Auth::id())
                                    ->with('creditPackage') // Eager load package info if needed
                                    ->latest() // เรียงตามวันที่ล่าสุด
                                    ->get();

        // 5. ส่งข้อมูลทั้งหมดไปที่ View
        return view('dashboard.index', [
            'novels' => $novels,
            'packages' => $packages,
            'finishedCount' => $finishedCount,     // <-- ส่งค่าที่คำนวณได้
            'inProgressCount' => $inProgressCount,
            'transactions' => $transactions
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

        if ($paymentSuccessful) {
            DB::beginTransaction();
            try {
                // 1. Create Transaction Record
                CreditTransaction::create([
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

                DB::commit();

                // Get the updated user credits
                $newCreditBalance = $user->fresh()->credits;

                return response()->json([
                    'status' => 'success',
                    'message' => 'ซื้อเครดิตสำเร็จ!',
                    'credits_added' => $package->credits,
                    'new_balance' => $newCreditBalance,
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
}
