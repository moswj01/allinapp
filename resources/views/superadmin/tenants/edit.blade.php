@extends('superadmin.layout')

@section('title', 'แก้ไขร้านค้า')
@section('page-title', 'แก้ไขร้านค้า: ' . $tenant->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ route('superadmin.tenants.update', $tenant->id) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-store text-indigo-600 mr-2"></i>ข้อมูลร้านค้า</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อร้านค้า</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono">
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">โทรศัพท์</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('address', $tenant->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขประจำตัวผู้เสียภาษี</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id', $tenant->tax_id) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">แพ็กเกจ</label>
                    <select name="plan_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ $tenant->plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} ({{ $plan->getFormattedPrice() }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="trial" {{ $tenant->status === 'trial' ? 'selected' : '' }}>ทดลองใช้ (Trial)</option>
                        <option value="active" {{ $tenant->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $tenant->status === 'suspended' ? 'selected' : '' }}>ระงับ</option>
                        <option value="cancelled" {{ $tenant->status === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผลการระงับ</label>
                    <textarea name="suspension_reason" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('suspension_reason', $tenant->suspension_reason) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>ยกเลิก
            </a>
            <div class="flex items-center gap-3">
                <form action="{{ route('superadmin.tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('ต้องการลบร้านค้านี้?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm text-red-600 hover:text-red-800"><i class="fas fa-trash mr-1"></i>ลบ</button>
                </form>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    <i class="fas fa-save mr-2"></i>บันทึก
                </button>
            </div>
        </div>
    </form>
</div>
@endsection