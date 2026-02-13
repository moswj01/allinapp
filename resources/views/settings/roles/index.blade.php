@extends('layouts.app')

@section('title', 'จัดการสิทธิ์ & บทบาท')
@section('page-title', 'จัดการสิทธิ์ & บทบาท')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">บทบาทและสิทธิ์</h2>
            <p class="text-gray-500">จัดการบทบาทผู้ใช้งานและสิทธิ์การเข้าถึง</p>
        </div>
        <a href="{{ route('roles.create') }}"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
            <i class="fas fa-plus mr-2"></i>
            เพิ่มบทบาทใหม่
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
            <span class="text-red-700">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Roles List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $role->name }}</h3>
                    <span class="inline-block px-2 py-0.5 text-xs font-mono bg-gray-100 text-gray-600 rounded mt-1">{{ $role->slug }}</span>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $role->users_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $role->users_count }} คน
                </span>
            </div>

            <p class="text-sm text-gray-500 mb-4">{{ $role->description ?: '-' }}</p>

            <div class="mb-4">
                <p class="text-xs font-medium text-gray-400 mb-2">สิทธิ์ ({{ count($role->permissions ?? []) }} รายการ)</p>
                <div class="flex flex-wrap gap-1">
                    @foreach(array_slice($role->permissions ?? [], 0, 6) as $perm)
                    <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 rounded">{{ $perm }}</span>
                    @endforeach
                    @if(count($role->permissions ?? []) > 6)
                    <span class="px-2 py-0.5 text-xs bg-gray-50 text-gray-500 rounded">+{{ count($role->permissions) - 6 }} อื่นๆ</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-100">
                <a href="{{ route('roles.edit', $role) }}"
                    class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-1"></i> แก้ไขสิทธิ์
                </a>
                @if(!in_array($role->slug, ['owner', 'admin']))
                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                    onsubmit="return confirm('ต้องการลบบทบาท «{{ $role->name }}» ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-1"></i> ลบ
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Permission Reference Table -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-shield-alt text-indigo-600 mr-2"></i>
            ตารางสิทธิ์ทั้งหมด
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 w-48">โมดูล</th>
                        @foreach($roles as $role)
                        <th class="px-3 py-3 text-center font-semibold text-gray-700">
                            <div class="text-xs">{{ $role->name }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($permissionGroups as $groupKey => $group)
                    <tr class="bg-gray-50/50">
                        <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 font-semibold text-gray-800 text-xs uppercase tracking-wider">
                            {{ $group['label'] }}
                        </td>
                    </tr>
                    @foreach($group['permissions'] as $perm => $permLabel)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-600">
                            <span class="text-xs">{{ $permLabel }}</span>
                            <span class="block text-xs text-gray-400 font-mono">{{ $perm }}</span>
                        </td>
                        @foreach($roles as $role)
                        <td class="px-3 py-2 text-center">
                            @if($role->hasPermission($perm))
                            <i class="fas fa-check-circle text-green-500"></i>
                            @else
                            <i class="fas fa-minus-circle text-gray-300"></i>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection