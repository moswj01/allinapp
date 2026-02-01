<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RepairController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Repair::with(['product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('repair_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $repairs = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $repairs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'repair_number' => 'required|string|max:255|unique:repairs,repair_number',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'repair_type' => 'required|in:express,leave',
            'problem_description' => 'required|string',
            'notes' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'expected_completion_date' => 'nullable|date',
            'device_serial' => 'nullable|string|max:255',
            'device_condition' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'pending';
        $validated['received_date'] = now();

        $repair = Repair::create($validated);
        $repair->load(['product']);

        return response()->json([
            'success' => true,
            'message' => 'สร้างงานซ่อมสำเร็จ',
            'data' => $repair,
        ], 201);
    }

    public function show(Repair $repair): JsonResponse
    {
        $repair->load(['product']);

        return response()->json([
            'success' => true,
            'data' => $repair,
        ]);
    }

    public function update(Request $request, Repair $repair): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'repair_number' => 'required|string|max:255|unique:repairs,repair_number,' . $repair->id,
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'repair_type' => 'required|in:express,leave',
            'problem_description' => 'required|string',
            'notes' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'status' => 'in:pending,in_progress,completed,cancelled',
            'expected_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'device_serial' => 'nullable|string|max:255',
            'device_condition' => 'nullable|string|max:255',
        ]);

        $repair->update($validated);
        $repair->load(['product']);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทงานซ่อมสำเร็จ',
            'data' => $repair,
        ]);
    }

    public function destroy(Repair $repair): JsonResponse
    {
        $repair->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบงานซ่อมสำเร็จ',
        ]);
    }

    public function updateStatus(Request $request, Repair $repair): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validated['status'] === 'completed') {
            $repair->actual_completion_date = now();
        }

        $repair->status = $validated['status'];
        $repair->save();
        $repair->load(['product']);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทสถานะสำเร็จ',
            'data' => $repair,
        ]);
    }
}
