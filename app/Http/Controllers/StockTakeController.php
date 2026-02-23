<?php

namespace App\Http\Controllers;

use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockTakeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = StockTake::with(['branch', 'createdBy', 'category'])->withCount('items');

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where('stock_take_number', 'like', "%{$search}%");
        }

        $stockTakes = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $statuses = StockTake::getStatuses();

        return view('stock-takes.index', compact('stockTakes', 'statuses'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $types = StockTake::getTypes();

        return view('stock-takes.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:full,partial,category',
            'category_id' => 'nullable|exists:categories,id',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $today = Carbon::today();
        $count = StockTake::whereDate('created_at', $today)->count() + 1;
        $stNumber = 'ST-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $stockTake = StockTake::create([
                'stock_take_number' => $stNumber,
                'branch_id' => $user->branch_id,
                'type' => $validated['type'],
                'category_id' => $validated['category_id'] ?? null,
                'status' => StockTake::STATUS_DRAFT,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // Auto-populate items from branch stock
            $productQuery = Product::where('is_active', true);
            if ($validated['type'] === 'category' && isset($validated['category_id'])) {
                $productQuery->where('category_id', $validated['category_id']);
            }
            $products = $productQuery->get();

            foreach ($products as $product) {
                $branchStock = BranchStock::where('branch_id', $user->branch_id)
                    ->where('stockable_type', Product::class)
                    ->where('stockable_id', $product->id)
                    ->first();

                StockTakeItem::create([
                    'stock_take_id' => $stockTake->id,
                    'itemable_type' => Product::class,
                    'itemable_id' => $product->id,
                    'system_quantity' => $branchStock ? $branchStock->quantity : 0,
                    'counted_quantity' => 0,
                    'difference' => 0,
                    'unit_cost' => $product->cost ?? 0,
                    'difference_value' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('stock-takes.show', $stockTake)
                ->with('success', 'สร้างใบตรวจนับสต๊อกเรียบร้อย เลขที่: ' . $stNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(StockTake $stockTake)
    {
        $stockTake->load(['branch', 'createdBy', 'approvedBy', 'category', 'items.itemable']);

        return view('stock-takes.show', compact('stockTake'));
    }

    public function start(StockTake $stockTake)
    {
        if (!$stockTake->canBeStarted()) {
            return redirect()->back()->with('error', 'ไม่สามารถเริ่มนับได้ในสถานะนี้');
        }

        $stockTake->update([
            'status' => StockTake::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return redirect()->back()->with('success', 'เริ่มตรวจนับสต๊อกเรียบร้อย');
    }

    public function updateCounts(Request $request, StockTake $stockTake)
    {
        if ($stockTake->status !== StockTake::STATUS_IN_PROGRESS) {
            return redirect()->back()->with('error', 'ไม่สามารถอัปเดตได้ในสถานะนี้');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_take_items,id',
            'items.*.counted_quantity' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        foreach ($validated['items'] as $item) {
            $stItem = StockTakeItem::findOrFail($item['id']);
            $stItem->counted_quantity = $item['counted_quantity'];
            $stItem->calculateDifference();
            $stItem->counted_by = $user->id;
            $stItem->counted_at = now();
            $stItem->notes = $item['notes'] ?? null;
            $stItem->save();
        }

        return redirect()->back()->with('success', 'บันทึกผลการนับเรียบร้อย');
    }

    public function complete(StockTake $stockTake)
    {
        if (!$stockTake->canBeCompleted()) {
            return redirect()->back()->with('error', 'ไม่สามารถเสร็จสิ้นได้ในสถานะนี้');
        }

        $stockTake->update([
            'status' => StockTake::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'ตรวจนับเสร็จสิ้น รอการอนุมัติ');
    }

    public function approve(StockTake $stockTake)
    {
        if (!$stockTake->canBeApproved()) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ในสถานะนี้');
        }

        $user = Auth::user();

        DB::transaction(function () use ($stockTake, $user) {
            // Adjust stock based on differences
            foreach ($stockTake->items as $item) {
                if ($item->difference == 0) continue;

                $branchStock = BranchStock::where('branch_id', $stockTake->branch_id)
                    ->where('stockable_type', $item->itemable_type)
                    ->where('stockable_id', $item->itemable_id)
                    ->first();

                if ($branchStock) {
                    $before = $branchStock->quantity;
                    $branchStock->update(['quantity' => $item->counted_quantity]);

                    StockMovement::create([
                        'branch_id' => $stockTake->branch_id,
                        'movable_type' => $item->itemable_type,
                        'movable_id' => $item->itemable_id,
                        'type' => StockMovement::TYPE_ADJUSTMENT,
                        'quantity' => abs($item->difference),
                        'before_quantity' => $before,
                        'after_quantity' => $item->counted_quantity,
                        'reference_number' => $stockTake->stock_take_number,
                        'notes' => "ปรับปรุงจากตรวจนับ: {$stockTake->stock_take_number}",
                        'created_by' => $user->id,
                    ]);
                }
            }

            $stockTake->update([
                'status' => StockTake::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'อนุมัติผลตรวจนับและปรับสต๊อกเรียบร้อย');
    }
}
