@extends('layouts.app')

@section('title', 'เพิ่มสินค้าใหม่')
@section('page-title', 'เพิ่มสินค้าใหม่')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">เพิ่มสินค้าใหม่</h2>
                <p class="text-gray-500">กรอกข้อมูลสินค้าสำหรับขาย</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                ข้อมูลพื้นฐาน
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รหัสสินค้า (SKU) <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="PRD-001">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">บาร์โค้ด</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="8850000000000">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อสินค้า <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อสินค้า">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="รายละเอียดสินค้า...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                    <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ซัพพลายเออร์</label>
                    <select name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกซัพพลายเออร์ --</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หน่วย</label>
                    <input type="text" name="unit" value="{{ old('unit', 'ชิ้น') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชิ้น, กล่อง, ตัว">
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-tags text-green-600 mr-2"></i>
                ราคา
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ต้นทุน <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="cost" value="{{ old('cost', 0) }}" required min="0" step="0.01"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ราคาขายปลีก <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="price_retail" value="{{ old('price_retail', 0) }}" required min="0" step="0.01"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ราคาส่ง</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="price_wholesale" value="{{ old('price_wholesale') }}" min="0" step="0.01"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ราคาช่าง</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="price_technician" value="{{ old('price_technician') }}" min="0" step="0.01"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ราคาออนไลน์</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="price_online" value="{{ old('price_online') }}" min="0" step="0.01"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-warehouse text-purple-600 mr-2"></i>
                สต๊อก
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนสต๊อกเริ่มต้น</label>
                    <input type="number" name="initial_stock" value="{{ old('initial_stock', 0) }}" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">สต๊อกเริ่มต้นสำหรับสาขาปัจจุบัน</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จุดสั่งซื้อใหม่ (Reorder Point)</label>
                    <input type="number" name="reorder_point" value="{{ old('reorder_point', 5) }}" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">แจ้งเตือนเมื่อสต๊อกต่ำกว่านี้</p>
                </div>
            </div>
        </div>

        <!-- Image -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-image text-blue-600 mr-2"></i>
                รูปภาพ
            </h3>

            <div x-data="{ imagePreview: null }">
                <input type="file" name="image" accept="image/*"
                    @change="imagePreview = URL.createObjectURL($event.target.files[0])"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-500 mt-1">รองรับ JPG, PNG, GIF ขนาดไม่เกิน 2MB</p>

                <div x-show="imagePreview" class="mt-4">
                    <img :src="imagePreview" class="h-32 w-32 object-cover rounded-lg">
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked
                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="text-sm font-medium text-gray-700">เปิดใช้งานสินค้า</span>
            </label>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('products.index') }}"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                ยกเลิก
            </a>
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                บันทึกสินค้า
            </button>
        </div>
    </form>
</div>
@endsection