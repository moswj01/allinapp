@extends('superadmin.layout')

@section('title', isset($plan) ? 'แก้ไขแพ็กเกจ' : 'สร้างแพ็กเกจใหม่')
@section('page-title', isset($plan) ? 'แก้ไขแพ็กเกจ: ' . $plan->name : 'สร้างแพ็กเกจใหม่')

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ isset($plan) ? route('superadmin.plans.update', $plan) : route('superadmin.plans.store') }}" method="POST" class="space-y-6">
        @csrf
        @if(isset($plan)) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-box-open text-indigo-600 mr-2"></i>ข้อมูลแพ็กเกจ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อแพ็กเกจ <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono">
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('description', $plan->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-coins text-amber-600 mr-2"></i>ราคา</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ราคา/เดือน (฿)</label>
                    <input type="number" name="price" value="{{ old('price', $plan->price ?? 0) }}" min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ราคา/ปี (฿)</label>
                    <input type="number" name="yearly_price" value="{{ old('yearly_price', $plan->yearly_price ?? 0) }}" min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ทดลองใช้ (วัน)</label>
                    <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 14) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-ruler text-purple-600 mr-2"></i>ขีดจำกัด <span class="text-xs text-gray-400">(-1 = ไม่จำกัด)</span></h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ผู้ใช้สูงสุด</label>
                    <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users ?? 5) }}" min="-1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สาขาสูงสุด</label>
                    <input type="number" name="max_branches" value="{{ old('max_branches', $plan->max_branches ?? 1) }}" min="-1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สินค้าสูงสุด</label>
                    <input type="number" name="max_products" value="{{ old('max_products', $plan->max_products ?? 500) }}" min="-1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">งานซ่อมสูงสุด</label>
                    <input type="number" name="max_repairs" value="{{ old('max_repairs', $plan->max_repairs ?? -1) }}" min="-1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-puzzle-piece text-green-600 mr-2"></i>ฟีเจอร์ที่รวม</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @php $currentFeatures = old('features', isset($plan) ? ($plan->features ?? []) : []); @endphp
                @foreach($features as $key => $feature)
                <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                    <input type="checkbox" name="features[]" value="{{ $key }}"
                        {{ in_array($key, $currentFeatures) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                    <i class="{{ $feature['icon'] }} text-gray-400 w-4"></i>
                    <span class="text-sm text-gray-700">{{ $feature['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <label class="block text-sm font-medium text-gray-700">ลำดับการแสดง</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 text-green-600 rounded">
                    <span class="text-sm font-medium text-gray-700">เปิดใช้งาน</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('superadmin.plans.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>ยกเลิก
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                <i class="fas fa-save mr-2"></i>{{ isset($plan) ? 'บันทึก' : 'สร้างแพ็กเกจ' }}
            </button>
        </div>
    </form>
</div>
@endsection