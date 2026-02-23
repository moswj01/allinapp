@extends('layouts.app')

@section('title', 'รายงานจัดซื้อ')
@section('page-title', 'รายงานจัดซื้อ')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">รายงานจัดซื้อ</h2>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="all">ทั้งหมด</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>ร่าง</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติ</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>รับบางส่วน</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>รับครบ</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"><i class="fas fa-search mr-1"></i>ค้นหา</button>
                <a href="{{ route('reports.purchasing') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ล้าง</a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">ใบสั่งซื้อทั้งหมด</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_count']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">มูลค่ารวม</p>
            <p class="text-2xl font-bold text-indigo-600">฿{{ number_format($summary['total_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">รับสินค้าแล้ว</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($summary['received']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">ร่าง / ยกเลิก</p>
            <p class="text-2xl font-bold text-gray-600">{{ $summary['draft'] }} / {{ $summary['cancelled'] }}</p>
        </div>
    </div>

    {{-- Top Suppliers --}}
    @if($topSuppliers->count() > 0)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-truck text-indigo-600 mr-2"></i>ซัพพลายเออร์ยอดนิยม</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600">#</th>
                        <th class="px-4 py-3 text-left text-gray-600">ซัพพลายเออร์</th>
                        <th class="px-4 py-3 text-right text-gray-600">จำนวน PO</th>
                        <th class="px-4 py-3 text-right text-gray-600">มูลค่ารวม</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSuppliers as $i => $sup)
                    <tr class="border-t">
                        <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $sup->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($sup->order_count) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-indigo-600">฿{{ number_format($sup->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- PO List --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-list text-gray-600 mr-2"></i>รายการใบสั่งซื้อ</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ซัพพลายเออร์</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขา</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">มูลค่า</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ $po->po_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $po->supplier->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $po->branch->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ number_format($po->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                            $colors = ['draft'=>'bg-gray-100 text-gray-800','approved'=>'bg-blue-100 text-blue-800','partial'=>'bg-yellow-100 text-yellow-800','received'=>'bg-green-100 text-green-800','cancelled'=>'bg-red-100 text-red-800'];
                            $labels = ['draft'=>'ร่าง','approved'=>'อนุมัติ','partial'=>'รับบางส่วน','received'=>'รับครบ','cancelled'=>'ยกเลิก'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$po->status] ?? 'bg-gray-100 text-gray-800' }}">{{ $labels[$po->status] ?? $po->status }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $po->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">ไม่พบข้อมูล</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="px-6 py-4 border-t">{{ $purchaseOrders->links() }}</div>
        @endif
    </div>
</div>
@endsection