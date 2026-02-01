<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = User::with(['role', 'branch']);

        // Non-owner/admin can only see users from their branch
        if (!$user->isOwner() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Filter by branch
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'branches'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $roles = Role::orderBy('name')->get();

        // Non-owner can only create for their branch
        if ($user->isOwner() || $user->isAdmin()) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'required|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validate branch access
        if (!$user->canAccessBranch($validated['branch_id'])) {
            return back()->with('error', 'คุณไม่มีสิทธิ์เพิ่มผู้ใช้ในสาขานี้');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }

    public function show(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Check access
        if (!$currentUser->canAccessBranch($user->branch_id)) {
            abort(403, 'คุณไม่มีสิทธิ์ดูข้อมูลผู้ใช้นี้');
        }

        $user->load(['role', 'branch']);

        // Get user's activity
        $recentRepairs = $user->repairs()->latest()->take(5)->get();
        $recentSales = $user->sales()->latest()->take(5)->get();

        return view('users.show', compact('user', 'recentRepairs', 'recentSales'));
    }

    public function edit(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Check access
        if (!$currentUser->canAccessBranch($user->branch_id)) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขข้อมูลผู้ใช้นี้');
        }

        $roles = Role::orderBy('name')->get();

        if ($currentUser->isOwner() || $currentUser->isAdmin()) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $currentUser->branch_id)->get();
        }

        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Check access
        if (!$currentUser->canAccessBranch($user->branch_id)) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขข้อมูลผู้ใช้นี้');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'required|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        // Validate new branch access
        if (!$currentUser->canAccessBranch($validated['branch_id'])) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ย้ายผู้ใช้ไปสาขานี้');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Cannot delete yourself
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        // Check access
        if (!$currentUser->canAccessBranch($user->branch_id)) {
            abort(403, 'คุณไม่มีสิทธิ์ลบผู้ใช้นี้');
        }

        // Soft delete by deactivating
        $user->update(['is_active' => false]);

        return redirect()->route('users.index')
            ->with('success', 'ปิดการใช้งานผู้ใช้เรียบร้อยแล้ว');
    }

    public function toggleStatus(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->canAccessBranch($user->branch_id)) {
            abort(403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with(
            'success',
            $user->is_active ? 'เปิดใช้งานผู้ใช้แล้ว' : 'ปิดใช้งานผู้ใช้แล้ว'
        );
    }
}
