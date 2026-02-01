@extends('layouts.app')

@section('title', 'แดชบอร์ด')
@section('page-title', 'แดชบอร์ด')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Today's Repairs -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-tools text-2xl text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">งานซ่อมวันนี้</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayRepairs ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-coins text-2xl text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">ยอดขายวันนี้</p>
                    <p class="text-2xl font-bold text-gray-900">฿{{ number_format($todaySales ?? 0, 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Repairs -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-2xl text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">งานซ่อมค้าง</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingRepairs ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">สินค้าใกล้หมด</p>
                    <p class="text-2xl font-bold text-gray-900">{{ ($lowStockProducts ?? 0) + ($lowStockParts ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Repair Status Summary -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>
            สถานะงานซ่อม
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-4">
            @php
            $statusColors = [
            'pending' => 'bg-gray-100 text-gray-800',
            'waiting_parts' => 'bg-orange-100 text-orange-800',
            'quoted' => 'bg-purple-100 text-purple-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'qc' => 'bg-cyan-100 text-cyan-800',
            'completed' => 'bg-green-100 text-green-800',
            'delivered' => 'bg-emerald-100 text-emerald-800',
            ];
            $statusNames = \App\Models\Repair::getStatuses();
            @endphp
            @foreach($statusNames as $key => $name)
            @if(!in_array($key, ['cancelled', 'claim']))
            <div class="text-center p-3 {{ $statusColors[$key] ?? 'bg-gray-100' }} rounded-lg">
                <p class="text-2xl font-bold">{{ $repairStats[$key] ?? 0 }}</p>
                <p class="text-xs mt-1">{{ $name }}</p>
            </div>
            @endif
            @endforeach
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Repairs -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-tools mr-2 text-blue-600"></i>
                    งานซ่อมล่าสุด
                </h3>
                <a href="{{ route('repairs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentRepairs ?? [] as $repair)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $repair->repair_number }}</p>
                            <p class="text-sm text-gray-500">{{ $repair->customer_name }} - {{ $repair->device_brand }} {{ $repair->device_model }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$repair->status] ?? 'bg-gray-100' }}">
                            {{ $statusNames[$repair->status] ?? $repair->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                    <p>ยังไม่มีงานซ่อม</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-receipt mr-2 text-green-600"></i>
                    รายการขายล่าสุด
                </h3>
                <a href="{{ route('sales.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentSales ?? [] as $sale)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $sale->sale_number }}</p>
                            <p class="text-sm text-gray-500">{{ $sale->customer_name ?: 'ลูกค้าทั่วไป' }}</p>
                        </div>
                        <span class="text-green-600 font-semibold">฿{{ number_format($sale->total, 0) }}</span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                    <p>ยังไม่มีรายการขาย</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-bolt mr-2 text-yellow-500"></i>
            การทำงานด่วน
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('repairs.create') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                <i class="fas fa-plus-circle text-3xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">รับงานซ่อมใหม่</span>
            </a>
            <a href="{{ route('pos') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors">
                <i class="fas fa-cash-register text-3xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">ขายสินค้า POS</span>
            </a>
            <a href="{{ route('products.create') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                <i class="fas fa-box text-3xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">เพิ่มสินค้าใหม่</span>
            </a>
            <a href="{{ route('customers.create') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors">
                <i class="fas fa-user-plus text-3xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">เพิ่มลูกค้าใหม่</span>
            </a>
        </div>
    </div>
</div>
@endsection