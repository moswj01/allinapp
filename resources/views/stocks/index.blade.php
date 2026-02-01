@extends('layouts.app')

@section('title', 'สต็อก')

@section('content')
<div x-data="stocks()" x-init="fetchData()">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">จัดการสต็อก</h1>
        <div class="flex gap-2">
            <button @click="openStockIn()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>รับเข้า
            </button>
            <button @click="openStockOut()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-minus mr-2"></i>จ่ายออก
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="tab = 'stocks'" :class="tab === 'stocks' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500'"
                    class="px-6 py-3 border-b-2 font-medium text-sm">
                    สต็อกปัจจุบัน
                </button>
                <button @click="tab = 'movements'; fetchMovements()" :class="tab === 'movements' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500'"
                    class="px-6 py-3 border-b-2 font-medium text-sm">
                    ประวัติการเคลื่อนไหว
                </button>
            </nav>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <select x-model="branchFilter" @change="fetchData()" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">ทุกสาขา</option>
                <template x-for="branch in branches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
            <select x-model="typeFilter" @change="fetchData()" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">ทุกประเภท</option>
                <option value="product">สินค้า</option>
                <option value="part">อะไหล่</option>
            </select>
        </div>
    </div>

    <!-- Stocks Table -->
    <div x-show="tab === 'stocks'" class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขา</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รายการ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ขั้นต่ำ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(stock, index) in items" :key="stock.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="index + 1"></td>
                        <td class="px-6 py-4 text-sm" x-text="stock.branch?.name || '-'"></td>
                        <td class="px-6 py-4">
                            <span :class="(stock.stockable_type || '').includes('Product') ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                class="px-2 py-1 text-xs font-semibold rounded">
                                <span x-text="(stock.stockable_type || '').includes('Product') ? 'สินค้า' : 'อะไหล่'"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium" x-text="stock.stockable?.name || '-'"></td>
                        <td class="px-6 py-4 text-sm font-bold" x-text="stock.quantity"></td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="stock.min_quantity || 0"></td>
                        <td class="px-6 py-4">
                            <span :class="stock.quantity <= (stock.min_quantity || 0) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                class="px-2 py-1 text-xs font-semibold rounded">
                                <span x-text="stock.quantity <= (stock.min_quantity || 0) ? 'ต่ำ' : 'ปกติ'"></span>
                            </span>
                        </td>
                    </tr>
                </template>
                <tr x-show="items.length === 0">
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">ไม่พบข้อมูลสต็อก</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Movements Table -->
    <div x-show="tab === 'movements'" class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รายการ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขอ้างอิง</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(movement, index) in movements" :key="movement.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="index + 1"></td>
                        <td class="px-6 py-4 text-sm" x-text="new Date(movement.created_at).toLocaleDateString('th-TH')"></td>
                        <td class="px-6 py-4">
                            <span :class="movement.type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="px-2 py-1 text-xs font-semibold rounded">
                                <span x-text="movement.type === 'in' ? 'รับเข้า' : 'จ่ายออก'"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm" x-text="movement.movable?.name || '-'"></td>
                        <td class="px-6 py-4 text-sm font-bold" :class="movement.type === 'in' ? 'text-green-600' : 'text-red-600'"
                            x-text="(movement.type === 'in' ? '+' : '-') + movement.quantity"></td>
                        <td class="px-6 py-4 text-sm" x-text="movement.reference_number || '-'"></td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="movement.notes || '-'"></td>
                    </tr>
                </template>
                <tr x-show="movements.length === 0">
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">ไม่พบประวัติการเคลื่อนไหว</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Stock In/Out Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="closeModal()"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold mb-4" x-text="modalType === 'in' ? 'รับสินค้าเข้า' : 'จ่ายสินค้าออก'"></h3>
                <form @submit.prevent="saveStock()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">สาขา *</label>
                            <select x-model="form.branch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกสาขา</option>
                                <template x-for="branch in branches" :key="branch.id">
                                    <option :value="branch.id" x-text="branch.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ประเภท *</label>
                            <select x-model="form.item_type" @change="fetchItems()" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกประเภท</option>
                                <option value="product">สินค้า</option>
                                <option value="part">อะไหล่</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รายการ *</label>
                            <select x-model="form.item_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกรายการ</option>
                                <template x-for="item in availableItems" :key="item.id">
                                    <option :value="item.id" x-text="item.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">จำนวน *</label>
                            <input type="number" x-model="form.quantity" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">เลขอ้างอิง</label>
                            <input type="text" x-model="form.reference_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                            <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-600">ยกเลิก</button>
                        <button type="submit" :class="modalType === 'in' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                            class="px-4 py-2 text-white rounded-lg" x-text="modalType === 'in' ? 'รับเข้า' : 'จ่ายออก'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function stocks() {
        return {
            items: [],
            movements: [],
            branches: [],
            products: [],
            parts: [],
            availableItems: [],
            tab: 'stocks',
            branchFilter: '',
            typeFilter: '',
            showModal: false,
            modalType: 'in',
            form: {
                branch_id: '',
                item_type: '',
                item_id: '',
                quantity: 1,
                reference_number: '',
                notes: ''
            },

            async fetchData() {
                let url = '/api/stocks?';
                if (this.branchFilter) url += `branch_id=${this.branchFilter}&`;
                if (this.typeFilter) url += `type=${this.typeFilter}`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                const list = payload?.data?.data ?? payload?.data ?? payload;
                this.items = Array.isArray(list) ? list : (list?.data ?? []);

                const branchResponse = await fetch('/api/branches', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const branchPayload = await branchResponse.json();
                this.branches = branchPayload?.data?.data ?? branchPayload?.data ?? branchPayload;
            },

            async fetchMovements() {
                const response = await fetch('/api/stock-movements', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                const list = payload?.data?.data ?? payload?.data ?? payload;
                this.movements = Array.isArray(list) ? list : (list?.data ?? []);
            },

            async fetchItems() {
                if (this.form.item_type === 'product') {
                    const response = await fetch('/api/products', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const payload = await response.json();
                    const list = payload?.data?.data ?? payload?.data ?? payload;
                    this.availableItems = Array.isArray(list) ? list : (list?.data ?? []);
                } else if (this.form.item_type === 'part') {
                    const response = await fetch('/api/parts', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const payload = await response.json();
                    const list = payload?.data?.data ?? payload?.data ?? payload;
                    this.availableItems = Array.isArray(list) ? list : (list?.data ?? []);
                } else {
                    this.availableItems = [];
                }
            },

            openStockIn() {
                this.modalType = 'in';
                this.resetForm();
                this.showModal = true;
            },
            openStockOut() {
                this.modalType = 'out';
                this.resetForm();
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
                this.resetForm();
            },
            resetForm() {
                this.form = {
                    branch_id: '',
                    item_type: '',
                    item_id: '',
                    quantity: 1,
                    reference_number: '',
                    notes: ''
                };
                this.availableItems = [];
            },

            async saveStock() {
                const payload = {
                    branch_id: this.form.branch_id,
                    quantity: this.form.quantity,
                    reference_number: this.form.reference_number,
                    notes: this.form.notes
                };

                if (this.form.item_type === 'product') {
                    payload.product_id = this.form.item_id;
                } else {
                    payload.part_id = this.form.item_id;
                }

                const url = this.modalType === 'in' ? '/api/stock/in' : '/api/stock/out';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    this.closeModal();
                    this.fetchData();
                    alert(this.modalType === 'in' ? 'รับเข้าสำเร็จ' : 'จ่ายออกสำเร็จ');
                } else {
                    const error = await response.json();
                    alert(error.message || 'เกิดข้อผิดพลาด');
                }
            }
        }
    }
</script>
@endpush
@endsection