<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::withCount(['users', 'repairs', 'sales']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $branches = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'line_token' => 'nullable|string|max:255',
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'เพิ่มสาขาเรียบร้อยแล้ว');
    }

    public function show(Branch $branch)
    {
        $branch->loadCount(['users', 'repairs', 'sales']);

        // Get users in this branch
        $users = $branch->users()->with('role')->get();

        // Get today's stats
        $todayRepairs = $branch->repairs()->whereDate('created_at', today())->count();
        $todaySales = $branch->sales()->whereDate('created_at', today())->sum('total');

        // Get monthly stats
        $monthlyRepairs = $branch->repairs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $monthlySales = $branch->sales()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        return view('branches.show', compact(
            'branch',
            'users',
            'todayRepairs',
            'todaySales',
            'monthlyRepairs',
            'monthlySales'
        ));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'line_token' => 'nullable|string|max:255',
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'อัปเดตสาขาเรียบร้อยแล้ว');
    }

    public function destroy(Branch $branch)
    {
        // Check if branch has users
        if ($branch->users()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสาขานี้ได้ เนื่องจากมีพนักงานอยู่');
        }

        // Check if branch has repairs
        if ($branch->repairs()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสาขานี้ได้ เนื่องจากมีงานซ่อม');
        }

        // Check if branch has sales
        if ($branch->sales()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสาขานี้ได้ เนื่องจากมีรายการขาย');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'ลบสาขาเรียบร้อยแล้ว');
    }

    public function toggleStatus(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        return back()->with(
            'success',
            $branch->is_active ? 'เปิดใช้งานสาขาแล้ว' : 'ปิดใช้งานสาขาแล้ว'
        );
    }
}
