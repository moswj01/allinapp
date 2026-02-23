<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use App\Models\DailySettlement;
use App\Models\Sale;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    // ==================== Petty Cash ====================

    public function pettyCashIndex(Request $request)
    {
        $user = Auth::user();
        $query = PettyCash::with(['branch', 'createdBy', 'approvedBy']);

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));
        $query->whereBetween('transaction_date', [$from, $to]);

        $pettyCash = $query->orderBy('transaction_date', 'desc')->paginate(20)->withQueryString();
        $categories = PettyCash::getCategories();

        // Summary
        $summaryBase = PettyCash::whereBetween('transaction_date', [$from, $to]);
        if (!$user->isOwner() && !$user->isAdmin()) {
            $summaryBase->where('branch_id', $user->branch_id);
        }
        $summary = [
            'total_in' => (clone $summaryBase)->where('type', 'in')->sum('amount'),
            'total_out' => (clone $summaryBase)->where('type', 'out')->sum('amount'),
        ];
        $summary['balance'] = $summary['total_in'] - $summary['total_out'];

        return view('finance.petty-cash.index', compact('pettyCash', 'categories', 'summary', 'from', 'to'));
    }

    public function pettyCashCreate()
    {
        $categories = PettyCash::getCategories();

        return view('finance.petty-cash.create', compact('categories'));
    }

    public function pettyCashStore(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'receipt_number' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
        ]);

        $user = Auth::user();

        PettyCash::create([
            'branch_id' => $user->branch_id,
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'receipt_number' => $validated['receipt_number'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'created_by' => $user->id,
        ]);

        return redirect()->route('finance.petty-cash.index')->with('success', 'บันทึกรายการเงินสดย่อยเรียบร้อย');
    }

    // ==================== Daily Settlement ====================

    public function dailySettlementIndex(Request $request)
    {
        $user = Auth::user();
        $query = DailySettlement::with(['branch', 'createdBy', 'approvedBy']);

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $dailySettlements = $query->orderBy('settlement_date', 'desc')->paginate(20)->withQueryString();

        return view('finance.daily-settlement.index', compact('dailySettlements'));
    }

    public function dailySettlementCreate()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        // Auto-calculate today's sales
        $salesQuery = Sale::where('branch_id', $user->branch_id)
            ->whereDate('created_at', $today)
            ->whereIn('status', ['completed', 'pending', 'paid']);

        $cashSales = (clone $salesQuery)->where('payment_method', 'cash')->sum('total');
        $transferSales = (clone $salesQuery)->where('payment_method', 'transfer')->sum('total');
        $qrSales = (clone $salesQuery)->where('payment_method', 'qr')->sum('total');
        $cardSales = (clone $salesQuery)->where('payment_method', 'card')->sum('total');
        $creditSales = (clone $salesQuery)->where('payment_method', 'credit')->sum('total');
        $totalSales = $salesQuery->sum('total');

        // Repair revenue
        $repairRevenue = Repair::where('branch_id', $user->branch_id)
            ->whereDate('created_at', $today)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('paid_amount');

        // Petty cash
        $cashIn = PettyCash::where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today)->where('type', 'in')->sum('amount');
        $cashOut = PettyCash::where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today)->where('type', 'out')->sum('amount');

        $autoData = compact('cashSales', 'transferSales', 'qrSales', 'cardSales', 'creditSales', 'totalSales', 'repairRevenue', 'cashIn', 'cashOut', 'today');

        return view('finance.daily-settlement.create', compact('autoData'));
    }

    public function dailySettlementStore(Request $request)
    {
        $validated = $request->validate([
            'settlement_date' => 'required|date',
            'opening_cash' => 'required|numeric|min:0',
            'cash_sales' => 'required|numeric|min:0',
            'transfer_sales' => 'required|numeric|min:0',
            'qr_sales' => 'required|numeric|min:0',
            'card_sales' => 'required|numeric|min:0',
            'credit_sales' => 'required|numeric|min:0',
            'total_sales' => 'required|numeric|min:0',
            'cash_in' => 'required|numeric|min:0',
            'cash_out' => 'required|numeric|min:0',
            'actual_cash' => 'required|numeric|min:0',
            'repair_revenue' => 'nullable|numeric|min:0',
            'product_revenue' => 'nullable|numeric|min:0',
            'difference_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $expectedCash = $validated['opening_cash'] + $validated['cash_sales'] + $validated['cash_in'] - $validated['cash_out'];
        $difference = $validated['actual_cash'] - $expectedCash;

        DailySettlement::create([
            'branch_id' => $user->branch_id,
            'settlement_date' => $validated['settlement_date'],
            'opening_cash' => $validated['opening_cash'],
            'cash_sales' => $validated['cash_sales'],
            'transfer_sales' => $validated['transfer_sales'],
            'qr_sales' => $validated['qr_sales'],
            'card_sales' => $validated['card_sales'],
            'credit_sales' => $validated['credit_sales'],
            'total_sales' => $validated['total_sales'],
            'cash_in' => $validated['cash_in'],
            'cash_out' => $validated['cash_out'],
            'expected_cash' => $expectedCash,
            'actual_cash' => $validated['actual_cash'],
            'difference' => $difference,
            'difference_reason' => $validated['difference_reason'] ?? null,
            'repair_revenue' => $validated['repair_revenue'] ?? 0,
            'product_revenue' => $validated['product_revenue'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'status' => DailySettlement::STATUS_PENDING,
            'created_by' => $user->id,
        ]);

        return redirect()->route('finance.daily-settlement.index')->with('success', 'บันทึกปิดยอดประจำวันเรียบร้อย');
    }

    public function dailySettlementShow(DailySettlement $dailySettlement)
    {
        $dailySettlement->load(['branch', 'createdBy', 'approvedBy']);

        return view('finance.daily-settlement.show', compact('dailySettlement'));
    }

    public function dailySettlementApprove(DailySettlement $dailySettlement)
    {
        if ($dailySettlement->status !== DailySettlement::STATUS_PENDING) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ในสถานะนี้');
        }

        $user = Auth::user();
        $dailySettlement->update([
            'status' => DailySettlement::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'อนุมัติปิดยอดเรียบร้อย');
    }
}
