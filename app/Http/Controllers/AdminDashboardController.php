<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Novel;
use App\Models\PostReport;
use Illuminate\Http\Request;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $packages = CreditPackage::orderBy('credits', 'asc')->get();

        // *** เพิ่ม: ดึงรายการธุรกรรมทั้งหมด ***
        $transactions = CreditTransaction::with(['user', 'creditPackage']) // Eager Load user และ package
                      ->orderBy('created_at', 'desc')
                      ->paginate(20); // อาจใช้ paginate เพื่อจัดการจำนวนข้อมูล
        
        $kpiStats = $this->getKpiStats();
        $endDate = now()->endOfMonth();
        $startDate = now()->subMonths(11)->startOfMonth(); // 12 เดือนย้อนหลังรวมเดือนปัจจุบัน
        $stats = $this->getMonthlyStats($startDate, $endDate);

        $monthlyNovelReports = $this->getMonthlyNovelReports();
        // dd($monthlyNovelReports);

        $users = User::where('type', 'writer')
                     ->orderBy('created_at', 'desc')
                     ->paginate(50); // (ใช้ Paginate ถ้าต้องการ)

        $reports = PostReport::with(['reporter', 'post.author']) 
                                 ->orderBy('status', 'asc') 
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(30);

        return view('admin.dashboard.index', [
            'packages' => $packages,
            'transactions' => $transactions,
            'monthly_stats' => $stats,
            'kpi_stats' => $kpiStats,
            'monthly_novel_reports' => $monthlyNovelReports,
            'users' => $users,
            'reports' => $reports
        ]);
    }

    /**
     * ดึงสถิติรวม (KPI) สำหรับแสดงใน Card
     *
     * @return array
     */
    protected function getKpiStats(): array
    {
        $today = now();
        $yesterday = now()->subDay();
        $lastMonth = now()->subMonth();

        // 1. รายได้รวม (ตลอดกาล) และ % เปลี่ยนแปลงจากเดือนที่แล้ว
        $totalRevenue = CreditTransaction::where('status', 'completed')->sum('amount_paid');
        
        $currentMonthRevenue = CreditTransaction::where('status', 'completed')
            ->whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->sum('amount_paid');
            
        $lastMonthRevenue = CreditTransaction::where('status', 'completed')
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('amount_paid');

        $revenueChange = 0;
        if ($lastMonthRevenue > 0) {
            $revenueChange = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        } elseif ($currentMonthRevenue > 0) {
            $revenueChange = 100; // หากเดือนที่แล้วเป็น 0 แต่เดือนนี้มีรายได้
        }


        // 2. ผู้ใช้ทั้งหมด (Writer เท่านั้น) และจำนวนที่เพิ่มขึ้นใน 30 วัน
        $totalUsers = User::where('type', 'writer')->count();
        $newUsers = User::where('type', 'writer')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();


        // 3. คำสั่งซื้อทั้งหมด (สำเร็จ) และจำนวนที่เพิ่มขึ้นใน 30 วัน
        $totalOrders = CreditTransaction::where('status', 'completed')->count();
        $newOrders = CreditTransaction::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();


        // 4. นิยายที่สร้าง (ทั้งหมด) และจำนวนที่เพิ่มขึ้นใน 30 วัน
        $totalNovels = Novel::count();
        $newNovels = Novel::where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'total_revenue' => $totalRevenue,
            'revenue_change' => $revenueChange,

            'total_users' => $totalUsers,
            'new_users' => $newUsers, // ใช้ +25 Users

            'total_orders' => $totalOrders,
            'new_orders' => $newOrders, // ใช้ +150 Orders

            'total_novels' => $totalNovels,
            'new_novels' => $newNovels, // ใช้ +40 เล่ม
        ];
    }
    /**
     * ดึงสถิติรายเดือน (รายได้, ลูกค้าใหม่, นิยายที่สร้าง)
     */
    protected function getMonthlyStats(Carbon $startDate, Carbon $endDate)
    {
        // 1. ดึงรายได้รวมและจำนวนลูกค้าที่ทำธุรกรรม (CreditTransaction)
        $revenueAndCustomers = CreditTransaction::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month, 
                SUM(CASE WHEN status = "completed" THEN amount_paid ELSE 0 END) as total_revenue,
                COUNT(DISTINCT user_id) as total_customers
            ')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()]) // ใช้ endOfDay() เพื่อให้ครอบคลุมวันสุดท้าย
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 2. ดึงจำนวนนิยายที่สร้าง (Novel)
        $novelCounts = Novel::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month, 
                COUNT(id) as total_novels
            ')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()]) // ใช้ endOfDay() เพื่อให้ครอบคลุมวันสุดท้าย
            ->groupBy('month')
            ->get()
            ->keyBy('month');
            
        // 3. รวมข้อมูลและคำนวณความเปลี่ยนแปลง
        $results = collect();
        $previousMonthStats = null;
        
        // สร้างช่วงเดือนทั้งหมด 12 เดือน (หรือตามที่กำหนด) เรียงจากเก่าไปใหม่
        $period = Carbon::parse($startDate)->monthsUntil($endDate); 
        
        foreach ($period as $currentMonth) {
            $monthKey = $currentMonth->format('Y-m');
            $monthName = $currentMonth->locale('th')->isoFormat('MMMM YYYY');

            // ดึงข้อมูลสำหรับเดือนปัจจุบัน
            $rev = $revenueAndCustomers->get($monthKey);
            $nov = $novelCounts->get($monthKey);
            
            $currentRevenue = (float) optional($rev)->total_revenue ?? 0.00;
            $currentNovels = (int) optional($nov)->total_novels ?? 0;
            $currentCustomers = (int) optional($rev)->total_customers ?? 0;
            
            // *** ส่วนสำคัญ: กรองเดือนที่เป็นศูนย์ทั้งหมดออก ***
            if ($currentRevenue === 0.00 && $currentNovels === 0 && $currentCustomers === 0) {
                continue; // ข้ามเดือนนี้ถ้าเป็นศูนย์ทั้งหมด
            }

            // คำนวณความเปลี่ยนแปลงจากเดือนก่อนหน้าที่มีข้อมูล
            $change = [
                'revenue_change' => 0.0,
                'customers_change' => 0,
                'novels_change' => 0,
            ];
            
            if ($previousMonthStats) {
                $prevRev = $previousMonthStats['revenue'];
                $prevCust = $previousMonthStats['customers'];
                $prevNov = $previousMonthStats['novels'];

                // รายได้ (เป็นเปอร์เซ็นต์)
                $change['revenue_change'] = ($prevRev > 0) 
                    ? (($currentRevenue - $prevRev) / $prevRev) * 100 
                    : ($currentRevenue > 0 ? 100 : 0);
                
                // ลูกค้า (เป็นผลต่าง)
                $change['customers_change'] = $currentCustomers - $prevCust;
                
                // นิยาย (เป็นผลต่าง)
                $change['novels_change'] = $currentNovels - $prevNov;
            }

            // เพิ่มผลลัพธ์ของเดือนนี้
            $results->push([
                'month_label' => $monthName,
                'revenue' => $currentRevenue,
                'customers' => $currentCustomers,
                'novels' => $currentNovels,
                'change' => $change,
            ]);
            
            // เก็บสถิติของเดือนนี้เพื่อใช้เปรียบเทียบในเดือนถัดไป (เฉพาะเดือนที่มีข้อมูล)
            $previousMonthStats = [
                'revenue' => $currentRevenue,
                'customers' => $currentCustomers,
                'novels' => $currentNovels,
            ];
        }
        
        // เรียงจากเดือนล่าสุดไปเก่าสุดเพื่อแสดงในตาราง Admin
        return $results->reverse()->values(); 
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

    public function getMonthlyNovelReports()
    {
        // (ตั้งค่าภาษาไทยเพื่อให้ Carbon แปลชื่อเดือนถูกต้อง)
        // (ควรตั้งค่านี้ใน AppServiceProvider.php)
        Carbon::setLocale('th'); 
        
        $styleMap = Novel::STYLE_MAP; // ดึง Map จาก Model (ที่เราแก้เป็น public แล้ว)
        $reports = [];
        
        // สร้างข้อมูลสำหรับ 3 เดือน (เดือนปัจจุบัน และ 2 เดือนก่อนหน้า)
        for ($i = 0; $i < 3; $i++) {
            $month = now()->subMonths($i);
            $year = $month->year;
            $monthNumber = $month->month;

            // สร้าง Key (e.g., "october")
            $monthKey = strtolower($month->format('F')); 
            // สร้างชื่อเดือน (e.g., "ตุลาคม 2568")
            $monthName = $month->translatedFormat('F Y'); 

            // --- Query Top 5 Genres (style) ---
            $topGenresQuery = Novel::select('style', DB::raw('COUNT(*) as count'))
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNumber)
                ->whereNotNull('style')
                ->where('style', '!=', '')
                ->groupBy('style')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // แปลงข้อมูล Query ให้อยู่ใน Format ที่ JS ต้องการ
            $topGenres = $topGenresQuery->map(function ($item, $key) use ($styleMap) {
                return [
                    'rank' => $key + 1,
                    // ใช้ Map เพื่อแปลง key (style_romance) เป็นชื่อ (แนวโรแมนติก)
                    'type' => $styleMap[$item->style] ?? $item->style, 
                    'count' => $item->count,
                ];
            });

            // --- Query Top 5 Nationalities ---
            $topNationalitiesQuery = Novel::select('character_nationality', DB::raw('COUNT(*) as count'))
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNumber)
                ->whereNotNull('character_nationality')
                ->where('character_nationality', '!=', '')
                ->groupBy('character_nationality')
                ->orderByDesc('count')
                ->limit(5)
                ->get();
            
            $topNationalities = $topNationalitiesQuery->map(function ($item, $key) {
                return [
                    'rank' => $key + 1,
                    'type' => $item->character_nationality, // สัญชาติเก็บเป็น String อยู่แล้ว
                    'count' => $item->count,
                ];
            });

            // เก็บข้อมูลของเดือนนี้
            $reports[$monthKey] = [
                'monthName' => $monthName,
                'genres' => $topGenres,
                'nationalities' => $topNationalities,
            ];
        }
        
        return $reports;
    }

    /**
     * ⭐️ 1. แก้ไขฟังก์ชันนี้ ⭐️
     * อัปเดตสถานะผู้ใช้ (1 = active, 2 = banned)
     */
    public function updateUserStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            // ⭐️ แก้ไข validation
            'new_status' => 'required|integer|in:1,2', 
        ]);

        try {
            $user = User::findOrFail($request->input('user_id'));
            
            if ($user->id === auth()->id()) {
                return response()->json(['success' => false, 'message' => 'คุณไม่สามารถเปลี่ยนสถานะตัวเองได้'], 403);
            }

            // ⭐️ บันทึกค่า 1 หรือ 2 ลง DB
            $user->status = $request->input('new_status'); 
            $user->save();

            return response()->json([
                'success' => true, 
                // ⭐️ ส่งค่า 1 หรือ 2 กลับไปให้ JS
                'new_status' => $user->status 
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query', ''); // รับคำค้นหา

        $users = User::where('type', 'writer')
                     // ⭐️ ค้นหาในฐานข้อมูล ⭐️
                     ->where('email', 'LIKE', "%{$query}%") 
                     ->orderBy('created_at', 'desc')
                     ->get(); // (ใช้ get() หรือ paginate() ก็ได้)

        // ⭐️ ส่ง View ใหม่กลับไปเป็น HTML ⭐️
        return view('admin.dashboard.partials._users_table_body', ['users' => $users])->render();
    }
    
    /**
     * ⭐️ METHOD ใหม่: ลบเฉพาะรายงาน (PostReport)
     */
    public function destroyReport(PostReport $report): JsonResponse
    {
        try {
            $report->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'ลบรายงานสำเร็จ'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ไม่สามารถลบรายงานได้'
            ], 500);
        }
    }

    /**
     * ⭐️ METHOD ใหม่: ลบโพสต์ (Post) และรายงานที่เกี่ยวข้อง
     */
    public function destroyPost(Post $post): JsonResponse
    {
        try {
            // 1. (ทางเลือก) ลบรายงานทั้งหมดที่เกี่ยวข้องกับโพสต์นี้
            PostReport::where('post_id', $post->id)->delete();
            
            // 2. ลบโพสต์
            $post->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'ลบโพสต์และรายงานที่เกี่ยวข้องสำเร็จ'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ไม่สามารถลบโพสต์ได้'
            ], 500);
        }
    }
}
