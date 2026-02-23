@extends('layouts.app')

@section('title', 'เพิ่มซัพพลายเออร์')
@section('page-title', 'เพิ่มซัพพลายเออร์')

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">เพิ่มซัพพลายเออร์ใหม่</h2>
                <p class="text-gray-500">กรอกข้อมูลซัพพลายเออร์</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="text-indigo-600 hover:text-indigo-800">
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
                <i class="fas fa-truck text-indigo-600 mr-2"></i>
                ข้อมูลพื้นฐาน
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รหัสซัพพลายเออร์</label>
                    <input type="text" name="code" value="{{ old('code') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="SUP-0001 (สร้างอัตโนมัติถ้าไม่กรอก)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อซัพพลายเออร์ <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อบริษัท / ร้านค้า">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ผู้ติดต่อ</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อผู้ติดต่อ">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขประจำตัวผู้เสียภาษี</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="เลข 13 หลัก">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เครดิต (วัน)</label>
                    <input type="number" name="credit_days" value="{{ old('credit_days', 30) }}" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="30">
                    <p class="text-xs text-gray-500 mt-1">จำนวนวันเครดิตที่ได้จากซัพพลายเออร์</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่</label>
                    <textarea name="address" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ที่อยู่">{{ old('address') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked
                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="text-sm font-medium text-gray-700">เปิดใช้งาน</span>
            </label>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('suppliers.index') }}"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                ยกเลิก
            </a>
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                บันทึก
            </button>
        </div>
    </form>
</div>
@endsection