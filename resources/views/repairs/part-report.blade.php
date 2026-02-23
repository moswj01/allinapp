@extends('layouts.app')

@section('title', 'รายงานเบิกอะไหล่')
@section('page-title', 'รายงานการเบิกอะไหล่ซ่อม')

@section('content')
<div class="space-y-4">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">จากวันที่</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ถึงวันที่</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">ทั้งหมด</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">ทั้งหมด</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-yellow-50 rounded-xl shadow-sm p-4 text-center border border-yellow-200">
            <p class="text-xs text-yellow-600 uppercase">รออนุมัติ</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl shadow-sm p-4 text-center border border-green-200">
            <p class="text-xs text-green-600 uppercase">อนุมัติแล้ว</p>
            <p class="text-2xl font-bold text-green-700">{{ $summary['approved'] }}</p>
        </div>
        <div class="bg-red-50 rounded-xl shadow-sm p-4 text-center border border-red-200">
            <p class="text-xs text-red-600 uppercase">ปฏิเสธ</p>
            <p class="text-2xl font-bold text-red-700">{{ $summary['rejected'] }}</p>
        </div>
        <div class="bg-indigo-50 rounded-xl shadow-sm p-4 text-center border border-indigo-200">
            <p class="text-xs text-indigo-600 uppercase">มูลค่ารวม (อนุมัติ)</p>
            <p class="text-2xl font-bold text-indigo-700">฿{{ number_format($summary['total_cost'] ?? 0, 0) }}</p>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขงานซ่อม</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">อะไหล่</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ราคา/หน่วย</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">รวม</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้เบิก</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้อนุมัติ/ปฏิเสธ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($parts as $part)
                    <tr class="{{ $part->status === 'rejected' ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $part->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('repairs.show', $part->repair) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $part->repair->repair_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $part->part_name }}</div>
                            @if($part->notes)
                            <div class="text-xs text-gray-500">{{ $part->notes }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center">{{ $part->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-right">฿{{ number_format($part->unit_price ?? 0, 0) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium">฿{{ number_format(($part->unit_price ?? 0) * $part->quantity, 0) }}</td>
                        <td class="px-4 py-3 text-sm">{{ $part->requestedBy->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($part->status === 'approved' && $part->approvedBy)
                            <span class="text-green-600">
                                <i class="fas fa-check mr-1"></i>{{ $part->approvedBy->name }}
                            </span>
                            <div class="text-xs text-gray-400">{{ $part->approved_at?->format('d/m H:i') }}</div>
                            @elseif($part->status === 'rejected' && $part->rejectedBy)
                            <span class="text-red-600">
                                <i class="fas fa-times mr-1"></i>{{ $part->rejectedBy->name }}
                            </span>
                            <div class="text-xs text-gray-400">{{ $part->rejected_at?->format('d/m H:i') }}</div>
                            @if($part->reject_reason)
                            <div class="text-xs text-red-500 mt-1">{{ $part->reject_reason }}</div>
                            @endif
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                            $sc = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            ];
                            $sl = [
                            'pending' => 'รออนุมัติ',
                            'approved' => 'อนุมัติแล้ว',
                            'rejected' => 'ปฏิเสธ',
                            ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $sc[$part->status] ?? 'bg-gray-100' }}">
                                {{ $sl[$part->status] ?? $part->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-box-open text-3xl mb-2"></i>
                            <p>ไม่พบรายการเบิกอะไหล่ในช่วงเวลาที่เลือก</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($parts->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right font-medium text-gray-700">รวมทั้งหมด (อนุมัติ):</td>
                        <td class="px-4 py-3 text-right font-bold text-indigo-700">฿{{ number_format($summary['total_cost'] ?? 0, 0) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($parts->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $parts->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection