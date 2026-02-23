@extends('layouts.app')

@section('title', 'ตรวจนับ ' . $stockTake->stock_take_number)
@section('page-title', 'รายละเอียดการตรวจนับสต๊อก')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('stock-takes.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $stockTake->stock_take_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">ตรวจนับสต๊อก</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php $color = \App\Models\StockTake::getStatusColor($stockTake->status); $statuses = \App\Models\StockTake::getStatuses(); $types = \App\Models\StockTake::getTypes(); @endphp
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">{{ $statuses[$stockTake->status] ?? $stockTake->status }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ข้อมูลการตรวจนับ</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ประเภท:</span><span class="font-medium">{{ $types[$stockTake->type] ?? $stockTake->type }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">สาขา:</span><span>{{ $stockTake->branch->name ?? '-' }}</span></div>
                @if($stockTake->category)
                <div class="flex justify-between"><span class="text-gray-500">หมวดหมู่:</span><span>{{ $stockTake->category->name }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-500">สร้างโดย:</span><span>{{ $stockTake->createdBy->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">วันที่สร้าง:</span><span>{{ $stockTake->created_at->format('d/m/Y H:i') }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">สรุป</h3>
            @php
            $totalItems = $stockTake->items->count();
            $matched = $stockTake->items->where('difference', 0)->count();
            $surplus = $stockTake->items->where('difference', '>', 0)->count();
            $shortage = $stockTake->items->where('difference', '<', 0)->count();
                @endphp
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">จำนวนรายการ:</span><span class="font-medium">{{ $totalItems }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">ตรงกัน:</span><span class="text-green-600 font-medium">{{ $matched }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">มากกว่า:</span><span class="text-blue-600 font-medium">{{ $surplus }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">น้อยกว่า:</span><span class="text-red-600 font-medium">{{ $shortage }}</span></div>
                </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ดำเนินการ</h3>
            <div class="space-y-2">
                @if($stockTake->canBeStarted())
                <form method="POST" action="{{ route('stock-takes.start', $stockTake) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg"><i class="fas fa-play mr-1"></i>เริ่มตรวจนับ</button>
                </form>
                @endif
                @if($stockTake->canBeCompleted())
                <form method="POST" action="{{ route('stock-takes.complete', $stockTake) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg"><i class="fas fa-check mr-1"></i>เสร็จสิ้นการนับ</button>
                </form>
                @endif
                @if($stockTake->canBeApproved())
                <form method="POST" action="{{ route('stock-takes.approve', $stockTake) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg" onclick="return confirm('อนุมัติผลตรวจนับจะปรับสต๊อกตามผลนับจริง ต้องการดำเนินการ?')"><i class="fas fa-stamp mr-1"></i>อนุมัติ & ปรับสต๊อก</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if($stockTake->notes)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <p class="text-sm text-yellow-700"><strong>หมายเหตุ:</strong> {{ $stockTake->notes }}</p>
    </div>
    @endif

    {{-- Items Table with Counting --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-list mr-2 text-indigo-500"></i>รายการสินค้า</h2>
        </div>

        @if($stockTake->status === \App\Models\StockTake::STATUS_IN_PROGRESS)
        <form method="POST" action="{{ route('stock-takes.update-counts', $stockTake) }}">
            @csrf @method('PUT')
            @endif

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">สินค้า</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">ในระบบ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">นับได้</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">ส่วนต่าง</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">มูลค่าส่วนต่าง</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stockTake->items as $i => $item)
                    <tr class="{{ $item->difference != 0 ? ($item->difference > 0 ? 'bg-blue-50' : 'bg-red-50') : '' }}">
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-800">{{ $item->itemable->name ?? 'สินค้า #' . $item->itemable_id }}</td>
                        <td class="px-4 py-2 text-center text-sm">{{ $item->system_quantity }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($stockTake->status === \App\Models\StockTake::STATUS_IN_PROGRESS)
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            <input type="number" name="items[{{ $i }}][counted_quantity]" value="{{ $item->counted_quantity }}" min="0" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-center focus:ring-2 focus:ring-indigo-500">
                            @else
                            <span class="text-sm font-medium">{{ $item->counted_quantity }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center text-sm font-semibold
                        {{ $item->difference > 0 ? 'text-blue-600' : ($item->difference < 0 ? 'text-red-600' : 'text-green-600') }}">
                            {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                        </td>
                        <td class="px-4 py-2 text-right text-sm {{ $item->difference_value < 0 ? 'text-red-600' : ($item->difference_value > 0 ? 'text-blue-600' : '') }}">
                            ฿{{ number_format(abs($item->difference_value), 2) }}
                        </td>
                        <td class="px-4 py-2">
                            @if($stockTake->status === \App\Models\StockTake::STATUS_IN_PROGRESS)
                            <input type="text" name="items[{{ $i }}][notes]" value="{{ $item->notes }}" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500" placeholder="หมายเหตุ">
                            @else
                            <span class="text-sm text-gray-500">{{ $item->notes ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($stockTake->status === \App\Models\StockTake::STATUS_IN_PROGRESS)
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"><i class="fas fa-save mr-1"></i>บันทึกผลนับ</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection