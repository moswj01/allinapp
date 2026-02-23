@extends('layouts.app')

@section('title', 'รายงานการขาย')
@section('page-title', 'รายงานการขาย')

@section('content')
<div class="space-y-4">
    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 bg-white rounded-xl shadow-sm px-4 py-2">
        <a href="{{ route('reports.sales', request()->query()) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white">
            <i class="fas fa-receipt mr-1"></i>การขาย
        </a>
        <a href="{{ route('reports.repairs', request()->query()) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
            <i class="fas fa-tools mr-1"></i>การซ่อม
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">จากวันที่</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">ถึงวันที่</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">สถานะ</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">ทั้งหมด</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอชำระ</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>ชำระแล้ว</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">ช่องทางชำระ</label>
                <select name="payment_method" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">ทั้งหมด</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>เงินสด</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>โอน</option>
                    <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>เครดิต</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>บัตร</option>
                    <option value="qr" {{ request('payment_method') === 'qr' ? 'selected' : '' }}>QR</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-1"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">ยอดขายรวม</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">฿{{ number_format($summary['total_amount'] ?? 0, 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-coins text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">จำนวนบิล</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_count'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-receipt text-indigo-600"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs">
                <span class="text-green-600">สำเร็จ {{ $summary['completed'] ?? 0 }}</span>
                <span class="text-yellow-600">รอ {{ $summary['pending'] ?? 0 }}</span>
                <span class="text-red-600">ยกเลิก {{ $summary['voided'] ?? 0 }}</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">ยอดเงินสด</p>
                    <p class="text-xl font-bold text-green-700 mt-1">฿{{ number_format($summary['cash'] ?? 0, 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">ยอดโอน / เครดิต</p>
                    <p class="text-lg font-bold text-blue-700 mt-1">฿{{ number_format($summary['transfer'] ?? 0, 0) }}</p>
                    <p class="text-xs text-orange-600 mt-0.5">เครดิต ฿{{ number_format($summary['credit'] ?? 0, 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-university text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Daily Sales Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-chart-line text-indigo-500 mr-1"></i>ยอดขายรายวัน</h3>
            <div class="space-y-1" style="max-height: 200px; overflow-y: auto;">
                @foreach($dailySales as $day)
                @php $maxVal = $dailySales->max('total') ?: 1; @endphp
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-16 text-gray-500">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-5 relative overflow-hidden">
                        <div class="bg-indigo-500 h-5 rounded-full" style="width: {{ ($day->total / $maxVal) * 100 }}%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-medium {{ ($day->total / $maxVal) > 0.5 ? 'text-white' : 'text-gray-700' }}">
                            ฿{{ number_format($day->total, 0) }} ({{ $day->count }})
                        </span>
                    </div>
                </div>
                @endforeach
                @if($dailySales->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4">ไม่มีข้อมูล</p>
                @endif
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-trophy text-yellow-500 mr-1"></i>สินค้าขายดี Top 10</h3>
            <div class="space-y-2" style="max-height: 200px; overflow-y: auto;">
                @foreach($topProducts as $i => $product)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                        <span class="text-gray-700 truncate max-w-[180px]">{{ $product->product_name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-medium text-gray-800">฿{{ number_format($product->total_amount, 0) }}</span>
                        <span class="text-xs text-gray-400 ml-1">({{ $product->total_qty }} ชิ้น)</span>
                    </div>
                </div>
                @endforeach
                @if($topProducts->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4">ไม่มีข้อมูล</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-list mr-1"></i>รายการขาย</h3>
            <span class="text-xs text-gray-500">{{ $sales->total() }} รายการ</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">วันที่</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">เลขที่บิล</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ลูกค้า</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">รายการ</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">ชำระ</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">ยอดรวม</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">สถานะ</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ผู้ขาย</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 {{ $sale->status === 'voided' ? 'bg-red-50 opacity-60' : '' }}">
                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $sale->created_at->format('d/m/y H:i') }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $sale->sale_number }}
                            </a>
                        </td>
                        <td class="px-3 py-2 text-gray-700 truncate max-w-[120px]">{{ $sale->customer->name ?? $sale->customer_name ?? 'ลูกค้าทั่วไป' }}</td>
                        <td class="px-3 py-2 text-center text-gray-500">{{ $sale->items->count() }}</td>
                        <td class="px-3 py-2 text-center">
                            @php
                            $pm = [
                            'cash' => ['เงินสด', 'text-green-700 bg-green-100'],
                            'transfer' => ['โอน', 'text-blue-700 bg-blue-100'],
                            'credit' => ['เครดิต', 'text-orange-700 bg-orange-100'],
                            'card' => ['บัตร', 'text-purple-700 bg-purple-100'],
                            'qr' => ['QR', 'text-cyan-700 bg-cyan-100'],
                            'mixed' => ['ผสม', 'text-gray-700 bg-gray-100'],
                            ];
                            $pmInfo = $pm[$sale->payment_method] ?? ['อื่นๆ', 'text-gray-700 bg-gray-100'];
                            @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $pmInfo[1] }}">{{ $pmInfo[0] }}</span>
                        </td>
                        <td class="px-3 py-2 text-right font-medium {{ $sale->status === 'voided' ? 'line-through text-gray-400' : 'text-gray-800' }}">
                            ฿{{ number_format($sale->total ?? 0, 0) }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            @php
                            $sc = [
                            'completed' => 'bg-green-100 text-green-800',
                            'paid' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'voided' => 'bg-red-100 text-red-800',
                            ];
                            $sl = [
                            'completed' => 'สำเร็จ',
                            'paid' => 'ชำระแล้ว',
                            'pending' => 'รอชำระ',
                            'voided' => 'ยกเลิก',
                            ];
                            @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $sc[$sale->status] ?? 'bg-gray-100' }}">
                                {{ $sl[$sale->status] ?? $sale->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500 truncate max-w-[80px]">{{ $sale->createdBy->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                            <i class="fas fa-receipt text-3xl mb-2 block"></i>
                            ไม่พบรายการขายในช่วงเวลาที่เลือก
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($sales->count() > 0)
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-right text-sm text-gray-600">รวมหน้านี้:</td>
                        <td class="px-3 py-2 text-right text-indigo-700">฿{{ number_format($sales->where('status', '!=', 'voided')->sum('total'), 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($sales->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $sales->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection