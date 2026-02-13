@extends('layouts.app')

@section('title', 'การตั้งค่า')
@section('page-title', 'การตั้งค่า')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">การตั้งค่าระบบ</h2>
            <p class="text-gray-500">จัดการข้อมูลบริษัท ค่าเริ่มต้น และการเชื่อมต่อ</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($groups as $groupKey => $groupLabel)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                @if($groupKey === 'company')
                <i class="fas fa-building text-indigo-600 mr-2"></i>
                @elseif($groupKey === 'defaults')
                <i class="fas fa-sliders-h text-green-600 mr-2"></i>
                @elseif($groupKey === 'receipt')
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                @elseif($groupKey === 'integrations')
                <i class="fas fa-plug text-purple-600 mr-2"></i>
                @endif
                {{ $groupLabel }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if(isset($settings[$groupKey]))
                @foreach($settings[$groupKey] as $setting)
                <div class="{{ in_array($setting->key, ['receipt_header', 'receipt_footer', 'quotation_terms', 'company_address']) ? 'md:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $settingLabels[$setting->key] ?? str_replace('_', ' ', ucfirst($setting->key)) }}
                    </label>

                    @if($setting->type === 'boolean')
                    <select name="settings[{{ $setting->key }}]"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="1" {{ $setting->value ? 'selected' : '' }}>เปิด</option>
                        <option value="0" {{ !$setting->value ? 'selected' : '' }}>ปิด</option>
                    </select>
                    @elseif(in_array($setting->key, ['receipt_header', 'receipt_footer', 'quotation_terms', 'company_address']))
                    <textarea name="settings[{{ $setting->key }}]" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old("settings.{$setting->key}", $setting->value) }}</textarea>
                    @elseif($setting->type === 'integer' || $setting->type === 'float')
                    <input type="number" name="settings[{{ $setting->key }}]"
                        value="{{ old("settings.{$setting->key}", $setting->value) }}"
                        step="{{ $setting->type === 'float' ? '0.01' : '1' }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @else
                    <input type="text" name="settings[{{ $setting->key }}]"
                        value="{{ old("settings.{$setting->key}", $setting->value) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @endif
                </div>
                @endforeach
                @else
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-400">ยังไม่มีการตั้งค่าในหมวดนี้</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <div class="flex items-center justify-end">
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                บันทึกการตั้งค่า
            </button>
        </div>
    </form>
</div>

@php
$settingLabels = [
'company_name' => 'ชื่อบริษัท',
'company_address' => 'ที่อยู่',
'company_phone' => 'เบอร์โทร',
'company_email' => 'อีเมล',
'company_tax_id' => 'เลขผู้เสียภาษี',
'company_logo' => 'โลโก้ (URL)',
'default_tax_rate' => 'อัตราภาษีเริ่มต้น (%)',
'default_warranty_days' => 'วันรับประกันเริ่มต้น',
'low_stock_threshold' => 'แจ้งเตือนสต๊อกต่ำ',
'receipt_header' => 'หัวใบเสร็จ',
'receipt_footer' => 'ท้ายใบเสร็จ',
'quotation_terms' => 'เงื่อนไขใบเสนอราคา',
'line_notify_token' => 'LINE Notify Token',
'sms_api_key' => 'SMS API Key',
];
@endphp
@endsection