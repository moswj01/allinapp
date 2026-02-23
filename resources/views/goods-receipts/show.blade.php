@extends('layouts.app')

@section('title', 'รับสินค้า ' . $goodsReceipt->gr_number)
@section('page-title', 'รายละเอียดการรับสินค้า')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('goods-receipts.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $goodsReceipt->gr_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">รับสินค้า</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ใบสั่งซื้อ</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">เลขที่ PO:</span>
                    <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ $goodsReceipt->purchaseOrder->po_number ?? '-' }}</a>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">ซัพพลายเออร์:</span><span>{{ $goodsReceipt->purchaseOrder->supplier->name ?? '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ข้อมูลการรับ</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">สาขา:</span><span>{{ $goodsReceipt->branch->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">ผู้รับ:</span><span>{{ $goodsReceipt->receivedBy->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">วันที่รับ:</span><span>{{ $goodsReceipt->created_at->format('d/m/Y H:i') }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">อ้างอิง</h3>
            <div class="space-y-2 text-sm">
                @if($goodsReceipt->supplier_invoice)
                <div class="flex justify-between"><span class="text-gray-500">Invoice:</span><span class="font-medium">{{ $goodsReceipt->supplier_invoice }}</span></div>
                @endif
                @if($goodsReceipt->notes)
                <div><span class="text-gray-500">หมายเหตุ:</span>
                    <p class="mt-1">{{ $goodsReceipt->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-box-open mr-2 text-green-500"></i>รายการที่รับ</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">รายการ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จำนวนที่รับ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ราคาต้นทุน/หน่วย</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">รวม</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $totalCost = 0; @endphp
                @foreach($goodsReceipt->items as $i => $item)
                @php $itemTotal = $item->quantity_received * $item->unit_cost; $totalCost += $itemTotal; @endphp
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $item->itemable->name ?? 'สินค้า #' . $item->itemable_id }}</td>
                    <td class="px-6 py-3 text-center text-sm font-semibold text-green-600">{{ $item->quantity_received }}</td>
                    <td class="px-6 py-3 text-right text-sm">฿{{ number_format($item->unit_cost, 2) }}</td>
                    <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($itemTotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right font-semibold">รวมมูลค่า:</td>
                    <td class="px-6 py-3 text-right font-bold text-lg text-indigo-700">฿{{ number_format($totalCost, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection