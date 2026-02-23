@extends('layouts.app')

@section('title', 'ใบเสนอราคา')
@section('page-title', 'ใบเสนอราคา')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ใบเสนอราคาทั้งหมด</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $quotations->total() }} รายการ</p>
        </div>
        <a href="{{ route('quotations.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> สร้างใบเสนอราคา
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="เลขที่, ชื่อลูกค้า, หัวข้อ..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">สถานะ</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                <i class="fas fa-search mr-1"></i>ค้นหา
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('quotations.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">ล้าง</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ลูกค้า</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">หัวข้อ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">มูลค่า</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันหมดอายุ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่สร้าง</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($quotations as $q)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('quotations.show', $q) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ $q->quotation_number }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $q->customer_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $q->subject ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-right font-medium">฿{{ number_format($q->total, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        @php $color = \App\Models\Quotation::getStatusColor($q->status); @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $statuses[$q->status] ?? $q->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($q->valid_until)
                        <span class="{{ $q->isExpired() ? 'text-red-500' : '' }}">{{ $q->valid_until->format('d/m/Y') }}</span>
                        @else - @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $q->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('quotations.show', $q) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            ดูรายละเอียด <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-file-alt text-4xl mb-3 block"></i> ยังไม่มีใบเสนอราคา
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($quotations->hasPages())
        <div class="px-6 py-4 border-t">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>
@endsection