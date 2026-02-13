@extends('layouts.app')

@section('title', 'แก้ไขสิทธิ์ - ' . $role->name)
@section('page-title', 'แก้ไขสิทธิ์บทบาท')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">แก้ไขสิทธิ์: {{ $role->name }}</h2>
            <p class="text-gray-500">
                <span class="font-mono text-sm bg-gray-100 px-2 py-0.5 rounded">{{ $role->slug }}</span>
                — {{ $role->description }}
            </p>
        </div>
        <a href="{{ route('roles.index') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <ul class="list-disc list-inside text-red-600 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6" x-data="permissionEditor()">
        @csrf
        @method('PUT')

        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-tag text-indigo-600 mr-2"></i>
                ข้อมูลบทบาท
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบทบาท <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" value="{{ $role->slug }}" disabled
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 font-mono">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">คำอธิบาย</label>
                    <input type="text" name="description" value="{{ old('description', $role->description) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="อธิบายหน้าที่ของบทบาทนี้">
                </div>
            </div>
        </div>

        <!-- Permission Groups -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                    สิทธิ์การเข้าถึง
                </h3>
                <div class="flex items-center space-x-3">
                    <button type="button" @click="selectAll()" class="text-xs px-3 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                        <i class="fas fa-check-double mr-1"></i> เลือกทั้งหมด
                    </button>
                    <button type="button" @click="deselectAll()" class="text-xs px-3 py-1 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
                        <i class="fas fa-times mr-1"></i> ยกเลิกทั้งหมด
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($permissionGroups as $groupKey => $group)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between cursor-pointer"
                        @click="toggleGroup('{{ $groupKey }}')">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mr-2 transform transition-transform"
                                :class="openGroups.includes('{{ $groupKey }}') ? 'rotate-90' : ''"></i>
                            <span class="font-semibold text-gray-700">{{ $group['label'] }}</span>
                            <span class="ml-2 text-xs text-gray-400">({{ count($group['permissions']) }} สิทธิ์)</span>
                        </div>
                        <button type="button" @click.stop="toggleGroupAll('{{ $groupKey }}')"
                            class="text-xs px-2 py-1 bg-indigo-50 text-indigo-600 rounded hover:bg-indigo-100">
                            เลือก/ยกเลิกทั้งกลุ่ม
                        </button>
                    </div>

                    <div class="px-4 py-3 grid grid-cols-1 md:grid-cols-2 gap-2"
                        x-show="openGroups.includes('{{ $groupKey }}')">
                        @foreach($group['permissions'] as $perm => $permLabel)
                        <label class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                {{ in_array($perm, old('permissions', $role->permissions ?? [])) ? 'checked' : '' }}
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                x-model="selectedPermissions">
                            <div>
                                <span class="text-sm text-gray-700">{{ $permLabel }}</span>
                                <span class="block text-xs text-gray-400 font-mono">{{ $perm }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4 p-3 bg-indigo-50 rounded-lg">
                <p class="text-sm text-indigo-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    สิทธิ์ที่ลงท้ายด้วย <code class="bg-indigo-100 px-1 rounded">.*</code> จะครอบคลุมทุกการกระทำในหมวดนั้น
                    (เช่น <code class="bg-indigo-100 px-1 rounded">repairs.*</code> = ดู, สร้าง, แก้ไข, ลบ)
                </p>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('roles.index') }}"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                ยกเลิก
            </a>
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                บันทึกสิทธิ์
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    var __jsData = @json($jsData);

    function permissionEditor() {
        return {
            openGroups: __jsData.openGroups,
            selectedPermissions: __jsData.selectedPermissions,
            allPermissions: __jsData.allPermissions,
            groupPermissions: __jsData.groupPermissions,

            toggleGroup(key) {
                if (this.openGroups.includes(key)) {
                    this.openGroups = this.openGroups.filter(g => g !== key);
                } else {
                    this.openGroups.push(key);
                }
            },

            toggleGroupAll(key) {
                const perms = this.groupPermissions[key] || [];
                const allSelected = perms.every(p => this.selectedPermissions.includes(p));

                if (allSelected) {
                    this.selectedPermissions = this.selectedPermissions.filter(p => !perms.includes(p));
                } else {
                    perms.forEach(p => {
                        if (!this.selectedPermissions.includes(p)) {
                            this.selectedPermissions.push(p);
                        }
                    });
                }
            },

            selectAll() {
                this.selectedPermissions = [...this.allPermissions];
            },

            deselectAll() {
                this.selectedPermissions = [];
            }
        }
    }
</script>
@endpush
@endsection