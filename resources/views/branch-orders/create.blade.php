@extends('layouts.app')

@section('title', 'สร้างใบสั่งซื้อจากสาขาใหญ่')

@section('content')
<div x-data="branchOrderForm()" class="h-[calc(100vh-140px)]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
        <!-- Products Section (Left) -->
        <div class="lg:col-span-2 flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('branch-orders.index') }}" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">สร้างใบสั่งซื้อจากสาขาใหญ่</h1>
                        <p class="text-xs text-gray-500">
                            จาก: <span class="font-medium text-indigo-600">{{ $mainBranch->name }}</span>
                            → <span class="font-medium text-green-600">{{ Auth::user()->branch->name ?? '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search & Categories -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" x-model="search" @input="filterProducts()"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="ค้นหาสินค้า... (ชื่อ, SKU, บาร์โค้ด)">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex gap-2 overflow-x-auto">
                        <button type="button" @click="selectedCategory = null; filterProducts()"
                            :class="selectedCategory === null ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap text-sm">
                            ทั้งหมด
                        </button>
                        @foreach($categories as $category)
                        <button type="button" @click="selectedCategory = {{ $category->id }}; filterProducts()"
                            :class="selectedCategory === {{ $category->id }} ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap text-sm">
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="flex-1 bg-white rounded-xl shadow-sm p-4 overflow-y-auto">
                <!-- View Toggle -->
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">สินค้าทั้งหมด</span>
                    <div class="flex border border-gray-300 rounded-lg overflow-hidden">
                        <button type="button" @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600'"
                            class="px-3 py-1.5 text-sm">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600'"
                            class="px-3 py-1.5 text-sm">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <!-- Grid View -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($products as $product)
                    @php
                    $stock = $product->branchStocks->first();
                    $qty = $stock ? $stock->quantity : 0;
                    @endphp
                    <div class="product-item border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-indigo-500 hover:shadow-md transition-all"
                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                        data-sku="{{ $product->sku ?? '' }}" data-barcode="{{ $product->barcode ?? '' }}"
                        data-cost="{{ $product->cost }}" data-stock="{{ $qty }}"
                        data-category="{{ $product->category_id }}"
                        @click="addItem(productPayload($event.currentTarget))">
                        <div
                            class="aspect-square bg-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="" class="w-full h-full object-cover">
                            @else
                            <i class="fas fa-box text-gray-400 text-2xl"></i>
                            @endif
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <span
                                class="text-sm font-bold text-green-600">฿{{ number_format($product->cost, 0) }}</span>
                            <span class="text-xs {{ $qty > 0 ? 'text-gray-500' : 'text-red-500' }}">
                                {{ $qty > 0 ? 'คงเหลือ ' . $qty : 'หมด' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Table View -->
                <div x-show="viewMode === 'table'">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">สินค้า</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">ราคาทุน
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">คงเหลือ
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($products as $product)
                            @php
                            $stock = $product->branchStocks->first();
                            $qty = $stock ? $stock->quantity : 0;
                            @endphp
                            <tr class="product-item hover:bg-indigo-50 cursor-pointer transition-colors"
                                data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                data-sku="{{ $product->sku ?? '' }}" data-barcode="{{ $product->barcode ?? '' }}"
                                data-cost="{{ $product->cost }}" data-stock="{{ $qty }}"
                                data-category="{{ $product->category_id }}"
                                @click="addItem(productPayload($event.currentTarget))">
                                <td class="px-3 py-2">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center mr-2 overflow-hidden">
                                            @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt=""
                                                class="w-full h-full object-cover">
                                            @else
                                            <i class="fas fa-box text-gray-400 text-xs"></i>
                                            @endif
                                        </div>
                                        <span class="font-medium text-gray-900 truncate">{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-gray-500">{{ $product->sku }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-green-600">
                                    ฿{{ number_format($product->cost, 0) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="{{ $qty > 0 ? 'text-gray-700' : 'text-red-500 font-semibold' }}">{{ $qty > 0 ? $qty : 'หมด' }}</span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-indigo-500"><i class="fas fa-plus-circle"></i></span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cart Section (Right) -->
        <div class="flex flex-col bg-white rounded-xl shadow-sm">
            <form method="POST" action="{{ route('branch-orders.store') }}" class="flex flex-col h-full">
                @csrf
                <!-- Cart Header -->
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-800">
                        <i class="fas fa-shopping-cart mr-2 text-indigo-500"></i>
                        รายการสั่งซื้อ (<span x-text="items.length"></span>)
                    </h3>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4">
                    <template x-if="items.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-2"></i>
                            <p>ยังไม่มีสินค้า</p>
                            <p class="text-sm">คลิกสินค้าเพื่อเพิ่ม</p>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 text-sm" x-text="item.name"></h4>
                                    <p class="text-xs text-gray-500"
                                        x-text="'฿' + parseFloat(item.unit_cost).toLocaleString() + ' /ชิ้น'"></p>
                                    <input type="hidden" :name="'items['+index+'][product_id]'"
                                        :value="item.product_id">
                                    <input type="hidden" :name="'items['+index+'][notes]'" :value="item.notes">
                                </div>
                                <div class="flex items-center space-x-1">
                                    <button type="button" @click="decrementQty(index)"
                                        class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded text-gray-600 flex items-center justify-center text-xs">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" :name="'items['+index+'][quantity]'"
                                        x-model.number="item.quantity" min="1"
                                        class="w-12 text-center border border-gray-300 rounded py-1 text-sm"
                                        @change="clampQty(index)">
                                    <button type="button" @click="incrementQty(index)"
                                        class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded text-gray-600 flex items-center justify-center text-xs">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="text-right min-w-[60px]">
                                    <p class="text-sm font-semibold text-gray-900"
                                        x-text="'฿' + (item.quantity * parseFloat(item.unit_cost)).toLocaleString()">
                                    </p>
                                    <button type="button" @click="removeItem(index)"
                                        class="text-red-400 hover:text-red-600 text-xs mt-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Notes -->
                <div class="px-4 pb-2">
                    <textarea name="notes" rows="2" placeholder="หมายเหตุ (ถ้ามี)..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Total & Submit -->
                <div class="p-4 border-t space-y-3">
                    <div class="flex items-center justify-between text-lg font-bold">
                        <span class="text-gray-700">รวมทั้งหมด</span>
                        <span class="text-indigo-700" x-text="'฿' + totalAmount.toLocaleString()"></span>
                    </div>

                    <button type="submit" x-bind:disabled="items.length === 0"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white rounded-lg font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>ส่งใบสั่งซื้อ</span>
                    </button>

                    <button type="button" @click="clearAll()"
                        class="w-full py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
                        <i class="fas fa-trash mr-2"></i>ล้างรายการ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function branchOrderForm() {
    return {
        search: '',
        selectedCategory: null,
        viewMode: 'grid',
        items: [],

        filterProducts() {
            const search = this.search.toLowerCase();
            const category = this.selectedCategory;

            document.querySelectorAll('.product-item').forEach(el => {
                const name = (el.dataset.name || '').toLowerCase();
                const sku = (el.dataset.sku || '').toLowerCase();
                const barcode = (el.dataset.barcode || '').toLowerCase();
                const cat = parseInt(el.dataset.category) || 0;

                const matchSearch = !search || name.includes(search) || sku.includes(search) || barcode
                    .includes(search);
                const matchCategory = !category || cat === category;

                el.style.display = (matchSearch && matchCategory) ? '' : 'none';
            });
        },

        productPayload(el) {
            return {
                id: parseInt(el.dataset.id),
                name: el.dataset.name,
                cost: parseFloat(el.dataset.cost || 0),
                stock: parseInt(el.dataset.stock || 0)
            };
        },

        addItem(product) {
            const existing = this.items.find(i => i.product_id === product.id);
            if (existing) {
                existing.quantity++;
                return;
            }
            this.items.push({
                product_id: product.id,
                name: product.name,
                quantity: 1,
                unit_cost: product.cost,
                notes: '',
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        incrementQty(index) {
            this.items[index].quantity++;
        },

        decrementQty(index) {
            if (this.items[index].quantity > 1) {
                this.items[index].quantity--;
            }
        },

        clampQty(index) {
            if (this.items[index].quantity < 1) this.items[index].quantity = 1;
        },

        clearAll() {
            if (this.items.length === 0) return;
            if (confirm('ล้างรายการทั้งหมด?')) {
                this.items = [];
            }
        },

        get totalAmount() {
            return this.items.reduce((sum, item) => sum + (item.quantity * parseFloat(item.unit_cost)), 0);
        }
    };
}
</script>
@endpush
@endsection