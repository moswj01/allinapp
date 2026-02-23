@extends('layouts.app')

@section('title', 'รายละเอียดบิล - ' . $sale->sale_number)
@section('page-title', 'รายละเอียดบิล')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $sale->sale_number }}</h2>
            <p class="text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @php
            $statusColors = [
            'completed' => 'bg-green-100 text-green-800 border-green-300',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'cancelled' => 'bg-red-100 text-red-800 border-red-300',
            ];
            $statusNames = [
            'completed' => 'สำเร็จ',
            'pending' => 'รอชำระ',
            'cancelled' => 'ยกเลิก',
            ];
            @endphp
            <span class="px-4 py-2 text-sm font-semibold rounded-full border-2 {{ $statusColors[$sale->status] ?? 'bg-gray-100' }}">
                {{ $statusNames[$sale->status] ?? $sale->status }}
            </span>

            @if($sale->status === 'pending')
            <button onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                <i class="fas fa-check mr-1"></i>ชำระแล้ว
            </button>
            @endif

            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-print mr-2"></i>พิมพ์
            </a>
            <a href="{{ route('sales.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user text-indigo-600 mr-2"></i>
                ข้อมูลลูกค้า
            </h3>

            @if($sale->customer)
            <dl class="space-y-2">
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">ชื่อ:</dt>
                    <dd class="font-medium text-gray-900">{{ $sale->customer->name }}</dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">โทร:</dt>
                    <dd class="font-medium text-gray-900">
                        <a href="tel:{{ $sale->customer->phone }}" class="text-indigo-600 hover:underline">
                            {{ $sale->customer->phone }}
                        </a>
                    </dd>
                </div>
                @if($sale->customer->line_id)
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">LINE:</dt>
                    <dd class="font-medium text-green-600">{{ $sale->customer->line_id }}</dd>
                </div>
                @endif
            </dl>
            @else
            <p class="text-gray-500">ลูกค้าทั่วไป</p>
            @if($sale->customer_name)
            <p class="text-gray-700 mt-2">{{ $sale->customer_name }}</p>
            @endif
            @if($sale->customer_phone)
            <p class="text-gray-700">{{ $sale->customer_phone }}</p>
            @endif
            @endif
        </div>

        <!-- Sale Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                ข้อมูลการขาย
            </h3>

            <dl class="space-y-2">
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">เลขบิล:</dt>
                    <dd class="font-mono font-medium text-gray-900">{{ $sale->sale_number }}</dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">สาขา:</dt>
                    <dd class="font-medium text-gray-900">{{ $sale->branch->name ?? '-' }}</dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">ผู้ขาย:</dt>
                    <dd class="font-medium text-gray-900">{{ $sale->createdBy->name ?? '-' }}</dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500 text-sm">ชำระโดย:</dt>
                    <dd class="font-medium text-gray-900">
                        @php
                        $methodNames = [
                        'cash' => 'เงินสด',
                        'transfer' => 'โอนเงิน',
                        'qr' => 'QR Payment',
                        'card' => 'บัตรเครดิต',
                        'credit' => 'เครดิต',
                        ];
                        @endphp
                        {{ $methodNames[$sale->payment_method] ?? $sale->payment_method }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-shopping-cart text-green-600 mr-2"></i>
            รายการสินค้า
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">สินค้า</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">ราคา/หน่วย</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">รวม</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                            @if($item->item_barcode)
                            <p class="text-xs text-gray-500">{{ $item->item_barcode }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-900">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">฿{{ number_format($item->unit_price, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">฿{{ number_format($item->total, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right font-medium text-gray-700">รวม:</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">฿{{ number_format($sale->subtotal, 0) }}</td>
                    </tr>
                    @if($sale->discount > 0)
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right font-medium text-red-600">ส่วนลด:</td>
                        <td class="px-4 py-2 text-right font-medium text-red-600">-฿{{ number_format($sale->discount, 0) }}</td>
                    </tr>
                    @endif
                    @if($sale->vat > 0)
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right font-medium text-gray-700">ภาษี:</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">฿{{ number_format($sale->vat, 0) }}</td>
                    </tr>
                    @endif
                    <tr class="bg-green-50">
                        <td colspan="4" class="px-4 py-3 text-right text-lg font-bold text-gray-900">รวมสุทธิ:</td>
                        <td class="px-4 py-3 text-right text-lg font-bold text-green-600">฿{{ number_format($sale->total, 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($sale->notes)
    <!-- Notes -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
            หมายเหตุ
        </h3>
        <p class="text-gray-700">{{ $sale->notes }}</p>
    </div>
    @endif
</div>

@if($sale->status === 'pending')
<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="document.getElementById('paymentModal').classList.add('hidden')"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-money-check-alt text-green-600 mr-2"></i>ยืนยันการชำระเงิน
                </h3>
                <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Sale Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>บิลเลขที่</span>
                    <span class="font-mono font-semibold">{{ $sale->sale_number }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>ลูกค้า</span>
                    <span class="font-semibold">{{ $sale->customer->name ?? 'ลูกค้าทั่วไป' }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-green-700 mt-2 pt-2 border-t">
                    <span>ยอดชำระ</span>
                    <span>฿{{ number_format($sale->total, 0) }}</span>
                </div>
            </div>

            <form action="{{ route('sales.updateStatus', $sale) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="completed">

                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">วิธีการชำระเงิน <span class="text-red-500">*</span></label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">-- เลือกวิธีชำระ --</option>
                        <option value="cash">เงินสด</option>
                        <option value="transfer">โอนเงิน</option>
                        <option value="qr">QR Payment</option>
                        <option value="card">บัตรเครดิต</option>
                    </select>
                </div>

                <!-- Reference Number -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">เลขอ้างอิง / เลขที่รายการ</label>
                    <input type="text" name="reference_number" placeholder="เช่น เลขที่ slip, เลขอ้างอิงธนาคาร"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                    <textarea name="notes" rows="2" placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                        ยกเลิก
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        <i class="fas fa-check mr-1"></i>ยืนยันชำระเงิน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection