@extends('superadmin.layout')

@section('title', 'ตั้งค่าการชำระเงิน')
@section('page-title', 'ตั้งค่าการชำระเงิน')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <form action="{{ route('superadmin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Bank Transfer -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-1 flex items-center">
                <i class="fas fa-university text-indigo-600 mr-2"></i>
                บัญชีธนาคาร
            </h3>
            <p class="text-sm text-gray-500 mb-5">ข้อมูลบัญชีธนาคารสำหรับรับชำระค่าแพ็กเกจ</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อธนาคาร</label>
                    <input type="text" name="payment_bank_name"
                        value="{{ old('payment_bank_name', $payment['payment_bank_name'] ?? '') }}"
                        placeholder="เช่น กสิกรไทย (KBank)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขที่บัญชี</label>
                    <input type="text" name="payment_account_number"
                        value="{{ old('payment_account_number', $payment['payment_account_number'] ?? '') }}"
                        placeholder="เช่น 123-4-56789-0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบัญชี</label>
                    <input type="text" name="payment_account_name"
                        value="{{ old('payment_account_name', $payment['payment_account_name'] ?? '') }}"
                        placeholder="เช่น บจก. ออลอินเซอร์วิส"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>
        </div>

        <!-- PromptPay -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-1 flex items-center">
                <i class="fas fa-qrcode text-blue-600 mr-2"></i>
                พร้อมเพย์ / QR Code
            </h3>
            <p class="text-sm text-gray-500 mb-5">ข้อมูล PromptPay สำหรับสแกนจ่าย</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเลขพร้อมเพย์</label>
                    <input type="text" name="payment_promptpay"
                        value="{{ old('payment_promptpay', $payment['payment_promptpay'] ?? '') }}"
                        placeholder="เบอร์โทร หรือ เลขบัตรประชาชน"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบัญชีพร้อมเพย์</label>
                    <input type="text" name="payment_promptpay_name"
                        value="{{ old('payment_promptpay_name', $payment['payment_promptpay_name'] ?? '') }}"
                        placeholder="ชื่อที่แสดงหลังสแกน"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL รูป QR Code (ถ้ามี)</label>
                    <input type="text" name="payment_qrcode_url"
                        value="{{ old('payment_qrcode_url', $payment['payment_qrcode_url'] ?? '') }}"
                        placeholder="https://example.com/qrcode.png"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @if(!empty($payment['payment_qrcode_url']))
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg inline-block">
                        <img src="{{ $payment['payment_qrcode_url'] }}" alt="QR Code" class="max-w-[200px] rounded">
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact & Note -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-1 flex items-center">
                <i class="fas fa-info-circle text-green-600 mr-2"></i>
                ข้อมูลเพิ่มเติม
            </h3>
            <p class="text-sm text-gray-500 mb-5">ข้อมูลติดต่อและหมายเหตุที่จะแสดงให้ร้านค้า</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LINE ID (สำหรับแจ้งชำระเงิน)</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 bg-green-50 border border-r-0 border-gray-300 rounded-l-lg text-green-600 text-sm">
                            <i class="fab fa-line"></i>
                        </span>
                        <input type="text" name="payment_line_id"
                            value="{{ old('payment_line_id', $payment['payment_line_id'] ?? '') }}"
                            placeholder="@allinservice"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุการชำระเงิน</label>
                    <textarea name="payment_note" rows="3"
                        placeholder="ข้อความแสดงให้ร้านค้าเห็น เช่น เงื่อนไขการชำระเงิน, ช่วงเวลาทำการ"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('payment_note', $payment['payment_note'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Preview -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-eye text-amber-600 mr-2"></i>
                ตัวอย่างที่ร้านค้าจะเห็น
            </h3>
            <div class="bg-amber-50 rounded-xl p-5 border border-amber-200">
                <p class="text-sm font-semibold text-gray-800 mb-2"><i class="fas fa-university text-indigo-500 mr-1"></i>ข้อมูลการชำระเงิน</p>
                <div class="space-y-1 text-xs text-gray-600" id="preview-content">
                    <p id="preview-bank" class="{{ empty($payment['payment_bank_name']) ? 'hidden' : '' }}">
                        ธนาคาร: <span class="font-medium text-gray-800">{{ $payment['payment_bank_name'] ?? '' }}</span>
                    </p>
                    <p id="preview-account" class="{{ empty($payment['payment_account_number']) ? 'hidden' : '' }}">
                        เลขบัญชี: <span class="font-medium text-gray-800">{{ $payment['payment_account_number'] ?? '' }}</span>
                    </p>
                    <p id="preview-name" class="{{ empty($payment['payment_account_name']) ? 'hidden' : '' }}">
                        ชื่อบัญชี: <span class="font-medium text-gray-800">{{ $payment['payment_account_name'] ?? '' }}</span>
                    </p>
                    <p id="preview-promptpay" class="{{ empty($payment['payment_promptpay']) ? 'hidden' : '' }}">
                        พร้อมเพย์: <span class="font-medium text-gray-800">{{ $payment['payment_promptpay'] ?? '' }}</span>
                        @if(!empty($payment['payment_promptpay_name']))
                        ({{ $payment['payment_promptpay_name'] }})
                        @endif
                    </p>
                    <p id="preview-line" class="{{ empty($payment['payment_line_id']) ? 'hidden' : '' }}">
                        <i class="fab fa-line text-green-500 mr-1"></i>LINE: <span class="font-medium text-gray-800">{{ $payment['payment_line_id'] ?? '' }}</span>
                    </p>
                    <p id="preview-note" class="mt-2 text-gray-500 {{ empty($payment['payment_note']) ? 'hidden' : '' }}">
                        * {{ $payment['payment_note'] ?? '' }}
                    </p>
                </div>
                @if(empty($payment))
                <p class="text-xs text-amber-600"><i class="fas fa-exclamation-triangle mr-1"></i>ยังไม่ได้ตั้งค่าข้อมูลการชำระเงิน</p>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end">
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center font-medium">
                <i class="fas fa-save mr-2"></i>
                บันทึกการตั้งค่า
            </button>
        </div>
    </form>
</div>
@endsection