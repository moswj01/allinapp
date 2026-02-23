@extends('superadmin.layout')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'แดชบอร์ด SaaS')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ร้านค้าทั้งหมด</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_tenants']) }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-store text-indigo-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-3 text-xs">
                <span class="text-green-600"><i class="fas fa-circle text-[8px] mr-1"></i>Active: {{ $stats['active_tenants'] }}</span>
                <span class="text-amber-600"><i class="fas fa-circle text-[8px] mr-1"></i>Trial: {{ $stats['trial_tenants'] }}</span>
                <span class="text-red-600"><i class="fas fa-circle text-[8px] mr-1"></i>ระงับ: {{ $stats['suspended_tenants'] }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ผู้ใช้งานทั้งหมด</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">รายได้เดือนนี้</p>
                    <p class="text-3xl font-bold text-gray-800">฿{{ number_format($monthlyRevenue, 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-coins text-amber-600 text-xl"></i>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500">รายได้รวม: ฿{{ number_format($stats['total_revenue'], 0) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ใบแจ้งหนี้</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_invoices'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-red-600 text-xl"></i>
                </div>
            </div>
            @if($stats['overdue_invoices'] > 0)
            <p class="mt-3 text-xs text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>เกินกำหนด: {{ $stats['overdue_invoices'] }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Recent Tenants -->
        <div class="xl:col-span-2 bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">ร้านค้าล่าสุด</h3>
                <a href="{{ route('superadmin.tenants.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentTenants as $tenant)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold">
                            {{ mb_substr($tenant->name, 0, 1) }}
                        </div>
                        <div>
                            <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" class="text-sm font-semibold text-gray-800 hover:text-indigo-600">{{ $tenant->name }}</a>
                            <p class="text-xs text-gray-500">{{ $tenant->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2 py-1 rounded-full {{ $tenant->plan ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $tenant->plan?->name ?? '-' }}
                        </span>
                        @php
                        $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'trial' => 'bg-amber-100 text-amber-700',
                        'suspended' => 'bg-red-100 text-red-700',
                        'cancelled' => 'bg-gray-100 text-gray-600',
                        ];
                        $statusLabels = ['active' => 'Active', 'trial' => 'Trial', 'suspended' => 'ระงับ', 'cancelled' => 'ยกเลิก'];
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$tenant->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $statusLabels[$tenant->status] ?? $tenant->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <i class="fas fa-store text-4xl mb-3"></i>
                    <p>ยังไม่มีร้านค้า</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Plans Summary -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">แพ็กเกจ</h3>
                </div>
                <div class="p-6 space-y-3">
                    @foreach($plans as $plan)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-500">{{ $plan->getFormattedPrice() }}</p>
                        </div>
                        <span class="text-lg font-bold text-indigo-600">{{ $plan->tenants_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Expiring Trials -->
            @if($expiringTrials->count() > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl">
                <div class="px-6 py-4 border-b border-amber-200">
                    <h3 class="text-sm font-semibold text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i>ทดลองใช้ใกล้หมดอายุ</h3>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($expiringTrials as $tenant)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-amber-800">{{ $tenant->name }}</span>
                        <span class="text-xs text-amber-600">{{ $tenant->daysLeftInTrial() }} วัน</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection