@extends('layouts.app')

@section('title', 'สาขา')

@section('content')
<div x-data="branches()" x-init="fetchData()">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">จัดการสาขา</h1>
        <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>เพิ่มสาขา
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="branch in items" :key="branch.id">
            <div class="bg-white rounded-lg shadow p-5 hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-semibold text-gray-800" x-text="branch.name"></h3>
                            <span :class="branch.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="px-2 py-0.5 text-xs font-semibold rounded">
                                <span x-text="branch.is_active ? 'เปิดบริการ' : 'ปิดบริการ'"></span>
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editItem(branch)" class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></button>
                        <button @click="deleteItem(branch.id)" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600">
                    <p x-show="branch.address"><i class="fas fa-map-marker-alt w-5 text-gray-400"></i> <span x-text="branch.address"></span></p>
                    <p x-show="branch.phone"><i class="fas fa-phone w-5 text-gray-400"></i> <span x-text="branch.phone"></span></p>
                </div>
            </div>
        </template>
    </div>

    <div x-show="items.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">ไม่พบข้อมูลสาขา</div>

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="closeModal()"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'แก้ไขสาขา' : 'เพิ่มสาขาใหม่'"></h3>
                <form @submit.prevent="saveItem()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อสาขา *</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่</label>
                            <textarea x-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร</label>
                            <input type="text" x-model="form.phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" x-model="form.is_active" id="branch_active" class="mr-2">
                            <label for="branch_active" class="text-sm text-gray-700">เปิดบริการ</label>
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
    function branches() {
        return {
            items: [],
            showModal: false,
            editMode: false,
            form: {
                id: null,
                name: '',
                address: '',
                phone: '',
                is_active: true
            },

            async fetchData() {
                const response = await fetch('/api/branches', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                this.items = payload.data || payload;
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
                    address: '',
                    phone: '',
                    is_active: true
                };
            },

            async saveItem() {
                const url = this.editMode ? `/api/branches/${this.form.id}` : '/api/branches';
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
                } else {
                    const error = await response.json().catch(() => ({
                        message: 'เกิดข้อผิดพลาด'
                    }));
                    alert(error.message || 'เกิดข้อผิดพลาด');
                }
            },

            async deleteItem(id) {
                if (!confirm('ลบสาขานี้?')) return;
                const response = await fetch(`/api/branches/${id}`, {
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