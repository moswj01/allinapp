@extends('layouts.app')

@section('title', 'ปิดยอด #' . $dailySettlement->id)
@section('page-title', 'รายละเอียดปิดยอด')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ปิดยอด #{{ $dailySettlement->id }}</h2>
            <p class="text-gray-500">วันที่ {{ \Carbon\Carbon::parse($dailySettlement->settlement_date)->format('d/m/Y') }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @if($dailySettlement->status === 'pending' && (auth()->user()->isOwner() || auth()->user()->isAdmin() || auth()->user()->isManager()))
            <form action="{{ route('finance.daily-settlement.approve', $dailySettlement) }}" method="POST" onsubmit="return confirm('ยืนยันอนุมัติปิดยอดนี้?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-check mr-2"></i>อนุมัติ</button>
            </form>
            @endif
            <a href="{{ route('finance.daily-settlement.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"><i class="fas fa-arrow-left mr-2"></i>กลับ</a>
        </div>
    </div>

    {{-- Status --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500">สถานะ</span>
                @if($dailySettlement->status === 'approved')
                <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>อนุมัติแล้ว</span>
                @else
                <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>รออนุมัติ</span>
                @endif
            </div>
            <div class="text-right text-sm text-gray-500">
                <div>สาขา: {{ $dailySettlement->branch->name ?? '-' }}</div>
                <div>สร้างโดย: {{ $dailySettlement->createdBy->name ?? '-' }}</div>
                @if($dailySettlement->approvedBy)
                <div>อนุมัติโดย: {{ $dailySettlement->approvedBy->name }} ({{ $dailySettlement->approved_at?->format('d/m/Y H:i') }})</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sales Summary --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-cash-register text-indigo-600 mr-2"></i>ยอดขาย</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">เงินสด</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->cash_sales, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">โอน</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->transfer_sales, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">QR</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->qr_sales, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">บัตร</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->card_sales, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">เครดิต</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->credit_sales, 2) }}</div>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                <div class="text-sm text-indigo-600">ยอดรวม</div>
                <div class="text-lg font-bold text-indigo-700">{{ number_format($dailySettlement->total_sales, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-coins text-green-600 mr-2"></i>รายได้</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">รายได้งานซ่อม</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->repair_revenue ?? 0, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">รายได้ขายสินค้า</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->product_revenue ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Cash Settlement --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-money-bill-wave text-yellow-600 mr-2"></i>ปิดยอดเงินสด</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">เงินเปิดร้าน</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($dailySettlement->opening_cash, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">เงินสดรับเข้า</div>
                <div class="text-lg font-bold text-green-600">+{{ number_format($dailySettlement->cash_in, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">เงินสดจ่ายออก</div>
                <div class="text-lg font-bold text-red-600">-{{ number_format($dailySettlement->cash_out, 2) }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="text-sm text-blue-600">ยอดเงินสดที่ควรจะเป็น</div>
                <div class="text-lg font-bold text-blue-700">{{ number_format($dailySettlement->expected_cash, 2) }}</div>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                <div class="text-sm text-indigo-600">เงินสดนับจริง</div>
                <div class="text-lg font-bold text-indigo-700">{{ number_format($dailySettlement->actual_cash, 2) }}</div>
            </div>
            @php $diff = $dailySettlement->difference; @endphp
            <div class="rounded-lg p-4 border {{ $diff == 0 ? 'bg-green-50 border-green-200' : ($diff > 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200') }}">
                <div class="text-sm {{ $diff == 0 ? 'text-green-600' : ($diff > 0 ? 'text-blue-600' : 'text-red-600') }}">ส่วนต่าง</div>
                <div class="text-lg font-bold {{ $diff == 0 ? 'text-green-700' : ($diff > 0 ? 'text-blue-700' : 'text-red-700') }}">
                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                    @if($diff == 0) <i class="fas fa-check-circle ml-1"></i> @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($dailySettlement->difference_reason || $dailySettlement->notes)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-sticky-note text-gray-600 mr-2"></i>หมายเหตุ</h3>
        @if($dailySettlement->difference_reason)
        <div class="mb-3">
            <span class="text-sm text-gray-500">เหตุผลส่วนต่าง:</span>
            <p class="text-gray-800 mt-1">{{ $dailySettlement->difference_reason }}</p>
        </div>
        @endif
        @if($dailySettlement->notes)
        <div>
            <span class="text-sm text-gray-500">หมายเหตุ:</span>
            <p class="text-gray-800 mt-1">{{ $dailySettlement->notes }}</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection