<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\RepairLog;
use App\Models\RepairPart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchStock;
use App\Http\Controllers\ReceiptTemplateController;
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

        $query = Repair::where('branch_id', $branchId)
            ->with(['customer', 'technician', 'receivedBy']);

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: exclude delivered & cancelled
            $query->whereNotIn('status', [Repair::STATUS_DELIVERED, Repair::STATUS_CANCELLED]);
        }

        // Priority filter
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('repair_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('device_brand', 'like', "%{$search}%")
                    ->orWhere('device_model', 'like', "%{$search}%");
            });
        }

        $repairs = $query->orderBy('priority', 'desc')
            ->orderBy('received_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statuses = Repair::getStatuses();

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

        // Redirect to show page and auto-open receipt in new tab
        return redirect()->route('repairs.show', $repair)
            ->with('success', 'บันทึกงานซ่อมเรียบร้อย เลขที่: ' . $repairNumber)
            ->with('open_receipt', route('repairs.receipt', ['repair' => $repair, 'auto_print' => 1]));
    }

    public function show(Repair $repair)
    {
        $repair->load(['customer', 'technician', 'receivedBy', 'parts.product', 'logs.user', 'communications']);
        $parts = Product::where('is_active', true)->orderBy('name')->get();
        $technicians = \App\Models\User::technicians()->where('branch_id', $repair->branch_id)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('repairs.show', compact('repair', 'parts', 'technicians', 'customers'));
    }

    public function receipt(Repair $repair)
    {
        $repair->load(['customer', 'branch']);
        $template = ReceiptTemplateController::getRepairTemplate();
        return view('repairs.receipt', compact('repair', 'template'));
    }

    public function invoice(Repair $repair)
    {
        $repair->load(['customer', 'branch', 'parts.product', 'technician']);
        $type = request('type', 'receipt'); // receipt or tax_invoice
        return view('repairs.invoice', compact('repair', 'type'));
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
            'customer_id' => 'nullable|exists:customers,id',
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
            'problem_description' => 'required|string',
            'diagnosis' => 'nullable|string',
            'solution' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'service_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'warranty_conditions' => 'nullable|string',
            'technician_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'estimated_completion' => 'nullable|date',
            'internal_notes' => 'nullable|string',
            'customer_notes' => 'nullable|string',
        ]);

        $oldValues = $repair->toArray();

        // Filter to only fillable model fields
        $fillable = $repair->getFillable();
        $updateData = array_intersect_key($validated, array_flip($fillable));
        $updateData['updated_at'] = now();

        // Use DB query to bypass Eloquent dirty detection and guarantee write
        DB::table('repairs')->where('id', $repair->id)->update($updateData);

        // Refresh model from DB and recalculate total
        $repair->refresh();
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

        return redirect()->route('repairs.show', $repair)->with('success', 'อัปเดตสถานะเรียบร้อย');
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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // ถ้ามีอะไหล่ชิ้นเดียวกันที่ยังรออนุมัติ ให้เพิ่มจำนวนแทน
        $existingPart = RepairPart::where('repair_id', $repair->id)
            ->where('product_id', $product->id)
            ->where('status', RepairPart::STATUS_PENDING)
            ->first();

        if ($existingPart) {
            $existingPart->quantity += $validated['quantity'];
            $existingPart->total_price = $existingPart->unit_price * $existingPart->quantity;
            if ($validated['notes']) {
                $existingPart->notes = $existingPart->notes
                    ? $existingPart->notes . ', ' . $validated['notes']
                    : $validated['notes'];
            }
            $existingPart->save();
            $repairPart = $existingPart;
        } else {
            $repairPart = RepairPart::create([
                'repair_id' => $repair->id,
                'product_id' => $product->id,
                'part_name' => $product->name,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->retail_price,
                'total_price' => $product->retail_price * $validated['quantity'],
                'status' => RepairPart::STATUS_PENDING,
                'requested_by' => $request->user()->id,
                'notes' => $validated['notes'],
            ]);
        }

        // Update repair status if needed
        if ($repair->status === Repair::STATUS_PENDING) {
            $repair->update(['status' => Repair::STATUS_WAITING_PARTS]);
        }

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => RepairLog::ACTION_PART_REQUESTED,
            'description' => "เบิกอะไหล่: {$product->name} x {$validated['quantity']}",
        ]);

        return redirect()->back()->with('success', 'เพิ่มรายการอะไหล่เรียบร้อย');
    }

    public function cancelPart(Request $request, Repair $repair, RepairPart $part)
    {
        if ($part->repair_id !== $repair->id) {
            abort(404);
        }

        if ($part->status !== RepairPart::STATUS_PENDING) {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกรายการที่อนุมัติแล้ว');
        }

        $partName = $part->part_name;
        $partQty = $part->quantity;
        $part->delete();

        // Recalculate repair total
        $repair->calculateTotal();

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $request->user()->id,
            'action' => 'part_cancelled',
            'description' => "ยกเลิกเบิกอะไหล่: {$partName} x {$partQty}",
        ]);

        return redirect()->back()->with('success', 'ยกเลิกรายการอะไหล่เรียบร้อย');
    }

    public function destroy(Request $request, Repair $repair)
    {
        $repairNumber = $repair->repair_number;

        // ลบข้อมูลที่เกี่ยวข้อง
        $repair->parts()->delete();
        $repair->logs()->delete();
        $repair->delete();

        return redirect()->route('repairs.index')->with('success', "ลบงานซ่อม {$repairNumber} เรียบร้อย");
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
    // ============================================
    // Part Approval Workflow
    // ============================================

    /**
     * รายการเบิกอะไหล่ที่รออนุมัติ
     */
    public function partApprovals(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = RepairPart::with(['repair', 'product', 'requestedBy', 'approvedBy', 'rejectedBy'])
            ->whereHas('repair', function ($q) use ($user, $branchId) {
                if (!$user->isOwner() && !$user->isAdmin()) {
                    $q->where('branch_id', $branchId);
                }
            });

        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $parts = $query->orderByDesc('created_at')->paginate(20);

        return view('repairs.part-approvals', compact('parts', 'status'));
    }

    /**
     * อนุมัติเบิกอะไหล่ - ตัดสต๊อก
     */
    public function approvePart(Request $request, RepairPart $repairPart)
    {
        if ($repairPart->status !== RepairPart::STATUS_PENDING) {
            return redirect()->back()->with('error', 'รายการนี้ไม่อยู่ในสถานะรออนุมัติ');
        }

        $repair = $repairPart->repair;
        $product = $repairPart->product;

        // Check stock availability
        $branchStock = BranchStock::where('branch_id', $repair->branch_id)
            ->where('stockable_type', Product::class)
            ->where('stockable_id', $product->id)
            ->first();

        $availableQty = $branchStock ? $branchStock->available_quantity : 0;

        if ($availableQty < $repairPart->quantity) {
            return redirect()->back()->with('error', "สต๊อกไม่เพียงพอ ({$product->name}: มี {$availableQty} ต้องการ {$repairPart->quantity})");
        }

        DB::transaction(function () use ($repairPart, $repair, $branchStock, $request) {
            // Update part status
            $repairPart->update([
                'status' => RepairPart::STATUS_APPROVED,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            // Deduct stock
            if ($branchStock) {
                $branchStock->decrement('quantity', $repairPart->quantity);
            }

            // Update repair parts cost
            $repair->calculateTotal();
            $repair->save();

            // Log
            RepairLog::create([
                'repair_id' => $repair->id,
                'user_id' => $request->user()->id,
                'action' => 'part_approved',
                'description' => "อนุมัติเบิกอะไหล่: {$repairPart->part_name} x {$repairPart->quantity} (โดย {$request->user()->name})",
            ]);
        });

        return redirect()->back()->with('success', "อนุมัติเบิกอะไหล่ {$repairPart->part_name} เรียบร้อย");
    }

    /**
     * ปฏิเสธเบิกอะไหล่
     */
    public function rejectPart(Request $request, RepairPart $repairPart)
    {
        if ($repairPart->status !== RepairPart::STATUS_PENDING) {
            return redirect()->back()->with('error', 'รายการนี้ไม่อยู่ในสถานะรออนุมัติ');
        }

        $validated = $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        $repairPart->update([
            'status' => RepairPart::STATUS_REJECTED,
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'reject_reason' => $validated['reject_reason'],
        ]);

        RepairLog::create([
            'repair_id' => $repairPart->repair_id,
            'user_id' => $request->user()->id,
            'action' => 'part_rejected',
            'description' => "ปฏิเสธเบิกอะไหล่: {$repairPart->part_name} - เหตุผล: {$validated['reject_reason']}",
        ]);

        return redirect()->back()->with('success', "ปฏิเสธเบิกอะไหล่ {$repairPart->part_name} เรียบร้อย");
    }

    /**
     * รายงานการเบิกอะไหล่
     */
    public function partReport(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id;

        $query = RepairPart::with(['repair', 'product', 'requestedBy', 'approvedBy', 'rejectedBy'])
            ->whereHas('repair', function ($q) use ($user, $branchId) {
                if (!$user->isOwner() && !$user->isAdmin()) {
                    $q->where('branch_id', $branchId);
                }
            });

        // Filter by date range
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));
        $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $parts = $query->orderByDesc('created_at')->paginate(30);

        // Summary
        $summaryQuery = RepairPart::whereHas('repair', function ($q) use ($user, $branchId) {
            if (!$user->isOwner() && !$user->isAdmin()) {
                $q->where('branch_id', $branchId);
            }
        })->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'pending' => (clone $summaryQuery)->where('status', 'pending')->count(),
            'approved' => (clone $summaryQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $summaryQuery)->where('status', 'rejected')->count(),
            'total_cost' => (clone $summaryQuery)->where('status', 'approved')->sum(DB::raw('quantity * unit_price')),
        ];

        return view('repairs.part-report', compact('parts', 'summary', 'from', 'to'));
    }

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
