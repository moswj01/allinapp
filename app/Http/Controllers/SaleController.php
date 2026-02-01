<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Part;
use App\Models\Customer;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = Sale::where('branch_id', $branchId)
            ->with(['customer', 'createdBy'])
            ->withCount('items');

        // Date filter
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);

        // Today's summary
        $todaySummary = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as count, SUM(total) as total')
            ->first();

        return view('sales.index', compact('sales', 'todaySummary'));
    }

    public function pos()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branchId = $user->branch_id;

        // Get products with stock
        $products = Product::where('is_active', true)
            ->with(['category', 'branchStocks' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }])
            ->orderBy('name')
            ->get();

        // Get customers
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:product,part',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,qr,card,credit',
            'received_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $branchId = $user->branch_id;

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discount = $validated['discount'] ?? 0;
        $vat = $validated['tax'] ?? 0; // map tax input to vat field
        $total = max(0, $subtotal - $discount + $vat);

        // Generate sale number
        $today = Carbon::today();
        $count = Sale::whereDate('created_at', $today)->count() + 1;
        $saleNumber = 'INV-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            // Create sale
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'branch_id' => $branchId,
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat' => $vat,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'credit' ? 'credit' : 'paid',
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'user_id' => $user->id,
                'cash_received' => $validated['payment_method'] === 'cash' ? ($validated['received_amount'] ?? 0) : 0,
                'change_amount' => $validated['payment_method'] === 'cash' ? (($validated['received_amount'] ?? 0) - $total) : 0,
            ]);

            // Create sale items and update stock
            foreach ($validated['items'] as $item) {
                $stockable = null;
                $stockableType = null;

                if ($item['type'] === 'product') {
                    $stockable = Product::findOrFail($item['id']);
                    $stockableType = Product::class;
                } else {
                    $stockable = Part::findOrFail($item['id']);
                    $stockableType = Part::class;
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'itemable_type' => $stockableType,
                    'itemable_id' => $stockable->id,
                    'item_name' => $stockable->name,
                    'item_barcode' => $stockable->sku ?? $stockable->part_number ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount' => 0,
                    'total' => $item['price'] * $item['quantity'],
                ]);

                // Update stock
                $branchStock = BranchStock::where('branch_id', $branchId)
                    ->where('stockable_type', $stockableType)
                    ->where('stockable_id', $stockable->id)
                    ->first();

                if ($branchStock) {
                    $branchStock->decrement('quantity', $item['quantity']);
                }

                // Log stock movement
                StockMovement::create([
                    'branch_id' => $branchId,
                    'movable_type' => $stockableType,
                    'movable_id' => $stockable->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'notes' => "ขายสินค้า #{$saleNumber}",
                    'created_by' => $user->id,
                ]);
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'sale' => $sale->load('items'),
                    'redirect' => route('sales.show', $sale),
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', 'บันทึกการขายเรียบร้อย เลขที่: ' . $saleNumber);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.itemable', 'createdBy', 'branch']);

        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        if ($sale->status === 'completed') {
            // Restore stock for cancelled sales
            DB::transaction(function () use ($sale) {
                foreach ($sale->items as $item) {
                    $branchStock = BranchStock::where('branch_id', $sale->branch_id)
                        ->where('stockable_type', $item->itemable_type)
                        ->where('stockable_id', $item->itemable_id)
                        ->first();

                    if ($branchStock) {
                        $branchStock->increment('quantity', $item->quantity);
                    }

                    // Log stock movement
                    StockMovement::create([
                        'branch_id' => $sale->branch_id,
                        'movable_type' => $item->itemable_type,
                        'movable_id' => $item->itemable_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => "คืนสต๊อกจากยกเลิกบิล #{$sale->sale_number}",
                        'created_by' => Auth::id(),
                    ]);
                }

                $sale->update(['status' => 'cancelled']);
            });
        }

        return redirect()->route('sales.index')
            ->with('success', 'ยกเลิกบิลขายเรียบร้อย');
    }

    // Print receipt
    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'items.itemable', 'branch']);

        return view('sales.receipt', compact('sale'));
    }
}