@extends('layouts.app')

@section('title', 'บัญชีลูกหนี้')
@section('page-title', 'บัญชีลูกหนี้ (Accounts Receivable)')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">บัญชีลูกหนี้</h2>
        <p class="text-sm text-gray-500 mt-1">ติดตามยอดค้างชำระจากลูกค้า</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">ยอดค้างทั้งหมด</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">฿{{ number_format($summary['total_outstanding'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center"><i class="fas fa-money-bill-wave text-orange-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">ยอดค้างเกินกำหนด</p>
                    <p class="text-xl font-bold text-red-600 mt-1">฿{{ number_format($summary['total_overdue'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-exclamation-triangle text-red-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">จำนวนรายการ</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">{{ $summary['total_count'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-file-invoice text-blue-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">เกินกำหนด</p>
                    <p class="text-xl font-bold text-red-600 mt-1">{{ $summary['overdue_count'] }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-clock text-red-600"></i></div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาเลข Invoice, ชื่อลูกค้า..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending" @selected(request('status')==='pending' )>รอชำระ</option>
                    <option value="partial" @selected(request('status')==='partial' )>ชำระบางส่วน</option>
                    <option value="paid" @selected(request('status')==='paid' )>ชำระแล้ว</option>
                    <option value="overdue" @selected(request('status')==='overdue' )>เกินกำหนด</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="overdue" value="1" @checked(request('overdue')) class="rounded">
                เกินกำหนดเท่านั้น
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>ค้นหา</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">เลข Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ลูกค้า</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ยอดรวม</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ชำระแล้ว</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">คงเหลือ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">ครบกำหนด</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">สถานะ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($receivables as $ar)
                @php
                $isOverdue = $ar->due_date && $ar->due_date < now() && $ar->status !== 'paid';
                    $statusColors = ['pending' => 'yellow', 'partial' => 'blue', 'paid' => 'green', 'overdue' => 'red'];
                    $statusLabels = ['pending' => 'รอชำระ', 'partial' => 'บางส่วน', 'paid' => 'ชำระแล้ว', 'overdue' => 'เกินกำหนด'];
                    $statusKey = $isOverdue ? 'overdue' : $ar->status;
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4"><a href="{{ route('accounts-receivable.show', $ar) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">{{ $ar->invoice_number }}</a></td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $ar->customer->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right text-sm">฿{{ number_format($ar->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm text-green-600">฿{{ number_format($ar->paid_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm font-semibold {{ $ar->balance > 0 ? 'text-orange-600' : 'text-green-600' }}">฿{{ number_format($ar->balance, 2) }}</td>
                        <td class="px-6 py-4 text-center text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ $ar->due_date ? $ar->due_date->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-4 text-center"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $statusColors[$statusKey] ?? 'gray' }}-100 text-{{ $statusColors[$statusKey] ?? 'gray' }}-800">{{ $statusLabels[$statusKey] ?? $ar->status }}</span></td>
                        <td class="px-6 py-4 text-center"><a href="{{ route('accounts-receivable.show', $ar) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">ไม่พบรายการ</td>
                    </tr>
                    @endforelse
            </tbody>
        </table>
        @if($receivables->hasPages())
        <div class="px-6 py-4 border-t">{{ $receivables->links() }}</div>
        @endif
    </div>
</div>
@endsection