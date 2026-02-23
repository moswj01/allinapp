<?php

namespace App\Http\Controllers;

use App\Models\AccountsReceivable;
use App\Models\ARPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountsReceivableController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AccountsReceivable::with(['customer', 'branch']);

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->input('overdue') === '1') {
            $query->where('due_date', '<', now())->where('status', '!=', AccountsReceivable::STATUS_PAID);
        }

        $receivables = $query->orderBy('due_date', 'asc')->paginate(20)->withQueryString();

        // Summary
        $summaryBase = AccountsReceivable::query();
        if (!$user->isOwner() && !$user->isAdmin()) {
            $summaryBase->where('branch_id', $user->branch_id);
        }
        $summary = [
            'total_outstanding' => (clone $summaryBase)->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance'),
            'total_overdue' => (clone $summaryBase)->where('due_date', '<', now())->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance'),
            'total_count' => (clone $summaryBase)->whereIn('status', ['pending', 'partial', 'overdue'])->count(),
            'overdue_count' => (clone $summaryBase)->where('due_date', '<', now())->whereIn('status', ['pending', 'partial', 'overdue'])->count(),
        ];

        return view('accounts-receivable.index', compact('receivables', 'summary'));
    }

    public function show(AccountsReceivable $accountsReceivable)
    {
        $accountsReceivable->load(['customer', 'branch', 'payments', 'source']);

        return view('accounts-receivable.show', compact('accountsReceivable'));
    }

    public function addPayment(Request $request, AccountsReceivable $accountsReceivable)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $accountsReceivable->balance,
            'payment_method' => 'required|in:cash,transfer,qr,card',
            'reference_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($accountsReceivable, $validated, $user) {
            ARPayment::create([
                'accounts_receivable_id' => $accountsReceivable->id,
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $accountsReceivable->updateBalance();
            $accountsReceivable->save();
        });

        return redirect()->back()->with('success', 'บันทึกการชำระเงินเรียบร้อย ฿' . number_format($validated['amount'], 2));
    }
}