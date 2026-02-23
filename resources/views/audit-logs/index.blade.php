@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'ประวัติการใช้งาน')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ประวัติการใช้งานระบบ</h2>
            <p class="text-gray-500">Audit Log - ติดตามการเปลี่ยนแปลงทั้งหมด</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ผู้ใช้, IP..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">การกระทำ</label>
                <select name="action" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($actions as $key => $label)
                    <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">จากวันที่</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ถึงวันที่</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"><i class="fas fa-search mr-1"></i>ค้นหา</button>
                <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ล้าง</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เวลา</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้ใช้</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">การกระทำ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เป้าหมาย</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">ดู</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $log->user->name ?? 'ระบบ' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $actionColors = [
                            'create' => 'bg-green-100 text-green-800',
                            'update' => 'bg-blue-100 text-blue-800',
                            'delete' => 'bg-red-100 text-red-800',
                            'login' => 'bg-indigo-100 text-indigo-800',
                            'logout' => 'bg-gray-100 text-gray-800',
                            'approve' => 'bg-emerald-100 text-emerald-800',
                            'reject' => 'bg-orange-100 text-orange-800',
                            'export' => 'bg-purple-100 text-purple-800',
                            'import' => 'bg-teal-100 text-teal-800',
                            ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $actions[$log->action] ?? $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                            {{ class_basename($log->auditable_type ?? '-') }} #{{ $log->auditable_id ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap font-mono">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <a href="{{ route('audit-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-history text-4xl mb-3 text-gray-300"></i>
                            <p>ยังไม่มีข้อมูล</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection