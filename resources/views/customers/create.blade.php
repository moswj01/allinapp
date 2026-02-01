@extends('layouts.app')

@section('title', 'เพิ่มลูกค้าใหม่')
@section('page-title', 'เพิ่มลูกค้าใหม่')

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">เพิ่มลูกค้าใหม่</h2>
                <p class="text-gray-500">กรอกข้อมูลลูกค้า</p>
            </div>
            <a href="{{ route('customers.index') }}" class="text-indigo-600 hover:text-indigo-800">
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
                <i class="fas fa-user text-indigo-600 mr-2"></i>
                ข้อมูลพื้นฐาน
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อลูกค้า <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อ-นามสกุล หรือชื่อร้าน">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="08X-XXX-XXXX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="email@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LINE ID</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-green-500"><i class="fab fa-line"></i></span>
                        <input type="text" name="line_id" value="{{ old('line_id') }}"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="@lineid">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-blue-500"><i class="fab fa-facebook"></i></span>
                        <input type="text" name="facebook_id" value="{{ old('facebook_id') }}"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="facebook.com/...">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทลูกค้า</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="retail" {{ old('type') === 'retail' ? 'selected' : '' }}>ลูกค้าทั่วไป (ปลีก)</option>
                        <option value="wholesale" {{ old('type') === 'wholesale' ? 'selected' : '' }}>ลูกค้าส่ง (ขายส่ง)</option>
                        <option value="technician" {{ old('type') === 'technician' ? 'selected' : '' }}>ช่าง (ราคาช่าง)</option>
                        <option value="vip" {{ old('type') === 'vip' ? 'selected' : '' }}>VIP</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Address & Tax -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                ที่อยู่ / ข้อมูลภาษี
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่</label>
                    <textarea name="address" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ที่อยู่สำหรับจัดส่งหรือออกใบเสร็จ">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบริษัท/ร้านค้า</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อบริษัท (สำหรับออกใบกำกับภาษี)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขประจำตัวผู้เสียภาษี</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="เลข 13 หลัก">
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                หมายเหตุ
            </h3>

            <textarea name="notes" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                placeholder="บันทึกเพิ่มเติมเกี่ยวกับลูกค้า...">{{ old('notes') }}</textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('customers.index') }}"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                ยกเลิก
            </a>
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                บันทึกลูกค้า
            </button>
        </div>
    </form>
</div>
@endsection