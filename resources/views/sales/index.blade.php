@extends('layouts.app')

@section('title', 'รายการขาย')
@section('page-title', 'รายการขาย')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">รายการขายทั้งหมด</h2>
            <p class="text-gray-500">{{ $sales->total() }} รายการ</p>
        </div>
        <a href="{{ route('pos') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-cash-register mr-2"></i>เปิด POS
        </a>
    </div>

    <!-- Today Summary -->
    @if($todaySummary)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600">ยอดขายวันนี้</p>
                    <p class="text-2xl font-bold text-green-700">฿{{ number_format($todaySummary->total ?? 0, 0) }}</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600">จำนวนบิลวันนี้</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $todaySummary->count ?? 0 }}</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="เลขบิล, ชื่อลูกค้า, เบอร์โทร...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">วันที่</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>สำเร็จ</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอชำระ</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขบิล</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลูกค้า</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">รายการ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ยอดรวม</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ชำระ</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('sales.show', $sale) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                {{ $sale->sale_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sale->customer->name ?? $sale->customer_name ?? 'ลูกค้าทั่วไป' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            {{ $sale->items_count ?? $sale->items->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                            ฿{{ number_format($sale->total, 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                            $methodIcons = [
                            'cash' => 'fa-money-bill-wave text-green-600',
                            'transfer' => 'fa-university text-blue-600',
                            'qr' => 'fa-qrcode text-purple-600',
                            'card' => 'fa-credit-card text-orange-600',
                            'credit' => 'fa-file-invoice text-gray-600',
                            ];
                            @endphp
                            <i class="fas {{ $methodIcons[$sale->payment_method] ?? 'fa-question' }}"></i>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                            $statusColors = [
                            'completed' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $statusNames = [
                            'completed' => 'สำเร็จ',
                            'pending' => 'รอชำระ',
                            'cancelled' => 'ยกเลิก',
                            ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$sale->status] ?? 'bg-gray-100' }}">
                                {{ $statusNames[$sale->status] ?? $sale->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sale->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('sales.show', $sale) }}" class="text-gray-400 hover:text-gray-600" title="ดู">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="text-blue-400 hover:text-blue-600" title="พิมพ์ใบเสร็จ">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if($sale->status !== 'cancelled')
                                <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="inline"
                                    onsubmit="return confirm('ต้องการยกเลิกบิลนี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600" title="ยกเลิก">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-receipt text-4xl mb-4"></i>
                            <p>ยังไม่มีรายการขาย</p>
                            <a href="{{ route('pos') }}" class="mt-2 inline-block text-green-600 hover:underline">
                                เปิด POS เพื่อขายสินค้า
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection