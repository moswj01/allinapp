<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseOrder::with(['supplier', 'branch', 'createdBy'])->withCount('items');

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $statuses = PurchaseOrder::getStatuses();

        return view('purchase-orders.index', compact('purchaseOrders', 'statuses'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_date' => 'nullable|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $today = Carbon::today();
        $count = PurchaseOrder::whereDate('created_at', $today)->count() + 1;
        $poNumber = 'PO-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $user->branch_id,
                'expected_date' => $validated['expected_date'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'subtotal' => 0,
                'total' => 0,
                'created_by' => $user->id,
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'itemable_type' => isset($item['product_id']) ? Product::class : null,
                    'itemable_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => 'ชิ้น',
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'received_quantity' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $total = $subtotal - ($validated['discount_amount'] ?? 0) + ($validated['tax_amount'] ?? 0);
            $po->update(['subtotal' => $subtotal, 'total' => $total]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $po)
                ->with('success', 'สร้างใบสั่งซื้อเรียบร้อย เลขที่: ' . $poNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'branch', 'createdBy', 'approvedBy', 'cancelledBy', 'items', 'goodsReceipts.items']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeApproved()) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ในสถานะนี้');
        }

        $user = Auth::user();
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'อนุมัติใบสั่งซื้อเรียบร้อย');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeCancelled()) {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกได้ในสถานะนี้');
        }

        $validated = $request->validate(['cancel_reason' => 'required|string|max:500']);

        $user = Auth::user();
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancel_reason' => $validated['cancel_reason'],
        ]);

        return redirect()->back()->with('success', 'ยกเลิกใบสั่งซื้อเรียบร้อย');
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'branch', 'createdBy', 'approvedBy', 'items']);

        return view('purchase-orders.print', compact('purchaseOrder'));
    }
}
