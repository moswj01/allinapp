<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::withCount('branchStocks')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:255|unique:branches,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:255',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_main'] = $request->boolean('is_main', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code'] = $validated['code'] ?? ('BR-' . strtoupper(Str::random(6)));

        $branch = Branch::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'สร้างสาขาสำเร็จ',
            'data' => $branch,
        ], 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        $branch->loadCount('branchStocks');

        return response()->json([
            'success' => true,
            'data' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:255|unique:branches,code,' . $branch->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:255',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_main'] = $request->boolean('is_main', $branch->is_main);
        $validated['is_active'] = $request->boolean('is_active', $branch->is_active);

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทสาขาสำเร็จ',
            'data' => $branch,
        ]);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบสาขาสำเร็จ',
        ]);
    }
}
