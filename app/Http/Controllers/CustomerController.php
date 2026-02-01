<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Repair;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount(['repairs', 'sales']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('line_id', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('customer_type', $type);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $customers = $query->orderBy('name')->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'line_id' => 'nullable|string|max:100',
            'facebook_id' => 'nullable|string|max:100',
            'type' => 'nullable|in:retail,wholesale,technician,vip',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payload = $validated;
        $payload['is_active'] = true;
        $payload['customer_type'] = $validated['type'] ?? 'retail';
        unset($payload['type']);

        // Auto-generate customer code if not provided
        $payload['code'] = 'CUS-' . strtoupper(Str::random(6));

        Customer::create($payload);

        return redirect()->route('customers.index')
            ->with('success', 'เพิ่มลูกค้าเรียบร้อยแล้ว');
    }

    public function show(Customer $customer)
    {
        $customer->load(['repairs' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }, 'sales' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }]);

        // Calculate stats
        $stats = [
            'total_repairs' => $customer->repairs()->count(),
            'total_sales' => $customer->sales()->count(),
            'total_spent' => $customer->sales()->sum('total'),
            'total_repairs_value' => $customer->repairs()->sum('total_cost'),
        ];

        return view('customers.show', compact('customer', 'stats'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255',
            'line_id' => 'nullable|string|max:100',
            'facebook_id' => 'nullable|string|max:100',
            'type' => 'nullable|in:retail,wholesale,technician,vip',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $payload = $validated;
        $payload['is_active'] = $request->has('is_active');
        if (array_key_exists('type', $payload)) {
            $payload['customer_type'] = $payload['type'] ?? $customer->customer_type;
            unset($payload['type']);
        }

        $customer->update($payload);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'บันทึกข้อมูลเรียบร้อย');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has repairs or sales
        if ($customer->repairs()->exists() || $customer->sales()->exists()) {
            $customer->update(['is_active' => false]);
            return redirect()->route('customers.index')
                ->with('success', 'ปิดใช้งานลูกค้าเรียบร้อย');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'ลบลูกค้าเรียบร้อย');
    }

    // API for search autocomplete
    public function search(Request $request)
    {
        $search = (string) $request->input('q', '');
        $digits = preg_replace('/\D+/', '', $search);

        $customers = Customer::where('is_active', true)
            ->where(function ($q) use ($search, $digits) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");

                // Normalize phone numbers for matching digits-only input (handles dashes/spaces)
                if ($digits && strlen($digits) >= 6) {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', ''), ')', ''), '+', '') LIKE ?",
                        ["%{$digits}%"]
                    );
                }
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'code', 'name', 'phone', 'email', 'line_id', 'address', 'company_name', 'customer_type']);

        return response()->json($customers);
    }
}
