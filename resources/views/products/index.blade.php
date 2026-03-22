@extends('layouts.app')

@section('title', 'จัดการสินค้า')
@section('page-title', 'สินค้า')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">สินค้าทั้งหมด</h2>
            <p class="text-gray-500">{{ $products->total() }} รายการ</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.export-csv') }}"
                class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-download mr-2"></i>Export CSV
            </a>
            @if(Auth::user()->hasPermission('products.create'))
            <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                class="inline-flex items-center px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors">
                <i class="fas fa-file-csv mr-2"></i>Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>เพิ่มสินค้าใหม่
            </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อ, SKU, บาร์โค้ด...">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>ใช้งาน</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สต๊อก</label>
                <select name="stock_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>ใกล้หมด</option>
                    <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>หมดสต๊อก</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ซัพพลายเออร์</label>
                <select name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-5 flex justify-end space-x-3">
                <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    ล้างตัวกรอง
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i>กรอง
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สินค้า</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ต้นทุน</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ราคาขาย</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สต๊อก</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    @if($product->image)
                                    <img class="h-12 w-12 rounded-lg object-cover" src="{{ Storage::url($product->image) }}" alt="">
                                    @else
                                    <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-box text-gray-400"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $product->name }}
                                    </a>
                                    @if($product->barcode)
                                    <p class="text-xs text-gray-500">{{ $product->barcode }}</p>
                                    @endif
                                    @if($product->supplier)
                                    <p class="text-xs text-gray-400"><i class="fas fa-truck text-xs mr-1"></i>{{ $product->supplier->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $product->sku }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $product->category->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            ฿{{ number_format($product->cost ?? 0, 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 text-right">
                            ฿{{ number_format($product->retail_price ?? 0, 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                            $stockQty = $product->stock_quantity ?? 0;
                            $stockClass = $stockQty <= 0 ? 'bg-red-100 text-red-800' : ($stockQty <=5 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' );
                                @endphp
                                <span class="px-3 py-1 text-sm rounded-full {{ $stockClass }}">
                                {{ $stockQty }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($product->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">ใช้งาน</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">ไม่ใช้งาน</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('products.show', $product) }}" class="text-gray-400 hover:text-gray-600" title="ดู">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(Auth::user()->hasPermission('products.edit'))
                                <a href="{{ route('products.edit', $product) }}" class="text-blue-400 hover:text-blue-600" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(Auth::user()->hasPermission('products.delete'))
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                    onsubmit="return confirm('ต้องการลบสินค้านี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600" title="ลบ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-4"></i>
                            <p>ยังไม่มีสินค้า</p>
                            @if(Auth::user()->hasPermission('products.create'))
                            <a href="{{ route('products.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline">
                                เพิ่มสินค้าใหม่
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- Import CSV Modal -->
    <div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-file-csv text-green-600 mr-2"></i>Import สินค้าจาก CSV
                    </h3>
                    <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('products.import-csv') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">เลือกไฟล์ CSV</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" required
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 p-2 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">รองรับไฟล์ .csv ขนาดไม่เกิน 5MB</p>
                    </div>

                    <div class="mb-4 bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">คอลัมน์ที่รองรับ:</p>
                        <div class="grid grid-cols-2 gap-1 text-xs text-gray-600">
                            <span><strong class="text-red-500">*</strong> sku</span>
                            <span>barcode</span>
                            <span><strong class="text-red-500">*</strong> name</span>
                            <span>category (ชื่อหมวดหมู่)</span>
                            <span>supplier (ชื่อ)</span>
                            <span>description</span>
                            <span>unit (ชิ้น/อัน/...)</span>
                            <span>cost (ต้นทุน)</span>
                            <span><strong class="text-red-500">*</strong> retail_price (ขายปลีก)</span>
                            <span>wholesale_price (ขายส่ง)</span>
                            <span>vip_price (ช่าง)</span>
                            <span>partner_price (ออนไลน์)</span>
                            <span>initial_stock (จำนวน)</span>
                            <span>reorder_point</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <strong class="text-red-500">*</strong> จำเป็น | ถ้า SKU ซ้ำจะอัปเดทข้อมูลเดิม
                        </p>
                    </div>

                    <div class="mb-4">
                        <a href="{{ route('products.import-template') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-download mr-1"></i>ดาวน์โหลดไฟล์ตัวอย่าง
                        </a>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-upload mr-1"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection