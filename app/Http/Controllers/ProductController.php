<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = Product::with(['category', 'branch'])
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
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        // Filter by stock level
        if ($stockLevel = $request->input('stock_level')) {
            if ($stockLevel === 'low') {
                $query->whereHas('branchStocks', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->whereColumn('quantity', '<=', 'reorder_point');
                });
            } elseif ($stockLevel === 'out') {
                $query->whereHas('branchStocks', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->where('quantity', '<=', 0);
                });
            }
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = Category::where('type', 'product')->where('is_active', true)->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('type', 'product')->where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'branch_id' => 'nullable|exists:branches,id',
            'cost' => 'required|numeric|min:0',
            'price_retail' => 'required|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'price_technician' => 'nullable|numeric|min:0',
            'price_online' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'initial_stock' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        // Map UI price fields to model columns
        $payload = $validated;
        $payload['retail_price'] = $validated['price_retail'];
        $payload['wholesale_price'] = $validated['price_wholesale'] ?? 0;
        $payload['vip_price'] = $validated['price_technician'] ?? 0; // ราคาช่าง
        $payload['partner_price'] = $validated['price_online'] ?? 0; // ราคาออนไลน์/พาร์ทเนอร์

        // Remove UI-only keys to avoid confusion
        unset(
            $payload['price_retail'],
            $payload['price_wholesale'],
            $payload['price_technician'],
            $payload['price_online']
        );

        $product = Product::create($payload);

        // Create initial stock for current branch
        $user = $request->user();
        if (isset($validated['initial_stock']) && $validated['initial_stock'] > 0) {
            BranchStock::create([
                'branch_id' => $user->branch_id,
                'stockable_type' => Product::class,
                'stockable_id' => $product->id,
                'quantity' => $validated['initial_stock'],
                'reorder_point' => $validated['reorder_point'] ?? 5,
            ]);

            // Log stock movement
            StockMovement::create([
                'branch_id' => $user->branch_id,
                'movable_type' => Product::class,
                'movable_id' => $product->id,
                'type' => 'in',
                'quantity' => $validated['initial_stock'],
                'reference_type' => 'initial',
                'reference_id' => null,
                'notes' => 'สต๊อกเริ่มต้น',
                'created_by' => $user->id,
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'branch', 'branchStocks.branch']);

        // Get stock movements
        $movements = StockMovement::where('movable_type', Product::class)
            ->where('movable_id', $product->id)
            ->with('branch', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('type', 'product')->where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'branches'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'branch_id' => 'nullable|exists:branches,id',
            'cost' => 'required|numeric|min:0',
            'price_retail' => 'required|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'price_technician' => 'nullable|numeric|min:0',
            'price_online' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'reorder_point' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        // Map UI price fields to model columns
        $payload = $validated;
        $payload['retail_price'] = $validated['price_retail'];
        $payload['wholesale_price'] = $validated['price_wholesale'] ?? 0;
        $payload['vip_price'] = $validated['price_technician'] ?? 0; // ราคาช่าง
        $payload['partner_price'] = $validated['price_online'] ?? 0; // ราคาออนไลน์/พาร์ทเนอร์

        unset(
            $payload['price_retail'],
            $payload['price_wholesale'],
            $payload['price_technician'],
            $payload['price_online']
        );

        $product->update($payload);

        return redirect()->route('products.show', $product)
            ->with('success', 'บันทึกข้อมูลเรียบร้อย');
    }

    public function destroy(Product $product)
    {
        // Check if product has any stock movements or sales
        $hasHistory = StockMovement::where('movable_type', Product::class)
            ->where('movable_id', $product->id)
            ->exists();

        if ($hasHistory) {
            // Soft delete - just deactivate
            $product->update(['is_active' => false]);
            return redirect()->route('products.index')
                ->with('success', 'ปิดใช้งานสินค้าเรียบร้อย');
        }

        // Hard delete - delete image too
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'ลบสินค้าเรียบร้อย');
    }

    // API for barcode scan
    public function findByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->with('category')
            ->first();

        if (!$product) {
            return response()->json(['error' => 'ไม่พบสินค้า'], 404);
        }

        $user = $request->user();
        $stock = BranchStock::where('branch_id', $user->branch_id)
            ->where('stockable_type', Product::class)
            ->where('stockable_id', $product->id)
            ->first();

        return response()->json([
            'product' => $product,
            'stock' => $stock ? $stock->quantity : 0,
        ]);
    }

    // Stock adjustment
    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $user = $request->user();

        $stock = BranchStock::firstOrCreate(
            [
                'branch_id' => $user->branch_id,
                'stockable_type' => Product::class,
                'stockable_id' => $product->id,
            ],
            ['quantity' => 0, 'reorder_point' => 5]
        );

        $oldQty = $stock->quantity;
        $newQty = $oldQty + $validated['adjustment'];

        if ($newQty < 0) {
            return redirect()->back()->withErrors(['adjustment' => 'สต๊อกไม่เพียงพอ']);
        }

        $stock->update(['quantity' => $newQty]);

        // Log movement
        StockMovement::create([
            'branch_id' => $user->branch_id,
            'movable_type' => Product::class,
            'movable_id' => $product->id,
            'type' => $validated['adjustment'] > 0 ? 'adjustment_in' : 'adjustment_out',
            'quantity' => abs($validated['adjustment']),
            'reference_type' => 'adjustment',
            'notes' => $validated['reason'],
            'created_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'ปรับปรุงสต๊อกเรียบร้อย');
    }
}
