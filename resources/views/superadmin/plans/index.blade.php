@extends('superadmin.layout')

@section('title', 'จัดการแพ็กเกจ')
@section('page-title', 'จัดการแพ็กเกจ')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-gray-500">จัดการแผนบริการและราคา</p>
        <a href="{{ route('superadmin.plans.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>สร้างแพ็กเกจใหม่
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden {{ !$plan->is_active ? 'opacity-60' : '' }}">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ $plan->name }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $plan->is_active ? 'Active' : 'ปิดใช้งาน' }}
                    </span>
                </div>

                <div class="mb-4">
                    <p class="text-3xl font-bold text-indigo-600">
                        {{ $plan->price > 0 ? '฿' . number_format($plan->price, 0) : 'ฟรี' }}
                        <span class="text-sm font-normal text-gray-400">{{ $plan->price > 0 ? '/เดือน' : '' }}</span>
                    </p>
                    @if($plan->yearly_price > 0)
                    <p class="text-sm text-gray-500">฿{{ number_format($plan->yearly_price, 0) }}/ปี</p>
                    @endif
                </div>

                <p class="text-sm text-gray-500 mb-4">{{ $plan->description ?? '-' }}</p>

                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ผู้ใช้สูงสุด</span>
                        <span class="font-semibold">{{ $plan->max_users == -1 ? '∞' : $plan->max_users }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">สาขาสูงสุด</span>
                        <span class="font-semibold">{{ $plan->max_branches == -1 ? '∞' : $plan->max_branches }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">สินค้าสูงสุด</span>
                        <span class="font-semibold">{{ $plan->max_products == -1 ? '∞' : number_format($plan->max_products) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">ทดลองใช้</span>
                        <span class="font-semibold">{{ $plan->trial_days }} วัน</span>
                    </div>
                </div>

                @if($plan->features)
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-semibold text-gray-500 mb-2">ฟีเจอร์:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($plan->features as $feature)
                        <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded">{{ $feature }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t">
                <span class="text-sm text-gray-500">{{ $plan->tenants_count }} ร้านค้า</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('superadmin.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                        <i class="fas fa-edit mr-1"></i>แก้ไข
                    </a>
                    @if($plan->tenants_count == 0)
                    <form action="{{ route('superadmin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('ลบแพ็กเกจนี้?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection