<?php

namespace App\Http\Controllers;

use App\Models\BranchOrder;
use App\Models\BranchOrderItem;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchOrderController extends Controller
{
    /**
     * รายการใบสั่งซื้อ — สาขาเห็นเฉพาะของตัวเอง, สาขาใหญ่เห็นทั้งหมด
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branch = $user->branch;

        $query = BranchOrder::with(['branch', 'mainBranch', 'createdBy'])
            ->withCount('items');

        // สาขาใหญ่เห็นทุกใบสั่ง, สาขาย่อยเห็นเฉพาะของตัวเอง
        if (!$branch || !$branch->is_main) {
            $query->where('branch_id', $user->branch_id);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('branch', fn($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $statuses = BranchOrder::getStatuses();

        return view('branch-orders.index', compact('orders', 'statuses'));
    }

    /**
     * ฟอร์มสร้างใบสั่งซื้อ (สาขาย่อย)
     */
    public function create()
    {
        $mainBranch = Branch::where('is_main', true)->first();

        if (!$mainBranch) {
            return redirect()->route('branch-orders.index')
                ->with('error', 'ไม่พบสาขาใหญ่ กรุณาตั้งค่าสาขาใหญ่ก่อน');
        }

        $products = Product::where('is_active', true)
            ->where('branch_id', $mainBranch->id)
            ->with(['category', 'branchStocks' => function ($q) use ($mainBranch) {
                $q->where('branch_id', $mainBranch->id);
            }])
            ->orderBy('name')
            ->get();

        $categories = \App\Models\Category::where('type', 'product')->where('is_active', true)->get();

        return view('branch-orders.create', compact('products', 'mainBranch', 'categories'));
    }

    /**
     * บันทึกใบสั่งซื้อใหม่
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mainBranch = Branch::where('is_main', true)->first();

        if (!$mainBranch) {
            return redirect()->back()->with('error', 'ไม่พบสาขาใหญ่');
        }

        // Generate order number: BO-YYMMDD-XXXX
        $today = Carbon::today();
        $count = BranchOrder::whereDate('created_at', $today)->count() + 1;
        $orderNumber = 'BO-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $order = BranchOrder::create([
                'order_number' => $orderNumber,
                'branch_id' => $user->branch_id,
                'main_branch_id' => $mainBranch->id,
                'status' => BranchOrder::STATUS_PENDING,
                'notes' => $validated['notes'],
                'total' => 0,
                'created_by' => $user->id,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->cost * $item['quantity'];
                $total += $subtotal;

                BranchOrderItem::create([
                    'branch_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_requested' => $item['quantity'],
                    'unit_cost' => $product->cost,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $order->update(['total' => $total]);

            DB::commit();

            return redirect()->route('branch-orders.show', $order)
                ->with('success', 'สร้างใบสั่งซื้อเรียบร้อย เลขที่: ' . $orderNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * แสดงรายละเอียดใบสั่งซื้อ
     */
    public function show(BranchOrder $branchOrder)
    {
        $branchOrder->load([
            'branch',
            'mainBranch',
            'createdBy',
            'approvedBy',
            'shippedBy',
            'receivedBy',
            'cancelledBy',
            'items.product',
        ]);

        return view('branch-orders.show', compact('branchOrder'));
    }

    /**
     * อนุมัติใบสั่งซื้อ (สาขาใหญ่)
     */
    public function approve(Request $request, BranchOrder $branchOrder)
    {
        if (!$branchOrder->canBeApproved()) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ในสถานะนี้');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($branchOrder, $user, $request) {
            // Update approved quantities from form or auto-approve all
            foreach ($branchOrder->items as $item) {
                $approvedQty = $request->input("items.{$item->id}.quantity_approved", $item->quantity_requested);
                $item->update(['quantity_approved' => $approvedQty]);
            }

            $branchOrder->update([
                'status' => BranchOrder::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'อนุมัติใบสั่งซื้อเรียบร้อย');
    }

    /**
     * จัดส่งสินค้า (สาขาใหญ่) — ตัดสต๊อกสาขาใหญ่
     */
    public function ship(Request $request, BranchOrder $branchOrder)
    {
        if (!$branchOrder->canBeShipped()) {
            return redirect()->back()->with('error', 'ไม่สามารถจัดส่งได้ในสถานะนี้');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($branchOrder, $user) {
            foreach ($branchOrder->items as $item) {
                $shipQty = $item->quantity_approved > 0 ? $item->quantity_approved : $item->quantity_requested;
                $item->update(['quantity_shipped' => $shipQty]);

                // ตัดสต๊อกจากสาขาใหญ่
                $mainStock = BranchStock::where('branch_id', $branchOrder->main_branch_id)
                    ->where('stockable_type', Product::class)
                    ->where('stockable_id', $item->product_id)
                    ->first();

                if ($mainStock && $mainStock->quantity >= $shipQty) {
                    $before = $mainStock->quantity;
                    $mainStock->decrement('quantity', $shipQty);

                    StockMovement::create([
                        'branch_id' => $branchOrder->main_branch_id,
                        'movable_type' => Product::class,
                        'movable_id' => $item->product_id,
                        'type' => 'out',
                        'quantity' => $shipQty,
                        'before_quantity' => $before,
                        'after_quantity' => $mainStock->quantity,
                        'reference_number' => $branchOrder->order_number,
                        'notes' => "จัดส่งสินค้าไปสาขา: {$branchOrder->branch->name}",
                        'created_by' => $user->id,
                    ]);
                }
            }

            $branchOrder->update([
                'status' => BranchOrder::STATUS_SHIPPED,
                'shipped_by' => $user->id,
                'shipped_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'จัดส่งสินค้าเรียบร้อย');
    }

    /**
     * รับสินค้า (สาขาย่อย) — เพิ่มสต๊อกสาขาปลายทาง
     */
    public function receive(Request $request, BranchOrder $branchOrder)
    {
        if (!$branchOrder->canBeReceived()) {
            return redirect()->back()->with('error', 'ไม่สามารถรับสินค้าได้ในสถานะนี้');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($branchOrder, $user) {
            foreach ($branchOrder->items as $item) {
                $receiveQty = $item->quantity_shipped;
                $item->update(['quantity_received' => $receiveQty]);

                // เพิ่มสต๊อกสาขาที่รับ
                $branchStock = BranchStock::firstOrCreate([
                    'branch_id' => $branchOrder->branch_id,
                    'stockable_type' => Product::class,
                    'stockable_id' => $item->product_id,
                ], [
                    'quantity' => 0,
                    'min_quantity' => 0,
                    'reserved_quantity' => 0,
                ]);

                $before = $branchStock->quantity;
                $branchStock->increment('quantity', $receiveQty);

                StockMovement::create([
                    'branch_id' => $branchOrder->branch_id,
                    'movable_type' => Product::class,
                    'movable_id' => $item->product_id,
                    'type' => 'in',
                    'quantity' => $receiveQty,
                    'before_quantity' => $before,
                    'after_quantity' => $branchStock->quantity,
                    'reference_number' => $branchOrder->order_number,
                    'notes' => "รับสินค้าจากสาขาใหญ่: {$branchOrder->mainBranch->name}",
                    'created_by' => $user->id,
                ]);
            }

            $branchOrder->update([
                'status' => BranchOrder::STATUS_RECEIVED,
                'received_by' => $user->id,
                'received_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'รับสินค้าเรียบร้อย');
    }

    /**
     * พิมพ์ใบสั่งซื้อ
     */
    public function print(BranchOrder $branchOrder)
    {
        $branchOrder->load([
            'branch',
            'mainBranch',
            'createdBy',
            'approvedBy',
            'shippedBy',
            'receivedBy',
            'items.product',
        ]);

        return view('branch-orders.print', compact('branchOrder'));
    }

    /**
     * ยกเลิกใบสั่งซื้อ
     */
    public function cancel(Request $request, BranchOrder $branchOrder)
    {
        if (!$branchOrder->canBeCancelled()) {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกได้ในสถานะนี้');
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $branchOrder->update([
            'status' => BranchOrder::STATUS_CANCELLED,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancel_reason' => $validated['cancel_reason'],
        ]);

        return redirect()->back()->with('success', 'ยกเลิกใบสั่งซื้อเรียบร้อย');
    }
}
