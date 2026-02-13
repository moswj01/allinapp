<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\RepairLog;
use App\Models\RepairPart;
use App\Models\Customer;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        // Get repairs grouped by status for Kanban
        $statuses = Repair::getStatuses();

        $repairs = Repair::where('branch_id', $branchId)
            ->whereNotIn('status', [Repair::STATUS_DELIVERED, Repair::STATUS_CANCELLED])
            ->with(['customer', 'technician', 'receivedBy', 'parts'])
            ->orderBy('priority', 'desc')
            ->orderBy('received_at', 'asc')
            ->get()
            ->groupBy('status');

        return view('repairs.index', compact('repairs', 'statuses'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        return view('repairs.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_line_id' => 'nullable|string|max:100',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'device_type' => 'required|string|max:100',
            'device_brand' => 'required|string|max:100',
            'device_model' => 'required|string|max:100',
            'device_color' => 'nullable|string|max:50',
            'device_serial' => 'nullable|string|max:100',
            'device_imei' => 'nullable|string|max:50',
            'device_password' => 'nullable|string|max:100',
            'device_condition' => 'nullable|string',
            'device_accessories' => 'nullable|array',
            'problem_description' => 'required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_completion' => 'nullable|date',
            'deposit' => 'nullable|numeric|min:0',
            'priority' => 'nullable|in:normal,urgent,vip',
            'internal_notes' => 'nullable|string',
        ]);

        $user = $request->user();

        // Generate repair number: REP-YYMMDD-XXXX
        $today = Carbon::today();
        $count = Repair::whereDate('created_at', $today)->count() + 1;
        $repairNumber = 'REP-' . $today->format('ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $repair = Repair::create([
            ...$validated,
            'repair_number' => $repairNumber,
            'branch_id' => $user->branch_id,
            'received_by' => $user->id,
            'status' => Repair::STATUS_PENDING,
            'payment_status' => Repair::PAYMENT_UNPAID,
            'received_at' => now(),
            'priority' => $validated['priority'] ?? 'normal',
            'paid_amount' => $validated['deposit'] ?? 0,
        ]);

        // Create or link customer if not provided
        $customerId = $validated['customer_id'] ?? null;
        if (!$customerId && !empty($validated['customer_phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'code' => 'CUS-' . strtoupper(Str::random(6)),
                    'name' => $validated['customer_name'],
                    'line_id' => $validated['customer_line_id'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['customer_address'] ?? null,
                    'is_active' => true,
                ]
            );
            $repair->update(['customer_id' => $customer->id]);
        }

        // Log the action
        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $user->id,
            'action' => RepairLog::ACTION_CREATED,
            'new_value' => Repair::STATUS_PENDING,
            'description' => 'รับงานซ่อมใหม่',
        ]);

        // Redirect to printable receipt with auto print query
        return redirect()->route('repairs.receipt', ['repair' => $repair, 'auto_print' => 1])
            ->with('success', 'บันทึกงานซ่อมเรียบร้อย เลขที่: ' . $repairNumber);
    }

    public function show(Repair $repair)
    {
        $repair->load(['customer', 'technician', 'receivedBy', 'parts.part', 'logs.user', 'communications']);
        $parts = Part::where('is_active', true)->orderBy('name')->get();
        $technicians = \App\Models\User::technicians()->where('branch_id', $repair->branch_id)->get();

        return view('repairs.show', compact('repair', 'parts', 'technicians'));
    }

    public function receipt(Repair $repair)
    {
        $repair->load(['customer', 'branch']);
        return view('repairs.receipt', compact('repair'));
    }

    // Public tracking page by repair number
    public function track(string $repairNumber)
    {
        $repair = Repair::where('repair_number', $repairNumber)
            ->with(['branch'])
            ->firstOrFail();

        // Map status code to name (limited info for public)
        $statusNames = Repair::getStatuses();
        $statusLabel = $statusNames[$repair->status] ?? $repair->status;

        return view('repairs.track', [
            'repair' => $repair,
            'statusLabel' => $statusLabel,
        ]);
    }

    public function edit(Repair $repair)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $technicians = \App\Models\User::technicians()->where('branch_id', $repair->branch_id)->get();
        return view('repairs.edit', compact('repair', 'customers', 'technicians'));
    }

    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_line_id' => 'nullable|string|max:100',
            'device_type' => 'required|string|max:100',
            'device_brand' => 'required|string|max:100',
            'device_model' => 'required|string|max:100',
            'device_color' => 'nullable|string|max:50',
            'device_serial' => 'nullable|string|max:100',
            'device_imei' => 'nullable|string|max:50',
            'device_password' => 'nullable|string|max:100',
            'device_condition' => 'nullable|string',
            'device_accessories' => 'nullable|array',
            'problem_description' => 'required|string',
            'diagnosis' => 'nullable|string',
            'solution' => 'nullable|string',
            'service_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'warranty_conditions' => 'nullable|string',
            'estimated_completion' => 'nullable|date',
            'internal_notes' => 'nullable|string',
            'customer_notes' => 'nullable|string',
        ]);

        $oldValues = $repair->toArray();
        $repair->update($validated);

        // Recalculate total
        $repair->calculateTotal();
        $repair->save();

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_UPDATED,
            'description' => 'อัปเดตข้อมูลงานซ่อม',
            'metadata' => [
                'old' => $oldValues,
                'new' => $repair->toArray(),
            ],
        ]);

        return redirect()->route('repairs.show', $repair)
            ->with('success', 'บันทึกข้อมูลเรียบร้อย');
    }

    public function updateStatus(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Repair::getStatuses())),
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $repair->status;
        $newStatus = $validated['status'];

        $repair->status = $newStatus;

        // Auto-update timestamps based on status
        if ($newStatus === Repair::STATUS_COMPLETED && !$repair->completed_at) {
            $repair->completed_at = now();

            // Set warranty expiration
            if ($repair->warranty_days > 0) {
                $repair->warranty_expires_at = now()->addDays($repair->warranty_days);
            }
        }

        if ($newStatus === Repair::STATUS_DELIVERED && !$repair->delivered_at) {
            $repair->delivered_at = now();
        }

        $repair->save();

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_STATUS_CHANGED,
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'description' => $validated['notes'] ?? 'เปลี่ยนสถานะงานซ่อม',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'repair' => $repair]);
        }

        return redirect()->back()->with('success', 'อัปเดตสถานะเรียบร้อย');
    }

    public function assignTechnician(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $repair->update(['technician_id' => $validated['technician_id']]);

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_ASSIGNED,
            'description' => 'มอบหมายช่างซ่อม',
        ]);

        // TODO: Send notification to technician

        return redirect()->back()->with('success', 'มอบหมายช่างเรียบร้อย');
    }

    public function addPart(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $part = Part::findOrFail($validated['part_id']);

        $repairPart = RepairPart::create([
            'repair_id' => $repair->id,
            'part_id' => $part->id,
            'part_name' => $part->name,
            'quantity' => $validated['quantity'],
            'unit_price' => $part->price,
            'total_price' => $part->price * $validated['quantity'],
            'status' => RepairPart::STATUS_PENDING,
            'requested_by' => $request->user()->id,
            'notes' => $validated['notes'],
        ]);

        // Update repair status if needed
        if ($repair->status === Repair::STATUS_PENDING) {
            $repair->update(['status' => Repair::STATUS_WAITING_PARTS]);
        }

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_PART_REQUESTED,
            'description' => "เบิกอะไหล่: {$part->name} x {$validated['quantity']}",
        ]);

        return redirect()->back()->with('success', 'เพิ่มรายการอะไหล่เรียบร้อย');
    }

    public function payment(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,qr,card',
            'payment_ref' => 'nullable|string|max:100',
        ]);

        $repair->paid_amount += $validated['amount'];

        if ($repair->paid_amount >= $repair->total_cost) {
            $repair->payment_status = Repair::PAYMENT_PAID;
        } else {
            $repair->payment_status = Repair::PAYMENT_PARTIAL;
        }

        $repair->payment_method = $validated['payment_method'];
        $repair->save();

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_PAYMENT,
            'description' => "รับชำระเงิน: ฿" . number_format($validated['amount'], 2),
        ]);

        return redirect()->back()->with('success', 'บันทึกการชำระเงินเรียบร้อย');
    }

    // API for Kanban drag & drop
    public function kanbanUpdate(Request $request)
    {
        $validated = $request->validate([
            'repair_id' => 'required|exists:repairs,id',
            'status' => 'required|in:' . implode(',', array_keys(Repair::getStatuses())),
        ]);

        $repair = Repair::findOrFail($validated['repair_id']);
        $oldStatus = $repair->status;

        $repair->update(['status' => $validated['status']]);

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_STATUS_CHANGED,
            'old_value' => $oldStatus,
            'new_value' => $validated['status'],
            'description' => 'อัปเดตสถานะผ่าน Kanban',
        ]);

        return response()->json(['success' => true]);
    }
}
