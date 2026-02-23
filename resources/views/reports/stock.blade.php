@extends('layouts.app')

@section('title', 'รายงานสต๊อก')
@section('page-title', 'รายงานสต๊อกสินค้า')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">รายงานสต๊อกสินค้า</h2>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">สินค้าทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_products']) }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center"><i class="fas fa-box text-indigo-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">มูลค่าสต๊อก</p>
                    <p class="text-2xl font-bold text-green-600">฿{{ number_format($summary['total_value'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"><i class="fas fa-coins text-green-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ใกล้หมด</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($summary['low_stock']) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center"><i class="fas fa-exclamation-triangle text-yellow-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">หมดสต๊อก</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($summary['out_of_stock']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-times-circle text-red-600"></i></div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ชื่อสินค้า, SKU..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ตัวกรอง</label>
                <select name="filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>ใกล้หมด</option>
                    <option value="out_of_stock" {{ request('filter') === 'out_of_stock' ? 'selected' : '' }}>หมดสต๊อก</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"><i class="fas fa-search mr-1"></i>ค้นหา</button>
                <a href="{{ route('reports.stock') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ล้าง</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สินค้า</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">หมวดหมู่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขา</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">คงเหลือ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ขั้นต่ำ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ทุน</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">มูลค่า</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($stocks as $stock)
                    @php
                    $isLow = $stock->min_quantity && $stock->quantity <= $stock->min_quantity && $stock->quantity > 0;
                        $isOut = $stock->quantity <= 0;
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $isOut ? 'bg-red-50' : ($isLow ? 'bg-yellow-50' : '') }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $stock->product->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $stock->product->sku ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $stock->product->category->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $stock->branch->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold {{ $isOut ? 'text-red-600' : ($isLow ? 'text-yellow-600' : 'text-gray-900') }}">{{ number_format($stock->quantity) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500">{{ number_format($stock->min_quantity ?? 0) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500">{{ number_format($stock->product->cost ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900 font-medium">{{ number_format(($stock->quantity ?? 0) * ($stock->product->cost ?? 0), 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($isOut)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">หมด</span>
                                @elseif($isLow)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">ใกล้หมด</span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">ปกติ</span>
                                @endif
                            </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">ไม่พบข้อมูล</td>
                            </tr>
                            @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="px-6 py-4 border-t">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>
@endsection