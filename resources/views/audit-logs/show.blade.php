@extends('layouts.app')

@section('title', 'Audit Log #' . $auditLog->id)
@section('page-title', 'รายละเอียด Audit Log')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Audit Log #{{ $auditLog->id }}</h2>
            <p class="text-gray-500">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    {{-- Info --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-info-circle text-indigo-600 mr-2"></i>ข้อมูลทั่วไป</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500">ผู้ใช้:</span>
                <p class="font-medium text-gray-900">{{ $auditLog->user->name ?? 'ระบบ' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">สาขา:</span>
                <p class="font-medium text-gray-900">{{ $auditLog->branch->name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">การกระทำ:</span>
                @php
                $actionLabels = ['create'=>'สร้าง','update'=>'แก้ไข','delete'=>'ลบ','login'=>'เข้าสู่ระบบ','logout'=>'ออกจากระบบ','approve'=>'อนุมัติ','reject'=>'ปฏิเสธ','export'=>'ส่งออก','import'=>'นำเข้า'];
                $actionColors = ['create'=>'bg-green-100 text-green-800','update'=>'bg-blue-100 text-blue-800','delete'=>'bg-red-100 text-red-800','login'=>'bg-indigo-100 text-indigo-800','logout'=>'bg-gray-100 text-gray-800','approve'=>'bg-emerald-100 text-emerald-800','reject'=>'bg-orange-100 text-orange-800','export'=>'bg-purple-100 text-purple-800','import'=>'bg-teal-100 text-teal-800'];
                @endphp
                <p><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $actionColors[$auditLog->action] ?? 'bg-gray-100 text-gray-800' }}">{{ $actionLabels[$auditLog->action] ?? $auditLog->action }}</span></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">เป้าหมาย:</span>
                <p class="font-medium text-gray-900">{{ class_basename($auditLog->auditable_type ?? '-') }} #{{ $auditLog->auditable_id ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">URL:</span>
                <p class="font-mono text-sm text-gray-700 break-all">{{ $auditLog->url ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">IP Address:</span>
                <p class="font-mono text-sm text-gray-700">{{ $auditLog->ip_address ?? '-' }}</p>
            </div>
            <div class="md:col-span-2">
                <span class="text-sm text-gray-500">User Agent:</span>
                <p class="font-mono text-xs text-gray-500 break-all">{{ $auditLog->user_agent ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Old Values --}}
    @if($auditLog->old_values && count($auditLog->old_values) > 0)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-arrow-left text-red-500 mr-2"></i>ค่าก่อนเปลี่ยนแปลง</h3>
        <div class="bg-red-50 rounded-lg p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-3 py-2 text-gray-600">ฟิลด์</th>
                        <th class="text-left px-3 py-2 text-gray-600">ค่า</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditLog->old_values as $key => $value)
                    <tr class="border-t border-red-100">
                        <td class="px-3 py-2 font-medium text-gray-800">{{ $key }}</td>
                        <td class="px-3 py-2 text-gray-600 font-mono">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- New Values --}}
    @if($auditLog->new_values && count($auditLog->new_values) > 0)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-arrow-right text-green-500 mr-2"></i>ค่าหลังเปลี่ยนแปลง</h3>
        <div class="bg-green-50 rounded-lg p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-3 py-2 text-gray-600">ฟิลด์</th>
                        <th class="text-left px-3 py-2 text-gray-600">ค่า</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditLog->new_values as $key => $value)
                    <tr class="border-t border-green-100">
                        <td class="px-3 py-2 font-medium text-gray-800">{{ $key }}</td>
                        <td class="px-3 py-2 text-gray-600 font-mono">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Changes Comparison (for update action) --}}
    @if($auditLog->action === 'update' && $auditLog->old_values && $auditLog->new_values)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-exchange-alt text-blue-500 mr-2"></i>เปรียบเทียบการเปลี่ยนแปลง</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600">ฟิลด์</th>
                        <th class="px-4 py-3 text-left text-gray-600">ค่าเดิม</th>
                        <th class="px-4 py-3 text-center text-gray-400">→</th>
                        <th class="px-4 py-3 text-left text-gray-600">ค่าใหม่</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditLog->new_values as $key => $newVal)
                    @php $oldVal = $auditLog->old_values[$key] ?? '-'; @endphp
                    <tr class="border-t">
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $key }}</td>
                        <td class="px-4 py-2 text-red-600 font-mono bg-red-50">{{ is_array($oldVal) ? json_encode($oldVal, JSON_UNESCAPED_UNICODE) : $oldVal }}</td>
                        <td class="px-4 py-2 text-center text-gray-400">→</td>
                        <td class="px-4 py-2 text-green-600 font-mono bg-green-50">{{ is_array($newVal) ? json_encode($newVal, JSON_UNESCAPED_UNICODE) : $newVal }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection