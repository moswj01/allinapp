<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount(['tenants' => fn($q) => $q->withoutGlobalScopes()])
            ->ordered()
            ->get();

        return view('superadmin.plans.index', compact('plans'));
    }

    public function create()
    {
        $features = $this->getFeatureOptions();
        return view('superadmin.plans.create', compact('features'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:plans,slug|alpha_dash',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:-1',
            'max_branches' => 'required|integer|min:-1',
            'max_products' => 'required|integer|min:-1',
            'max_repairs' => 'required|integer|min:-1',
            'features' => 'nullable|array',
            'trial_days' => 'required|integer|min:0',
            'sort_order' => 'required|integer|min:0',
        ]);

        $validated['features'] = $request->input('features', []);
        $validated['is_active'] = $request->boolean('is_active', true);

        Plan::create($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'สร้างแพ็กเกจ "' . $validated['name'] . '" เรียบร้อย');
    }

    public function edit(Plan $plan)
    {
        $features = $this->getFeatureOptions();
        return view('superadmin.plans.edit', compact('plan', 'features'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|alpha_dash|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:-1',
            'max_branches' => 'required|integer|min:-1',
            'max_products' => 'required|integer|min:-1',
            'max_repairs' => 'required|integer|min:-1',
            'features' => 'nullable|array',
            'trial_days' => 'required|integer|min:0',
            'sort_order' => 'required|integer|min:0',
        ]);

        $validated['features'] = $request->input('features', []);
        $validated['is_active'] = $request->boolean('is_active', true);

        $plan->update($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'อัปเดตแพ็กเกจ "' . $validated['name'] . '" เรียบร้อย');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->exists()) {
            return back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีร้านค้าใช้แพ็กเกจนี้อยู่');
        }

        $plan->delete();

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'ลบแพ็กเกจเรียบร้อย');
    }

    private function getFeatureOptions(): array
    {
        return [
            Plan::FEATURE_REPAIRS => ['label' => 'ระบบซ่อม', 'icon' => 'fas fa-tools'],
            Plan::FEATURE_POS => ['label' => 'ขายหน้าร้าน (POS)', 'icon' => 'fas fa-cash-register'],
            Plan::FEATURE_STOCK => ['label' => 'สต๊อกสินค้า', 'icon' => 'fas fa-boxes'],
            Plan::FEATURE_PURCHASING => ['label' => 'จัดซื้อ', 'icon' => 'fas fa-shopping-cart'],
            Plan::FEATURE_FINANCE => ['label' => 'การเงิน', 'icon' => 'fas fa-coins'],
            Plan::FEATURE_REPORTS => ['label' => 'รายงาน', 'icon' => 'fas fa-chart-bar'],
            Plan::FEATURE_LINE_OA => ['label' => 'LINE OA Chatbot', 'icon' => 'fab fa-line'],
            Plan::FEATURE_API => ['label' => 'API Access', 'icon' => 'fas fa-plug'],
            Plan::FEATURE_QUOTATIONS => ['label' => 'ใบเสนอราคา', 'icon' => 'fas fa-file-invoice'],
            Plan::FEATURE_MULTI_BRANCH => ['label' => 'หลายสาขา', 'icon' => 'fas fa-store'],
        ];
    }
}
