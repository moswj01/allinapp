<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BranchStock::with(['branch', 'stockable']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Support legacy filters: product_id/part_id
        if ($request->filled('product_id')) {
            $query->where('stockable_type', Product::class)
                ->where('stockable_id', $request->product_id);
        }

        if ($request->filled('part_id')) {
            $query->where('stockable_type', Part::class)
                ->where('stockable_id', $request->part_id);
        }

        // Optional: filter by type ('product'|'part')
        if ($request->filled('type')) {
            $type = strtolower($request->type);
            if (in_array($type, ['product', 'part'], true)) {
                $query->where('stockable_type', $type === 'product' ? Product::class : Part::class);
            }
        }

        // Optional search by name or sku/barcode across stockable
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where(function ($productSub) use ($q) {
                    $productSub->where('stockable_type', Product::class)
                        ->whereHas('stockable', function ($p) use ($q) {
                            $p->where('name', 'like', "%$q%");
                        });
                })->orWhere(function ($partSub) use ($q) {
                    $partSub->where('stockable_type', Part::class)
                        ->whereHas('stockable', function ($p) use ($q) {
                            $p->where('name', 'like', "%$q%");
                        });
                });
            });
        }

        $stocks = $query->orderByDesc('id')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $stocks,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'nullable|exists:products,id',
            'part_id' => 'nullable|exists:parts,id',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'reserved_quantity' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['product_id']) && empty($validated['part_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'ต้องระบุสินค้า (product_id) หรืออะไหล่ (part_id) อย่างน้อยหนึ่งรายการ',
            ], 422);
        }

        $stock = new BranchStock();
        $stock->branch_id = $validated['branch_id'];
        $stock->quantity = $validated['quantity'];
        $stock->min_quantity = $validated['min_quantity'] ?? 0;
        $stock->reserved_quantity = $validated['reserved_quantity'] ?? 0;

        if (!empty($validated['product_id'])) {
            $stock->stockable_type = Product::class;
            $stock->stockable_id = $validated['product_id'];
        } else {
            $stock->stockable_type = Part::class;
            $stock->stockable_id = $validated['part_id'];
        }

        $stock->save();
        $stock->load(['branch', 'stockable']);

        return response()->json([
            'success' => true,
            'message' => 'สร้างสต็อกสาขาสำเร็จ',
            'data' => $stock,
        ], 201);
    }

    public function show(BranchStock $stock): JsonResponse
    {
        $stock->load(['branch', 'stockable']);

        // Load movements related to this stockable in this branch
        $movements = StockMovement::with(['branch', 'movable'])
            ->where('branch_id', $stock->branch_id)
            ->where('movable_type', $stock->stockable_type)
            ->where('movable_id', $stock->stockable_id)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stock' => $stock,
                'movements' => $movements,
            ],
        ]);
    }

    public function update(Request $request, BranchStock $stock): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'product_id' => 'nullable|exists:products,id',
            'part_id' => 'nullable|exists:parts,id',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'reserved_quantity' => 'nullable|integer|min:0',
        ]);

        if (!empty($validated['branch_id'])) {
            $stock->branch_id = $validated['branch_id'];
        }

        if (!empty($validated['product_id'])) {
            $stock->stockable_type = Product::class;
            $stock->stockable_id = $validated['product_id'];
        } elseif (!empty($validated['part_id'])) {
            $stock->stockable_type = Part::class;
            $stock->stockable_id = $validated['part_id'];
        }

        $stock->quantity = $validated['quantity'];
        $stock->min_quantity = $validated['min_quantity'] ?? $stock->min_quantity;
        $stock->reserved_quantity = $validated['reserved_quantity'] ?? $stock->reserved_quantity;

        $stock->save();
        $stock->load(['branch', 'stockable']);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทสต็อกสาขาสำเร็จ',
            'data' => $stock,
        ]);
    }

    public function destroy(BranchStock $stock): JsonResponse
    {
        $stock->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบสต็อกสาขาสำเร็จ',
        ]);
    }

    public function movements(Request $request): JsonResponse
    {
        $query = StockMovement::with(['branch', 'movable']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('movable_type', Product::class)
                ->where('movable_id', $request->product_id);
        }

        if ($request->filled('part_id')) {
            $query->where('movable_type', Part::class)
                ->where('movable_id', $request->part_id);
        }

        $movements = $query->latest()->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }

    public function stockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_stock_id' => 'nullable|exists:branch_stocks,id',
            'branch_id' => 'nullable|exists:branches,id',
            'product_id' => 'nullable|exists:products,id',
            'part_id' => 'nullable|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Resolve stock record
            if (!empty($validated['branch_stock_id'])) {
                $stock = BranchStock::findOrFail($validated['branch_stock_id']);
            } else {
                if (empty($validated['branch_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ต้องระบุ branch_id เมื่อไม่ได้ส่ง branch_stock_id',
                    ], 422);
                }

                if (!empty($validated['product_id'])) {
                    $stock = BranchStock::firstOrCreate([
                        'branch_id' => $validated['branch_id'],
                        'stockable_type' => Product::class,
                        'stockable_id' => $validated['product_id'],
                    ], [
                        'quantity' => 0,
                        'min_quantity' => 0,
                        'reserved_quantity' => 0,
                    ]);
                } elseif (!empty($validated['part_id'])) {
                    $stock = BranchStock::firstOrCreate([
                        'branch_id' => $validated['branch_id'],
                        'stockable_type' => Part::class,
                        'stockable_id' => $validated['part_id'],
                    ], [
                        'quantity' => 0,
                        'min_quantity' => 0,
                        'reserved_quantity' => 0,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'ต้องระบุสินค้า (product_id) หรืออะไหล่ (part_id)',
                    ], 422);
                }
            }

            $before = $stock->quantity;
            $stock->increment('quantity', $validated['quantity']);
            $after = $stock->quantity;

            $movement = StockMovement::create([
                'branch_id' => $stock->branch_id,
                'movable_type' => $stock->stockable_type,
                'movable_id' => $stock->stockable_id,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $validated['quantity'],
                'before_quantity' => $before,
                'after_quantity' => $after,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $movement->load(['branch', 'movable']);

            return response()->json([
                'success' => true,
                'message' => 'รับสินค้าเข้าสำเร็จ',
                'data' => $movement,
            ], 201);
        });
    }

    public function stockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_stock_id' => 'nullable|exists:branch_stocks,id',
            'branch_id' => 'nullable|exists:branches,id',
            'product_id' => 'nullable|exists:products,id',
            'part_id' => 'nullable|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Resolve stock record
            if (!empty($validated['branch_stock_id'])) {
                $stock = BranchStock::findOrFail($validated['branch_stock_id']);
            } else {
                if (empty($validated['branch_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ต้องระบุ branch_id เมื่อไม่ได้ส่ง branch_stock_id',
                    ], 422);
                }

                if (!empty($validated['product_id'])) {
                    $stock = BranchStock::where([
                        'branch_id' => $validated['branch_id'],
                        'stockable_type' => Product::class,
                        'stockable_id' => $validated['product_id'],
                    ])->first();
                } elseif (!empty($validated['part_id'])) {
                    $stock = BranchStock::where([
                        'branch_id' => $validated['branch_id'],
                        'stockable_type' => Part::class,
                        'stockable_id' => $validated['part_id'],
                    ])->first();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'ต้องระบุสินค้า (product_id) หรืออะไหล่ (part_id)',
                    ], 422);
                }
            }

            if (!$stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบสต็อกของรายการนี้ในสาขา',
                ], 404);
            }

            if ($stock->quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'สินค้าในคลังไม่เพียงพอ (คงเหลือ: ' . $stock->quantity . ')',
                ], 400);
            }

            $before = $stock->quantity;
            $stock->decrement('quantity', $validated['quantity']);
            $after = $stock->quantity;

            $movement = StockMovement::create([
                'branch_id' => $stock->branch_id,
                'movable_type' => $stock->stockable_type,
                'movable_id' => $stock->stockable_id,
                'type' => StockMovement::TYPE_OUT,
                'quantity' => $validated['quantity'],
                'before_quantity' => $before,
                'after_quantity' => $after,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $movement->load(['branch', 'movable']);

            return response()->json([
                'success' => true,
                'message' => 'จ่ายสินค้าออกสำเร็จ',
                'data' => $movement,
            ], 201);
        });
    }
}