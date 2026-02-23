@extends('superadmin.layout')

@section('title', 'รายละเอียดร้านค้า')
@section('page-title', $tenant->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.tenants.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i>กลับ
        </a>
        <div class="flex items-center gap-2">
            <form action="{{ route('superadmin.tenants.login-as', $tenant->id) }}" method="POST" class="inline">
                @csrf
                <button class="px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200">
                    <i class="fas fa-sign-in-alt mr-1"></i>เข้าสู่ระบบในนาม
                </button>
            </form>
            <a href="{{ route('superadmin.tenants.edit', $tenant->id) }}" class="px-3 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm hover:bg-amber-200">
                <i class="fas fa-edit mr-1"></i>แก้ไข
            </a>
            @if($tenant->status !== 'suspended')
            <form action="{{ route('superadmin.tenants.suspend', $tenant->id) }}" method="POST" class="inline" onsubmit="return confirm('ต้องการระงับร้านค้านี้?')">
                @csrf @method('PATCH')
                <button class="px-3 py-2 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200">
                    <i class="fas fa-ban mr-1"></i>ระงับ
                </button>
            </form>
            @else
            <form action="{{ route('superadmin.tenants.activate', $tenant->id) }}" method="POST" class="inline">
                @csrf @method('PATCH')
                <button class="px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200">
                    <i class="fas fa-check mr-1"></i>เปิดใช้งาน
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Info -->
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 text-2xl font-bold">
                        {{ mb_substr($tenant->name, 0, 2) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $tenant->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $tenant->email }} · {{ $tenant->slug }}</p>
                    </div>
                    @php
                    $sc = ['active' => 'bg-green-100 text-green-700', 'trial' => 'bg-amber-100 text-amber-700', 'suspended' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-200 text-gray-600'];
                    $sl = ['active' => 'Active', 'trial' => 'ทดลองใช้', 'suspended' => 'ระงับ', 'cancelled' => 'ยกเลิก'];
                    @endphp
                    <span class="ml-auto text-sm px-3 py-1.5 rounded-full {{ $sc[$tenant->status] ?? '' }}">{{ $sl[$tenant->status] ?? $tenant->status }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <p class="text-xs text-gray-500">แพ็กเกจ</p>
                        <p class="text-sm font-bold text-indigo-600 mt-1">{{ $tenant->plan?->name }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <p class="text-xs text-gray-500">สมัครเมื่อ</p>
                        <p class="text-sm font-bold text-gray-700 mt-1">{{ $tenant->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <p class="text-xs text-gray-500">วันหมดอายุ</p>
                        <p class="text-sm font-bold mt-1 {{ ($tenant->trial_ends_at ?? $tenant->subscription_ends_at)?->isPast() ? 'text-red-600' : 'text-gray-700' }}">
                            {{ ($tenant->trial_ends_at ?? $tenant->subscription_ends_at)?->format('d/m/Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <p class="text-xs text-gray-500">โทร</p>
                        <p class="text-sm font-bold text-gray-700 mt-1">{{ $tenant->phone ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Usage -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">การใช้งาน</h3>
                <div class="grid grid-cols-3 gap-4">
                    @foreach($usage as $key => $u)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 capitalize">{{ ['users' => 'ผู้ใช้', 'branches' => 'สาขา', 'products' => 'สินค้า'][$key] ?? $key }}</span>
                            <span class="font-semibold">{{ $u['current'] }}/{{ $u['max'] == -1 ? '∞' : $u['max'] }}</span>
                        </div>
                        @php $pct = $u['max'] == -1 ? 10 : ($u['max'] > 0 ? min(100, ($u['current'] / $u['max']) * 100) : 0); @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $pct > 80 ? 'bg-red-500' : ($pct > 60 ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Users -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">ผู้ใช้งาน ({{ $users->count() }})</h3>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500">ผู้ใช้</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500">ตำแหน่ง</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500">สาขา</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <p class="text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $user->role?->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $user->branch?->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Active' : 'ปิด' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Branches & Invoices -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">สาขา ({{ $branches->count() }})</h3>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($branches as $branch)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-semibold">{{ $branch->name }}</p>
                            <p class="text-xs text-gray-500">{{ $branch->code }}</p>
                        </div>
                        <span class="text-xs {{ $branch->is_active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $branch->is_active ? 'Active' : 'ปิด' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">ใบแจ้งหนี้ล่าสุด</h3>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($invoices as $invoice)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-semibold">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold">฿{{ number_format($invoice->total_amount, 0) }}</p>
                            <span class="text-xs {{ $invoice->isPaid() ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $invoice->isPaid() ? 'ชำระแล้ว' : 'รอชำระ' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-sm text-gray-400 py-4">ยังไม่มีใบแจ้งหนี้</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection