@extends('layouts.app')

@section('title', 'ข้อมูลผู้ใช้')
@section('page-title', 'ข้อมูลผู้ใช้: ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- User Info Card -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center">
                    <span class="text-3xl text-indigo-600 font-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div class="ml-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="px-3 py-1 rounded-full text-sm
                            @if($user->role?->slug === 'owner') bg-purple-100 text-purple-700
                            @elseif($user->role?->slug === 'admin') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700
                            @endif">
                            {{ $user->role?->name ?? '-' }}
                        </span>
                        @if($user->is_active)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">ใช้งาน</span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">ปิดใช้งาน</span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </a>
        </div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">เบอร์โทร</p>
                <p class="font-semibold">{{ $user->phone ?? '-' }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">สาขา</p>
                <p class="font-semibold">{{ $user->branch?->name ?? '-' }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">วันที่สร้าง</p>
                <p class="font-semibold">{{ $user->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Login ล่าสุด</p>
                <p class="font-semibold">{{ $user->last_login_at?->diffForHumans() ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Recent Repairs -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-wrench text-yellow-500 mr-2"></i>งานซ่อมล่าสุด
            </h3>
            @forelse($recentRepairs as $repair)
            <div class="flex items-center justify-between py-3 border-b last:border-0">
                <div>
                    <a href="{{ route('repairs.show', $repair) }}" class="font-medium text-indigo-600 hover:underline">
                        {{ $repair->repair_number }}
                    </a>
                    <p class="text-sm text-gray-500">{{ $repair->device_brand }} {{ $repair->device_model }}</p>
                </div>
                <span class="text-sm text-gray-400">{{ $repair->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">ยังไม่มีงานซ่อม</p>
            @endforelse
        </div>

        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-shopping-cart text-green-500 mr-2"></i>การขายล่าสุด
            </h3>
            @forelse($recentSales as $sale)
            <div class="flex items-center justify-between py-3 border-b last:border-0">
                <div>
                    <a href="{{ route('sales.show', $sale) }}" class="font-medium text-indigo-600 hover:underline">
                        {{ $sale->sale_number }}
                    </a>
                    <p class="text-sm text-gray-500">฿{{ number_format($sale->total, 2) }}</p>
                </div>
                <span class="text-sm text-gray-400">{{ $sale->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">ยังไม่มีรายการขาย</p>
            @endforelse
        </div>
    </div>

    <!-- Back Button -->
    <div class="flex justify-start">
        <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
    </div>
</div>
@endsection