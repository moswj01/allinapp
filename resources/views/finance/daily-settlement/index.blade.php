@extends('layouts.app')

@section('title', 'ปิดยอดประจำวัน')
@section('page-title', 'ปิดยอดประจำวัน (Daily Settlement)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ปิดยอดประจำวัน</h2>
            <p class="text-sm text-gray-500 mt-1">บันทึกยอดขายและปิดยอดเงินสดประจำวัน</p>
        </div>
        <a href="{{ route('finance.daily-settlement.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"><i class="fas fa-plus mr-2"></i>ปิดยอดวันนี้</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending" @selected(request('status')==='pending' )>รออนุมัติ</option>
                    <option value="approved" @selected(request('status')==='approved' )>อนุมัติแล้ว</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>ค้นหา</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">วันที่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">สาขา</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ยอดขาย</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">เงินสดจริง</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ส่วนต่าง</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">สถานะ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ผู้บันทึก</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($dailySettlements as $ds)
                <tr class="hover:bg-gray-50 {{ $ds->difference != 0 ? ($ds->difference < 0 ? 'bg-red-50' : 'bg-blue-50') : '' }}">
                    <td class="px-6 py-4 text-center text-sm font-medium">{{ \Carbon\Carbon::parse($ds->settlement_date)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $ds->branch->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-sm">฿{{ number_format($ds->total_sales, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm">฿{{ number_format($ds->actual_cash, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm font-semibold {{ $ds->difference < 0 ? 'text-red-600' : ($ds->difference > 0 ? 'text-blue-600' : 'text-green-600') }}">
                        {{ $ds->difference > 0 ? '+' : '' }}฿{{ number_format($ds->difference, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $ds->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $ds->status === 'approved' ? 'อนุมัติ' : 'รออนุมัติ' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $ds->createdBy->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center"><a href="{{ route('finance.daily-settlement.show', $ds) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">ไม่พบรายการปิดยอด</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($dailySettlements->hasPages())
        <div class="px-6 py-4 border-t">{{ $dailySettlements->links() }}</div>
        @endif
    </div>
</div>
@endsection