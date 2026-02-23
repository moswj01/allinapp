@extends('layouts.app')

@section('title', 'ใบสั่งซื้อ')
@section('page-title', 'ใบสั่งซื้อ (Purchase Orders)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ใบสั่งซื้อ</h2>
            <p class="text-sm text-gray-500 mt-1">จัดการใบสั่งซื้อจากซัพพลายเออร์</p>
        </div>
        <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"><i class="fas fa-plus mr-2"></i>สร้างใบสั่งซื้อ</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาเลขที่ PO, ซัพพลายเออร์..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกสถานะ</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>ค้นหา</button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">ล้าง</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">เลขที่ PO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ซัพพลายเออร์</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">รายการ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">มูลค่า</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">สถานะ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">วันที่</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($purchaseOrders as $po)
                @php $color = \App\Models\PurchaseOrder::getStatusColor($po->status); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">{{ $po->po_number }}</a></td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $po->supplier->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $po->items_count }}</td>
                    <td class="px-6 py-4 text-right text-sm font-medium">฿{{ number_format($po->total, 2) }}</td>
                    <td class="px-6 py-4 text-center"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">{{ $statuses[$po->status] ?? $po->status }}</span></td>
                    <td class="px-6 py-4 text-center text-sm text-gray-500">{{ $po->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">ไม่พบใบสั่งซื้อ</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($purchaseOrders->hasPages())
        <div class="px-6 py-4 border-t">{{ $purchaseOrders->links() }}</div>
        @endif
    </div>
</div>
@endsection