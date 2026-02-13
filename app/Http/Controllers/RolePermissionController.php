<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * All available permissions grouped by module.
     */
    private function getPermissionGroups(): array
    {
        return [
            'dashboard' => [
                'label' => 'แดชบอร์ด',
                'permissions' => [
                    'dashboard.view' => 'ดูแดชบอร์ด',
                    'dashboard.all_branches' => 'ดูทุกสาขา',
                ],
            ],
            'repairs' => [
                'label' => 'งานซ่อม',
                'permissions' => [
                    'repairs.view' => 'ดูงานซ่อม',
                    'repairs.create' => 'สร้างงานซ่อม',
                    'repairs.edit' => 'แก้ไขงานซ่อม',
                    'repairs.delete' => 'ลบงานซ่อม',
                    'repairs.update_status' => 'อัพเดทสถานะ',
                    'repairs.add_parts' => 'เพิ่มอะไหล่',
                    'repairs.*' => 'ทั้งหมด',
                ],
            ],
            'products' => [
                'label' => 'สินค้า',
                'permissions' => [
                    'products.view' => 'ดูสินค้า',
                    'products.create' => 'เพิ่มสินค้า',
                    'products.edit' => 'แก้ไขสินค้า',
                    'products.delete' => 'ลบสินค้า',
                    'products.*' => 'ทั้งหมด',
                ],
            ],
            'parts' => [
                'label' => 'อะไหล่',
                'permissions' => [
                    'parts.view' => 'ดูอะไหล่',
                    'parts.create' => 'เพิ่มอะไหล่',
                    'parts.edit' => 'แก้ไขอะไหล่',
                    'parts.delete' => 'ลบอะไหล่',
                    'parts.requisition' => 'เบิกอะไหล่',
                    'parts.*' => 'ทั้งหมด',
                ],
            ],
            'categories' => [
                'label' => 'หมวดหมู่',
                'permissions' => [
                    'categories.view' => 'ดูหมวดหมู่',
                    'categories.create' => 'เพิ่มหมวดหมู่',
                    'categories.edit' => 'แก้ไขหมวดหมู่',
                    'categories.delete' => 'ลบหมวดหมู่',
                    'categories.*' => 'ทั้งหมด',
                ],
            ],
            'customers' => [
                'label' => 'ลูกค้า',
                'permissions' => [
                    'customers.view' => 'ดูลูกค้า',
                    'customers.create' => 'เพิ่มลูกค้า',
                    'customers.edit' => 'แก้ไขลูกค้า',
                    'customers.delete' => 'ลบลูกค้า',
                    'customers.*' => 'ทั้งหมด',
                ],
            ],
            'sales' => [
                'label' => 'การขาย',
                'permissions' => [
                    'sales.view' => 'ดูรายการขาย',
                    'sales.create' => 'สร้างรายการขาย',
                    'sales.edit' => 'แก้ไขรายการขาย',
                    'sales.delete' => 'ลบรายการขาย',
                    'sales.*' => 'ทั้งหมด',
                ],
            ],
            'quotations' => [
                'label' => 'ใบเสนอราคา',
                'permissions' => [
                    'quotations.view' => 'ดูใบเสนอราคา',
                    'quotations.create' => 'สร้างใบเสนอราคา',
                    'quotations.edit' => 'แก้ไขใบเสนอราคา',
                    'quotations.delete' => 'ลบใบเสนอราคา',
                    'quotations.*' => 'ทั้งหมด',
                ],
            ],
            'purchase_orders' => [
                'label' => 'ใบสั่งซื้อ',
                'permissions' => [
                    'purchase_orders.view' => 'ดูใบสั่งซื้อ',
                    'purchase_orders.create' => 'สร้างใบสั่งซื้อ',
                    'purchase_orders.edit' => 'แก้ไขใบสั่งซื้อ',
                    'purchase_orders.delete' => 'ลบใบสั่งซื้อ',
                    'purchase_orders.*' => 'ทั้งหมด',
                ],
            ],
            'stock_transfers' => [
                'label' => 'โอนสต๊อก',
                'permissions' => [
                    'stock_transfers.view' => 'ดูรายการโอน',
                    'stock_transfers.create' => 'สร้างรายการโอน',
                    'stock_transfers.*' => 'ทั้งหมด',
                ],
            ],
            'stock_takes' => [
                'label' => 'ตรวจนับสต๊อก',
                'permissions' => [
                    'stock_takes.view' => 'ดูการตรวจนับ',
                    'stock_takes.create' => 'สร้างการตรวจนับ',
                    'stock_takes.*' => 'ทั้งหมด',
                ],
            ],
            'reports' => [
                'label' => 'รายงาน',
                'permissions' => [
                    'reports.branch' => 'รายงานสาขา',
                    'reports.*' => 'ทั้งหมด',
                ],
            ],
            'finance' => [
                'label' => 'การเงิน',
                'permissions' => [
                    'petty_cash.view' => 'ดูเงินสดย่อย',
                    'petty_cash.create' => 'เพิ่มเงินสดย่อย',
                    'petty_cash.approve' => 'อนุมัติเงินสดย่อย',
                    'petty_cash.*' => 'เงินสดย่อย - ทั้งหมด',
                    'daily_settlement.view' => 'ดูปิดยอดประจำวัน',
                    'daily_settlement.approve' => 'อนุมัติปิดยอด',
                    'daily_settlement.*' => 'ปิดยอด - ทั้งหมด',
                    'accounts_receivable.view' => 'ดูลูกหนี้',
                    'accounts_receivable.*' => 'ลูกหนี้ - ทั้งหมด',
                ],
            ],
            'system' => [
                'label' => 'ระบบ',
                'permissions' => [
                    'settings.*' => 'การตั้งค่า',
                    'users.*' => 'จัดการพนักงาน',
                    'branches.*' => 'จัดการสาขา',
                    'roles.*' => 'จัดการสิทธิ์',
                    'suppliers.*' => 'จัดการซัพพลายเออร์',
                    'audit_logs.view' => 'ดู Audit Log',
                ],
            ],
        ];
    }

    public function index()
    {
        $roles = Role::withCount('users')->orderBy('slug')->get();
        $permissionGroups = $this->getPermissionGroups();

        return view('settings.roles.index', compact('roles', 'permissionGroups'));
    }

    public function edit(Role $role)
    {
        $permissionGroups = $this->getPermissionGroups();

        // Precompute JSON for Alpine.js to avoid formatter breaking PHP in <script>
        $jsData = [
            'openGroups' => array_keys($permissionGroups),
            'selectedPermissions' => old('permissions', $role->permissions ?? []),
            'allPermissions' => collect($permissionGroups)->flatMap(function ($g) {
                return array_keys($g['permissions']);
            })->values()->toArray(),
            'groupPermissions' => collect($permissionGroups)->map(function ($g) {
                return array_keys($g['permissions']);
            })->toArray(),
        ];

        return view('settings.roles.edit', compact('role', 'permissionGroups', 'jsData'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // Prevent editing owner/admin slug
        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $role->description,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return redirect()->route('roles.index')
            ->with('success', "บันทึกสิทธิ์บทบาท «{$role->name}» เรียบร้อย");
    }

    public function create()
    {
        $permissionGroups = $this->getPermissionGroups();

        // Precompute JSON for Alpine.js to avoid formatter breaking PHP in <script>
        $jsData = [
            'openGroups' => [],
            'selectedPermissions' => old('permissions', []),
            'allPermissions' => collect($permissionGroups)->flatMap(function ($g) {
                return array_keys($g['permissions']);
            })->values()->toArray(),
            'groupPermissions' => collect($permissionGroups)->map(function ($g) {
                return array_keys($g['permissions']);
            })->toArray(),
        ];

        return view('settings.roles.create', compact('permissionGroups', 'jsData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:roles,slug|regex:/^[a-z0-9_]+$/',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? '',
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'สร้างบทบาทใหม่เรียบร้อย');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting system roles
        $systemRoles = [Role::OWNER, Role::ADMIN];
        if (in_array($role->slug, $systemRoles)) {
            return redirect()->route('roles.index')
                ->with('error', 'ไม่สามารถลบบทบาทหลักของระบบได้');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'ไม่สามารถลบบทบาทที่มีผู้ใช้อยู่ได้ กรุณาย้ายผู้ใช้ก่อน');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'ลบบทบาทเรียบร้อย');
    }
}
