<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'createdBy'])->withCount('items');

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('from_branch_id', $user->branch_id)
                    ->orWhere('to_branch_id', $user->branch_id);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where('transfer_number', 'like', "%{$search}%");
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $statuses = StockTransfer::getStatuses();

        return view('stock-transfers.index', compact('transfers', 'statuses'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $user = Auth::user();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('stock-transfers.create', compact('branches', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $count = StockTransfer::whereDate('created_at', $today)->count() + 1;
        $transferNumber = 'TF-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_branch_id' => $validated['from_branch_id'],
                'to_branch_id' => $validated['to_branch_id'],
                'status' => StockTransfer::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'itemable_type' => Product::class,
                    'itemable_id' => $product->id,
                    'quantity_requested' => $item['quantity'],
                    'quantity_shipped' => 0,
                    'quantity_received' => 0,
                    'unit_cost' => $product->cost ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('stock-transfers.show', $transfer)
                ->with('success', 'สร้างใบโอนสต๊อกเรียบร้อย เลขที่: ' . $transferNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'fromBranch',
            'toBranch',
            'createdBy',
            'approvedBy',
            'shippedBy',
            'receivedBy',
            'cancelledBy',
            'items.itemable',
        ]);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    public function approve(StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->canBeApproved()) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ในสถานะนี้');
        }

        $user = Auth::user();
        $stockTransfer->update([
            'status' => StockTransfer::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'อนุมัติการโอนสต๊อกเรียบร้อย');
    }

    public function ship(StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->canBeShipped()) {
            return redirect()->back()->with('error', 'ไม่สามารถจัดส่งได้ในสถานะนี้');
        }

        $user = Auth::user();

        DB::transaction(function () use ($stockTransfer, $user) {
            foreach ($stockTransfer->items as $item) {
                $shipQty = $item->quantity_requested;
                $item->update(['quantity_shipped' => $shipQty]);

                // Deduct from source branch
                $fromStock = BranchStock::where('branch_id', $stockTransfer->from_branch_id)
                    ->where('stockable_type', $item->itemable_type)
                    ->where('stockable_id', $item->itemable_id)
                    ->first();

                if ($fromStock) {
                    $before = $fromStock->quantity;
                    $fromStock->decrement('quantity', $shipQty);

                    StockMovement::create([
                        'branch_id' => $stockTransfer->from_branch_id,
                        'movable_type' => $item->itemable_type,
                        'movable_id' => $item->itemable_id,
                        'type' => StockMovement::TYPE_TRANSFER_OUT,
                        'quantity' => $shipQty,
                        'before_quantity' => $before,
                        'after_quantity' => $fromStock->quantity,
                        'reference_number' => $stockTransfer->transfer_number,
                        'notes' => "โอนสต๊อกไป: {$stockTransfer->toBranch->name}",
                        'created_by' => $user->id,
                    ]);
                }
            }

            $stockTransfer->update([
                'status' => StockTransfer::STATUS_SHIPPED,
                'shipped_by' => $user->id,
                'shipped_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'จัดส่งสินค้าเรียบร้อย');
    }

    public function receive(StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->canBeReceived()) {
            return redirect()->back()->with('error', 'ไม่สามารถรับสินค้าได้ในสถานะนี้');
        }

        $user = Auth::user();

        DB::transaction(function () use ($stockTransfer, $user) {
            foreach ($stockTransfer->items as $item) {
                $receiveQty = $item->quantity_shipped;
                $item->update(['quantity_received' => $receiveQty]);

                // Add to destination branch
                $toStock = BranchStock::firstOrCreate([
                    'branch_id' => $stockTransfer->to_branch_id,
                    'stockable_type' => $item->itemable_type,
                    'stockable_id' => $item->itemable_id,
                ], ['quantity' => 0, 'min_quantity' => 0, 'reserved_quantity' => 0]);

                $before = $toStock->quantity;
                $toStock->increment('quantity', $receiveQty);

                StockMovement::create([
                    'branch_id' => $stockTransfer->to_branch_id,
                    'movable_type' => $item->itemable_type,
                    'movable_id' => $item->itemable_id,
                    'type' => StockMovement::TYPE_TRANSFER_IN,
                    'quantity' => $receiveQty,
                    'before_quantity' => $before,
                    'after_quantity' => $toStock->quantity,
                    'reference_number' => $stockTransfer->transfer_number,
                    'notes' => "รับสต๊อกจาก: {$stockTransfer->fromBranch->name}",
                    'created_by' => $user->id,
                ]);
            }

            $stockTransfer->update([
                'status' => StockTransfer::STATUS_RECEIVED,
                'received_by' => $user->id,
                'received_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'รับสินค้าเรียบร้อย');
    }

    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->canBeCancelled()) {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกได้ในสถานะนี้');
        }

        $validated = $request->validate(['cancel_reason' => 'required|string|max:500']);

        $user = Auth::user();
        $stockTransfer->update([
            'status' => StockTransfer::STATUS_CANCELLED,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancel_reason' => $validated['cancel_reason'],
        ]);

        return redirect()->back()->with('success', 'ยกเลิกการโอนสต๊อกเรียบร้อย');
    }
}
