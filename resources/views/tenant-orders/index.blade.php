@extends('layouts.app')

@section('title', 'สั่งสินค้าจากร้านกลาง')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">สั่งสินค้าจากร้านกลาง</h1>
            <p class="text-sm text-gray-500 mt-1">รายการคำสั่งซื้อสินค้าจาก All In Mobile</p>
        </div>
        <a href="{{ route('tenant-orders.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium flex items-center gap-2">
            <i class="fas fa-shopping-cart"></i>
            สั่งสินค้า
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="เลขที่ออเดอร์..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">สถานะ</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700">
                <i class="fas fa-filter mr-1"></i>กรอง
            </button>
            <a href="{{ route('tenant-orders.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">ล้าง</a>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่สั่ง</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">รายการ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ยอดรวม</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('tenant-orders.show', $order) }}" class="text-indigo-600 hover:underline font-medium">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-center text-sm">{{ $order->items->count() }} รายการ</td>
                        <td class="px-6 py-4 text-right font-medium">฿{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @php $color = \App\Models\TenantOrder::getStatusColor($order->status); @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ $statuses[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('tenant-orders.show', $order) }}" class="text-gray-400 hover:text-indigo-600">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-shopping-bag text-4xl mb-3 text-gray-300"></i>
                            <p>ยังไม่มีคำสั่งซื้อ</p>
                            <a href="{{ route('tenant-orders.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline">สั่งสินค้าเลย</a>
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