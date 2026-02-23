<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Repair;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = Customer::withCount(['repairs', 'sales'])
            ->where('branch_id', $branchId);

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
        $payload['branch_id'] = $request->user()->branch_id;

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

        $branchId = $request->user()->branch_id;

        $customers = Customer::where('is_active', true)
            ->where('branch_id', $branchId)
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

    /**
     * Export customers to CSV
     */
    public function exportCsv(Request $request)
    {
        $customers = Customer::orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customers_export_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'code',
                'name',
                'phone',
                'email',
                'line_id',
                'facebook_id',
                'customer_type',
                'address',
                'tax_id',
                'company_name',
                'notes',
                'is_active'
            ]);

            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->code,
                    $customer->name,
                    $customer->phone,
                    $customer->email,
                    $customer->line_id,
                    $customer->facebook_id,
                    $customer->customer_type,
                    $customer->address,
                    $customer->tax_id,
                    $customer->company_name,
                    $customer->notes,
                    $customer->is_active ? 'Y' : 'N',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download customer import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customer_import_template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'name',
                'phone',
                'email',
                'line_id',
                'facebook_id',
                'customer_type',
                'address',
                'tax_id',
                'company_name',
                'notes'
            ]);

            fputcsv($handle, [
                'สมชาย ใจดี',
                '0891234567',
                'somchai@email.com',
                '@somchai',
                'fb.com/somchai',
                'retail',
                '123 ถ.สุขุมวิท กรุงเทพฯ',
                '1234567890123',
                'บริษัท เอบีซี จำกัด',
                'ลูกค้าประจำ'
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import customers from CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $user = $request->user();
        $branchId = $user->branch_id;
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'ไฟล์ CSV ไม่ถูกต้อง');
        }

        $header[0] = preg_replace('/\x{FEFF}/u', '', $header[0]);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $required = ['name', 'phone'];
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            fclose($handle);
            return redirect()->back()->with('error', 'คอลัมน์จำเป็นขาดหาย: ' . implode(', ', $missing));
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $data = array_combine($header, $row);
                $data = array_map('trim', $data);

                if (empty($data['name']) || empty($data['phone'])) {
                    $skipped++;
                    continue;
                }

                $validTypes = ['retail', 'wholesale', 'technician', 'vip'];
                $type = in_array($data['customer_type'] ?? '', $validTypes) ? $data['customer_type'] : 'retail';

                $customerData = [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? null,
                    'line_id' => $data['line_id'] ?? null,
                    'facebook_id' => $data['facebook_id'] ?? null,
                    'customer_type' => $type,
                    'address' => $data['address'] ?? null,
                    'tax_id' => $data['tax_id'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'is_active' => true,
                ];

                $existing = Customer::where('phone', $data['phone'])->first();
                if ($existing) {
                    $existing->update($customerData);
                    $updated++;
                } else {
                    $customerData['code'] = 'CUS-' . Str::random(6);
                    $customerData['branch_id'] = $branchId;
                    Customer::create($customerData);
                    $imported++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        fclose($handle);

        $msg = "Import เสร็จ! เพิ่มใหม่ {$imported} | อัปเดท {$updated} | ข้าม {$skipped} ราย";
        return redirect()->route('customers.index')->with('success', $msg);
    }
}
