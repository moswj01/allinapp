@extends('layouts.app')

@section('title', 'ใบสั่งซื้อ ' . $branchOrder->order_number)

@section('content')
<div x-data="{ showCancelModal: false }" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('branch-orders.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $branchOrder->order_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">ใบสั่งซื้อจากสาขาใหญ่</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php
            $color = \App\Models\BranchOrder::getStatusColor($branchOrder->status);
            $statuses = \App\Models\BranchOrder::getStatuses();
            $userBranch = Auth::user()->branch;
            $isMainBranch = $userBranch && $userBranch->is_main;
            @endphp
            <a href="{{ route('branch-orders.print', $branchOrder) }}" target="_blank"
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg font-medium">
                <i class="fas fa-print mr-1"></i>พิมพ์ใบสั่งซื้อ
            </a>
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                {{ $statuses[$branchOrder->status] ?? $branchOrder->status }}
            </span>
        </div>
    </div>

    <!-- Order Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ข้อมูลคำสั่งซื้อ</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">เลขที่:</span>
                    <span class="font-medium">{{ $branchOrder->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">สาขาที่สั่ง:</span>
                    <span class="font-medium text-indigo-600">{{ $branchOrder->branch->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">สาขาใหญ่:</span>
                    <span class="font-medium">{{ $branchOrder->mainBranch->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">วันที่สั่ง:</span>
                    <span>{{ $branchOrder->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">ผู้สั่ง:</span>
                    <span>{{ $branchOrder->createdBy->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">สถานะ & มูลค่า</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">สถานะ:</span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ $statuses[$branchOrder->status] ?? $branchOrder->status }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">มูลค่ารวม:</span>
                    <span class="font-bold text-lg text-indigo-700">฿{{ number_format($branchOrder->total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">จำนวนรายการ:</span>
                    <span>{{ $branchOrder->items->count() }} รายการ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ไทม์ไลน์</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-500 w-4"></i>
                    <span class="text-gray-500">สร้าง:</span>
                    <span>{{ $branchOrder->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($branchOrder->approved_at)
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500 w-4"></i>
                    <span class="text-gray-500">อนุมัติ:</span>
                    <span>{{ $branchOrder->approved_at->format('d/m/Y H:i') }}</span>
                    <span class="text-xs text-gray-400">({{ $branchOrder->approvedBy->name ?? '-' }})</span>
                </div>
                @endif
                @if($branchOrder->shipped_at)
                <div class="flex items-center gap-2">
                    <i class="fas fa-truck text-purple-500 w-4"></i>
                    <span class="text-gray-500">จัดส่ง:</span>
                    <span>{{ $branchOrder->shipped_at->format('d/m/Y H:i') }}</span>
                    <span class="text-xs text-gray-400">({{ $branchOrder->shippedBy->name ?? '-' }})</span>
                </div>
                @endif
                @if($branchOrder->received_at)
                <div class="flex items-center gap-2">
                    <i class="fas fa-box-open text-green-600 w-4"></i>
                    <span class="text-gray-500">รับสินค้า:</span>
                    <span>{{ $branchOrder->received_at->format('d/m/Y H:i') }}</span>
                    <span class="text-xs text-gray-400">({{ $branchOrder->receivedBy->name ?? '-' }})</span>
                </div>
                @endif
                @if($branchOrder->cancelled_at)
                <div class="flex items-center gap-2">
                    <i class="fas fa-times-circle text-red-500 w-4"></i>
                    <span class="text-gray-500">ยกเลิก:</span>
                    <span>{{ $branchOrder->cancelled_at->format('d/m/Y H:i') }}</span>
                    <span class="text-xs text-gray-400">({{ $branchOrder->cancelledBy->name ?? '-' }})</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($branchOrder->notes)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-yellow-800 mb-1"><i class="fas fa-sticky-note mr-1"></i>หมายเหตุ</h3>
        <p class="text-sm text-yellow-700">{{ $branchOrder->notes }}</p>
    </div>
    @endif

    @if($branchOrder->cancel_reason)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-red-800 mb-1"><i class="fas fa-ban mr-1"></i>เหตุผลที่ยกเลิก</h3>
        <p class="text-sm text-red-700">{{ $branchOrder->cancel_reason }}</p>
    </div>
    @endif

    <!-- Items Table -->
    @if($branchOrder->canBeApproved() && $isMainBranch)
    {{-- Approve form with editable quantities --}}
    <form method="POST" action="{{ route('branch-orders.approve', $branchOrder) }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2 text-indigo-500"></i>รายการสินค้า — อนุมัติจำนวน
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">สินค้า</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จำนวนที่สั่ง</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จำนวนอนุมัติ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ราคาทุน</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">รวม</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($branchOrder->items as $i => $item)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-6 py-3">
                                <span class="text-sm font-medium text-gray-800">{{ $item->product_name }}</span>
                                @if($item->notes)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $item->notes }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center text-sm font-medium">{{ $item->quantity_requested }}</td>
                            <td class="px-6 py-3 text-center">
                                <input type="number" name="items[{{ $item->id }}][quantity_approved]"
                                    value="{{ $item->quantity_requested }}" min="0" max="{{ $item->quantity_requested }}"
                                    class="w-20 text-center border border-gray-300 rounded py-1 text-sm focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="px-6 py-3 text-right text-sm text-gray-600">฿{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-6 py-3 text-right font-semibold text-gray-700">รวมทั้งหมด:</td>
                            <td class="px-6 py-3 text-right font-bold text-lg text-indigo-700">฿{{ number_format($branchOrder->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="showCancelModal = true"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg font-medium">
                    <i class="fas fa-times mr-1"></i>ปฏิเสธ
                </button>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-medium">
                    <i class="fas fa-check mr-1"></i>อนุมัติใบสั่งซื้อ
                </button>
            </div>
        </div>
    </form>
    @else
    {{-- Read-only items table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list mr-2 text-indigo-500"></i>รายการสินค้า
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">สินค้า</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">สั่ง</th>
                        @if(in_array($branchOrder->status, ['approved','preparing','shipped','received']))
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">อนุมัติ</th>
                        @endif
                        @if(in_array($branchOrder->status, ['shipped','received']))
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดส่ง</th>
                        @endif
                        @if($branchOrder->status === 'received')
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">รับ</th>
                        @endif
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ราคาทุน</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">รวม</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($branchOrder->items as $i => $item)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-6 py-3">
                            <span class="text-sm font-medium text-gray-800">{{ $item->product_name }}</span>
                            @if($item->notes)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item->notes }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center text-sm font-medium">{{ $item->quantity_requested }}</td>
                        @if(in_array($branchOrder->status, ['approved','preparing','shipped','received']))
                        <td class="px-6 py-3 text-center text-sm font-medium text-blue-600">{{ $item->quantity_approved ?? '-' }}</td>
                        @endif
                        @if(in_array($branchOrder->status, ['shipped','received']))
                        <td class="px-6 py-3 text-center text-sm font-medium text-purple-600">{{ $item->quantity_shipped ?? '-' }}</td>
                        @endif
                        @if($branchOrder->status === 'received')
                        <td class="px-6 py-3 text-center text-sm font-medium text-green-600">{{ $item->quantity_received ?? '-' }}</td>
                        @endif
                        <td class="px-6 py-3 text-right text-sm text-gray-600">฿{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        @php
                        $colspan = 5;
                        if(in_array($branchOrder->status, ['approved','preparing','shipped','received'])) $colspan++;
                        if(in_array($branchOrder->status, ['shipped','received'])) $colspan++;
                        if($branchOrder->status === 'received') $colspan++;
                        @endphp
                        <td colspan="{{ $colspan }}" class="px-6 py-3 text-right font-semibold text-gray-700">รวมทั้งหมด:</td>
                        <td class="px-6 py-3 text-right font-bold text-lg text-indigo-700">฿{{ number_format($branchOrder->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Action Buttons --}}
        @if($branchOrder->canBeShipped() && $isMainBranch)
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
            <button type="button" @click="showCancelModal = true"
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg font-medium">
                <i class="fas fa-times mr-1"></i>ยกเลิก
            </button>
            <form method="POST" action="{{ route('branch-orders.ship', $branchOrder) }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg font-medium"
                    onclick="return confirm('ยืนยันจัดส่งสินค้า? ระบบจะตัดสต๊อกจากสาขาใหญ่')">
                    <i class="fas fa-truck mr-1"></i>จัดส่งสินค้า
                </button>
            </form>
        </div>
        @endif

        @if($branchOrder->canBeReceived() && !$isMainBranch)
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
            <form method="POST" action="{{ route('branch-orders.receive', $branchOrder) }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-medium"
                    onclick="return confirm('ยืนยันรับสินค้า? ระบบจะเพิ่มสต๊อกเข้าสาขาของคุณ')">
                    <i class="fas fa-box-open mr-1"></i>ยืนยันรับสินค้า
                </button>
            </form>
        </div>
        @endif

        @if($branchOrder->canBeCancelled() && !$isMainBranch)
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
            <button type="button" @click="showCancelModal = true"
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg font-medium">
                <i class="fas fa-times mr-1"></i>ยกเลิกใบสั่งซื้อ
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- Cancel Modal --}}
    <div x-show="showCancelModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCancelModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>ยกเลิกใบสั่งซื้อ
            </h3>
            <form method="POST" action="{{ route('branch-orders.cancel', $branchOrder) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผลในการยกเลิก <span class="text-red-500">*</span></label>
                    <textarea name="cancel_reason" rows="3" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm"
                        placeholder="กรุณาระบุเหตุผล..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showCancelModal = false"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                        ปิด
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                        <i class="fas fa-ban mr-1"></i>ยืนยันยกเลิก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection