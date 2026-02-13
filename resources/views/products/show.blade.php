@extends('layouts.app')

@section('title', $product->name)
@section('page-title', 'รายละเอียดสินค้า')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h2>
            <p class="text-gray-500">SKU: {{ $product->sku }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </a>
            <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Image -->
                    <div class="flex-shrink-0">
                        @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                            class="w-48 h-48 object-cover rounded-xl">
                        @else
                        <div class="w-48 h-48 bg-gray-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-gray-400 text-4xl"></i>
                        </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $product->name }}</h3>

                        <div class="flex items-center space-x-3 mb-4">
                            @if($product->is_active)
                            <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">ใช้งาน</span>
                            @else
                            <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800">ไม่ใช้งาน</span>
                            @endif
                            @if($product->category)
                            <span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">{{ $product->category->name }}</span>
                            @endif
                        </div>

                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">SKU</dt>
                                <dd class="font-mono font-medium">{{ $product->sku }}</dd>
                            </div>
                            @if($product->barcode)
                            <div>
                                <dt class="text-gray-500">บาร์โค้ด</dt>
                                <dd class="font-mono font-medium">{{ $product->barcode }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-gray-500">หน่วย</dt>
                                <dd class="font-medium">{{ $product->unit ?? 'ชิ้น' }}</dd>
                            </div>
                            @if($product->branch)
                            <div>
                                <dt class="text-gray-500">คลังสินค้า</dt>
                                <dd class="font-medium">{{ $product->branch->name }}</dd>
                            </div>
                            @endif
                        </dl>

                        @if($product->description)
                        <div class="mt-4 pt-4 border-t">
                            <h4 class="text-sm font-medium text-gray-500 mb-1">รายละเอียด</h4>
                            <p class="text-gray-700">{{ $product->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tags text-green-600 mr-2"></i>
                    ราคา
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-500">ต้นทุน</p>
                        <p class="text-xl font-bold text-gray-900">฿{{ number_format($product->cost, 0) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-green-600">ราคาปลีก</p>
                        <p class="text-xl font-bold text-green-700">฿{{ number_format($product->retail_price, 0) }}</p>
                    </div>
                    @if($product->wholesale_price)
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-blue-600">ราคาส่ง</p>
                        <p class="text-xl font-bold text-blue-700">฿{{ number_format($product->wholesale_price, 0) }}</p>
                    </div>
                    @endif
                    @if($product->vip_price)
                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-purple-600">ราคาช่าง</p>
                        <p class="text-xl font-bold text-purple-700">฿{{ number_format($product->vip_price, 0) }}</p>
                    </div>
                    @endif
                    @if($product->partner_price)
                    <div class="bg-orange-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-orange-600">ราคาออนไลน์</p>
                        <p class="text-xl font-bold text-orange-700">฿{{ number_format($product->partner_price, 0) }}</p>
                    </div>
                    @endif
                </div>

                @php
                $profit = $product->retail_price - $product->cost;
                $margin = $product->retail_price > 0 ? ($profit / $product->retail_price) * 100 : 0;
                @endphp
                <div class="mt-4 pt-4 border-t flex justify-between text-sm">
                    <span class="text-gray-500">กำไรต่อชิ้น: <span class="font-medium text-green-600">฿{{ number_format($profit, 0) }}</span></span>
                    <span class="text-gray-500">Margin: <span class="font-medium text-blue-600">{{ number_format($margin, 1) }}%</span></span>
                </div>
            </div>

            <!-- Stock Movement History -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-history text-gray-600 mr-2"></i>
                    ประวัติการเคลื่อนไหวสต๊อก
                </h3>

                @if($movements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">สาขา</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($movements as $movement)
                            <tr>
                                <td class="px-4 py-2 text-gray-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2">
                                    @php
                                    $typeColors = [
                                    'in' => 'bg-green-100 text-green-800',
                                    'out' => 'bg-red-100 text-red-800',
                                    'adjustment_in' => 'bg-blue-100 text-blue-800',
                                    'adjustment_out' => 'bg-orange-100 text-orange-800',
                                    'transfer_in' => 'bg-purple-100 text-purple-800',
                                    'transfer_out' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $typeNames = [
                                    'in' => 'รับเข้า',
                                    'out' => 'ขายออก',
                                    'adjustment_in' => 'ปรับเพิ่ม',
                                    'adjustment_out' => 'ปรับลด',
                                    'transfer_in' => 'โอนเข้า',
                                    'transfer_out' => 'โอนออก',
                                    ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$movement->type] ?? 'bg-gray-100' }}">
                                        {{ $typeNames[$movement->type] ?? $movement->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center font-medium {{ in_array($movement->type, ['in', 'adjustment_in', 'transfer_in']) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ in_array($movement->type, ['in', 'adjustment_in', 'transfer_in']) ? '+' : '-' }}{{ $movement->quantity }}
                                </td>
                                <td class="px-4 py-2 text-gray-500">{{ $movement->branch->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $movement->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p>ยังไม่มีประวัติการเคลื่อนไหว</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stock Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-warehouse text-purple-600 mr-2"></i>
                    สต๊อกตามสาขา
                </h3>

                @if($product->branchStocks->count() > 0)
                <div class="space-y-3">
                    @foreach($product->branchStocks as $stock)
                    @php
                    $stockClass = $stock->quantity <= 0 ? 'text-red-600 bg-red-50' : ($stock->quantity <= $stock->reorder_point ? 'text-yellow-600 bg-yellow-50' : 'text-green-600 bg-green-50');
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-lg {{ $stockClass }}">
                                <span class="font-medium">{{ $stock->branch->name ?? 'ไม่ระบุสาขา' }}</span>
                                <span class="text-lg font-bold">{{ $stock->quantity }}</span>
                            </div>
                            @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <p>ยังไม่มีสต๊อก</p>
                </div>
                @endif

                <div class="mt-4 pt-4 border-t">
                    <button type="button" onclick="document.getElementById('adjustStockModal').classList.remove('hidden')"
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-balance-scale mr-2"></i>ปรับปรุงสต๊อก
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">สถิติ</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">สต๊อกรวม:</dt>
                        <dd class="font-bold text-gray-900">{{ $product->branchStocks->sum('quantity') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">มูลค่าสต๊อก:</dt>
                        <dd class="font-bold text-green-600">฿{{ number_format($product->branchStocks->sum('quantity') * $product->cost, 0) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">สร้างเมื่อ:</dt>
                        <dd class="text-gray-700">{{ $product->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">อัปเดตล่าสุด:</dt>
                        <dd class="text-gray-700">{{ $product->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div id="adjustStockModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('adjustStockModal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">ปรับปรุงสต๊อก</h3>

            <form action="{{ route('products.adjust-stock', $product) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนที่ปรับ</label>
                    <input type="number" name="adjustment" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ใส่จำนวน (+ เพิ่ม, - ลด)">
                    <p class="text-xs text-gray-500 mt-1">ใส่ค่าบวกเพื่อเพิ่มสต๊อก, ค่าลบเพื่อลดสต๊อก</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผล</label>
                    <select name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกเหตุผล --</option>
                        <option value="ตรวจนับสต๊อก">ตรวจนับสต๊อก</option>
                        <option value="สินค้าเสียหาย">สินค้าเสียหาย</option>
                        <option value="สินค้าหาย">สินค้าหาย</option>
                        <option value="คืนสินค้าจากลูกค้า">คืนสินค้าจากลูกค้า</option>
                        <option value="รับสินค้าเพิ่ม">รับสินค้าเพิ่ม</option>
                        <option value="อื่นๆ">อื่นๆ</option>
                    </select>
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                        onclick="document.getElementById('adjustStockModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-check mr-2"></i>ยืนยัน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection