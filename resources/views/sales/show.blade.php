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
@endsection