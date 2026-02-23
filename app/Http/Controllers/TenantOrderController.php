<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantOrder;
use App\Models\TenantOrderItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantOrderController extends Controller
{
    /**
     * Get the system-admin tenant (super admin's store).
     */
    private function getAdminTenant(): ?Tenant
    {
        return Tenant::withoutGlobalScopes()->where('slug', 'system-admin')->first();
    }

    /**
     * List current tenant's orders.
     */
    public function index(Request $request)
    {
        $query = TenantOrder::where('tenant_id', Tenant::currentId())
            ->with(['items', 'createdBy', 'branch'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('tenant-orders.index', [
            'orders' => $orders,
            'statuses' => TenantOrder::getStatuses(),
        ]);
    }

    /**
     * Show the super admin's product catalog for ordering.
     */
    public function create(Request $request)
    {
        $adminTenant = $this->getAdminTenant();
        if (!$adminTenant) {
            return back()->with('error', 'ยังไม่มีร้านค้ากลาง กรุณาติดต่อผู้ดูแลระบบ');
        }

        // Get admin's products (bypass tenant scope)
        $query = Product::withoutGlobalScope('tenant')
            ->where('tenant_id', $adminTenant->id)
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->with('category')->get();

        // Get admin's categories
        $categories = Category::withoutGlobalScope('tenant')
            ->where('tenant_id', $adminTenant->id)
            ->orderBy('name')
            ->get();

        $currentTenant = Tenant::current();

        return view('tenant-orders.create', [
            'products' => $products,
            'categories' => $categories,
            'tenant' => $currentTenant,
            'adminTenant' => $adminTenant,
        ]);
    }

    /**
     * Store a new order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:500',
            'shipping_phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $adminTenant = $this->getAdminTenant();

        if (!$adminTenant) {
            return back()->with('error', 'ยังไม่มีร้านค้ากลาง');
        }

        DB::beginTransaction();
        try {
            $order = TenantOrder::create([
                'order_number' => TenantOrder::generateOrderNumber(),
                'tenant_id' => Tenant::currentId(),
                'branch_id' => $user->branch_id,
                'created_by' => $user->id,
                'status' => TenantOrder::STATUS_PENDING,
                'notes' => $request->notes,
                'shipping_address' => $request->shipping_address ?: Tenant::current()->address,
                'shipping_phone' => $request->shipping_phone ?: Tenant::current()->phone,
            ]);

            $subtotal = 0;

            foreach ($request->items as $item) {
                // Get product from admin's catalog
                $product = Product::withoutGlobalScope('tenant')
                    ->where('tenant_id', $adminTenant->id)
                    ->findOrFail($item['product_id']);

                // Use wholesale_price if available, otherwise retail
                $price = $product->wholesale_price > 0
                    ? $product->wholesale_price
                    : $product->retail_price;

                $itemSubtotal = $price * $item['quantity'];
                $subtotal += $itemSubtotal;

                TenantOrderItem::create([
                    'tenant_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            DB::commit();

            return redirect()->route('tenant-orders.show', $order)
                ->with('success', 'สั่งสินค้าเรียบร้อยแล้ว หมายเลข: ' . $order->order_number);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Show order detail.
     */
    public function show(TenantOrder $tenantOrder)
    {
        // Only allow viewing own tenant's orders
        if ($tenantOrder->tenant_id !== Tenant::currentId()) {
            abort(403);
        }

        $tenantOrder->load(['items.product', 'createdBy', 'confirmedBy', 'shippedBy', 'receivedBy', 'cancelledBy', 'branch', 'tenant']);

        return view('tenant-orders.show', [
            'order' => $tenantOrder,
            'statuses' => TenantOrder::getStatuses(),
        ]);
    }

    /**
     * Tenant marks order as received.
     */
    public function receive(Request $request, TenantOrder $tenantOrder)
    {
        if ($tenantOrder->tenant_id !== Tenant::currentId()) {
            abort(403);
        }

        if (!$tenantOrder->canBeReceived()) {
            return back()->with('error', 'ไม่สามารถรับสินค้าได้ในสถานะนี้');
        }

        $tenantOrder->update([
            'status' => TenantOrder::STATUS_RECEIVED,
            'received_by' => $request->user()->id,
            'received_at' => now(),
        ]);

        return back()->with('success', 'รับสินค้าเรียบร้อยแล้ว');
    }

    /**
     * Tenant cancels their own order.
     */
    public function cancel(Request $request, TenantOrder $tenantOrder)
    {
        if ($tenantOrder->tenant_id !== Tenant::currentId()) {
            abort(403);
        }

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
