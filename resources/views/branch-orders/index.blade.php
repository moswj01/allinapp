@extends('layouts.app')

@section('title', 'ใบสั่งซื้อจากสาขาใหญ่')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ใบสั่งซื้อจากสาขาใหญ่</h1>
            <p class="text-sm text-gray-500 mt-1">สร้างใบสั่งซื้อสินค้าจากสาขาใหญ่มายังสาขาของคุณ</p>
        </div>
        @php
        $branch = Auth::user()->branch;
        @endphp
        @if(!$branch || !$branch->is_main)
        <a href="{{ route('branch-orders.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i>
            สร้างใบสั่งซื้อ
        </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="เลขที่ใบสั่ง, ชื่อสาขา..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">สถานะ</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                <i class="fas fa-search mr-1"></i>ค้นหา
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('branch-orders.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">ล้าง</a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขาที่สั่ง</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จำนวนรายการ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">มูลค่ารวม</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่สร้าง</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('branch-orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $order->branch->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $order->items_count }} รายการ</td>
                    <td class="px-6 py-4 text-sm font-medium">฿{{ number_format($order->total, 0) }}</td>
                    <td class="px-6 py-4">
                        @php $color = \App\Models\BranchOrder::getStatusColor($order->status); @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $statuses[$order->status] ?? $order->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('branch-orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            ดูรายละเอียด <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-3 block"></i>
                        ยังไม่มีใบสั่งซื้อ
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($orders->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection