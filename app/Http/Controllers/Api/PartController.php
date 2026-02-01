<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Part::with(['category']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        $parts = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $parts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255|unique:parts,part_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'unit' => 'string|max:50',
            'min_stock' => 'integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $part = Part::create($validated);
        $part->load(['category']);

        return response()->json([
            'success' => true,
            'message' => 'สร้างอะไหล่สำเร็จ',
            'data' => $part,
        ], 201);
    }

    public function show(Part $part): JsonResponse
    {
        $part->load(['category', 'stocks']);

        return response()->json([
            'success' => true,
            'data' => $part,
        ]);
    }

    public function update(Request $request, Part $part): JsonResponse
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255|unique:parts,part_number,' . $part->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'unit' => 'string|max:50',
            'min_stock' => 'integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $part->update($validated);
        $part->load(['category']);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทอะไหล่สำเร็จ',
            'data' => $part,
        ]);
    }

    public function destroy(Part $part): JsonResponse
    {
        $part->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบอะไหล่สำเร็จ',
        ]);
    }
}
