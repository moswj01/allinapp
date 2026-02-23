<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::withoutGlobalScopes()->with('plan');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($planId = $request->input('plan_id')) {
            $query->where('plan_id', $planId);
        }

        $tenants = $query->withCount([
            'users' => fn($q) => $q->withoutGlobalScopes(),
            'branches' => fn($q) => $q->withoutGlobalScopes(),
        ])->latest()->paginate(20)->withQueryString();

        $plans = Plan::active()->ordered()->get();

        return view('superadmin.tenants.index', compact('tenants', 'plans'));
    }

    public function create()
    {
        $plans = Plan::active()->ordered()->get();
        return view('superadmin.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:tenants,slug|alpha_dash',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:trial,active,suspended',
            // Owner user
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|min:8',
        ]);

        DB::transaction(function () use ($validated) {
            $plan = Plan::findOrFail($validated['plan_id']);

            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'plan_id' => $plan->id,
                'status' => $validated['status'],
                'trial_ends_at' => $validated['status'] === 'trial' ? now()->addDays($plan->trial_days) : null,
                'subscription_starts_at' => $validated['status'] === 'active' ? now() : null,
                'subscription_ends_at' => $validated['status'] === 'active' ? now()->addMonth() : null,
                'is_active' => true,
            ]);

            // Create default Owner role for this tenant
            $ownerRole = Role::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'เจ้าของร้าน',
                'slug' => 'owner',
                'description' => 'เจ้าของร้าน - สิทธิ์ทั้งหมด',
                'permissions' => ['*'],
            ]);

            // Create default roles
            $defaultRoles = [
                ['name' => 'ผู้ดูแลระบบ', 'slug' => 'admin', 'permissions' => ['*']],
                ['name' => 'ผู้จัดการ', 'slug' => 'manager', 'permissions' => ['repairs.*', 'products.*', 'sales.*', 'reports.*', 'customers.*', 'stock.*']],
                ['name' => 'พนักงานขาย', 'slug' => 'sales', 'permissions' => ['sales.*', 'products.view', 'customers.*', 'repairs.view']],
                ['name' => 'ช่างซ่อม', 'slug' => 'technician', 'permissions' => ['repairs.*', 'products.view']],
                ['name' => 'พนักงานคลัง', 'slug' => 'warehouse', 'permissions' => ['stock.*', 'products.*']],
                ['name' => 'พนักงานบัญชี', 'slug' => 'accountant', 'permissions' => ['finance.*', 'reports.*', 'sales.view']],
            ];

            foreach ($defaultRoles as $role) {
                Role::withoutGlobalScopes()->create(array_merge($role, ['tenant_id' => $tenant->id]));
            }

            // Create default branch
            $branch = Branch::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'code' => 'HQ',
                'name' => 'สาขาหลัก',
                'address' => $validated['address'] ?? '',
                'phone' => $validated['phone'] ?? '',
                'is_main' => true,
                'is_active' => true,
            ]);

            // Create owner user
            User::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role_id' => $ownerRole->id,
                'branch_id' => $branch->id,
                'is_active' => true,
                'is_super_admin' => false,
            ]);
        });

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'สร้างร้านค้า "' . $validated['name'] . '" เรียบร้อยแล้ว');
    }

    public function show(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->with('plan')->findOrFail($id);
        $users = User::withoutGlobalScopes()->where('tenant_id', $id)->with('role', 'branch')->get();
        $branches = Branch::withoutGlobalScopes()->where('tenant_id', $id)->get();
        $invoices = $tenant->invoices()->latest()->take(10)->get();
        $usage = $tenant->getUsageSummary();

        return view('superadmin.tenants.show', compact('tenant', 'users', 'branches', 'invoices', 'usage'));
    }

    public function edit(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $plans = Plan::active()->ordered()->get();

        return view('superadmin.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|alpha_dash|unique:tenants,slug,' . $id,
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:trial,active,suspended,cancelled',
            'suspension_reason' => 'nullable|string',
        ]);

        $tenant->update($validated);

        // Update is_active based on status
        $tenant->is_active = in_array($validated['status'], ['active', 'trial']);
        $tenant->save();

        return redirect()->route('superadmin.tenants.show', $id)
            ->with('success', 'อัปเดตข้อมูลร้านค้าเรียบร้อย');
    }

    public function suspend(Request $request, int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $tenant->suspend($request->input('reason', ''));

        return back()->with('success', 'ระงับร้านค้า "' . $tenant->name . '" แล้ว');
    }

    public function activate(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $tenant->activate();

        return back()->with('success', 'เปิดใช้งานร้านค้า "' . $tenant->name . '" แล้ว');
    }

    public function destroy(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $tenantName = $tenant->name;

        // Soft delete
        $tenant->update(['status' => 'cancelled', 'is_active' => false]);
        $tenant->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'ลบร้านค้า "' . $tenantName . '" แล้ว');
    }

    public function loginAs(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $owner = User::withoutGlobalScopes()
            ->where('tenant_id', $id)
            ->whereHas('role', fn($q) => $q->withoutGlobalScopes()->where('slug', 'owner'))
            ->first();

        if (!$owner) {
            return back()->with('error', 'ไม่พบ Owner ของร้านค้านี้');
        }

        // Store original admin id to allow switching back
        session(['super_admin_id' => auth()->id()]);
        auth()->login($owner);

        return redirect()->route('dashboard')
            ->with('success', 'เข้าสู่ระบบในนาม "' . $tenant->name . '"');
    }

    public function switchBack()
    {
        $superAdminId = session('super_admin_id');
        if ($superAdminId) {
            $admin = User::withoutGlobalScopes()->find($superAdminId);
            if ($admin && $admin->is_super_admin) {
                session()->forget('super_admin_id');
                auth()->login($admin);
                return redirect()->route('superadmin.dashboard')
                    ->with('success', 'กลับสู่ Super Admin แล้ว');
            }
        }
        return redirect()->route('dashboard');
    }
}
