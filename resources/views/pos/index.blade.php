@extends('layouts.app')

@section('title', 'POS - ขายสินค้า')
@section('page-title', 'ขายสินค้า (POS)')

@section('content')
<div x-data="posApp()" x-init="init()" data-product-count="{{ $products->count() }}" class="h-[calc(100vh-140px)]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
        <!-- Products Section -->
        <div class="lg:col-span-2 flex flex-col">
            <!-- Search / Barcode -->
            <div class="bg-white rounded-xl shadow-sm p-3 mb-3">
                <div class="flex gap-2 items-center">
                    <div class="flex-1 relative">
                        <i class="fas fa-barcode absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input type="text" x-ref="barcodeInput" x-model="search" @input="filterProducts()"
                            @keydown.enter.prevent="handleBarcodeScan()"
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                            placeholder="สแกนบาร์โค้ด / ค้นหาสินค้า..." autofocus>
                    </div>
                    <div class="flex border border-gray-300 rounded-lg overflow-hidden flex-shrink-0">
                        <button @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600'"
                            class="px-3 py-2 text-sm">
                            <i class="fas fa-list"></i>
                        </button>
                        <button @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600'"
                            class="px-3 py-2 text-sm">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>
                <!-- Barcode scan indicator -->
                <div x-show="lastScanned" x-transition
                    class="mt-1.5 flex items-center text-xs text-green-600 bg-green-50 px-2.5 py-1 rounded-md">
                    <i class="fas fa-check-circle mr-1.5"></i>
                    <span x-text="lastScanned"></span>
                    <button @click="lastScanned = ''" class="ml-auto text-green-400 hover:text-green-600"><i
                            class="fas fa-times text-xs"></i></button>
                </div>
            </div>

            <!-- Products -->
            <div class="flex-1 bg-white rounded-xl shadow-sm p-3 overflow-y-auto">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-400">สินค้า <span class="font-semibold text-gray-600"
                            x-text="visibleCount"></span> รายการ</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full" :class="priceTypeColors[customerType]"
                        x-text="priceTypeLabels[customerType]"></span>
                </div>

                <!-- Table View (Default) -->
                <div x-show="viewMode === 'table'">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/80 sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider">สินค้า</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">SKU</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-medium text-gray-400 uppercase tracking-wider">ราคา</th>
                                <th class="px-2 py-1.5 text-center text-[11px] font-medium text-gray-400 uppercase tracking-wider w-12">คลัง</th>
                                <th class="px-1 py-1.5 w-8"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($products as $product)
                            @php
                            $stock = $product->branchStocks->first();
                            $qty = $stock ? $stock->quantity : 0;
                            @endphp
                            <tr class="product-item hover:bg-indigo-50/60 cursor-pointer transition-colors"
                                data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                data-sku="{{ $product->sku }}" data-barcode="{{ $product->barcode }}"
                                data-price-retail="{{ $product->retail_price ?? 0 }}"
                                data-price-wholesale="{{ $product->wholesale_price ?? $product->retail_price ?? 0 }}"
                                data-price-vip="{{ $product->vip_price ?? $product->retail_price ?? 0 }}"
                                data-price-partner="{{ $product->partner_price ?? $product->retail_price ?? 0 }}"
                                data-stock="{{ $qty }}" data-category="{{ $product->category_id }}"
                                @click="addToCart(productPayload($event.currentTarget))">
                                <td class="px-2 py-1.5">
                                    <div class="flex items-center">
                                        <div
                                            class="w-7 h-7 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center mr-2 overflow-hidden">
                                            @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt=""
                                                class="w-full h-full object-cover">
                                            @else
                                            <i class="fas fa-box text-gray-300 text-[10px]"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-medium text-gray-800 text-sm truncate block leading-tight">{{ $product->name }}</span>
                                            <span class="text-[11px] text-gray-400 sm:hidden">{{ $product->sku }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 hidden sm:table-cell">
                                    <span class="text-xs text-gray-400">{{ $product->sku }}</span>
                                    @if($product->barcode)
                                    <span class="block text-[11px] text-gray-300"><i
                                            class="fas fa-barcode mr-0.5"></i>{{ $product->barcode }}</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-right font-semibold text-green-600 text-sm price-cell"
                                    data-retail="{{ number_format($product->retail_price ?? 0, 0) }}"
                                    data-wholesale="{{ number_format($product->wholesale_price ?? $product->retail_price ?? 0, 0) }}"
                                    data-vip="{{ number_format($product->vip_price ?? $product->retail_price ?? 0, 0) }}"
                                    data-partner="{{ number_format($product->partner_price ?? $product->retail_price ?? 0, 0) }}">
                                    ฿{{ number_format($product->retail_price ?? 0, 0) }}
                                </td>
                                <td class="px-2 py-1.5 text-center">
                                    <span class="text-xs {{ $qty > 0 ? 'text-gray-500' : 'text-red-500 font-bold' }}">{{ $qty > 0 ? $qty : 'หมด' }}</span>
                                </td>
                                <td class="px-1 py-1.5 text-center">
                                    <i class="fas fa-plus-circle text-indigo-400 hover:text-indigo-600 text-sm"></i>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Grid View -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    @foreach($products as $product)
                    @php
                    $stock = $product->branchStocks->first();
                    $qty = $stock ? $stock->quantity : 0;
                    @endphp
                    <div class="product-item border border-gray-100 rounded-lg p-2 cursor-pointer hover:border-indigo-400 hover:shadow-md transition-all"
                        data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-sku="{{ $product->sku }}"
                        data-barcode="{{ $product->barcode }}" data-price-retail="{{ $product->retail_price }}"
                        data-price-wholesale="{{ $product->wholesale_price ?? $product->retail_price }}"
                        data-price-vip="{{ $product->vip_price ?? $product->retail_price }}"
                        data-price-partner="{{ $product->partner_price ?? $product->retail_price }}"
                        data-stock="{{ $qty }}" data-category="{{ $product->category_id }}"
                        @click="addToCart(productPayload($event.currentTarget))">
                        <div
                            class="aspect-square bg-gray-50 rounded-md mb-1.5 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="" class="w-full h-full object-cover">
                            @else
                            <i class="fas fa-box text-gray-300 text-xl"></i>
                            @endif
                        </div>
                        <h4 class="text-xs font-medium text-gray-800 truncate leading-tight">{{ $product->name }}</h4>
                        <p class="text-[11px] text-gray-400">{{ $product->sku }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-sm font-bold text-green-600 price-cell"
                                data-retail="{{ number_format($product->retail_price, 0) }}"
                                data-wholesale="{{ number_format($product->wholesale_price ?? $product->retail_price, 0) }}"
                                data-vip="{{ number_format($product->vip_price ?? $product->retail_price, 0) }}"
                                data-partner="{{ number_format($product->partner_price ?? $product->retail_price, 0) }}">
                                ฿{{ number_format($product->retail_price, 0) }}
                            </span>
                            <span class="text-[11px] {{ $qty > 0 ? 'text-gray-400' : 'text-red-500 font-bold' }}">
                                {{ $qty > 0 ? $qty : 'หมด' }}
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
            <div class="p-3 border-b">
                <div class="flex items-center justify-between mb-1.5">
                    <h3 class="font-semibold text-gray-800 text-sm"><i class="fas fa-user text-gray-400 mr-1.5"></i>ลูกค้า</h3>
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium" :class="priceTypeColors[customerType]"
                        x-text="priceTypeLabels[customerType]"></span>
                </div>
                <select x-model="customerId" @change="onCustomerChange()"
                    class="w-full px-3 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-gray-50">
                    <option value="">ลูกค้าทั่วไป (ปลีก)</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" data-type="{{ $customer->customer_type }}"
                        data-credit-limit="{{ $customer->credit_limit }}"
                        data-credit-days="{{ $customer->credit_days }}"
                        data-has-credit="{{ $customer->hasCredit() ? '1' : '0' }}">
                        {{ $customer->name }} - {{ $customer->phone }}
                        @if($customer->customer_type !== 'retail')
                        [{{ ['wholesale'=>'ขายส่ง','vip'=>'VIP','partner'=>'พาร์ทเนอร์','corporate'=>'องค์กร'][$customer->customer_type] ?? $customer->customer_type }}]
                        @endif
                        @if($customer->hasCredit()) (เครดิต {{ $customer->credit_days }} วัน) @endif
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-3">
                <template x-if="cart.length === 0">
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-shopping-cart text-3xl mb-1"></i>
                        <p class="text-sm">ยังไม่มีสินค้าในตะกร้า</p>
                    </div>
                </template>

                <div class="space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex items-center gap-2 p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 text-sm leading-tight truncate" x-text="item.name"></h4>
                                <p class="text-[11px] text-gray-400" x-text="item.sku"></p>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <button @click="decreaseQty(index)"
                                        class="w-6 h-6 bg-gray-200 rounded-md hover:bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <input type="number" x-model.number="item.quantity" min="1" :max="item.stock"
                                        @change="validateQty(index)"
                                        class="w-12 text-center border border-gray-200 rounded-md py-0.5 text-sm">
                                    <button @click="increaseQty(index)"
                                        class="w-6 h-6 bg-gray-200 rounded-md hover:bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <input type="number" x-model.number="item.price" min="0"
                                    class="w-[72px] text-right border border-gray-200 rounded-md px-1.5 py-0.5 text-sm">
                                <p class="text-sm font-bold text-green-600 mt-0.5"
                                    x-text="'฿' + formatNumber(item.price * item.quantity)"></p>
                            </div>
                            <button @click="removeFromCart(index)" class="text-red-300 hover:text-red-500 ml-0.5">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-3 border-t bg-gray-50/80">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>รวม (<span x-text="cart.length"></span> รายการ)</span>
                        <span x-text="'฿' + formatNumber(subtotal)"></span>
                    </div>
                    <div class="flex justify-between items-center text-gray-500">
                        <span>ส่วนลด</span>
                        <div class="flex items-center gap-1">
                            <input type="number" x-model.number="discount" min="0"
                                class="w-20 text-right border border-gray-200 rounded-md px-1.5 py-0.5 text-sm">
                            <span class="text-xs">฿</span>
                        </div>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-gray-200 font-bold">
                        <span class="text-base">รวมสุทธิ</span>
                        <span class="text-lg text-green-600" x-text="'฿' + formatNumber(total)"></span>
                    </div>
                </div>

                <!-- Payment -->
                <div class="mt-3 space-y-2">
                    <div class="grid grid-cols-4 gap-1.5">
                        <button @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="py-1.5 rounded-lg text-xs font-medium transition-all">
                            <i class="fas fa-money-bill-wave text-sm"></i>
                            <span class="block mt-0.5">เงินสด</span>
                        </button>
                        <button @click="paymentMethod = 'transfer'"
                            :class="paymentMethod === 'transfer' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="py-1.5 rounded-lg text-xs font-medium transition-all">
                            <i class="fas fa-university text-sm"></i>
                            <span class="block mt-0.5">โอน</span>
                        </button>
                        <button @click="paymentMethod = 'qr'"
                            :class="paymentMethod === 'qr' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="py-1.5 rounded-lg text-xs font-medium transition-all">
                            <i class="fas fa-qrcode text-sm"></i>
                            <span class="block mt-0.5">QR</span>
                        </button>
                        <button @click="paymentMethod = 'credit'"
                            :class="paymentMethod === 'credit' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="py-1.5 rounded-lg text-xs font-medium transition-all">
                            <i class="fas fa-file-invoice text-sm"></i>
                            <span class="block mt-0.5">เครดิต</span>
                        </button>
                    </div>

                    <!-- Credit Term Options -->
                    <template x-if="paymentMethod === 'credit'">
                        <div class="bg-blue-50/70 border border-blue-100 p-2.5 rounded-lg space-y-1.5">
                            <template x-if="!customerId">
                                <div class="text-red-600 text-xs flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1.5"></i>
                                    กรุณาเลือกลูกค้าก่อนขายเครดิต
                                </div>
                            </template>
                            <template x-if="customerId">
                                <div>
                                    <template x-if="customerHasCredit">
                                        <div class="text-xs text-blue-700">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            วงเงิน: <strong x-text="'฿' + formatNumber(customerCreditLimit)"></strong>
                                            · <strong x-text="customerCreditDays + ' วัน'"></strong>
                                        </div>
                                    </template>
                                    <template x-if="!customerHasCredit">
                                        <div class="text-xs text-amber-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            ลูกค้ายังไม่ได้ตั้งวงเงินเครดิต (ไม่จำกัดวงเงิน)
                                        </div>
                                    </template>
                                    <div class="flex items-center gap-1.5">
                                        <label class="text-xs text-gray-600 whitespace-nowrap">ครบกำหนด</label>
                                        <input type="date" x-model="creditDueDate" :min="todayISO"
                                            class="flex-1 border border-gray-200 rounded-md px-1.5 py-0.5 text-xs">
                                        <span class="text-[11px] text-gray-400" x-show="creditDaysDisplay > 0"
                                            x-text="'(' + creditDaysDisplay + ' วัน)'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="paymentMethod === 'cash'">
                        <div class="grid grid-cols-4 gap-1.5">
                            <button @click="receivedAmount = total"
                                class="py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-medium transition-colors">พอดี</button>
                            <button @click="receivedAmount = Math.ceil(total/100)*100"
                                class="py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-medium transition-colors"
                                x-text="formatNumber(Math.ceil(total/100)*100)"></button>
                            <button @click="receivedAmount = Math.ceil(total/500)*500"
                                class="py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-medium transition-colors"
                                x-text="formatNumber(Math.ceil(total/500)*500)"></button>
                            <button @click="receivedAmount = Math.ceil(total/1000)*1000"
                                class="py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-medium transition-colors"
                                x-text="formatNumber(Math.ceil(total/1000)*1000)"></button>
                        </div>
                    </template>

                    <template x-if="paymentMethod === 'cash'">
                        <div class="flex items-center justify-between bg-amber-50 p-2.5 rounded-lg border border-amber-100">
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-0.5">รับเงิน</label>
                                <input type="number" x-model.number="receivedAmount" min="0"
                                    class="w-28 text-base font-bold border border-gray-200 rounded-md px-2 py-0.5">
                            </div>
                            <div class="text-right">
                                <label class="block text-[11px] text-gray-500 mb-0.5">เงินทอน</label>
                                <span class="text-lg font-bold" :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
                                    x-text="'฿' + formatNumber(change)"></span>
                            </div>
                        </div>
                    </template>

                    <button @click="completeSale()" :disabled="cart.length === 0 || loading"
                        class="w-full py-2.5 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all shadow-sm">
                        <template x-if="!loading">
                            <span><i class="fas fa-check mr-1.5"></i>ชำระเงิน</span>
                        </template>
                        <template x-if="loading">
                            <span><i class="fas fa-spinner fa-spin mr-1.5"></i>กำลังบันทึก...</span>
                        </template>
                    </button>

                    <button @click="clearCart()"
                        class="w-full py-1.5 text-sm border border-gray-200 text-gray-500 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-trash mr-1.5 text-xs"></i>ล้างตะกร้า
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
            viewMode: 'table',
            cart: JSON.parse(localStorage.getItem('pos_cart') || '[]'),
            customerId: localStorage.getItem('pos_customerId') || '',
            customerType: localStorage.getItem('pos_customerType') || 'retail',
            discount: parseFloat(localStorage.getItem('pos_discount') || '0'),
            paymentMethod: localStorage.getItem('pos_paymentMethod') || 'cash',
            receivedAmount: parseFloat(localStorage.getItem('pos_receivedAmount') || '0'),
            loading: false,
            creditDueDate: localStorage.getItem('pos_creditDueDate') || '',
            customerHasCredit: false,
            customerCreditLimit: 0,
            customerCreditDays: 30,
            lastScanned: '',
            scanBuffer: '',
            scanTimeout: null,
            visibleCount: 0,

            priceTypeLabels: {
                retail: 'ราคาปลีก',
                wholesale: 'ราคาส่ง',
                vip: 'ราคา VIP',
                partner: 'ราคาพาร์ทเนอร์'
            },
            priceTypeColors: {
                retail: 'bg-blue-100 text-blue-800',
                wholesale: 'bg-orange-100 text-orange-800',
                vip: 'bg-purple-100 text-purple-800',
                partner: 'bg-emerald-100 text-emerald-800'
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            get total() {
                return Math.max(0, this.subtotal - this.discount);
            },

            get change() {
                return this.receivedAmount - this.total;
            },

            get todayISO() {
                return new Date().toISOString().split('T')[0];
            },

            get creditDaysDisplay() {
                if (!this.creditDueDate) return 0;
                const due = new Date(this.creditDueDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return Math.max(0, Math.round((due - today) / 86400000));
            },

            init() {
                this.visibleCount = parseInt(this.$el.dataset.productCount) || 0;
                this.initBarcode();
                this.initWatchers();
                // Restore customer selection if saved
                if (this.customerId) {
                    this.$nextTick(() => this.onCustomerChange());
                }
            },

            initWatchers() {
                this.$watch('cart', (val) => {
                    localStorage.setItem('pos_cart', JSON.stringify(val));
                }, {
                    deep: true
                });
                this.$watch('customerId', (val) => localStorage.setItem('pos_customerId', val));
                this.$watch('customerType', (val) => localStorage.setItem('pos_customerType', val));
                this.$watch('discount', (val) => localStorage.setItem('pos_discount', val));
                this.$watch('paymentMethod', (val) => localStorage.setItem('pos_paymentMethod', val));
                this.$watch('receivedAmount', (val) => localStorage.setItem('pos_receivedAmount', val));
                this.$watch('creditDueDate', (val) => localStorage.setItem('pos_creditDueDate', val));
            },

            initBarcode() {
                // Hardware barcode scanner sends keystrokes rapidly then Enter
                // Listen globally for fast sequential keystrokes
                document.addEventListener('keydown', (e) => {
                    // Skip if focused on cart input fields
                    const tag = e.target.tagName;
                    const isCartInput = e.target.closest('.cart-input');
                    if (isCartInput) return;

                    // If the barcode input isn't focused and user types, refocus it
                    if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') {
                        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
                            this.$refs.barcodeInput.focus();
                        }
                    }
                });
            },

            setDefaultCreditDueDate() {
                const d = new Date();
                d.setDate(d.getDate() + (this.customerCreditDays || 30));
                this.creditDueDate = d.toISOString().split('T')[0];
            },

            onCustomerChange() {
                const select = document.querySelector('select[x-model="customerId"]');
                const option = select?.selectedOptions[0];
                if (option && this.customerId) {
                    this.customerHasCredit = option.dataset.hasCredit === '1';
                    this.customerCreditLimit = parseFloat(option.dataset.creditLimit || 0);
                    this.customerCreditDays = parseInt(option.dataset.creditDays || 30);
                    this.setDefaultCreditDueDate();

                    // Auto-set price type based on customer type
                    const cType = option.dataset.type || 'retail';
                    const typeMap = {
                        retail: 'retail',
                        wholesale: 'wholesale',
                        vip: 'vip',
                        partner: 'partner',
                        corporate: 'retail'
                    };
                    this.customerType = typeMap[cType] || 'retail';
                    this.updatePrices();
                } else {
                    this.customerHasCredit = false;
                    this.customerCreditLimit = 0;
                    this.customerCreditDays = 30;
                    this.customerType = 'retail';
                    this.updatePrices();
                    this.setDefaultCreditDueDate();
                }
            },

            formatNumber(num) {
                return new Intl.NumberFormat('th-TH').format(num);
            },

            filterProducts() {
                const search = this.search.toLowerCase();
                let count = 0;

                document.querySelectorAll('.product-item').forEach(el => {
                    const name = (el.dataset.name || '').toLowerCase();
                    const sku = (el.dataset.sku || '').toLowerCase();
                    const barcode = (el.dataset.barcode || '').toLowerCase();

                    const match = !search || name.includes(search) || sku.includes(search) || barcode.includes(
                        search);
                    el.style.display = match ? '' : 'none';
                    if (match) count++;
                });

                // Count unique products (table + grid both have .product-item)
                this.visibleCount = Math.round(count / 2) || count;
            },

            handleBarcodeScan() {
                const barcode = this.search.trim();
                if (!barcode) return;

                // Find product by exact barcode or SKU match
                const el = document.querySelector(
                    `tr.product-item[data-barcode="${barcode}"], tr.product-item[data-sku="${barcode}"], div.product-item[data-barcode="${barcode}"], div.product-item[data-sku="${barcode}"]`
                );
                if (el) {
                    this.addToCart(this.productPayload(el));
                    this.lastScanned = '✓ ' + el.dataset.name + ' (฿' + this.formatNumber(this.getPriceForType(el)) + ')';
                    this.search = '';
                    this.filterProducts();

                    // Auto-clear scan message after 3 seconds
                    setTimeout(() => {
                        this.lastScanned = '';
                    }, 3000);
                } else {
                    this.lastScanned = '✗ ไม่พบสินค้า: ' + barcode;
                    setTimeout(() => {
                        this.lastScanned = '';
                    }, 3000);
                }

                // Re-focus barcode input
                this.$nextTick(() => this.$refs.barcodeInput.focus());
            },

            getPriceForType(el) {
                return parseFloat(el.dataset['price' + this.customerType.charAt(0).toUpperCase() + this.customerType.slice(
                    1)] || el.dataset.priceRetail);
            },

            productPayload(el) {
                return {
                    type: 'product',
                    id: parseInt(el.dataset.id),
                    name: el.dataset.name,
                    sku: el.dataset.sku,
                    price: this.getPriceForType(el),
                    stock: parseInt(el.dataset.stock)
                };
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
                // Update price display in product list
                document.querySelectorAll('.price-cell').forEach(cell => {
                    const price = cell.dataset[this.customerType] || cell.dataset.retail;
                    cell.textContent = '฿' + price;
                });

                // Update prices in cart
                this.cart.forEach(item => {
                    const el = document.querySelector(`.product-item[data-id="${item.id}"]`);
                    if (el) {
                        item.price = this.getPriceForType(el);
                    }
                });
            },

            clearCart() {
                if (this.cart.length === 0 || confirm('ต้องการล้างตะกร้าทั้งหมด?')) {
                    this.resetPosState();
                }
            },

            resetPosState() {
                this.cart = [];
                this.discount = 0;
                this.receivedAmount = 0;
                this.customerId = '';
                this.customerType = 'retail';
                this.paymentMethod = 'cash';
                this.creditDueDate = '';
                localStorage.removeItem('pos_cart');
                localStorage.removeItem('pos_customerId');
                localStorage.removeItem('pos_customerType');
                localStorage.removeItem('pos_discount');
                localStorage.removeItem('pos_paymentMethod');
                localStorage.removeItem('pos_receivedAmount');
                localStorage.removeItem('pos_creditDueDate');
            },

            async completeSale() {
                if (this.cart.length === 0) return;

                if (this.paymentMethod === 'cash' && this.receivedAmount < this.total) {
                    alert('จำนวนเงินที่รับไม่เพียงพอ');
                    return;
                }

                if (this.paymentMethod === 'credit') {
                    if (!this.customerId) {
                        alert('กรุณาเลือกลูกค้าก่อนขายเครดิต');
                        return;
                    }
                    if (!this.creditDueDate) {
                        alert('กรุณาเลือกวันครบกำหนดชำระ');
                        return;
                    }
                    if (!confirm('ยืนยันขายเครดิต ' + this.creditDaysDisplay + ' วัน\nครบกำหนด: ' + this.creditDueDate +
                            '\nยอด: ฿' + this.formatNumber(this.total))) {
                        return;
                    }
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
                            received_amount: this.receivedAmount,
                            credit_due_date: this.paymentMethod === 'credit' ? this.creditDueDate : null
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('บันทึกการขายเรียบร้อย\nเลขที่: ' + data.sale.sale_number);

                        // Reset all state and localStorage
                        this.resetPosState();
                        this.updatePrices();

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
            }
        }
    }
</script>
@endsection