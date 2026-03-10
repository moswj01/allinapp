<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlanChangeRequest;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PlanChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PlanChangeRequest::with('tenant', 'currentPlan', 'requestedPlan', 'requestedByUser')
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: show pending first
            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')");
        }

        $requests = $query->paginate(20)->withQueryString();

        $pendingCount = PlanChangeRequest::where('status', 'pending')->count();

        return view('superadmin.plan-requests.index', compact('requests', 'pendingCount'));
    }

    public function approve(Request $request, int $id)
    {
        $planRequest = PlanChangeRequest::with('tenant', 'requestedPlan')->findOrFail($id);

        if (!$planRequest->isPending()) {
            return back()->with('error', 'คำขอนี้ถูกดำเนินการแล้ว');
        }

        if (!$planRequest->is_paid) {
            return back()->with('error', 'ร้านค้ายังไม่ได้ชำระเงิน ไม่สามารถอนุมัติได้');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $tenant = Tenant::withoutGlobalScopes()->findOrFail($planRequest->tenant_id);
        $newPlan = $planRequest->requestedPlan;

        // Update plan
        $tenant->update(['plan_id' => $newPlan->id]);

        // Create invoice
        if ($newPlan->price > 0) {
            TenantInvoice::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $newPlan->id,
                'invoice_number' => TenantInvoice::generateNumber(),
                'amount' => $planRequest->amount,
                'tax_amount' => $planRequest->tax_amount,
                'total_amount' => $planRequest->total_amount,
                'status' => 'paid',
                'billing_cycle' => 'monthly',
                'period_start' => now(),
                'period_end' => now()->addMonth(),
                'paid_at' => $planRequest->paid_at ?? now(),
                'payment_method' => $planRequest->payment_method,
            ]);
        }

        // Activate tenant if trial
        if ($tenant->isTrial()) {
            $tenant->update([
                'status' => Tenant::STATUS_ACTIVE,
                'subscription_starts_at' => now(),
                'subscription_ends_at' => now()->addMonth(),
            ]);
        }

        // Mark request approved
        $planRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        return back()->with('success', 'อนุมัติการเปลี่ยนแพ็กเกจของ "' . $tenant->name . '" เรียบร้อย');
    }

    public function reject(Request $request, int $id)
    {
        $planRequest = PlanChangeRequest::with('tenant')->findOrFail($id);

        if (!$planRequest->isPending()) {
            return back()->with('error', 'คำขอนี้ถูกดำเนินการแล้ว');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $planRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_note' => $validated['admin_note'],
        ]);

        return back()->with('success', 'ปฏิเสธคำขอของ "' . $planRequest->tenant->name . '" แล้ว');
    }

    public function markPaid(Request $request, int $id)
    {
        $planRequest = PlanChangeRequest::findOrFail($id);

        if (!$planRequest->isPending()) {
            return back()->with('error', 'คำขอนี้ถูกดำเนินการแล้ว');
        }

        $validated = $request->validate([
            'payment_method' => 'nullable|string|max:100',
        ]);

        $planRequest->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
        ]);

        return back()->with('success', 'ยืนยันการชำระเงินเรียบร้อย');
    }
}
