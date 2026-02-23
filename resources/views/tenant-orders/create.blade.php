@extends('layouts.app')

@section('title', 'สั่งสินค้าจากร้านกลาง')

@section('content')
<div x-data="orderForm()" x-init="init()">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">สั่งสินค้าจาก {{ $adminTenant->name }}</h1>
            <p class="text-sm text-gray-500">เลือกสินค้าและจำนวนที่ต้องการ</p>
        </div>
        <a href="{{ route('tenant-orders.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i>กลับ
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Catalog (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Search & Filter -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="filterProducts()"
                                placeholder="ค้นหาสินค้า..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <select x-model="categoryFilter" @change="filterProducts()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">ทุกหมวดหมู่</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer border-2"
                        :class="isInCart(product.id) ? 'border-indigo-400 bg-indigo-50' : 'border-transparent'"
                        @click="addToCart(product)">
                        <div class="w-full h-24 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                            <i class="fas fa-box text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="font-medium text-sm text-gray-800 line-clamp-2" x-text="product.name"></h3>
                        <p class="text-xs text-gray-400 mt-1" x-text="product.sku || '-'"></p>
                        <p class="text-xs text-gray-400" x-text="product.category_name || ''"></p>
                        <p class="mt-2 text-indigo-600 font-bold text-sm">
                            ฿<span x-text="numberFormat(product.price)"></span>
                        </p>
                        <template x-if="isInCart(product.id)">
                            <div class="mt-2 flex items-center justify-center bg-indigo-100 rounded-lg py-1">
                                <span class="text-xs text-indigo-700 font-medium">
                                    <i class="fas fa-check mr-1"></i>ในตะกร้า (<span x-text="getCartQty(product.id)"></span>)
                                </span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div x-show="filteredProducts.length === 0" class="bg-white rounded-xl p-8 text-center text-gray-400">
                <i class="fas fa-box-open text-4xl mb-3"></i>
                <p>ไม่พบสินค้า</p>
            </div>
        </div>

        <!-- Cart Sidebar (1 col) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-4 sticky top-4">
                <h2 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-shopping-cart text-indigo-600 mr-2"></i>
                    ตะกร้าสินค้า
                    <span class="ml-auto bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full"
                        x-text="cart.length + ' รายการ'"></span>
                </h2>

                <div x-show="cart.length === 0" class="py-8 text-center text-gray-400">
                    <i class="fas fa-cart-plus text-3xl mb-2"></i>
                    <p class="text-sm">คลิกสินค้าเพื่อเพิ่มในตะกร้า</p>
                </div>

                <div class="space-y-3 max-h-[400px] overflow-y-auto">
                    <template x-for="(item, index) in cart" :key="item.product_id">
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400">฿<span x-text="numberFormat(item.price)"></span>/ชิ้น</p>
                                </div>
                                <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 ml-2">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <button @click="changeQty(index, -1)" class="w-7 h-7 bg-gray-100 rounded text-gray-600 hover:bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <input type="number" x-model.number="item.quantity" min="1"
                                    class="w-14 text-center border border-gray-300 rounded text-sm py-1"
                                    @change="item.quantity = Math.max(1, item.quantity)">
                                <button @click="changeQty(index, 1)" class="w-7 h-7 bg-gray-100 rounded text-gray-600 hover:bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                                <span class="ml-auto text-sm font-medium text-gray-800">
                                    ฿<span x-text="numberFormat(item.price * item.quantity)"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Totals -->
                <template x-if="cart.length > 0">
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">ยอดสินค้า</span>
                            <span class="font-medium">฿<span x-text="numberFormat(cartTotal())"></span></span>
                        </div>
                        <div class="flex justify-between text-lg font-bold mt-3">
                            <span>ยอดรวม</span>
                            <span class="text-indigo-600">฿<span x-text="numberFormat(cartTotal())"></span></span>
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-gray-500 mb-1">หมายเหตุ</label>
                            <textarea x-model="notes" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
                        </div>

                        <button @click="submitOrder()" :disabled="submitting"
                            class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane" x-show="!submitting"></i>
                            <i class="fas fa-spinner fa-spin" x-show="submitting"></i>
                            <span x-text="submitting ? 'กำลังส่ง...' : 'ส่งคำสั่งซื้อ'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function orderForm() {
        return {
            allProducts: @json($products - > map(fn($p) => [
                'id' => $p - > id,
                'name' => $p - > name,
                'sku' => $p - > sku,
                'barcode' => $p - > barcode,
                'price' => $p - > wholesale_price > 0 ? $p - > wholesale_price : $p - > retail_price,
                'category_id' => $p - > category_id,
                'category_name' => $p - > category ? - > name,
            ])),
            filteredProducts: [],
            cart: [],
            searchQuery: '',
            categoryFilter: '',
            notes: '',
            submitting: false,

            init() {
                this.filteredProducts = this.allProducts;
            },

            filterProducts() {
                let result = this.allProducts;
                if (this.categoryFilter) {
                    result = result.filter(p => p.category_id == this.categoryFilter);
                }
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(p =>
                        (p.name && p.name.toLowerCase().includes(q)) ||
                        (p.sku && p.sku.toLowerCase().includes(q)) ||
                        (p.barcode && p.barcode.toLowerCase().includes(q))
                    );
                }
                this.filteredProducts = result;
            },

            addToCart(product) {
                const existing = this.cart.find(i => i.product_id === product.id);
                if (existing) {
                    existing.quantity++;
                } else {
                    this.cart.push({
                        product_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        price: product.price,
                        quantity: 1,
                    });
                }
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
            },

            changeQty(index, delta) {
                this.cart[index].quantity = Math.max(1, this.cart[index].quantity + delta);
            },

            isInCart(productId) {
                return this.cart.some(i => i.product_id === productId);
            },

            getCartQty(productId) {
                const item = this.cart.find(i => i.product_id === productId);
                return item ? item.quantity : 0;
            },

            cartTotal() {
                return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
            },

            numberFormat(num) {
                return Number(num || 0).toLocaleString('th-TH', {
                    minimumFractionDigits: 2
                });
            },

            async submitOrder() {
                if (this.cart.length === 0) {
                    alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ');
                    return;
                }
                if (!confirm('ยืนยันส่งคำสั่งซื้อ?')) return;

                this.submitting = true;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("tenant-orders.store") }}';

                // CSRF
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrf);

                // Items
                this.cart.forEach((item, i) => {
                    ['product_id', 'quantity'].forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `items[${i}][${field}]`;
                        input.value = item[field];
                        form.appendChild(input);
                    });
                });

                // Notes
                if (this.notes) {
                    const notesInput = document.createElement('input');
                    notesInput.type = 'hidden';
                    notesInput.name = 'notes';
                    notesInput.value = this.notes;
                    form.appendChild(notesInput);
                }

                document.body.appendChild(form);
                form.submit();
            }
        }
    }
</script>
@endpush
@endsection