<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Check if the current tenant's plan has the required feature.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        // Super admin can access everything
        if ($user?->is_super_admin) {
            return $next($request);
        }

        $tenant = Tenant::current();

        if (!$tenant) {
            abort(403, 'ไม่พบข้อมูลร้านค้า');
        }

        if (!$tenant->hasFeature($feature)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'ฟีเจอร์นี้ไม่อยู่ในแพ็กเกจของคุณ กรุณาอัปเกรดแพ็กเกจ',
                    'feature' => $feature,
                    'upgrade_url' => route('tenant.billing'),
                ], 403);
            }

            return redirect()->route('tenant.billing')
                ->with('error', 'ฟีเจอร์ "' . $feature . '" ไม่อยู่ในแพ็กเกจปัจจุบัน กรุณาอัปเกรด');
        }

        return $next($request);
    }
}
