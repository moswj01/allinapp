@extends('layouts.app')

@section('title', 'รายละเอียดหมวดหมู่')
@section('page-title', 'หมวดหมู่: ' . $category->name)

@section('content')
<div class="space-y-6">
    <!-- Category Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h2>
                <p class="text-gray-500">
                    ประเภท: {{ $category->type == 'product' ? 'สินค้า' : 'อะไหล่' }}
                </p>
            </div>
            <a href="{{ route('categories.edit', $category) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </a>
        </div>

        @if($category->description)
        <div class="mt-4">
            <p class="text-gray-600">{{ $category->description }}</p>
        </div>
        @endif

        <div class="mt-6 flex items-center space-x-6">
            <div>
                <span class="text-3xl font-bold text-indigo-600">{{ $category->products_count ?? 0 }}</span>
                <span class="text-gray-500 ml-2">สินค้า</span>
            </div>
            <div>
                <span class="text-3xl font-bold text-yellow-600">{{ $category->parts_count ?? 0 }}</span>
                <span class="text-gray-500 ml-2">อะไหล่</span>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold">รายการในหมวดหมู่นี้</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ราคา</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">สต๊อก</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $item->sku ?? '-' }}</td>
                    <td class="px-6 py-4 text-right">฿{{ number_format($item->price ?? $item->price_retail ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ $item->quantity ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        ยังไม่มีรายการในหมวดหมู่นี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($items->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $items->links() }}
        </div>
        @endif
    </div>

    <div class="flex justify-start">
        <a href="{{ route('categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
    </div>
</div>
@endsection