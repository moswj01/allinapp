@extends('superadmin.layout')

@section('title', 'จัดการร้านค้า')
@section('page-title', 'จัดการร้านค้า')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <!-- Search -->
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาร้านค้า..."
                        class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                    <option value="">สถานะทั้งหมด</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>ระงับ</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
                <select name="plan_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                    <option value="">แพ็กเกจทั้งหมด</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">ค้นหา</button>
            </form>
        </div>
        <a href="{{ route('superadmin.tenants.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>สร้างร้านค้าใหม่
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">ร้านค้า</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">แพ็กเกจ</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase">สถานะ</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase">ผู้ใช้</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase">สาขา</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">วันหมดอายุ</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">การจัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($tenants as $tenant)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-sm">
                                {{ mb_substr($tenant->name, 0, 2) }}
                            </div>
                            <div>
                                <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" class="text-sm font-semibold text-gray-800 hover:text-indigo-600">{{ $tenant->name }}</a>
                                <p class="text-xs text-gray-500">{{ $tenant->email }}</p>
                                <p class="text-xs text-gray-400">{{ $tenant->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-700">{{ $tenant->plan?->name ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                        $sc = ['active' => 'bg-green-100 text-green-700', 'trial' => 'bg-amber-100 text-amber-700', 'suspended' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-200 text-gray-600'];
                        $sl = ['active' => 'Active', 'trial' => 'Trial', 'suspended' => 'ระงับ', 'cancelled' => 'ยกเลิก'];
                        @endphp
                        <span class="text-xs px-2.5 py-1 rounded-full {{ $sc[$tenant->status] ?? '' }}">{{ $sl[$tenant->status] ?? $tenant->status }}</span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $tenant->users_count }}</td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $tenant->branches_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($tenant->status === 'trial' && $tenant->trial_ends_at)
                        <span class="{{ $tenant->trial_ends_at->isPast() ? 'text-red-600' : 'text-amber-600' }}">
                            {{ $tenant->trial_ends_at->format('d/m/Y') }}
                            @if(!$tenant->trial_ends_at->isPast())
                            ({{ $tenant->daysLeftInTrial() }} วัน)
                            @else
                            (หมดอายุ)
                            @endif
                        </span>
                        @elseif($tenant->subscription_ends_at)
                        {{ $tenant->subscription_ends_at->format('d/m/Y') }}
                        @else
                        -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('superadmin.tenants.edit', $tenant->id) }}" class="p-2 text-gray-400 hover:text-amber-600" title="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('superadmin.tenants.login-as', $tenant->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-gray-400 hover:text-green-600" title="เข้าสู่ระบบในนาม">
                                    <i class="fas fa-sign-in-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-store text-4xl mb-3 block"></i>
                        <p>ยังไม่มีร้านค้า</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</div>
@endsection