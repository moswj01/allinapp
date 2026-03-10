<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\PlanChangeRequest;
use App\Models\SystemSetting;
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
        $pendingRequest = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->with('requestedPlan', 'currentPlan')
            ->first();

        $paymentSettings = SystemSetting::getByGroup('payment');

        return view('tenant.billing', compact('tenant', 'plans', 'invoices', 'usage', 'pendingRequest', 'paymentSettings'));
    }

    /**
     * Request plan change (requires Super Admin approval)
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'note' => 'nullable|string|max:500',
        ]);

        $tenant = Tenant::current();
        if (!$tenant) abort(403);

        $newPlan = Plan::findOrFail($validated['plan_id']);
        $currentPlan = $tenant->plan;

        // Check if already has a pending request
        $existingRequest = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'คุณมีคำขอเปลี่ยนแพ็กเกจที่รออนุมัติอยู่แล้ว กรุณารอการตอบกลับจากผู้ดูแลระบบ');
        }

        // Determine type
        $type = $newPlan->price > $currentPlan->price ? 'upgrade' : 'downgrade';
        $amount = $newPlan->price;
        $taxAmount = $amount * 0.07;
        $totalAmount = $amount + $taxAmount;

        PlanChangeRequest::create([
            'tenant_id' => $tenant->id,
            'current_plan_id' => $currentPlan->id,
            'requested_plan_id' => $newPlan->id,
            'requested_by' => auth()->id(),
            'type' => $type,
            'status' => 'pending',
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'tenant_note' => $validated['note'] ?? null,
        ]);

        $typeLabel = $type === 'upgrade' ? 'อัพเกรด' : 'ดาวน์เกรด';
        return back()->with('success', "ส่งคำขอ{$typeLabel}เป็น \"{$newPlan->name}\" เรียบร้อย กรุณารอการอนุมัติจากผู้ดูแลระบบ");
    }

    /**
     * Cancel pending plan change request
     */
    public function cancelPlanRequest(int $id)
    {
        $tenant = Tenant::current();
        if (!$tenant) abort(403);

        $request = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->update(['status' => 'cancelled']);

        return back()->with('success', 'ยกเลิกคำขอเปลี่ยนแพ็กเกจเรียบร้อย');
    }
}
