@extends('layouts.app')

@section('title', 'อะไหล่')

@section('content')
<div x-data="parts()" x-init="fetchData()">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">จัดการอะไหล่</h1>
        <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>เพิ่มอะไหล่
        </button>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <input type="text" x-model="search" @input="fetchData()" placeholder="ค้นหาอะไหล่..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
    </div>

    <!-- Parts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รูปภาพ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่ออะไหล่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัส</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">หมวดหมู่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ราคาทุน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ราคาขาย</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(part, index) in items" :key="part.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="index + 1"></td>
                        <td class="px-6 py-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden">
                                <img :src="part.image || '/images/no-image.png'" class="w-full h-full object-cover" onerror="this.style.display='none'">
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900" x-text="part.name"></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="part.sku || '-'"></td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="part.category?.name || '-'"></td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="formatNumber(part.cost) + ' ฿'"></td>
                        <td class="px-6 py-4 text-sm font-medium" x-text="formatNumber(part.price) + ' ฿'"></td>
                        <td class="px-6 py-4">
                            <span :class="part.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded">
                                <span x-text="part.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน'"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <button @click="editItem(part)" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i></button>
                            <button @click="deleteItem(part.id)" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                </template>
                <tr x-show="items.length === 0">
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">ไม่พบข้อมูลอะไหล่</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="closeModal()"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'แก้ไขอะไหล่' : 'เพิ่มอะไหล่ใหม่'"></h3>
                <form @submit.prevent="saveItem()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่ออะไหล่ *</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รหัส (SKU)</label>
                            <input type="text" x-model="form.sku" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                            <select x-model="form.category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกหมวดหมู่</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ราคาทุน</label>
                                <input type="number" step="0.01" x-model="form.cost" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ราคาขาย</label>
                                <input type="number" step="0.01" x-model="form.price" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด</label>
                            <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" x-model="form.is_active" id="part_active" class="mr-2">
                            <label for="part_active" class="text-sm text-gray-700">เปิดใช้งาน</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-600">ยกเลิก</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function parts() {
        return {
            items: [],
            categories: [],
            search: '',
            showModal: false,
            editMode: false,
            form: {
                id: null,
                name: '',
                sku: '',
                category_id: '',
                cost: 0,
                price: 0,
                description: '',
                is_active: true
            },

            async fetchData() {
                const url = this.search ? `/api/parts?search=${this.search}` : '/api/parts';
                const response = await fetch(url);
                const data = await response.json();
                this.items = data.data || data;

                const catResponse = await fetch('/api/categories');
                const catData = await catResponse.json();
                this.categories = catData.data || catData;
            },

            openModal() {
                this.editMode = false;
                this.resetForm();
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
                this.resetForm();
            },
            editItem(item) {
                this.editMode = true;
                this.form = {
                    ...item
                };
                this.showModal = true;
            },
            resetForm() {
                this.form = {
                    id: null,
                    name: '',
                    sku: '',
                    category_id: '',
                    cost: 0,
                    price: 0,
                    description: '',
                    is_active: true
                };
            },

            async saveItem() {
                const url = this.editMode ? `/api/parts/${this.form.id}` : '/api/parts';
                const response = await fetch(url, {
                    method: this.editMode ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                if (response.ok) {
                    this.closeModal();
                    this.fetchData();
                    alert(this.editMode ? 'แก้ไขสำเร็จ' : 'เพิ่มสำเร็จ');
                }
            },

            async deleteItem(id) {
                if (!confirm('ลบอะไหล่นี้?')) return;
                const response = await fetch(`/api/parts/${id}`, {
                    method: 'DELETE'
                });
                if (response.ok) {
                    this.fetchData();
                    alert('ลบสำเร็จ');
                }
            },

            formatNumber(num) {
                return Number(num || 0).toLocaleString('th-TH');
            }
        }
    }
</script>
@endpush
@endsection