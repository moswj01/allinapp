<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $lastSupplier = Supplier::orderBy('id', 'desc')->first();
            $nextId = $lastSupplier ? $lastSupplier->id + 1 : 1;
            $validated['code'] = 'SUP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'เพิ่มซัพพลายเออร์เรียบร้อย');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:suppliers,code,' . $supplier->id,
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'บันทึกข้อมูลเรียบร้อย');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {
            $supplier->update(['is_active' => false]);
            return redirect()->route('suppliers.index')
                ->with('success', 'ปิดใช้งานซัพพลายเออร์เรียบร้อย (มีสินค้าผูกอยู่)');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'ลบซัพพลายเออร์เรียบร้อย');
    }
}
