@extends('layouts.app')

@section('title', 'รับสินค้า')
@section('page-title', 'รับสินค้า (Goods Receipts)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">รับสินค้า</h2>
            <p class="text-sm text-gray-500 mt-1">บันทึกการรับสินค้าจากใบสั่งซื้อ</p>
        </div>
        <a href="{{ route('goods-receipts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"><i class="fas fa-plus mr-2"></i>รับสินค้าใหม่</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาเลขที่ GR, PO, Invoice..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>ค้นหา</button>
            @if(request('search'))
            <a href="{{ route('goods-receipts.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">ล้าง</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">เลขที่ GR</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">เลขที่ PO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ซัพพลายเออร์</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">รายการ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ผู้รับ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">วันที่</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($goodsReceipts as $gr)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><a href="{{ route('goods-receipts.show', $gr) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">{{ $gr->gr_number }}</a></td>
                    <td class="px-6 py-4 text-sm">{{ $gr->purchaseOrder->po_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $gr->purchaseOrder->supplier->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $gr->items_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $gr->supplier_invoice ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $gr->receivedBy->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center text-sm text-gray-500">{{ $gr->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-center"><a href="{{ route('goods-receipts.show', $gr) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">ไม่พบรายการรับสินค้า</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($goodsReceipts->hasPages())
        <div class="px-6 py-4 border-t">{{ $goodsReceipts->links() }}</div>
        @endif
    </div>
</div>
@endsection