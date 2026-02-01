<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get branch_id from route parameter or request
        $branchId = $request->route('branch_id')
            ?? $request->route('branch')
            ?? $request->input('branch_id');

        if ($branchId && !$user->canAccessBranch((int) $branchId)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลสาขานี้');
        }

        return $next($request);
    }
}
