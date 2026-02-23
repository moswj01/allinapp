@extends('superadmin.layout')

@section('title', 'ออเดอร์ ' . $order->order_number)
@section('page-title', 'ออเดอร์ ' . $order->order_number)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.tenant-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> กลับรายการ
        </a>
        @php $color = \App\Models\TenantOrder::getStatusColor($order->status); @endphp
        <span class="inline-flex px-3 py-1.5 rounded-full text-sm font-semibold bg-{{ $color }}-100 text-{{ $color }}-800">
            {{ \App\Models\TenantOrder::getStatusLabel($order->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">ข้อมูลออเดอร์</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">เลขที่ออเดอร์</p>
                        <p class="font-medium">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">วันที่สั่ง</p>
                        <p class="font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">ร้านค้า</p>
                        <p class="font-medium">{{ $order->tenant->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">สาขา</p>
                        <p class="font-medium">{{ $order->branch->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">ผู้สั่ง</p>
                        <p class="font-medium">{{ $order->createdBy->name ?? '-' }}</p>
                    </div>
                    @if($order->shipping_phone)
                    <div>
                        <p class="text-gray-500">เบอร์โทร</p>
                        <p class="font-medium">{{ $order->shipping_phone }}</p>
                    </div>
                    @endif
                </div>
                @if($order->shipping_address)
                <div class="mt-4 text-sm">
                    <p class="text-gray-500">ที่อยู่จัดส่ง</p>
                    <p class="font-medium">{{ $order->shipping_address }}</p>
                </div>
                @endif
                @if($order->notes)
                <div class="mt-4 text-sm">
                    <p class="text-gray-500">หมายเหตุ</p>
                    <p class="font-medium">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">รายการสินค้า ({{ $order->items->count() }})</h3>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">สินค้า</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ราคา</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จำนวน</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">รวม</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $i => $item)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-3">
                                <p class="text-sm font-medium text-gray-800">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-400">{{ $item->product_sku }}</p>
                            </td>
                            <td class="px-6 py-3 text-right text-sm">฿{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-3 text-center text-sm">{{ $item->quantity }}</td>
                            <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-600">ยอดรวมสินค้า</td>
                            <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-red-600">ส่วนลด</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-red-600">-฿{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="4" class="px-6 py-4 text-right font-bold text-gray-800">ยอดรวมทั้งหมด</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-indigo-600">฿{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Sidebar: Actions + Timeline --}}
        <div class="space-y-6">
            {{-- Actions --}}
            @if(in_array($order->status, ['pending', 'confirmed', 'preparing', 'shipped']))
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">ดำเนินการ</h3>

                @if($order->canBeConfirmed())
                <form method="POST" action="{{ route('superadmin.tenant-orders.confirm', $order) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition"
                        onclick="return confirm('ยืนยันออเดอร์นี้?')">
                        <i class="fas fa-check mr-1"></i> ยืนยันออเดอร์
                    </button>
                </form>
                @endif

                @if($order->canBeShipped())
                <form method="POST" action="{{ route('superadmin.tenant-orders.ship', $order) }}" class="mb-3" x-data="{ show: false }">
                    @csrf
                    <button type="button" @click="show = !show"
                        class="w-full px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition">
                        <i class="fas fa-truck mr-1"></i> จัดส่ง
                    </button>
                    <div x-show="show" x-cloak class="mt-3 space-y-2">
                        <input type="text" name="tracking_number" placeholder="เลขพัสดุ (ถ้ามี)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button type="submit" class="w-full px-3 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-sm"
                            onclick="return confirm('ยืนยันจัดส่งออเดอร์นี้?')">
                            ยืนยันจัดส่ง
                        </button>
                    </div>
                </form>
                @endif

                @if($order->canBeCancelled())
                <form method="POST" action="{{ route('superadmin.tenant-orders.cancel', $order) }}" x-data="{ show: false }">
                    @csrf
                    <button type="button" @click="show = !show"
                        class="w-full px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-times mr-1"></i> ยกเลิก
                    </button>
                    <div x-show="show" x-cloak class="mt-3 space-y-2">
                        <textarea name="cancel_reason" rows="2" placeholder="เหตุผลที่ยกเลิก..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required></textarea>
                        <button type="submit" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm"
                            onclick="return confirm('ยืนยันยกเลิกออเดอร์นี้?')">
                            ยืนยันยกเลิก
                        </button>
                    </div>
                </form>
                @endif
            </div>
            @endif

            {{-- Timeline --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">ไทม์ไลน์</h3>
                <div class="space-y-4">
                    {{-- Created --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <div class="w-0.5 flex-1 bg-gray-200"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-gray-800">สร้างออเดอร์</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">โดย {{ $order->createdBy->name ?? '-' }}</p>
                        </div>
                    </div>

                    @if($order->confirmed_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                            <div class="w-0.5 flex-1 bg-gray-200"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-gray-800">ยืนยัน</p>
                            <p class="text-xs text-gray-400">{{ $order->confirmed_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">โดย {{ $order->confirmedBy->name ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($order->shipped_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-purple-400"></div>
                            <div class="w-0.5 flex-1 bg-gray-200"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-gray-800">จัดส่ง</p>
                            <p class="text-xs text-gray-400">{{ $order->shipped_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">โดย {{ $order->shippedBy->name ?? '-' }}</p>
                            @if($order->tracking_number)
                            <p class="text-xs text-indigo-600 mt-1"><i class="fas fa-box mr-1"></i>{{ $order->tracking_number }}</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($order->received_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">รับสินค้าแล้ว</p>
                            <p class="text-xs text-gray-400">{{ $order->received_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">โดย {{ $order->receivedBy->name ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($order->cancelled_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">ยกเลิก</p>
                            <p class="text-xs text-gray-400">{{ $order->cancelled_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">โดย {{ $order->cancelledBy->name ?? '-' }}</p>
                            @if($order->cancel_reason)
                            <p class="text-xs text-red-500 mt-1">{{ $order->cancel_reason }}</p>
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
