@extends('layouts.app')

@section('title', 'หมวดหมู่')

@section('content')
<div x-data="categories()" x-init="fetchData()">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">จัดการหมวดหมู่</h1>
        <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>เพิ่มหมวดหมู่
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="category in items" :key="category.id">
            <div class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-folder text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800" x-text="category.name"></h3>
                            <p class="text-sm text-gray-500" x-text="category.description || 'ไม่มีคำอธิบาย'"></p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editItem(category)" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click="deleteItem(category.id)" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">สินค้า: <span x-text="category.products_count || 0"></span></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div x-show="items.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        ไม่พบหมวดหมู่
    </div>

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="closeModal()"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่ใหม่'"></h3>
                <form @submit.prevent="saveItem()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหมวดหมู่ *</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">คำอธิบาย</label>
                            <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
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
    function categories() {
        return {
            items: [],
            showModal: false,
            editMode: false,
            form: {
                id: null,
                name: '',
                description: ''
            },

            async fetchData() {
                const response = await fetch('/api/categories', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.items = data.data || data;
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
                    description: ''
                };
            },

            async saveItem() {
                const url = this.editMode ? `/api/categories/${this.form.id}` : '/api/categories';
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
                if (!confirm('ลบหมวดหมู่นี้?')) return;
                const response = await fetch(`/api/categories/${id}`, {
                    method: 'DELETE'
                });
                if (response.ok) {
                    this.fetchData();
                    alert('ลบสำเร็จ');
                }
            }
        }
    }
</script>
@endpush
@endsection