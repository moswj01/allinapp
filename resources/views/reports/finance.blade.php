@extends('layouts.app')

@section('title', 'รายงานการเงิน')
@section('page-title', 'รายงานการเงิน')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">รายงานการเงิน</h2>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">จากวันที่</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ถึงวันที่</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สาขา</label>
                <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกสาขา</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"><i class="fas fa-search mr-1"></i>ค้นหา</button>
                <a href="{{ route('reports.finance') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ล้าง</a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">ยอดขายรวม</p>
            <p class="text-2xl font-bold text-indigo-600">฿{{ number_format($summary['total_sales'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">รายได้งานซ่อม</p>
            <p class="text-2xl font-bold text-green-600">฿{{ number_format($summary['total_repair_revenue'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">เงินสดย่อย (รับ/จ่าย)</p>
            <p class="text-lg font-bold">
                <span class="text-green-600">+{{ number_format($summary['petty_cash_in'], 2) }}</span> /
                <span class="text-red-600">-{{ number_format($summary['petty_cash_out'], 2) }}</span>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">ผลต่างเงินสดรวม</p>
            @php $diff = $summary['total_difference']; @endphp
            <p class="text-2xl font-bold {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $diff >= 0 ? '+' : '' }}฿{{ number_format($diff, 2) }}</p>
        </div>
    </div>

    {{-- Payment Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-money-bill-wave text-green-600 mr-2"></i>สรุปยอดขายตามช่องทาง</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600"><i class="fas fa-money-bill mr-2"></i>เงินสด</span>
                    <span class="font-bold text-gray-900">฿{{ number_format($summary['total_cash_sales'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600"><i class="fas fa-exchange-alt mr-2"></i>โอน</span>
                    <span class="font-bold text-gray-900">฿{{ number_format($summary['total_transfer_sales'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-indigo-50 rounded-lg border border-indigo-200">
                    <span class="font-medium text-indigo-700">ยอดรวม</span>
                    <span class="font-bold text-indigo-700">฿{{ number_format($summary['total_sales'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-chart-line text-indigo-600 mr-2"></i>แนวโน้มยอดขายรายวัน</h3>
            @if($dailySummary->count() > 0)
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($dailySummary as $day)
                <div class="flex justify-between items-center p-2 border-b text-sm">
                    <span class="text-gray-600">{{ \Carbon\Carbon::parse($day->settlement_date)->format('d/m/Y') }}</span>
                    <div class="text-right">
                        <span class="font-medium text-gray-900">฿{{ number_format($day->total_sales, 2) }}</span>
                        @if($day->total_diff != 0)
                        <span class="ml-2 text-xs {{ $day->total_diff >= 0 ? 'text-green-600' : 'text-red-600' }}">({{ $day->total_diff >= 0 ? '+' : '' }}{{ number_format($day->total_diff, 2) }})</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">ไม่มีข้อมูลในช่วงนี้</p>
            @endif
        </div>
    </div>

    {{-- Settlement Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-list text-gray-600 mr-2"></i>รายการปิดยอดประจำวัน</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขา</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ยอดขาย</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">เงินสดจริง</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ส่วนต่าง</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($settlements as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($s->settlement_date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $s->branch->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ number_format($s->total_sales, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($s->actual_cash, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-bold {{ $s->difference == 0 ? 'text-green-600' : ($s->difference > 0 ? 'text-blue-600' : 'text-red-600') }}">
                            {{ $s->difference > 0 ? '+' : '' }}{{ number_format($s->difference, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($s->status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">อนุมัติ</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">รออนุมัติ</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection