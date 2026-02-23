@extends('layouts.app')

@section('title', 'แก้ไข ' . $quotation->quotation_number)
@section('page-title', 'แก้ไขใบเสนอราคา')

@section('content')
<div x-data="quotationEditForm()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">แก้ไขใบเสนอราคา {{ $quotation->quotation_number }}</h2>
        </div>
        <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    <form action="{{ route('quotations.update', $quotation) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-user text-indigo-600 mr-2"></i>ข้อมูลลูกค้า</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลือกลูกค้า</label>
                    <select name="customer_id" x-model="customerId" @change="fillCustomer()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- ลูกค้าทั่วไป --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อลูกค้า <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" x-model="customerName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร</label>
                    <input type="text" name="customer_phone" x-model="customerPhone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                    <input type="email" name="customer_email" x-model="customerEmail" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หัวข้อ</label>
                    <input type="text" name="subject" value="{{ old('subject', $quotation->subject) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">วันหมดอายุ</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-list text-indigo-600 mr-2"></i>รายการสินค้า</h3>
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-3 mb-3 items-end">
                    <div class="col-span-4">
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
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm font-medium" x-text="'฿' + (item.quantity * item.unit_price).toLocaleString()"></div>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">คำอธิบาย</label>
                        <div class="flex gap-2">
                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 px-2" x-show="items.length > 1"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </template>
            <button type="button" @click="addItem()" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium"><i class="fas fa-plus mr-1"></i>เพิ่มรายการ</button>

            <div class="mt-6 border-t pt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ส่วนลด</label>
                    <div class="flex gap-2">
                        <select name="discount_type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="" @selected(!$quotation->discount_type)>ไม่มี</option>
                            <option value="fixed" @selected($quotation->discount_type === 'fixed')>บาท</option>
                            <option value="percent" @selected($quotation->discount_type === 'percent')>%</option>
                        </select>
                        <input type="number" name="discount_value" value="{{ old('discount_value', $quotation->discount_value) }}" min="0" step="0.01" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาษี (%)</label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', $quotation->tax_rate) }}" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงื่อนไข</label>
                    <textarea name="terms" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('terms', $quotation->terms) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('notes', $quotation->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" onsubmit="return confirm('ต้องการลบใบเสนอราคานี้?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash mr-1"></i>ลบใบเสนอราคา</button>
            </form>
            <div class="flex items-center space-x-3">
                <a href="{{ route('quotations.show', $quotation) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center"><i class="fas fa-save mr-2"></i>บันทึก</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function quotationEditForm() {
        return {
            customerId: '{{ $quotation->customer_id ?? '
            ' }}',
            customerName: '{{ $quotation->customer_name }}',
            customerPhone: '{{ $quotation->customer_phone ?? '
            ' }}',
            customerEmail: '{{ $quotation->customer_email ?? '
            ' }}',
            items: @json($quotation - > items - > map(fn($i) => ['item_name' => $i - > item_name, 'quantity' => $i - > quantity, 'unit_price' => $i - > unit_price, 'description' => $i - > description ?? '', 'product_id' => $i - > itemable_id]) - > values()),
            customers: @json($customers),
            fillCustomer() {
                const c = this.customers.find(c => c.id == this.customerId);
                if (c) {
                    this.customerName = c.name;
                    this.customerPhone = c.phone || '';
                    this.customerEmail = c.email || '';
                }
            },
            addItem() {
                this.items.push({
                    item_name: '',
                    quantity: 1,
                    unit_price: 0,
                    description: ''
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