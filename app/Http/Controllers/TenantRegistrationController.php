<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TenantRegistrationController extends Controller
{
    /**
     * Show SaaS landing page
     */
    public function landing()
    {
        $plans = Plan::active()->ordered()->get();
        return view('landing', compact('plans'));
    }

    /**
     * Show registration form
     */
    public function showRegistration(Request $request)
    {
        $plans = Plan::active()->ordered()->get();
        $selectedPlan = $request->input('plan');

        return view('auth.register', compact('plans', 'selectedPlan'));
    }

    /**
     * Process tenant registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:tenants,slug|alpha_dash',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'plan_id' => 'required|exists:plans,id',
            'owner_name' => 'required|string|max:255',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $plan = Plan::findOrFail($validated['plan_id']);

            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['shop_name'],
                'slug' => $validated['slug'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'plan_id' => $plan->id,
                'status' => Tenant::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays($plan->trial_days ?: 14),
                'is_active' => true,
            ]);

            // Create default roles
            $ownerRole = Role::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'เจ้าของร้าน',
                'slug' => 'owner',
                'description' => 'เจ้าของร้าน - สิทธิ์ทั้งหมด',
                'permissions' => ['*'],
            ]);

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
                'is_main' => true,
                'is_active' => true,
            ]);

            // Create owner user
            $user = User::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $ownerRole->id,
                'branch_id' => $branch->id,
                'is_active' => true,
                'is_super_admin' => false,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'ยินดีต้อนรับ! ร้านค้าของคุณพร้อมใช้งานแล้ว คุณมีระยะทดลองใช้ฟรี');
    }

    /**
     * Check slug availability (AJAX)
     */
    public function checkSlug(Request $request)
    {
        $slug = $request->input('slug');
        $available = !Tenant::withoutGlobalScopes()->where('slug', $slug)->exists();

        return response()->json([
            'available' => $available,
            'message' => $available ? 'ใช้ได้' : 'URL นี้ถูกใช้แล้ว',
        ]);
    }

    /**
     * Tenant billing / subscription page
     */
    public function billing()
    {
        $tenant = Tenant::current();
        if (!$tenant) abort(403);

        $plans = Plan::active()->ordered()->get();
        $invoices = $tenant->invoices()->latest()->paginate(10);
        $usage = $tenant->getUsageSummary();

        return view('tenant.billing', compact('tenant', 'plans', 'invoices', 'usage'));
    }

    /**
     * Change plan
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = Tenant::current();
        if (!$tenant) abort(403);

        $newPlan = Plan::findOrFail($validated['plan_id']);
        $tenant->update(['plan_id' => $newPlan->id]);

        // Create invoice for upgrade
        if ($newPlan->price > 0) {
            TenantInvoice::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $newPlan->id,
                'invoice_number' => TenantInvoice::generateNumber(),
                'amount' => $newPlan->price,
                'tax_amount' => $newPlan->price * 0.07,
                'total_amount' => $newPlan->price * 1.07,
                'status' => 'pending',
                'billing_cycle' => 'monthly',
                'period_start' => now(),
                'period_end' => now()->addMonth(),
            ]);
        }

        // Activate if was trial
        if ($tenant->isTrial()) {
            $tenant->update([
                'status' => Tenant::STATUS_ACTIVE,
                'subscription_starts_at' => now(),
                'subscription_ends_at' => now()->addMonth(),
            ]);
        }

        return back()->with('success', 'เปลี่ยนแพ็กเกจเป็น "' . $newPlan->name . '" เรียบร้อย');
    }
}
