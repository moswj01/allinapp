@extends('superadmin.layout')

@section('title', 'จัดการออเดอร์จากร้านค้า')
@section('page-title', 'ออเดอร์จากร้านค้า')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-400">
            <p class="text-xs font-medium text-gray-500 uppercase">รอยืนยัน</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-400">
            <p class="text-xs font-medium text-gray-500 uppercase">ยืนยันแล้ว</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-400">
            <p class="text-xs font-medium text-gray-500 uppercase">จัดส่งแล้ว</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['shipped'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-400">
            <p class="text-xs font-medium text-gray-500 uppercase">ยอดขาย (รับแล้ว)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">฿{{ number_format($stats['total_revenue'], 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="เลขที่ออเดอร์, ชื่อร้านค้า..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">ร้านค้า</label>
                <select name="tenant_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">ทั้งหมด</option>
                    @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">สถานะ</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">ทั้งหมด</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">
                <i class="fas fa-filter mr-1"></i>กรอง
            </button>
            <a href="{{ route('superadmin.tenant-orders.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">ล้าง</a>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ร้านค้า</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">รายการ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ยอดรวม</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 {{ $order->status === 'pending' ? 'bg-yellow-50' : '' }}">
                        <td class="px-6 py-4">
                            <a href="{{ route('superadmin.tenant-orders.show', $order) }}" class="text-indigo-600 hover:underline font-medium">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-800">{{ $order->tenant->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $order->branch->name ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-center text-sm">{{ $order->items->count() }}</td>
                        <td class="px-6 py-4 text-right font-medium">฿{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @php $color = \App\Models\TenantOrder::getStatusColor($order->status); @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ $statuses[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('superadmin.tenant-orders.show', $order) }}" class="text-gray-400 hover:text-indigo-600">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                            <p>ยังไม่มีออเดอร์</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection