<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditPackage;
use Illuminate\Support\Facades\Validator;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $packages = CreditPackage::orderBy('credits', 'asc')->get();
        return view('admin.dashboard.index', [
            'packages' => $packages
        ]);
    }
    /**
     * Update the credit packages.
     */
   public function updatePackages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'packages' => 'required|array',
            'packages.*.credits' => 'required|integer|min:0', // ตรวจสอบทุก credits ใน array
            'packages.*.price' => 'required|integer|min:0', // ตรวจสอบทุก price ใน array
            'is_highlighted' => 'required|integer|exists:credit_packages,id' // ตรวจสอบว่า ID ที่ส่งมามีจริง
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $packageInputs = $request->input('packages');
        $highlightedId = (int)$request->input('is_highlighted');

        try {
            // --- 💡 โค้ดที่แก้ไข ---

            $packageIds = array_keys($packageInputs);
            $allPackages = CreditPackage::findMany($packageIds);

            foreach ($allPackages as $package) {
                // ตรวจสอบว่ามีข้อมูล input สำหรับ package นี้ (เผื่อไว้)
                if (isset($packageInputs[$package->id])) {
                    $data = $packageInputs[$package->id];
                    
                    // อัปเดต credits และ price
                    $package->credits = $data['credits'];
                    $package->price = $data['price'];

                    // อัปเดต is_highlighted (true ถ้า ID ตรง, false ถ้าไม่ตรง)
                    $package->is_highlighted = ($package->id == $highlightedId);

                    // บันทึกการเปลี่ยนแปลงลง Database
                    $package->save();
                }
            }
            // --- จบส่วนที่แก้ไข ---

        } catch (\Exception $e) {
            // ไม่มีการ Rollback เพราะไม่ได้ใช้ Transaction
            // จัดการ error (เช่น log ไว้)
            return back()->with('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
        }
        
        return back()->with('success', 'บันทึกแพ็กเกจเรียบร้อยแล้ว');
    }
}
