<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount(['products']);

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Order by name only (no sort_order column)
        $categories = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Force type to 'product' (we no longer use parts categories)
        $validated['type'] = 'product';

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
    }

    public function show(Category $category)
    {
        $category->loadCount(['products']);

        // Only products are shown
        $items = $category->products()->paginate(20);

        return view('categories.show', compact('category', 'items'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Keep type as 'product'
        $validated['type'] = 'product';

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'อัปเดตหมวดหมู่เรียบร้อยแล้ว');
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->exists()) {
            return back()->with('error', 'ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากมีสินค้าอยู่');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'ลบหมวดหมู่เรียบร้อยแล้ว');
    }

    // API for dropdown
    public function list(Request $request)
    {
        $type = $request->input('type');

        $query = Category::where('is_active', true);

        // Force product type or filter if provided as 'product'
        if ($type) {
            $query->where('type', $type);
        } else {
            $query->where('type', 'product');
        }

        // Order by name only
        $categories = $query->orderBy('name')->get(['id', 'name', 'type']);

        return response()->json($categories);
    }
}
