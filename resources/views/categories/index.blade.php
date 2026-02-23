@extends('layouts.app')

@section('title', 'หมวดหมู่')

@section('content')
<div x-data="categories()" x-init="fetchData()">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">จัดการหมวดหมู่</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('categories.export-csv') }}" class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-file-export mr-2"></i>Export CSV
            </a>
            <button onclick="document.getElementById('catImportModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors">
                <i class="fas fa-file-import mr-2"></i>Import CSV
            </button>
            <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>เพิ่มหมวดหมู่
            </button>
        </div>
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
                            <input type="text" x-model="form.name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">คำอธิบาย</label>
                            <textarea x-model="form.description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-600">ยกเลิก</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div id="catImportModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="document.getElementById('catImportModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-file-csv text-green-600 mr-2"></i>Import หมวดหมู่จาก CSV
                </h3>
                <button onclick="document.getElementById('catImportModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('categories.import-csv') }}" method="POST" enctype="multipart/form-data">
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
                        <span><strong class="text-red-500">*</strong> name</span>
                        <span>description</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <strong class="text-red-500">*</strong> จำเป็น | หมวดหมู่ที่มีชื่อซ้ำจะถูกอัปเดต
                    </p>
                </div>

                <div class="mb-4">
                    <a href="{{ route('categories.import-template') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-download mr-1"></i>ดาวน์โหลดไฟล์ตัวอย่าง
                    </a>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('catImportModal').classList.add('hidden')"
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
                    headers: apiHeaders()
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
                    headers: apiJsonHeaders(),
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
                    method: 'DELETE',
                    headers: apiHeaders()
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