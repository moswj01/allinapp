<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\TenantInvoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::withoutGlobalScopes()->count(),
            'active_tenants' => Tenant::withoutGlobalScopes()->where('status', 'active')->count(),
            'trial_tenants' => Tenant::withoutGlobalScopes()->where('status', 'trial')->count(),
            'suspended_tenants' => Tenant::withoutGlobalScopes()->where('status', 'suspended')->count(),
            'total_users' => User::withoutGlobalScopes()->where('is_super_admin', false)->count(),
            'total_revenue' => TenantInvoice::where('status', 'paid')->sum('total_amount'),
            'pending_invoices' => TenantInvoice::where('status', 'pending')->count(),
            'overdue_invoices' => TenantInvoice::where('status', 'pending')
                ->where('period_end', '<', now())->count(),
        ];

        $recentTenants = Tenant::withoutGlobalScopes()
            ->with('plan')
            ->latest()
            ->take(10)
            ->get();

        $expiringTrials = Tenant::withoutGlobalScopes()
            ->where('status', 'trial')
            ->where('trial_ends_at', '<=', now()->addDays(3))
            ->where('trial_ends_at', '>', now())
            ->with('plan')
            ->get();

        $plans = Plan::withCount(['tenants' => function ($q) {
            $q->withoutGlobalScopes();
        }])->ordered()->get();

        $monthlyRevenue = TenantInvoice::where('status', 'paid')
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('total_amount');

        return view('superadmin.dashboard', compact(
            'stats',
            'recentTenants',
            'expiringTrials',
            'plans',
            'monthlyRevenue'
        ));
    }
}
