<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TenantOrder;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantOrderController extends Controller
{
    /**
     * List all tenant orders (for super admin).
     */
    public function index(Request $request)
    {
        $query = TenantOrder::with(['tenant', 'items', 'createdBy', 'branch'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', fn($t) => $t->withoutGlobalScopes()->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $tenants = Tenant::withoutGlobalScopes()
            ->where('slug', '!=', 'system-admin')
            ->active()
            ->orderBy('name')
            ->get();

        // Stats
        $stats = [
            'pending' => TenantOrder::where('status', TenantOrder::STATUS_PENDING)->count(),
            'confirmed' => TenantOrder::where('status', TenantOrder::STATUS_CONFIRMED)->count(),
            'shipped' => TenantOrder::where('status', TenantOrder::STATUS_SHIPPED)->count(),
            'total_revenue' => TenantOrder::where('status', TenantOrder::STATUS_RECEIVED)->sum('total'),
        ];

        return view('superadmin.tenant-orders.index', [
            'orders' => $orders,
            'tenants' => $tenants,
            'statuses' => TenantOrder::getStatuses(),
            'stats' => $stats,
        ]);
    }

    /**
     * Show single order detail.
     */
    public function show(TenantOrder $tenantOrder)
    {
        $tenantOrder->load([
            'items.product',
            'tenant',
            'branch',
            'createdBy',
            'confirmedBy',
            'shippedBy',
            'receivedBy',
            'cancelledBy',
        ]);

        return view('superadmin.tenant-orders.show', [
            'order' => $tenantOrder,
            'statuses' => TenantOrder::getStatuses(),
        ]);
    }

    /**
     * Confirm an order.
     */
    public function confirm(Request $request, TenantOrder $tenantOrder)
    {
        if (!$tenantOrder->canBeConfirmed()) {
            return back()->with('error', 'ไม่สามารถยืนยันออเดอร์ในสถานะนี้');
        }

        $tenantOrder->update([
            'status' => TenantOrder::STATUS_CONFIRMED,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'ยืนยันออเดอร์เรียบร้อยแล้ว');
    }

    /**
     * Mark as shipped.
     */
    public function ship(Request $request, TenantOrder $tenantOrder)
    {
        if (!$tenantOrder->canBeShipped()) {
            return back()->with('error', 'ไม่สามารถจัดส่งได้ในสถานะนี้');
        }

        $request->validate([
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $tenantOrder->update([
            'status' => TenantOrder::STATUS_SHIPPED,
            'shipped_by' => $request->user()->id,
            'shipped_at' => now(),
            'tracking_number' => $request->tracking_number,
        ]);

        return back()->with('success', 'อัปเดตสถานะจัดส่งเรียบร้อยแล้ว');
    }

    /**
     * Cancel order (admin side).
     */
    public function cancel(Request $request, TenantOrder $tenantOrder)
    {
        if (!$tenantOrder->canBeCancelled()) {
            return back()->with('error', 'ไม่สามารถยกเลิกออเดอร์ได้ในสถานะนี้');
        }

        $request->validate(['cancel_reason' => 'required|string|max:255']);

        $tenantOrder->update([
            'status' => TenantOrder::STATUS_CANCELLED,
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancel_reason' => $request->cancel_reason,
        ]);

        return back()->with('success', 'ยกเลิกออเดอร์เรียบร้อยแล้ว');
    }
}
