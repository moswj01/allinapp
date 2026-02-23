<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Repair;
use App\Models\Product;
use App\Models\Customer;
use App\Models\BranchStock;
use App\Models\PurchaseOrder;
use App\Models\DailySettlement;
use App\Models\PettyCash;
use App\Models\StockMovement;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Sales Report
     */
    public function sales(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $query = Sale::with(['customer', 'createdBy', 'items'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // Branch restriction
        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Payment method filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderByDesc('created_at')->paginate(30);

        // Summary
        $summaryBase = Sale::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $summaryBase->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $summaryBase->where('branch_id', $request->branch_id);
        }

        $summary = [
            'total_count' => (clone $summaryBase)->count(),
            'total_amount' => (clone $summaryBase)->whereIn('status', ['completed', 'pending', 'paid'])->sum('total'),
            'completed' => (clone $summaryBase)->whereIn('status', ['completed', 'paid'])->count(),
            'pending' => (clone $summaryBase)->where('status', 'pending')->count(),
            'voided' => (clone $summaryBase)->where('status', 'voided')->count(),
            'cash' => (clone $summaryBase)->where('payment_method', 'cash')->whereIn('status', ['completed', 'pending', 'paid'])->sum('total'),
            'transfer' => (clone $summaryBase)->where('payment_method', 'transfer')->whereIn('status', ['completed', 'pending', 'paid'])->sum('total'),
            'credit' => (clone $summaryBase)->where('payment_method', 'credit')->whereIn('status', ['completed', 'pending', 'paid'])->sum('total'),
        ];

        // Daily breakdown for chart
        $dailySales = Sale::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereIn('status', ['completed', 'pending', 'paid']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $dailySales->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $dailySales->where('branch_id', $request->branch_id);
        }
        $dailySales = $dailySales->groupBy('date')->orderBy('date')->get();

        // Top products
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereIn('sales.status', ['completed', 'pending', 'paid']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $topProducts->where('sales.branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $topProducts->where('sales.branch_id', $request->branch_id);
        }
        $topProducts = $topProducts->selectRaw('sale_items.item_name as product_name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.total) as total_amount')
            ->groupBy('sale_items.item_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('reports.sales', compact('sales', 'summary', 'dailySales', 'topProducts', 'from', 'to', 'branches'));
    }

    /**
     * Repair Report
     */
    public function repairs(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $query = Repair::with(['customer', 'technician', 'receivedBy', 'branch'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // Branch restriction
        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        $repairs = $query->orderByDesc('created_at')->paginate(30);

        // Summary
        $summaryBase = Repair::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $summaryBase->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $summaryBase->where('branch_id', $request->branch_id);
        }

        $statuses = ['pending', 'in_progress', 'waiting_parts', 'completed', 'delivered', 'cancelled'];
        $statusCounts = [];
        foreach ($statuses as $s) {
            $statusCounts[$s] = (clone $summaryBase)->where('status', $s)->count();
        }

        $summary = [
            'total_count' => (clone $summaryBase)->count(),
            'total_revenue' => (clone $summaryBase)->whereIn('status', ['completed', 'delivered'])->sum('total_cost'),
            'total_paid' => (clone $summaryBase)->whereIn('status', ['completed', 'delivered'])->sum('paid_amount'),
            'total_unpaid' => (clone $summaryBase)->whereIn('status', ['completed', 'delivered'])
                ->selectRaw('SUM(total_cost - paid_amount) as balance')->value('balance') ?? 0,
            'status_counts' => $statusCounts,
            'avg_service_cost' => (clone $summaryBase)->whereIn('status', ['completed', 'delivered'])->avg('service_cost') ?? 0,
        ];

        // Daily breakdown
        $dailyRepairs = Repair::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_cost) as total')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $dailyRepairs->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $dailyRepairs->where('branch_id', $request->branch_id);
        }
        $dailyRepairs = $dailyRepairs->groupBy('date')->orderBy('date')->get();

        // Top technicians
        $topTechnicians = Repair::join('users', 'repairs.technician_id', '=', 'users.id')
            ->whereBetween('repairs.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereIn('repairs.status', ['completed', 'delivered']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $topTechnicians->where('repairs.branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $topTechnicians->where('repairs.branch_id', $request->branch_id);
        }
        $topTechnicians = $topTechnicians->selectRaw('users.name, COUNT(*) as total_jobs, SUM(repairs.total_cost) as total_revenue')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_jobs')
            ->limit(10)
            ->get();

        // Device brand breakdown
        $deviceBrands = Repair::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $deviceBrands->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $deviceBrands->where('branch_id', $request->branch_id);
        }
        $deviceBrands = $deviceBrands->selectRaw('device_brand, COUNT(*) as count')
            ->whereNotNull('device_brand')
            ->where('device_brand', '!=', '')
            ->groupBy('device_brand')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('reports.repairs', compact('repairs', 'summary', 'dailyRepairs', 'topTechnicians', 'deviceBrands', 'from', 'to', 'branches'));
    }

    /**
     * Stock Report
     */
    public function stock(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = BranchStock::with(['product.category', 'branch'])
            ->where('stockable_type', 'App\\Models\\Product');

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%"));
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'low_stock') {
                $query->whereColumn('quantity', '<=', DB::raw('COALESCE(min_quantity, 0)'));
            } elseif ($request->filter === 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            }
        }

        $stocks = $query->orderBy('quantity', 'asc')->paginate(50)->withQueryString();

        $stockBase = BranchStock::where('stockable_type', 'App\\Models\\Product');

        $summary = [
            'total_products' => Product::count(),
            'total_value' => (clone $stockBase)->when(!$user->isOwner() && !$user->isAdmin(), fn($q) => $q->where('branch_id', $branchId))
                ->join('products', 'branch_stocks.stockable_id', '=', 'products.id')
                ->selectRaw('SUM(branch_stocks.quantity * products.cost) as total')
                ->value('total') ?? 0,
            'low_stock' => (clone $stockBase)->when(!$user->isOwner() && !$user->isAdmin(), fn($q) => $q->where('branch_id', $branchId))
                ->whereColumn('quantity', '<=', DB::raw('COALESCE(min_quantity, 0)'))
                ->where('quantity', '>', 0)->count(),
            'out_of_stock' => (clone $stockBase)->when(!$user->isOwner() && !$user->isAdmin(), fn($q) => $q->where('branch_id', $branchId))
                ->where('quantity', '<=', 0)->count(),
        ];

        $branches = Branch::orderBy('name')->get();

        return view('reports.stock', compact('stocks', 'summary', 'branches'));
    }

    /**
     * Finance Report
     */
    public function finance(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $settlementBase = DailySettlement::whereBetween('settlement_date', [$from, $to]);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $settlementBase->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $settlementBase->where('branch_id', $request->branch_id);
        }

        $settlements = (clone $settlementBase)->with(['branch', 'createdBy'])->orderBy('settlement_date', 'desc')->get();

        $pettyCashBase = PettyCash::whereBetween('transaction_date', [$from, $to]);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $pettyCashBase->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $pettyCashBase->where('branch_id', $request->branch_id);
        }

        $summary = [
            'total_sales' => (clone $settlementBase)->sum('total_sales'),
            'total_cash_sales' => (clone $settlementBase)->sum('cash_sales'),
            'total_transfer_sales' => (clone $settlementBase)->sum('transfer_sales'),
            'total_repair_revenue' => (clone $settlementBase)->sum('repair_revenue'),
            'petty_cash_in' => (clone $pettyCashBase)->where('type', 'in')->sum('amount'),
            'petty_cash_out' => (clone $pettyCashBase)->where('type', 'out')->sum('amount'),
            'total_difference' => (clone $settlementBase)->sum('difference'),
        ];

        $dailySummary = DailySettlement::selectRaw('settlement_date, SUM(total_sales) as total_sales, SUM(actual_cash) as total_cash, SUM(difference) as total_diff')
            ->whereBetween('settlement_date', [$from, $to])
            ->when(!$user->isOwner() && !$user->isAdmin(), fn($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->groupBy('settlement_date')
            ->orderBy('settlement_date')
            ->get();

        $branches = Branch::orderBy('name')->get();

        return view('reports.finance', compact('settlements', 'summary', 'dailySummary', 'from', 'to', 'branches'));
    }

    /**
     * Purchasing Report
     */
    public function purchasing(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $query = PurchaseOrder::with(['supplier', 'branch', 'createdBy'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        $summaryBase = PurchaseOrder::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $summaryBase->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $summaryBase->where('branch_id', $request->branch_id);
        }

        $summary = [
            'total_count' => (clone $summaryBase)->count(),
            'total_amount' => (clone $summaryBase)->whereNotIn('status', ['cancelled'])->sum('total'),
            'approved' => (clone $summaryBase)->where('status', 'approved')->count(),
            'received' => (clone $summaryBase)->where('status', 'received')->count(),
            'draft' => (clone $summaryBase)->where('status', 'draft')->count(),
            'cancelled' => (clone $summaryBase)->where('status', 'cancelled')->count(),
        ];

        $topSuppliers = PurchaseOrder::join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchase_orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereNotIn('purchase_orders.status', ['cancelled'])
            ->when(!$user->isOwner() && !$user->isAdmin(), fn($q) => $q->where('purchase_orders.branch_id', $branchId))
            ->when($request->filled('branch_id'), fn($q) => $q->where('purchase_orders.branch_id', $request->branch_id))
            ->selectRaw('suppliers.name, COUNT(*) as order_count, SUM(purchase_orders.total) as total_amount')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $branches = Branch::orderBy('name')->get();

        return view('reports.purchasing', compact('purchaseOrders', 'summary', 'topSuppliers', 'from', 'to', 'branches'));
    }
}
