<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'branch'])->orderBy('created_at', 'desc');

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();
        $actions = [
            'create' => 'สร้าง',
            'update' => 'แก้ไข',
            'delete' => 'ลบ',
            'login' => 'เข้าสู่ระบบ',
            'logout' => 'ออกจากระบบ',
            'approve' => 'อนุมัติ',
            'reject' => 'ปฏิเสธ',
            'export' => 'ส่งออก',
            'import' => 'นำเข้า',
        ];

        return view('audit-logs.index', compact('logs', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['user', 'branch']);

        return view('audit-logs.show', compact('auditLog'));
    }
}
