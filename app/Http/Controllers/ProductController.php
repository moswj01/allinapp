<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Supplier;
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
        $isAdmin = $user->isOwner() || $user->isAdmin();

        $query = Product::with(['category', 'branch', 'supplier']);

        // Non-admin users only see their branch products
        if (!$isAdmin) {
            $query->where('branch_id', $branchId);
        }

        $query->withCount(['branchStocks as stock_quantity' => function ($q) use ($branchId) {
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

        // Filter by supplier
        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = Category::where('type', 'product')->where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = Category::where('type', 'product')->where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'branches', 'suppliers'));
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
            'supplier_id' => 'nullable|exists:suppliers,id',
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

        // Auto-assign branch from current user if not specified
        if (empty($payload['branch_id'])) {
            $payload['branch_id'] = $request->user()->branch_id;
        }

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
        $product->load(['category', 'branch', 'supplier', 'branchStocks.branch']);

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
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'branches', 'suppliers'));
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
            'supplier_id' => 'nullable|exists:suppliers,id',
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

    /**
     * Download CSV template for product import
     */
    public function downloadTemplate()
    {
        $headers = ['sku', 'barcode', 'name', 'category', 'supplier', 'description', 'unit', 'cost', 'retail_price', 'wholesale_price', 'vip_price', 'partner_price', 'initial_stock', 'reorder_point'];
        $example = ['SKU-001', '8850001234567', 'หน้าจอ iPhone 15', 'อะไหล่', 'ซัพพลายเออร์ A', 'หน้าจอกระจกเทมป์', 'ชิ้น', '150', '350', '300', '280', '250', '10', '5'];

        $callback = function () use ($headers, $example) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ]);
    }

    /**
     * Import products from CSV file
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $user = $request->user();
        $branchId = $user->branch_id;

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'ไม่สามารถเปิดไฟล์ CSV');
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'ไฟล์ CSV ว่างเปล่า');
        }

        // Clean BOM from first column
        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        $header = array_map('trim', array_map('strtolower', $header));

        $requiredCols = ['sku', 'name', 'retail_price'];
        foreach ($requiredCols as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return redirect()->back()->with('error', "ไม่พบคอลัมน์ '{$col}' ในไฟล์ CSV (ต้องมี: sku, name, retail_price)");
            }
        }

        // Cache categories & suppliers by name for lookup
        $categories = Category::where('type', 'product')->pluck('id', 'name')->toArray();
        $suppliers = Supplier::pluck('id', 'name')->toArray();

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;

                // Skip empty rows
                if (empty(array_filter($row))) continue;

                // Map header to values
                $data = [];
                foreach ($header as $i => $col) {
                    $data[$col] = isset($row[$i]) ? trim($row[$i]) : '';
                }

                // Validate required
                if (empty($data['sku']) || empty($data['name'])) {
                    $errors[] = "แถว {$rowNum}: SKU หรือชื่อสินค้าว่าง";
                    $skipped++;
                    continue;
                }

                // Resolve category
                $categoryId = null;
                if (!empty($data['category'])) {
                    if (isset($categories[$data['category']])) {
                        $categoryId = $categories[$data['category']];
                    } else {
                        // Auto-create category
                        $cat = Category::create([
                            'name' => $data['category'],
                            'type' => 'product',
                            'is_active' => true,
                        ]);
                        $categories[$data['category']] = $cat->id;
                        $categoryId = $cat->id;
                    }
                }

                // Resolve supplier
                $supplierId = null;
                if (!empty($data['supplier'])) {
                    if (isset($suppliers[$data['supplier']])) {
                        $supplierId = $suppliers[$data['supplier']];
                    }
                }

                // Build product data
                $productData = [
                    'sku' => $data['sku'],
                    'barcode' => $data['barcode'] ?? null,
                    'name' => $data['name'],
                    'category_id' => $categoryId,
                    'supplier_id' => $supplierId,
                    'branch_id' => $branchId,
                    'description' => $data['description'] ?? null,
                    'unit' => $data['unit'] ?? 'ชิ้น',
                    'cost' => floatval($data['cost'] ?? 0),
                    'retail_price' => floatval($data['retail_price'] ?? 0),
                    'wholesale_price' => floatval($data['wholesale_price'] ?? 0),
                    'vip_price' => floatval($data['vip_price'] ?? 0),
                    'partner_price' => floatval($data['partner_price'] ?? 0),
                    'reorder_point' => intval($data['reorder_point'] ?? 5),
                    'is_active' => true,
                ];

                // Check existing by SKU
                $existing = Product::where('sku', $data['sku'])->first();

                if ($existing) {
                    // Update existing product (except SKU)
                    unset($productData['sku']);
                    $existing->update($productData);
                    $updated++;
                } else {
                    // Create new product
                    $product = Product::create($productData);
                    $imported++;

                    // Create initial stock if specified
                    $initialStock = intval($data['initial_stock'] ?? 0);
                    if ($initialStock > 0) {
                        BranchStock::create([
                            'branch_id' => $branchId,
                            'stockable_type' => Product::class,
                            'stockable_id' => $product->id,
                            'quantity' => $initialStock,
                            'reorder_point' => intval($data['reorder_point'] ?? 5),
                        ]);

                        StockMovement::create([
                            'branch_id' => $branchId,
                            'movable_type' => Product::class,
                            'movable_id' => $product->id,
                            'type' => 'in',
                            'quantity' => $initialStock,
                            'reference_type' => 'csv_import',
                            'notes' => 'Import จาก CSV',
                            'created_by' => $user->id,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        fclose($handle);

        $msg = "Import เสร็จ! เพิ่มใหม่ {$imported} | อัปเดท {$updated} | ข้าม {$skipped} รายการ";
        if (!empty($errors)) {
            $msg .= ' | ปัญหา: ' . implode(', ', array_slice($errors, 0, 5));
        }

        return redirect()->route('products.index')->with('success', $msg);
    }

    /**
     * Export products to CSV
     */
    public function exportCsv(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = Product::with(['category', 'supplier'])
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        $products = $query->orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_export_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($products, $branchId) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'sku', 'barcode', 'name', 'category', 'supplier', 'description', 'unit',
                'cost', 'retail_price', 'wholesale_price', 'vip_price', 'partner_price',
                'stock', 'reorder_point', 'is_active'
            ]);

            foreach ($products as $product) {
                $stock = $product->branchStocks()->where('branch_id', $branchId)->sum('quantity');
                fputcsv($handle, [
                    $product->sku,
                    $product->barcode,
                    $product->name,
                    $product->category?->name,
                    $product->supplier?->name,
                    $product->description,
                    $product->unit,
                    $product->cost,
                    $product->retail_price,
                    $product->wholesale_price,
                    $product->vip_price,
                    $product->partner_price,
                    $stock,
                    $product->reorder_point,
                    $product->is_active ? 'Y' : 'N',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
