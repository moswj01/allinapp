<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Quotation::with(['customer', 'createdBy', 'branch'])->withCount('items');

        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $statuses = Quotation::getStatuses();

        return view('quotations.index', compact('quotations', 'statuses'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('quotations.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'valid_until' => 'nullable|date|after:today',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Generate quotation number
        $today = Carbon::today();
        $count = Quotation::whereDate('created_at', $today)->count() + 1;
        $quotationNumber = 'QT-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $quotation = Quotation::create([
                'quotation_number' => $quotationNumber,
                'branch_id' => $user->branch_id,
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
                'discount_type' => $validated['discount_type'] ?? null,
                'discount_value' => $validated['discount_value'] ?? 0,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => Quotation::STATUS_DRAFT,
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'created_by' => $user->id,
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'itemable_type' => isset($item['product_id']) ? Product::class : null,
                    'itemable_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => 'ชิ้น',
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Calculate totals
            $discountAmount = 0;
            if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 0) {
                $discountAmount = $subtotal * ($validated['discount_value'] / 100);
            } elseif ($validated['discount_type'] === 'fixed') {
                $discountAmount = $validated['discount_value'] ?? 0;
            }

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * (($validated['tax_rate'] ?? 0) / 100);
            $total = $afterDiscount + $taxAmount;

            $quotation->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'สร้างใบเสนอราคาเรียบร้อย เลขที่: ' . $quotationNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'createdBy', 'approvedBy', 'branch', 'items', 'convertedToSale']);

        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if (!$quotation->canBeEdited()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'ไม่สามารถแก้ไขใบเสนอราคาในสถานะนี้ได้');
        }

        $quotation->load('items');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('quotations.edit', compact('quotation', 'customers', 'products'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if (!$quotation->canBeEdited()) {
            return redirect()->route('quotations.show', $quotation)->with('error', 'ไม่สามารถแก้ไขได้');
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'valid_until' => 'nullable|date',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $quotation->items()->delete();

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'itemable_type' => isset($item['product_id']) ? Product::class : null,
                    'itemable_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => 'ชิ้น',
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }

            $discountAmount = 0;
            if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 0) {
                $discountAmount = $subtotal * ($validated['discount_value'] / 100);
            } elseif ($validated['discount_type'] === 'fixed') {
                $discountAmount = $validated['discount_value'] ?? 0;
            }

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * (($validated['tax_rate'] ?? 0) / 100);
            $total = $afterDiscount + $taxAmount;

            $quotation->update([
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'valid_until' => $validated['valid_until'],
                'discount_type' => $validated['discount_type'] ?? null,
                'discount_value' => $validated['discount_value'] ?? 0,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('quotations.show', $quotation)->with('success', 'บันทึกใบเสนอราคาเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Quotation $quotation)
    {
        if (!$quotation->canBeEdited()) {
            return redirect()->route('quotations.index')->with('error', 'ไม่สามารถลบใบเสนอราคาในสถานะนี้ได้');
        }

        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('quotations.index')->with('success', 'ลบใบเสนอราคาเรียบร้อย');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'status' => 'required|in:sent,approved,rejected,expired',
        ]);

        $user = Auth::user();
        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'approved') {
            $updateData['approved_by'] = $user->id;
            $updateData['approved_at'] = now();
        }

        $quotation->update($updateData);

        return redirect()->back()->with('success', 'อัปเดตสถานะเรียบร้อย');
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['customer', 'createdBy', 'branch', 'items']);

        return view('quotations.print', compact('quotation'));
    }
}