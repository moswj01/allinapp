@extends('layouts.app')

@section('title', 'สร้างใบโอนสต๊อก')
@section('page-title', 'สร้างใบโอนสต๊อก')

@section('content')
<div x-data="transferForm()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">สร้างใบโอนสต๊อกใหม่</h2>
            <p class="text-gray-500">เลือกสาขาต้นทาง/ปลายทาง และรายการสินค้า</p>
        </div>
        <a href="{{ route('stock-transfers.index') }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    <form action="{{ route('stock-transfers.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-exchange-alt text-indigo-600 mr-2"></i>สาขา</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สาขาต้นทาง <span class="text-red-500">*</span></label>
                    <select name="from_branch_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกสาขาต้นทาง --</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('from_branch_id', Auth::user()->branch_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สาขาปลายทาง <span class="text-red-500">*</span></label>
                    <select name="to_branch_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกสาขาปลายทาง --</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('to_branch_id')==$b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-list text-indigo-600 mr-2"></i>รายการสินค้า</h3>
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-3 mb-3 items-end">
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">สินค้า *</label>
                        <select :name="`items[${index}][product_id]`" x-model="item.product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- เลือกสินค้า --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->sku ? $p->sku.' - ' : '' }}{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">จำนวน *</label>
                        <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1" x-show="index === 0">หมายเหตุ</label>
                        <input type="text" :name="`items[${index}][notes]`" x-model="item.notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" placeholder="หมายเหตุ">
                    </div>
                    <div class="col-span-1">
                        <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600" x-show="items.length > 1"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </template>
            <button type="button" @click="addItem()" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium"><i class="fas fa-plus mr-1"></i>เพิ่มรายการ</button>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
            <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('stock-transfers.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center"><i class="fas fa-save mr-2"></i>บันทึก</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function transferForm() {
        return {
            items: [{
                product_id: '',
                quantity: 1,
                notes: ''
            }],
            addItem() {
                this.items.push({
                    product_id: '',
                    quantity: 1,
                    notes: ''
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