@extends('layouts.app')

@section('title', 'ใบสั่งซื้อ ' . $purchaseOrder->po_number)
@section('page-title', 'รายละเอียดใบสั่งซื้อ')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $purchaseOrder->po_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">ใบสั่งซื้อ</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php $color = \App\Models\PurchaseOrder::getStatusColor($purchaseOrder->status); $statuses = \App\Models\PurchaseOrder::getStatuses(); @endphp
            <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg"><i class="fas fa-print mr-1"></i>พิมพ์</a>
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">{{ $statuses[$purchaseOrder->status] ?? $purchaseOrder->status }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ซัพพลายเออร์</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ชื่อ:</span><span class="font-medium">{{ $purchaseOrder->supplier->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">โทร:</span><span>{{ $purchaseOrder->supplier->phone ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">อีเมล:</span><span>{{ $purchaseOrder->supplier->email ?? '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">มูลค่า</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">รวมก่อนส่วนลด:</span><span>฿{{ number_format($purchaseOrder->subtotal, 2) }}</span></div>
                @if($purchaseOrder->discount_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">ส่วนลด:</span><span class="text-red-600">-฿{{ number_format($purchaseOrder->discount_amount, 2) }}</span></div>
                @endif
                @if($purchaseOrder->tax_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">ภาษี:</span><span>฿{{ number_format($purchaseOrder->tax_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between border-t pt-2"><span class="font-semibold">รวมทั้งหมด:</span><span class="font-bold text-lg text-indigo-700">฿{{ number_format($purchaseOrder->total, 2) }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ข้อมูลเอกสาร</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">สาขา:</span><span>{{ $purchaseOrder->branch->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">สร้างโดย:</span><span>{{ $purchaseOrder->createdBy->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">วันที่สร้าง:</span><span>{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</span></div>
                @if($purchaseOrder->expected_date)
                <div class="flex justify-between"><span class="text-gray-500">วันรับสินค้า:</span><span>{{ $purchaseOrder->expected_date->format('d/m/Y') }}</span></div>
                @endif
                @if($purchaseOrder->approvedBy)
                <div class="flex justify-between"><span class="text-gray-500">อนุมัติโดย:</span><span>{{ $purchaseOrder->approvedBy->name }}</span></div>
                @endif
            </div>
        </div>
    </div>

    @if($purchaseOrder->notes)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <p class="text-sm text-yellow-700"><strong>หมายเหตุ:</strong> {{ $purchaseOrder->notes }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-list mr-2 text-indigo-500"></i>รายการสินค้า</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">รายการ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">สั่งซื้อ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">รับแล้ว</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ราคา/หน่วย</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">รวม</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($purchaseOrder->items as $i => $item)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $item->item_name }}</td>
                    <td class="px-6 py-3 text-center text-sm">{{ $item->quantity }}</td>
                    <td class="px-6 py-3 text-center text-sm">
                        <span class="{{ $item->received_quantity >= $item->quantity ? 'text-green-600 font-semibold' : 'text-orange-600' }}">{{ $item->received_quantity ?? 0 }}</span>
                    </td>
                    <td class="px-6 py-3 text-right text-sm">฿{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="5" class="px-6 py-3 text-right font-semibold">รวมทั้งหมด:</td>
                    <td class="px-6 py-3 text-right font-bold text-lg text-indigo-700">฿{{ number_format($purchaseOrder->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Action Buttons --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap gap-3">
            @if($purchaseOrder->canBeApproved())
            <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg"><i class="fas fa-check mr-1"></i>อนุมัติ</button>
            </form>
            @endif

            @if(in_array($purchaseOrder->status, [\App\Models\PurchaseOrder::STATUS_APPROVED, \App\Models\PurchaseOrder::STATUS_PARTIAL]))
            <a href="{{ route('goods-receipts.create', ['po_id' => $purchaseOrder->id]) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg"><i class="fas fa-box-open mr-1"></i>รับสินค้า</a>
            @endif

            @if($purchaseOrder->canBeCancelled())
            <button type="button" x-data="{ open: false }" @click="open = true" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg"><i class="fas fa-times mr-1"></i>ยกเลิก</button>
            @endif
        </div>
    </div>

    {{-- Goods Receipts --}}
    @if($purchaseOrder->goodsReceipts && $purchaseOrder->goodsReceipts->count() > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-box-open mr-2 text-green-500"></i>การรับสินค้า</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">เลขที่ GR</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จำนวนรายการ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">วันที่รับ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($purchaseOrder->goodsReceipts as $gr)
                <tr>
                    <td class="px-6 py-3 text-sm font-medium text-indigo-600">{{ $gr->gr_number }}</td>
                    <td class="px-6 py-3 text-center text-sm">{{ $gr->items->count() }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $gr->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-3 text-center"><a href="{{ route('goods-receipts.show', $gr) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if($purchaseOrder->canBeCancelled())
    <div x-data="{ showCancel: false }">
        <button @click="showCancel = true" class="hidden" id="cancelBtn"></button>
        <div x-show="showCancel" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-6 w-full max-w-md m-4">
                <h3 class="text-lg font-semibold mb-4">ยกเลิกใบสั่งซื้อ</h3>
                <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผล <span class="text-red-500">*</span></label>
                    <textarea name="cancel_reason" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="ระบุเหตุผลการยกเลิก..."></textarea>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="showCancel = false" class="px-4 py-2 border rounded-lg">ยกเลิก</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">ยืนยันยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection