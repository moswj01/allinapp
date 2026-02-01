@extends('layouts.app')

@section('title', 'POS - ขายสินค้า')
@section('page-title', 'ขายสินค้า (POS)')

@section('content')
<div x-data="posApp()" class="h-[calc(100vh-140px)]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
        <!-- Products Section -->
        <div class="lg:col-span-2 flex flex-col">
            <!-- Search & Categories -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" x-model="search" @input="filterProducts()"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="ค้นหาสินค้า, สแกนบาร์โค้ด..." @keydown.enter="handleBarcodeScan()">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex gap-2 overflow-x-auto">
                        <button @click="selectedCategory = null; filterProducts()"
                            :class="selectedCategory === null ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap">
                            ทั้งหมด
                        </button>
                        @php
                        $categories = \App\Models\Category::where('type', 'product')->where('is_active', true)->get();
                        @endphp
                        @foreach($categories as $category)
                        <button @click="selectedCategory = {{ $category->id }}; filterProducts()"
                            :class="selectedCategory === {{ $category->id }} ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap">
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 bg-white rounded-xl shadow-sm p-4 overflow-y-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($products as $product)
                    @php
                    $stock = $product->branchStocks->first();
                    $qty = $stock ? $stock->quantity : 0;
                    @endphp
                    <div class="product-item border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-indigo-500 hover:shadow-md transition-all"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-sku="{{ $product->sku }}"
                        data-barcode="{{ $product->barcode }}"
                        data-price="{{ $product->retail_price }}"
                        data-price-wholesale="{{ $product->wholesale_price ?? $product->retail_price }}"
                        data-price-technician="{{ $product->retail_price }}"
                        data-stock="{{ $qty }}"
                        data-category="{{ $product->category_id }}"
                        @click="addToCart(productPayload($event.currentTarget))">
                        <div class="aspect-square bg-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="" class="w-full h-full object-cover">
                            @else
                            <i class="fas fa-box text-gray-400 text-2xl"></i>
                            @endif
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-bold text-green-600">฿{{ number_format($product->retail_price, 0) }}</span>
                            <span class="text-xs {{ $qty > 0 ? 'text-gray-500' : 'text-red-500' }}">
                                {{ $qty > 0 ? 'คงเหลือ ' . $qty : 'หมด' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="flex flex-col bg-white rounded-xl shadow-sm">
            <!-- Customer -->
            <div class="p-4 border-b">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-800">ลูกค้า</h3>
                    <select x-model="customerType" @change="updatePrices()" class="text-sm border border-gray-300 rounded-lg px-2 py-1">
                        <option value="retail">ปลีก</option>
                        <option value="wholesale">ส่ง</option>
                        <option value="technician">ช่าง</option>
                    </select>
                </div>
                <select x-model="customerId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ลูกค้าทั่วไป</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" data-type="{{ $customer->type }}">
                        {{ $customer->name }} - {{ $customer->phone }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                <template x-if="cart.length === 0">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                        <p>ยังไม่มีสินค้าในตะกร้า</p>
                        <p class="text-sm">คลิกสินค้าเพื่อเพิ่ม</p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 text-sm" x-text="item.name"></h4>
                                <p class="text-xs text-gray-500" x-text="item.sku"></p>
                                <div class="flex items-center space-x-2 mt-2">
                                    <button @click="decreaseQty(index)" class="w-7 h-7 bg-gray-200 rounded-full hover:bg-gray-300">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input type="number" x-model.number="item.quantity" min="1" :max="item.stock"
                                        @change="validateQty(index)"
                                        class="w-16 text-center border border-gray-300 rounded-lg py-1 text-sm">
                                    <button @click="increaseQty(index)" class="w-7 h-7 bg-gray-200 rounded-full hover:bg-gray-300">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right">
                                <input type="number" x-model.number="item.price" min="0"
                                    class="w-20 text-right border border-gray-300 rounded-lg px-2 py-1 text-sm mb-1">
                                <p class="text-sm font-bold text-green-600" x-text="'฿' + formatNumber(item.price * item.quantity)"></p>
                            </div>
                            <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-4 border-t bg-gray-50">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">รวม (<span x-text="cart.length"></span> รายการ)</span>
                        <span x-text="'฿' + formatNumber(subtotal)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">ส่วนลด</span>
                        <div class="flex items-center space-x-2">
                            <input type="number" x-model.number="discount" min="0"
                                class="w-24 text-right border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            <span>฿</span>
                        </div>
                    </div>
                    <div class="flex justify-between pt-2 border-t text-lg font-bold">
                        <span>รวมสุทธิ</span>
                        <span class="text-green-600" x-text="'฿' + formatNumber(total)"></span>
                    </div>
                </div>

                <!-- Payment -->
                <div class="mt-4 space-y-3">
                    <div class="grid grid-cols-4 gap-2">
                        <button @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="py-2 rounded-lg text-sm">
                            <i class="fas fa-money-bill-wave"></i><br>เงินสด
                        </button>
                        <button @click="paymentMethod = 'transfer'"
                            :class="paymentMethod === 'transfer' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="py-2 rounded-lg text-sm">
                            <i class="fas fa-university"></i><br>โอน
                        </button>
                        <button @click="paymentMethod = 'qr'"
                            :class="paymentMethod === 'qr' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="py-2 rounded-lg text-sm">
                            <i class="fas fa-qrcode"></i><br>QR
                        </button>
                        <button @click="paymentMethod = 'credit'"
                            :class="paymentMethod === 'credit' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="py-2 rounded-lg text-sm">
                            <i class="fas fa-file-invoice"></i><br>เครดิต
                        </button>
                    </div>

                    <template x-if="paymentMethod === 'cash'">
                        <div class="grid grid-cols-4 gap-2">
                            <button @click="receivedAmount = total" class="py-2 bg-gray-100 rounded-lg text-sm">พอดี</button>
                            <button @click="receivedAmount = Math.ceil(total/100)*100" class="py-2 bg-gray-100 rounded-lg text-sm" x-text="formatNumber(Math.ceil(total/100)*100)"></button>
                            <button @click="receivedAmount = Math.ceil(total/500)*500" class="py-2 bg-gray-100 rounded-lg text-sm" x-text="formatNumber(Math.ceil(total/500)*500)"></button>
                            <button @click="receivedAmount = Math.ceil(total/1000)*1000" class="py-2 bg-gray-100 rounded-lg text-sm" x-text="formatNumber(Math.ceil(total/1000)*1000)"></button>
                        </div>
                    </template>

                    <template x-if="paymentMethod === 'cash'">
                        <div class="flex items-center justify-between bg-yellow-50 p-3 rounded-lg">
                            <div>
                                <label class="block text-sm text-gray-500">รับเงิน</label>
                                <input type="number" x-model.number="receivedAmount" min="0"
                                    class="w-32 text-lg font-bold border border-gray-300 rounded-lg px-2 py-1">
                            </div>
                            <div class="text-right">
                                <label class="block text-sm text-gray-500">เงินทอน</label>
                                <span class="text-xl font-bold" :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
                                    x-text="'฿' + formatNumber(change)"></span>
                            </div>
                        </div>
                    </template>

                    <button @click="completeSale()"
                        :disabled="cart.length === 0 || loading"
                        class="w-full py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                        <template x-if="!loading">
                            <span><i class="fas fa-check mr-2"></i>ชำระเงิน</span>
                        </template>
                        <template x-if="loading">
                            <span><i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...</span>
                        </template>
                    </button>

                    <button @click="clearCart()" class="w-full py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-trash mr-2"></i>ล้างตะกร้า
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function posApp() {
        return {
            search: '',
            selectedCategory: null,
            cart: [],
            customerId: '',
            customerType: 'retail',
            discount: 0,
            paymentMethod: 'cash',
            receivedAmount: 0,
            loading: false,

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            get total() {
                return Math.max(0, this.subtotal - this.discount);
            },

            get change() {
                return this.receivedAmount - this.total;
            },

            formatNumber(num) {
                return new Intl.NumberFormat('th-TH').format(num);
            },

            filterProducts() {
                const search = this.search.toLowerCase();
                const category = this.selectedCategory;

                document.querySelectorAll('.product-item').forEach(el => {
                    const name = el.dataset.name.toLowerCase();
                    const sku = el.dataset.sku.toLowerCase();
                    const barcode = el.dataset.barcode?.toLowerCase() || '';
                    const catId = parseInt(el.dataset.category);

                    const matchSearch = !search || name.includes(search) || sku.includes(search) || barcode.includes(search);
                    const matchCategory = !category || catId === category;

                    el.style.display = (matchSearch && matchCategory) ? 'block' : 'none';
                });
            },

            handleBarcodeScan() {
                const barcode = this.search.trim();
                if (!barcode) return;

                const el = document.querySelector(`.product-item[data-barcode="${barcode}"], .product-item[data-sku="${barcode}"]`);
                if (el) {
                    this.addToCart({
                        type: 'product',
                        id: parseInt(el.dataset.id),
                        name: el.dataset.name,
                        sku: el.dataset.sku,
                        price: parseFloat(el.dataset.price),
                        stock: parseInt(el.dataset.stock)
                    });
                    this.search = '';
                }
            },

            addToCart(item) {
                if (item.stock <= 0) {
                    alert('สินค้าหมด');
                    return;
                }

                const existing = this.cart.find(c => c.type === item.type && c.id === item.id);
                if (existing) {
                    if (existing.quantity < item.stock) {
                        existing.quantity++;
                    } else {
                        alert('สินค้าในสต๊อกไม่เพียงพอ');
                    }
                } else {
                    this.cart.push({
                        ...item,
                        quantity: 1
                    });
                }
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
            },

            increaseQty(index) {
                const item = this.cart[index];
                if (item.quantity < item.stock) {
                    item.quantity++;
                }
            },

            decreaseQty(index) {
                const item = this.cart[index];
                if (item.quantity > 1) {
                    item.quantity--;
                }
            },

            validateQty(index) {
                const item = this.cart[index];
                if (item.quantity < 1) item.quantity = 1;
                if (item.quantity > item.stock) item.quantity = item.stock;
            },

            updatePrices() {
                const priceKey = {
                    'retail': 'price',
                    'wholesale': 'price-wholesale',
                    'technician': 'price-technician'
                } [this.customerType];

                this.cart.forEach(item => {
                    const el = document.querySelector(`.product-item[data-id="${item.id}"]`);
                    if (el) {
                        item.price = parseFloat(el.dataset[priceKey] || el.dataset.price);
                    }
                });
            },

            clearCart() {
                if (this.cart.length === 0 || confirm('ต้องการล้างตะกร้าทั้งหมด?')) {
                    this.cart = [];
                    this.discount = 0;
                    this.receivedAmount = 0;
                }
            },

            async completeSale() {
                if (this.cart.length === 0) return;

                if (this.paymentMethod === 'cash' && this.receivedAmount < this.total) {
                    alert('จำนวนเงินที่รับไม่เพียงพอ');
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('{{ route("sales.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            customer_id: this.customerId || null,
                            items: this.cart.map(item => ({
                                type: item.type,
                                id: item.id,
                                quantity: item.quantity,
                                price: item.price
                            })),
                            discount: this.discount,
                            payment_method: this.paymentMethod,
                            received_amount: this.receivedAmount
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('บันทึกการขายเรียบร้อย\nเลขที่: ' + data.sale.sale_number);

                        // Reset
                        this.cart = [];
                        this.discount = 0;
                        this.receivedAmount = 0;
                        this.customerId = '';

                        // Open receipt in new tab
                        window.open(data.redirect, '_blank');

                        // Refresh page to update stock
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + (data.error || 'Unknown error'));
                    }
                } catch (error) {
                    alert('เกิดข้อผิดพลาด: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },
            productPayload(el) {
                return {
                    type: 'product',
                    id: parseInt(el.dataset.id),
                    name: el.dataset.name,
                    sku: el.dataset.sku,
                    price: parseFloat(el.dataset.price),
                    stock: parseInt(el.dataset.stock)
                };
            }
        }
    }
</script>
@endsection