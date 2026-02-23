@extends('layouts.app')

@section('title', 'ตรวจนับสต๊อก')
@section('page-title', 'ตรวจนับสต๊อก (Stock Take)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ตรวจนับสต๊อก</h2>
            <p class="text-sm text-gray-500 mt-1">จัดการการตรวจนับสินค้า</p>
        </div>
        <a href="{{ route('stock-takes.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"><i class="fas fa-plus mr-2"></i>สร้างใบตรวจนับ</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาเลขที่..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกสถานะ</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>ค้นหา</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">เลขที่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">สาขา</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">ประเภท</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">รายการ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">สถานะ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">วันที่</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stockTakes as $st)
                @php $color = \App\Models\StockTake::getStatusColor($st->status); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><a href="{{ route('stock-takes.show', $st) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">{{ $st->stock_take_number }}</a></td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $st->branch->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center text-sm">
                        @php $types = \App\Models\StockTake::getTypes(); @endphp
                        {{ $types[$st->type] ?? $st->type }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm">{{ $st->items_count }}</td>
                    <td class="px-6 py-4 text-center"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">{{ $statuses[$st->status] ?? $st->status }}</span></td>
                    <td class="px-6 py-4 text-center text-sm text-gray-500">{{ $st->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-center"><a href="{{ route('stock-takes.show', $st) }}" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">ไม่พบรายการตรวจนับ</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($stockTakes->hasPages())
        <div class="px-6 py-4 border-t">{{ $stockTakes->links() }}</div>
        @endif
    </div>
</div>
@endsection