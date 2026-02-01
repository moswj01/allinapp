<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Part;
use App\Models\Customer;
use App\Models\DailySettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->canAccessBranch($request->input('branch_id'))
            ? $request->input('branch_id')
            : $user->branch_id;

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Today's stats
        $todayRepairs = Repair::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->count();

        $todaySales = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->where('payment_status', '!=', 'voided')
            ->sum('total');

        $pendingRepairs = Repair::where('branch_id', $branchId)
            ->whereNotIn('status', [Repair::STATUS_DELIVERED, Repair::STATUS_CANCELLED])
            ->count();

        // Monthly stats
        $monthlyRevenue = Sale::where('branch_id', $branchId)
            ->where('created_at', '>=', $startOfMonth)
            ->where('payment_status', '!=', 'voided')
            ->sum('total');

        $monthlyRepairRevenue = Repair::where('branch_id', $branchId)
            ->where('created_at', '>=', $startOfMonth)
            ->whereIn('status', [Repair::STATUS_COMPLETED, Repair::STATUS_DELIVERED])
            ->sum('total_cost');

        // Repair status breakdown for Kanban summary
        $repairStats = Repair::where('branch_id', $branchId)
            ->whereNotIn('status', [Repair::STATUS_DELIVERED, Repair::STATUS_CANCELLED])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Low stock alerts
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->count();

        $lowStockParts = Part::where('is_active', true)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->count();

        // Recent repairs (last 5)
        $recentRepairs = Repair::where('branch_id', $branchId)
            ->with(['customer', 'technician'])
            ->latest()
            ->take(5)
            ->get();

        // Recent sales (last 5)
        $recentSales = Sale::where('branch_id', $branchId)
            ->with(['customer', 'createdBy'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todayRepairs',
            'todaySales',
            'pendingRepairs',
            'monthlyRevenue',
            'monthlyRepairRevenue',
            'repairStats',
            'lowStockProducts',
            'lowStockParts',
            'recentRepairs',
            'recentSales'
        ));
    }
}
