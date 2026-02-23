<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Export categories to CSV
     */
    public function exportCsv()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="categories_export_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($categories) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['name', 'description', 'is_active', 'products_count']);

            foreach ($categories as $category) {
                fputcsv($handle, [
                    $category->name,
                    $category->description,
                    $category->is_active ? 'Y' : 'N',
                    $category->products_count,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download category import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="category_import_template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['name', 'description']);
            fputcsv($handle, ['เคสมือถือ', 'เคสกันกระแทก เคสซิลิโคน เคสฝาพับ']);
            fputcsv($handle, ['ฟิล์มกระจก', 'ฟิล์มกันรอย ฟิล์มเต็มจอ']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import categories from CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'ไฟล์ CSV ไม่ถูกต้อง');
        }

        $header[0] = preg_replace('/\x{FEFF}/u', '', $header[0]);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        if (!in_array('name', $header)) {
            fclose($handle);
            return redirect()->back()->with('error', 'ต้องมีคอลัมน์ name');
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

                if (empty($data['name'])) {
                    $skipped++;
                    continue;
                }

                $existing = Category::where('name', $data['name'])->first();
                if ($existing) {
                    if (!empty($data['description'])) {
                        $existing->update(['description' => $data['description']]);
                    }
                    $updated++;
                } else {
                    Category::create([
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'type' => 'product',
                        'is_active' => true,
                    ]);
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

        $msg = "Import เสร็จ! เพิ่มใหม่ {$imported} | อัปเดท {$updated} | ข้าม {$skipped} รายการ";
        return redirect()->route('categories.index')->with('success', $msg);
    }
}
