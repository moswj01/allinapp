<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'branch', 'receivedBy'])->withCount('items');

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('gr_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn($pq) => $pq->where('po_number', 'like', "%{$search}%"));
            });
        }

        $goodsReceipts = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('goods-receipts.index', compact('goodsReceipts'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $poQuery = PurchaseOrder::with(['supplier', 'items'])
            ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIAL]);

        if (!$user->isOwner() && !$user->isAdmin()) {
            $poQuery->where('branch_id', $user->branch_id);
        }

        $purchaseOrders = $poQuery->orderBy('created_at', 'desc')->get();
        $selectedPo = $request->input('po_id') ? PurchaseOrder::with('items')->find($request->input('po_id')) : null;

        return view('goods-receipts.create', compact('purchaseOrders', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_invoice' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $po = PurchaseOrder::with('items')->findOrFail($validated['purchase_order_id']);

        $today = Carbon::today();
        $count = GoodsReceipt::whereDate('created_at', $today)->count() + 1;
        $grNumber = 'GR-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $gr = GoodsReceipt::create([
                'gr_number' => $grNumber,
                'purchase_order_id' => $po->id,
                'branch_id' => $po->branch_id,
                'supplier_invoice' => $validated['supplier_invoice'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => $user->id,
            ]);

            foreach ($validated['items'] as $item) {
                if ($item['quantity_received'] <= 0) continue;

                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $poItem->id,
                    'itemable_type' => $poItem->itemable_type,
                    'itemable_id' => $poItem->itemable_id,
                    'quantity_received' => $item['quantity_received'],
                    'unit_cost' => $poItem->unit_price,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update PO item received quantity
                $poItem->increment('received_quantity', $item['quantity_received']);

                // Update branch stock
                if ($poItem->itemable_type === Product::class && $poItem->itemable_id) {
                    $branchStock = BranchStock::firstOrCreate([
                        'branch_id' => $po->branch_id,
                        'stockable_type' => Product::class,
                        'stockable_id' => $poItem->itemable_id,
                    ], ['quantity' => 0, 'min_quantity' => 0, 'reserved_quantity' => 0]);

                    $before = $branchStock->quantity;
                    $branchStock->increment('quantity', $item['quantity_received']);

                    StockMovement::create([
                        'branch_id' => $po->branch_id,
                        'movable_type' => Product::class,
                        'movable_id' => $poItem->itemable_id,
                        'type' => StockMovement::TYPE_IN,
                        'quantity' => $item['quantity_received'],
                        'before_quantity' => $before,
                        'after_quantity' => $branchStock->quantity,
                        'unit_cost' => $poItem->unit_price,
                        'reference_number' => $grNumber,
                        'notes' => "รับสินค้าจาก PO: {$po->po_number}",
                        'created_by' => $user->id,
                    ]);
                }
            }

            // Update PO status
            $allReceived = $po->items->every(fn($i) => $i->fresh()->isFullyReceived());
            $anyReceived = $po->items->contains(fn($i) => $i->fresh()->received_quantity > 0);

            if ($allReceived) {
                $po->update(['status' => PurchaseOrder::STATUS_RECEIVED]);
            } elseif ($anyReceived) {
                $po->update(['status' => PurchaseOrder::STATUS_PARTIAL]);
            }

            DB::commit();

            return redirect()->route('goods-receipts.show', $gr)
                ->with('success', 'บันทึกการรับสินค้าเรียบร้อย เลขที่: ' . $grNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['purchaseOrder.supplier', 'branch', 'receivedBy', 'items']);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }
}
