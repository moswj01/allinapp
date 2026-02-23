@extends('layouts.app')

@section('title', 'ปิดยอดวันนี้')
@section('page-title', 'ปิดยอดประจำวัน')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ปิดยอดประจำวัน</h2>
            <p class="text-gray-500">วันที่ {{ now()->format('d/m/Y') }} - ระบบคำนวณยอดอัตโนมัติ</p>
        </div>
        <a href="{{ route('finance.daily-settlement.index') }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
    </div>

    <form action="{{ route('finance.daily-settlement.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="list-disc list-inside text-red-600 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <input type="hidden" name="settlement_date" value="{{ $autoData['today'] }}">

        {{-- Auto-calculated Sales --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-cash-register text-indigo-600 mr-2"></i>ยอดขายวันนี้ (คำนวณอัตโนมัติ)</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ขายเงินสด</label>
                    <input type="number" name="cash_sales" value="{{ old('cash_sales', $autoData['cashSales']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ขายโอน</label>
                    <input type="number" name="transfer_sales" value="{{ old('transfer_sales', $autoData['transferSales']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ขาย QR</label>
                    <input type="number" name="qr_sales" value="{{ old('qr_sales', $autoData['qrSales']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ขายบัตร</label>
                    <input type="number" name="card_sales" value="{{ old('card_sales', $autoData['cardSales']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ขายเครดิต</label>
                    <input type="number" name="credit_sales" value="{{ old('credit_sales', $autoData['creditSales']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ยอดขายรวม</label>
                    <input type="number" name="total_sales" value="{{ old('total_sales', $autoData['totalSales']) }}" step="0.01" min="0" required class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-bold">
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-tools text-green-600 mr-2"></i>รายได้อื่น</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รายได้งานซ่อม</label>
                    <input type="number" name="repair_revenue" value="{{ old('repair_revenue', $autoData['repairRevenue']) }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รายได้ขายสินค้า</label>
                    <input type="number" name="product_revenue" value="{{ old('product_revenue', $autoData['totalSales']) }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Cash Settlement --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-money-bill-wave text-yellow-600 mr-2"></i>ปิดยอดเงินสด</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงินเปิดร้าน <span class="text-red-500">*</span></label>
                    <input type="number" name="opening_cash" value="{{ old('opening_cash', 0) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงินสดรับเข้า</label>
                    <input type="number" name="cash_in" value="{{ old('cash_in', $autoData['cashIn']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงินสดจ่ายออก</label>
                    <input type="number" name="cash_out" value="{{ old('cash_out', $autoData['cashOut']) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงินสดนับจริง <span class="text-red-500">*</span></label>
                    <input type="number" name="actual_cash" value="{{ old('actual_cash') }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-indigo-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 bg-indigo-50 font-bold">
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผลส่วนต่าง (ถ้ามี)</label>
                    <textarea name="difference_reason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="อธิบายเหตุผลส่วนต่าง...">{{ old('difference_reason') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('finance.daily-settlement.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center"><i class="fas fa-save mr-2"></i>บันทึกปิดยอด</button>
        </div>
    </form>
</div>
@endsection