<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = Part::with(['category', 'supplier'])
            ->withCount(['branchStocks as stock_quantity' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }]);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by stock level
        if ($stockLevel = $request->input('stock_level')) {
            switch ($stockLevel) {
                case 'low':
                    $query->whereColumn('quantity', '<=', 'min_stock');
                    break;
                case 'out':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
            }
        }

        $parts = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('type', 'part')->orderBy('name')->get();

        return view('parts.index', compact('parts', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('type', 'part')->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('parts.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:parts,sku',
            'barcode' => 'nullable|string|max:100|unique:parts,barcode',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'compatible_models' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('parts', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['quantity'] = $validated['initial_stock'] ?? 0;
        unset($validated['initial_stock']);

        $part = Part::create($validated);

        // Create initial stock for user's branch
        if (isset($request->initial_stock) && $request->initial_stock > 0) {
            BranchStock::create([
                'branch_id' => $request->user()->branch_id,
                'stockable_type' => Part::class,
                'stockable_id' => $part->id,
                'quantity' => $request->initial_stock,
                'min_stock' => $validated['min_stock'],
            ]);

            StockMovement::create([
                'branch_id' => $request->user()->branch_id,
                'movable_type' => Part::class,
                'movable_id' => $part->id,
                'type' => 'in',
                'quantity' => $request->initial_stock,
                'reference_type' => 'initial',
                'reference_id' => null,
                'notes' => 'สต๊อกเริ่มต้น',
                'created_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('parts.index')
            ->with('success', 'เพิ่มอะไหล่เรียบร้อยแล้ว');
    }

    public function show(Part $part)
    {
        $part->load(['category', 'supplier']);

        $branchStocks = BranchStock::where('stockable_type', Part::class)
            ->where('stockable_id', $part->id)
            ->with('branch')
            ->get();

        $movements = StockMovement::where('movable_type', Part::class)
            ->where('movable_id', $part->id)
            ->with(['branch', 'createdBy'])
            ->latest()
            ->take(20)
            ->get();

        return view('parts.show', compact('part', 'branchStocks', 'movements'));
    }

    public function edit(Part $part)
    {
        $categories = Category::where('type', 'part')->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('parts.edit', compact('part', 'categories', 'suppliers'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:parts,sku,' . $part->id,
            'barcode' => 'nullable|string|max:100|unique:parts,barcode,' . $part->id,
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'compatible_models' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($part->image) {
                Storage::disk('public')->delete($part->image);
            }
            $validated['image'] = $request->file('image')->store('parts', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $part->update($validated);

        return redirect()->route('parts.show', $part)
            ->with('success', 'อัปเดตอะไหล่เรียบร้อยแล้ว');
    }

    public function destroy(Part $part)
    {
        // Check if part is used in repairs
        if ($part->repairParts()->exists()) {
            return back()->with('error', 'ไม่สามารถลบอะไหล่นี้ได้ เนื่องจากมีการใช้งานในงานซ่อม');
        }

        // Delete image
        if ($part->image) {
            Storage::disk('public')->delete($part->image);
        }

        $part->delete();

        return redirect()->route('parts.index')
            ->with('success', 'ลบอะไหล่เรียบร้อยแล้ว');
    }

    public function adjustStock(Request $request, Part $part)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjust',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $branchId = $user->branch_id;

        // Get or create branch stock
        $branchStock = BranchStock::firstOrCreate(
            [
                'branch_id' => $branchId,
                'stockable_type' => Part::class,
                'stockable_id' => $part->id,
            ],
            [
                'quantity' => 0,
                'min_stock' => $part->min_stock,
            ]
        );

        $oldQuantity = $branchStock->quantity;

        switch ($validated['type']) {
            case 'in':
                $branchStock->increment('quantity', $validated['quantity']);
                $part->increment('quantity', $validated['quantity']);
                break;
            case 'out':
                if ($branchStock->quantity < $validated['quantity']) {
                    return back()->with('error', 'สต๊อกไม่เพียงพอ');
                }
                $branchStock->decrement('quantity', $validated['quantity']);
                $part->decrement('quantity', $validated['quantity']);
                break;
            case 'adjust':
                $diff = $validated['quantity'] - $branchStock->quantity;
                $branchStock->update(['quantity' => $validated['quantity']]);
                $part->increment('quantity', $diff);
                break;
        }

        // Record movement
        StockMovement::create([
            'branch_id' => $branchId,
            'movable_type' => Part::class,
            'movable_id' => $part->id,
            'type' => $validated['type'],
            'quantity' => $validated['type'] === 'adjust'
                ? abs($validated['quantity'] - $oldQuantity)
                : $validated['quantity'],
            'before_quantity' => $oldQuantity,
            'after_quantity' => $branchStock->fresh()->quantity,
            'notes' => $validated['reason'],
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'ปรับสต๊อกเรียบร้อยแล้ว');
    }

    public function findByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        $part = Part::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (!$part) {
            return response()->json(['error' => 'ไม่พบอะไหล่'], 404);
        }

        return response()->json([
            'id' => $part->id,
            'name' => $part->name,
            'sku' => $part->sku,
            'barcode' => $part->barcode,
            'price' => $part->price,
            'cost' => $part->cost,
            'quantity' => $part->quantity,
            'unit' => $part->unit,
        ]);
    }
}
