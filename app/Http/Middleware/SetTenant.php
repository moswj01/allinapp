<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    /**
     * Set the current tenant from authenticated user.
     * Super admins bypass tenant scoping.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admin — no tenant scoping (they manage all tenants)
        if ($user->is_super_admin) {
            Tenant::setCurrent(null);
            return $next($request);
        }

        // Regular user — set their tenant
        if ($user->tenant_id) {
            $tenant = Tenant::withoutGlobalScopes()->find($user->tenant_id);

            if (!$tenant || !$tenant->isActive()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $tenant?->isSuspended()
                    ? 'บัญชีร้านค้าของคุณถูกระงับ กรุณาติดต่อผู้ดูแลระบบ'
                    : 'บัญชีร้านค้าของคุณไม่พร้อมใช้งาน';

                return redirect()->route('login')->withErrors(['email' => $message]);
            }

            // Check trial expiry
            if ($tenant->isTrialExpired()) {
                Tenant::setCurrent($tenant);
                // Allow access but will show warning banner
            } else {
                Tenant::setCurrent($tenant);
            }
        }

        // Share tenant data with all views
        $currentTenant = Tenant::current();
        view()->share('currentTenant', $currentTenant);
        view()->share('isSuperAdmin', $user->is_super_admin);

        return $next($request);
    }
}
