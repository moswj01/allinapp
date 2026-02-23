@extends('layouts.app')

@section('title', 'รายละเอียดคำสั่งซื้อ #' . $order->order_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">คำสั่งซื้อ {{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">สั่งเมื่อ {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('tenant-orders.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i>กลับรายการ
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status & Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">ข้อมูลคำสั่งซื้อ</h2>
                    @php $color = \App\Models\TenantOrder::getStatusColor($order->status); @endphp
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ $statuses[$order->status] ?? $order->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">เลขที่ออเดอร์</span>
                        <p class="font-medium">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">สาขา</span>
                        <p class="font-medium">{{ $order->branch->name ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">ผู้สั่ง</span>
                        <p class="font-medium">{{ $order->createdBy->name ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">วันที่สั่ง</span>
                        <p class="font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($order->tracking_number)
                    <div>
                        <span class="text-gray-500">เลขพัสดุ</span>
                        <p class="font-medium text-indigo-600">{{ $order->tracking_number }}</p>
                    </div>
                    @endif
                    @if($order->notes)
                    <div class="col-span-2">
                        <span class="text-gray-500">หมายเหตุ</span>
                        <p class="font-medium">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="font-bold text-gray-800">รายการสินค้า</h2>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สินค้า</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ราคา/หน่วย</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">รวม</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $index => $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-800">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-400">{{ $item->product_sku }}</p>
                            </td>
                            <td class="px-6 py-4 text-center text-sm">{{ number_format($item->quantity) }}</td>
                            <td class="px-6 py-4 text-right text-sm">฿{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">฿{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right font-bold text-gray-700">ยอดรวมทั้งสิ้น</td>
                            <td class="px-6 py-3 text-right font-bold text-lg text-indigo-600">฿{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Sidebar: Timeline & Actions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Actions -->
            @if($order->canBeReceived())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">ดำเนินการ</h3>
                <form action="{{ route('tenant-orders.receive', $order) }}" method="POST"
                    onsubmit="return confirm('ยืนยันรับสินค้าแล้ว?')">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg font-medium">
                        <i class="fas fa-check-circle mr-2"></i>รับสินค้าแล้ว
                    </button>
                </form>
            </div>
            @endif

            @if($order->canBeCancelled())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">ยกเลิกออเดอร์</h3>
                <form action="{{ route('tenant-orders.cancel', $order) }}" method="POST"
                    onsubmit="return confirm('ยืนยันยกเลิกออเดอร์นี้?')">
                    @csrf
                    <textarea name="cancel_reason" required rows="2" placeholder="เหตุผลที่ยกเลิก..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-3"></textarea>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg font-medium">
                        <i class="fas fa-times-circle mr-2"></i>ยกเลิกออเดอร์
                    </button>
                </form>
            </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">ไทม์ไลน์</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-cart-plus text-indigo-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">สร้างคำสั่งซื้อ</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }} - {{ $order->createdBy->name ?? '' }}</p>
                        </div>
                    </div>

                    @if($order->confirmed_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-blue-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">ยืนยันแล้ว</p>
                            <p class="text-xs text-gray-400">{{ $order->confirmed_at->format('d/m/Y H:i') }} - {{ $order->confirmedBy->name ?? '' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($order->shipped_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shipping-fast text-purple-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">จัดส่งแล้ว</p>
                            <p class="text-xs text-gray-400">{{ $order->shipped_at->format('d/m/Y H:i') }} - {{ $order->shippedBy->name ?? '' }}</p>
                            @if($order->tracking_number)
                            <p class="text-xs text-indigo-600 mt-1">เลขพัสดุ: {{ $order->tracking_number }}</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($order->received_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-box-open text-green-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">รับสินค้าแล้ว</p>
                            <p class="text-xs text-gray-400">{{ $order->received_at->format('d/m/Y H:i') }} - {{ $order->receivedBy->name ?? '' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($order->cancelled_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-ban text-red-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">ยกเลิก</p>
                            <p class="text-xs text-gray-400">{{ $order->cancelled_at->format('d/m/Y H:i') }} - {{ $order->cancelledBy->name ?? '' }}</p>
                            @if($order->cancel_reason)
                            <p class="text-xs text-red-500 mt-1">เหตุผล: {{ $order->cancel_reason }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection