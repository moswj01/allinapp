@extends('layouts.app')

@section('title', 'โอนสต๊อก ' . $stockTransfer->transfer_number)
@section('page-title', 'รายละเอียดการโอนสต๊อก')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('stock-transfers.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $stockTransfer->transfer_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">โอนสต๊อก</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php $color = \App\Models\StockTransfer::getStatusColor($stockTransfer->status); $statuses = \App\Models\StockTransfer::getStatuses(); @endphp
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">{{ $statuses[$stockTransfer->status] ?? $stockTransfer->status }}</span>
        </div>
    </div>

    {{-- Progress Timeline --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between relative">
            @php
            $steps = [
            ['key' => 'pending', 'icon' => 'fa-file', 'label' => 'สร้าง'],
            ['key' => 'approved', 'icon' => 'fa-check', 'label' => 'อนุมัติ'],
            ['key' => 'shipped', 'icon' => 'fa-truck', 'label' => 'จัดส่ง'],
            ['key' => 'received', 'icon' => 'fa-box-open', 'label' => 'รับสินค้า'],
            ];
            $statusOrder = ['pending' => 0, 'approved' => 1, 'shipped' => 2, 'received' => 3, 'cancelled' => -1];
            $currentStep = $statusOrder[$stockTransfer->status] ?? 0;
            @endphp
            <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 z-0"></div>
            @foreach($steps as $i => $step)
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm {{ $i <= $currentStep ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                    <i class="fas {{ $step['icon'] }}"></i>
                </div>
                <span class="text-xs mt-1 {{ $i <= $currentStep ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}">{{ $step['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">สาขา</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ต้นทาง:</span><span class="font-medium text-red-600">{{ $stockTransfer->fromBranch->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">ปลายทาง:</span><span class="font-medium text-green-600">{{ $stockTransfer->toBranch->name ?? '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ผู้ดำเนินการ</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">สร้างโดย:</span><span>{{ $stockTransfer->createdBy->name ?? '-' }}</span></div>
                @if($stockTransfer->approvedBy)<div class="flex justify-between"><span class="text-gray-500">อนุมัติ:</span><span>{{ $stockTransfer->approvedBy->name }}</span></div>@endif
                @if($stockTransfer->shippedBy)<div class="flex justify-between"><span class="text-gray-500">จัดส่ง:</span><span>{{ $stockTransfer->shippedBy->name }}</span></div>@endif
                @if($stockTransfer->receivedBy)<div class="flex justify-between"><span class="text-gray-500">รับสินค้า:</span><span>{{ $stockTransfer->receivedBy->name }}</span></div>@endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ดำเนินการ</h3>
            <div class="space-y-2">
                @if($stockTransfer->canBeApproved())
                <form method="POST" action="{{ route('stock-transfers.approve', $stockTransfer) }}">@csrf
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg"><i class="fas fa-check mr-1"></i>อนุมัติ</button>
                </form>
                @endif
                @if($stockTransfer->canBeShipped())
                <form method="POST" action="{{ route('stock-transfers.ship', $stockTransfer) }}">@csrf
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg" onclick="return confirm('จะหักสต๊อกจากสาขาต้นทาง ต้องการดำเนินการ?')"><i class="fas fa-truck mr-1"></i>จัดส่ง</button>
                </form>
                @endif
                @if($stockTransfer->canBeReceived())
                <form method="POST" action="{{ route('stock-transfers.receive', $stockTransfer) }}">@csrf
                    <button type="submit" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg"><i class="fas fa-box-open mr-1"></i>รับสินค้า</button>
                </form>
                @endif
                @if($stockTransfer->canBeCancelled())
                <div x-data="{ showCancel: false }">
                    <button @click="showCancel = true" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg"><i class="fas fa-times-circle mr-1"></i>ยกเลิก</button>
                    <div x-show="showCancel" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                        <div class="bg-white rounded-xl p-6 w-full max-w-md m-4">
                            <h3 class="text-lg font-semibold mb-4">ยกเลิกการโอนสต๊อก</h3>
                            <form method="POST" action="{{ route('stock-transfers.cancel', $stockTransfer) }}">@csrf
                                <textarea name="cancel_reason" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="ระบุเหตุผล..."></textarea>
                                <div class="flex justify-end gap-3 mt-4">
                                    <button type="button" @click="showCancel = false" class="px-4 py-2 border rounded-lg">ปิด</button>
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">ยืนยันยกเลิก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($stockTransfer->cancel_reason)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm text-red-700"><strong>เหตุผลที่ยกเลิก:</strong> {{ $stockTransfer->cancel_reason }}</p>
        <p class="text-xs text-red-500 mt-1">โดย: {{ $stockTransfer->cancelledBy->name ?? '-' }} เมื่อ {{ $stockTransfer->cancelled_at?->format('d/m/Y H:i') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-list mr-2 text-indigo-500"></i>รายการสินค้า</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">สินค้า</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">ขอโอน</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดส่ง</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">รับแล้ว</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ต้นทุน/หน่วย</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($stockTransfer->items as $i => $item)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $item->itemable->name ?? 'สินค้า #' . $item->itemable_id }}</td>
                    <td class="px-6 py-3 text-center text-sm">{{ $item->quantity_requested }}</td>
                    <td class="px-6 py-3 text-center text-sm {{ $item->quantity_shipped > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">{{ $item->quantity_shipped }}</td>
                    <td class="px-6 py-3 text-center text-sm {{ $item->quantity_received > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">{{ $item->quantity_received }}</td>
                    <td class="px-6 py-3 text-right text-sm">฿{{ number_format($item->unit_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection