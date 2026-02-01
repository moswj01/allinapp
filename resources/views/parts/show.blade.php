@extends('layouts.app')

@section('title', 'รายละเอียดอะไหล่')
@section('page-title', 'อะไหล่: ' . $part->name)

@section('content')
<div class="space-y-6">
    <!-- Part Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Image -->
            <div class="w-full md:w-48 flex-shrink-0">
                @if($part->image)
                <img src="{{ Storage::url($part->image) }}" alt="{{ $part->name }}" class="w-full h-48 object-cover rounded-lg">
                @else
                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-4xl text-gray-300"></i>
                </div>
                @endif
            </div>

            <!-- Info -->
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $part->name }}</h2>
                        <p class="text-gray-500">SKU: {{ $part->sku ?? '-' }} | Barcode: {{ $part->barcode ?? '-' }}</p>
                    </div>
                    <a href="{{ route('parts.edit', $part) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-edit mr-2"></i>แก้ไข
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-500">ราคาทุน</p>
                        <p class="text-lg font-bold text-blue-600">฿{{ number_format($part->cost, 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-500">ราคาขาย</p>
                        <p class="text-lg font-bold text-green-600">฿{{ number_format($part->price, 2) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-gray-500">สต๊อกรวม</p>
                        <p class="text-lg font-bold text-yellow-600">{{ $part->quantity }} {{ $part->unit }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">สต๊อกขั้นต่ำ</p>
                        <p class="text-lg font-bold text-gray-600">{{ $part->min_stock }} {{ $part->unit }}</p>
                    </div>
                </div>

                @if($part->compatible_models)
                <div class="mt-4">
                    <p class="text-sm text-gray-500">รุ่นที่รองรับ:</p>
                    <p class="text-gray-700">{{ $part->compatible_models }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stock by Branch -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-warehouse text-indigo-500 mr-2"></i>สต๊อกแยกตามสาขา
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขา</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">สต๊อกขั้นต่ำ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($branchStocks as $stock)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $stock->branch?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ $stock->quantity }}</td>
                        <td class="px-4 py-3 text-right">{{ $stock->min_stock }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($stock->quantity <= 0)
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">หมด</span>
                                @elseif($stock->quantity <= $stock->min_stock)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">ใกล้หมด</span>
                                    @else
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">ปกติ</span>
                                    @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">ยังไม่มีข้อมูลสต๊อก</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-history text-green-500 mr-2"></i>ประวัติเคลื่อนไหว
        </h3>
        <div class="space-y-3">
            @forelse($movements as $movement)
            <div class="flex items-center justify-between py-3 border-b last:border-0">
                <div class="flex items-center">
                    @if($movement->type === 'in')
                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-arrow-down"></i>
                    </span>
                    @else
                    <span class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-arrow-up"></i>
                    </span>
                    @endif
                    <div>
                        <p class="font-medium">{{ $movement->type === 'in' ? 'รับเข้า' : 'เบิกออก' }} {{ $movement->quantity }} {{ $part->unit }}</p>
                        <p class="text-sm text-gray-500">{{ $movement->reason ?? $movement->notes ?? '-' }}</p>
                    </div>
                </div>
                <div class="text-right text-sm text-gray-500">
                    <p>{{ $movement->branch?->name }}</p>
                    <p>{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-500 py-4">ยังไม่มีประวัติเคลื่อนไหว</p>
            @endforelse
        </div>
    </div>

    <div class="flex justify-start">
        <a href="{{ route('parts.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
    </div>
</div>
@endsection