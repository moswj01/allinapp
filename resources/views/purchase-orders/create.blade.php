@extends('layouts.app')

@section('title', 'สร้างใบสั่งซื้อ')
@section('page-title', 'สร้างใบสั่งซื้อ')

@section('content')
<div x-data="poForm()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">สร้างใบสั่งซื้อใหม่</h2>
            <p class="text-gray-500">กรอกข้อมูลใบสั่งซื้อจากซัพพลายเออร์</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    <form action="{{ route('purchase-orders.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-truck text-indigo-600 mr-2"></i>ข้อมูลซัพพลายเออร์</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ซัพพลายเออร์ <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกซัพพลายเออร์ --</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">วันที่คาดว่าจะได้รับ</label>
                    <input type="date" name="expected_date" value="{{ old('expected_date') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-list text-indigo-600 mr-2"></i>รายการสินค้า</h3>
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-3 mb-3 items-end">
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">สินค้า</label>
                        <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="fillProduct(index)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- สินค้าที่กำหนดเอง --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-cost="{{ $p->cost ?? 0 }}">{{ $p->sku ? $p->sku.' - ' : '' }}{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">ชื่อรายการ *</label>
                        <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">จำนวน *</label>
                        <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">ราคาต่อหน่วย *</label>
                        <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">รวม</label>
                        <div class="flex gap-2 items-center">
                            <div class="flex-1 px-3 py-2 bg-gray-50 rounded-lg text-sm font-medium" x-text="'฿' + (item.quantity * item.unit_price).toLocaleString()"></div>
                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600" x-show="items.length > 1"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </template>
            <button type="button" @click="addItem()" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium"><i class="fas fa-plus mr-1"></i>เพิ่มรายการ</button>

            <div class="mt-6 border-t pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ส่วนลดรวม (฿)</label>
                    <input type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาษี (฿)</label>
                    <input type="number" name="tax_amount" value="{{ old('tax_amount', 0) }}" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงื่อนไข</label>
                    <textarea name="terms" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="เงื่อนไขการชำระเงิน...">{{ old('terms') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('purchase-orders.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center"><i class="fas fa-save mr-2"></i>บันทึก</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function poForm() {
        return {
            items: [{
                product_id: '',
                item_name: '',
                quantity: 1,
                unit_price: 0
            }],
            products: @json($products),
            fillProduct(index) {
                const p = this.products.find(p => p.id == this.items[index].product_id);
                if (p) {
                    this.items[index].item_name = p.name;
                    this.items[index].unit_price = p.cost || 0;
                }
            },
            addItem() {
                this.items.push({
                    product_id: '',
                    item_name: '',
                    quantity: 1,
                    unit_price: 0
                });
            },
            removeItem(i) {
                this.items.splice(i, 1);
            }
        }
    }
</script>
@endpush
@endsection