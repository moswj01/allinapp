@extends('layouts.app')

@section('title', 'สร้างใบตรวจนับสต๊อก')
@section('page-title', 'สร้างใบตรวจนับสต๊อก')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">สร้างใบตรวจนับสต๊อก</h2>
            <p class="text-gray-500">เลือกประเภทการตรวจนับ ระบบจะดึงรายการสินค้าอัตโนมัติ</p>
        </div>
        <a href="{{ route('stock-takes.index') }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    <form action="{{ route('stock-takes.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ type: '{{ old('type', 'full') }}' }">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-clipboard-list text-indigo-600 mr-2"></i>ตั้งค่าการตรวจนับ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ประเภท <span class="text-red-500">*</span></label>
                    <select name="type" x-model="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="type === 'category'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                    <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-700"><i class="fas fa-info-circle mr-1"></i>ระบบจะดึงรายการสินค้าทั้งหมดในสาขาของคุณ พร้อมจำนวนในระบบอัตโนมัติ</p>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('stock-takes.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center"><i class="fas fa-save mr-2"></i>สร้างใบตรวจนับ</button>
        </div>
    </form>
</div>
@endsection