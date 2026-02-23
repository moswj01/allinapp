<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\BranchStock;
use App\Models\AccountsReceivable;
use App\Models\DailySettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $requestedBranch = $request->input('branch_id');
        $branchId = ($requestedBranch && $user->canAccessBranch((int) $requestedBranch))
            ? (int) $requestedBranch
            : $user->branch_id;

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Today's repair count
        $todayRepairs = Repair::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->count();

        // Today's sales (exclude cancelled)
        $todaySalesData = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        $todaySales = $todaySalesData->total ?? 0;
        $todaySalesCount = $todaySalesData->count ?? 0;

        // Pending repairs (not delivered, not cancelled)
        $pendingRepairs = Repair::where('branch_id', $branchId)
            ->whereNotIn('status', [Repair::STATUS_DELIVERED, Repair::STATUS_CANCELLED])
            ->count();

        // Monthly sales revenue (exclude cancelled)
        $monthlyRevenue = Sale::where('branch_id', $branchId)
            ->where('created_at', '>=', $startOfMonth)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Monthly repair revenue
        $monthlyRepairRevenue = Repair::where('branch_id', $branchId)
            ->where('created_at', '>=', $startOfMonth)
            ->whereIn('status', [Repair::STATUS_COMPLETED, Repair::STATUS_DELIVERED])
            ->sum('total_cost');

        // Monthly total revenue
        $monthlyTotal = $monthlyRevenue + $monthlyRepairRevenue;

        // Monthly sales count
        $monthlySalesCount = Sale::where('branch_id', $branchId)
            ->where('created_at', '>=', $startOfMonth)
            ->where('status', '!=', 'cancelled')
            ->count();

        // Monthly repair count
        $monthlyRepairCount = Repair::where('branch_id', $branchId)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Repair status breakdown for Kanban summary
        $repairStats = Repair::where('branch_id', $branchId)
            ->whereNotIn('status', [Repair::STATUS_DELIVERED, Repair::STATUS_CANCELLED])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Low stock alerts (per-branch from branch_stocks)
        $lowStockProducts = BranchStock::where('branch_id', $branchId)
            ->where('stockable_type', Product::class)
            ->where('min_quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();

        // Credit sales pending (รอชำระ)
        $creditPending = Sale::where('branch_id', $branchId)
            ->where('status', 'pending')
            ->where('payment_method', 'credit')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        // Accounts receivable overdue
        $arOverdue = AccountsReceivable::where('branch_id', $branchId)
            ->whereIn('status', [AccountsReceivable::STATUS_PENDING, AccountsReceivable::STATUS_PARTIAL])
            ->where('due_date', '<', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance), 0) as total')
            ->first();

        // Accounts receivable total outstanding
        $arOutstanding = AccountsReceivable::where('branch_id', $branchId)
            ->whereIn('status', [AccountsReceivable::STATUS_PENDING, AccountsReceivable::STATUS_PARTIAL])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance), 0) as total')
            ->first();

        // Today's repair revenue
        $todayRepairRevenue = Repair::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->whereIn('status', [Repair::STATUS_COMPLETED, Repair::STATUS_DELIVERED])
            ->sum('total_cost');

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
            'todaySalesCount',
            'todayRepairRevenue',
            'pendingRepairs',
            'monthlyRevenue',
            'monthlyRepairRevenue',
            'monthlyTotal',
            'monthlySalesCount',
            'monthlyRepairCount',
            'repairStats',
            'lowStockProducts',
            'recentRepairs',
            'recentSales',
            'creditPending',
            'arOverdue',
            'arOutstanding'
        ));
    }
}
