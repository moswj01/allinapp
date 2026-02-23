@extends('layouts.app')

@section('title', 'รับสินค้า')
@section('page-title', 'รับสินค้าจากใบสั่งซื้อ')

@section('content')
<div x-data="grForm()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">รับสินค้าใหม่</h2>
            <p class="text-gray-500">เลือกใบสั่งซื้อและระบุจำนวนที่รับ</p>
        </div>
        <a href="{{ route('goods-receipts.index') }}" class="text-indigo-600 hover:text-indigo-800"><i
                class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    <form action="{{ route('goods-receipts.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}
                </li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i
                    class="fas fa-file-alt text-indigo-600 mr-2"></i>เลือกใบสั่งซื้อ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ใบสั่งซื้อ <span
                            class="text-red-500">*</span></label>
                    <select name="purchase_order_id" x-model="selectedPo" @change="loadPoItems()" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือก PO --</option>
                        @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier->name ?? '' }}
                            (฿{{ number_format($po->total, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลข Invoice ซัพพลายเออร์</label>
                    <input type="text" name="supplier_invoice"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="เลข Invoice (ถ้ามี)">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6" x-show="poItems.length > 0" x-cloak>
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i
                    class="fas fa-list text-indigo-600 mr-2"></i>รายการรับสินค้า</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">รายการ</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">สั่งซื้อ</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">รับแล้ว</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">คงเหลือ</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">จำนวนที่รับ</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(item, index) in poItems" :key="index">
                        <tr>
                            <td class="px-4 py-2 text-sm font-medium" x-text="item.item_name"></td>
                            <td class="px-4 py-2 text-center text-sm" x-text="item.quantity"></td>
                            <td class="px-4 py-2 text-center text-sm" x-text="item.received_quantity"></td>
                            <td class="px-4 py-2 text-center text-sm font-semibold text-orange-600"
                                x-text="item.quantity - item.received_quantity"></td>
                            <td class="px-4 py-2 text-center">
                                <input type="hidden" :name="`items[${index}][purchase_order_item_id]`" :value="item.id">
                                <input type="number" :name="`items[${index}][quantity_received]`"
                                    x-model.number="item.qty_to_receive" min="0"
                                    :max="item.quantity - item.received_quantity"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-center focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" :name="`items[${index}][notes]`"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500"
                                    placeholder="หมายเหตุ">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุทั่วไป</label>
            <textarea name="notes" rows="2"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                placeholder="หมายเหตุเพิ่มเติม..."></textarea>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('goods-receipts.index') }}"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</a>
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center"><i
                    class="fas fa-save mr-2"></i>บันทึกการรับสินค้า</button>
        </div>
    </form>
</div>

@php
$grSelectedPoId = $selectedPo->id ?? '';
$grPoItems = $selectedPo
? $selectedPo->items->map(fn($i) => [
'id' => $i->id,
'item_name' => $i->item_name,
'quantity' => $i->quantity,
'received_quantity' => $i->received_quantity,
'qty_to_receive' => $i->quantity - $i->received_quantity,
])
: [];
$grAllPos = $purchaseOrders->mapWithKeys(fn($po) => [
$po->id => $po->items->map(fn($i) => [
'id' => $i->id,
'item_name' => $i->item_name,
'quantity' => $i->quantity,
'received_quantity' => $i->received_quantity,
'qty_to_receive' => $i->quantity - $i->received_quantity,
]),
]);
@endphp

@push('scripts')
<script>
    function grForm() {
        return {
            selectedPo: '{{ $grSelectedPoId }}',
            poItems: @json($grPoItems),
            allPos: @json($grAllPos),
            loadPoItems() {
                this.poItems = this.allPos[this.selectedPo] || [];
            }
        }
    }
</script>
@endpush
@endsection