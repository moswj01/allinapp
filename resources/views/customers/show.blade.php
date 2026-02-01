@extends('layouts.app')

@section('title', $customer->name)
@section('page-title', 'รายละเอียดลูกค้า')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="h-16 w-16 bg-indigo-100 rounded-full flex items-center justify-center">
                <span class="text-2xl font-bold text-indigo-600">{{ mb_substr($customer->name, 0, 1) }}</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h2>
                @php
                $typeColors = [
                'retail' => 'bg-gray-100 text-gray-800',
                'wholesale' => 'bg-blue-100 text-blue-800',
                'technician' => 'bg-purple-100 text-purple-800',
                'vip' => 'bg-yellow-100 text-yellow-800',
                ];
                $typeNames = [
                'retail' => 'ทั่วไป',
                'wholesale' => 'ขายส่ง',
                'technician' => 'ช่าง',
                'vip' => 'VIP',
                ];
                @endphp
                <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$customer->type] ?? 'bg-gray-100' }}">
                    {{ $typeNames[$customer->type] ?? $customer->type }}
                </span>
                @if(!$customer->is_active)
                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 ml-2">ไม่ใช้งาน</span>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </a>
            <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">งานซ่อมทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_repairs'] }}</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-tools text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">การซื้อทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_sales'] }}</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ยอดซื้อรวม</p>
                    <p class="text-2xl font-bold text-green-600">฿{{ number_format($stats['total_spent'], 0) }}</p>
                </div>
                <div class="h-12 w-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-emerald-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ยอดซ่อมรวม</p>
                    <p class="text-2xl font-bold text-blue-600">฿{{ number_format($stats['total_repairs_value'], 0) }}</p>
                </div>
                <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-wrench text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contact Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-address-card text-indigo-600 mr-2"></i>
                ข้อมูลติดต่อ
            </h3>

            <dl class="space-y-4">
                <div class="flex items-center">
                    <dt class="text-gray-400 w-8"><i class="fas fa-phone"></i></dt>
                    <dd class="text-gray-900">
                        <a href="tel:{{ $customer->phone }}" class="text-indigo-600 hover:underline">{{ $customer->phone }}</a>
                    </dd>
                </div>

                @if($customer->email)
                <div class="flex items-center">
                    <dt class="text-gray-400 w-8"><i class="fas fa-envelope"></i></dt>
                    <dd class="text-gray-900">
                        <a href="mailto:{{ $customer->email }}" class="text-indigo-600 hover:underline">{{ $customer->email }}</a>
                    </dd>
                </div>
                @endif

                @if($customer->line_id)
                <div class="flex items-center">
                    <dt class="text-green-500 w-8"><i class="fab fa-line"></i></dt>
                    <dd class="text-gray-900">{{ $customer->line_id }}</dd>
                </div>
                @endif

                @if($customer->facebook_id)
                <div class="flex items-center">
                    <dt class="text-blue-500 w-8"><i class="fab fa-facebook"></i></dt>
                    <dd class="text-gray-900">{{ $customer->facebook_id }}</dd>
                </div>
                @endif

                @if($customer->address)
                <div class="flex items-start">
                    <dt class="text-gray-400 w-8"><i class="fas fa-map-marker-alt"></i></dt>
                    <dd class="text-gray-900">{{ $customer->address }}</dd>
                </div>
                @endif
            </dl>

            @if($customer->company_name || $customer->tax_id)
            <div class="mt-4 pt-4 border-t">
                <h4 class="text-sm font-medium text-gray-500 mb-2">ข้อมูลภาษี</h4>
                @if($customer->company_name)
                <p class="text-gray-900">{{ $customer->company_name }}</p>
                @endif
                @if($customer->tax_id)
                <p class="text-sm text-gray-500">เลขผู้เสียภาษี: {{ $customer->tax_id }}</p>
                @endif
            </div>
            @endif

            @if($customer->notes)
            <div class="mt-4 pt-4 border-t">
                <h4 class="text-sm font-medium text-gray-500 mb-2">หมายเหตุ</h4>
                <p class="text-gray-700 text-sm">{{ $customer->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Recent Repairs -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-tools text-blue-600 mr-2"></i>
                    งานซ่อมล่าสุด
                </h3>
            </div>

            @if($customer->repairs->count() > 0)
            <div class="space-y-3">
                @foreach($customer->repairs as $repair)
                <a href="{{ route('repairs.show', $repair) }}" class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-900">{{ $repair->repair_number }}</span>
                        @php
                        $statusColors = [
                        'pending' => 'bg-gray-100 text-gray-800',
                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'delivered' => 'bg-emerald-100 text-emerald-800',
                        ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $statusColors[$repair->status] ?? 'bg-gray-100' }}">
                            {{ $repair->status }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $repair->device_brand }} {{ $repair->device_model }}</p>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>{{ $repair->created_at->format('d/m/Y') }}</span>
                        <span class="font-medium text-green-600">฿{{ number_format($repair->total_cost, 0) }}</span>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-2xl mb-2"></i>
                <p class="text-sm">ยังไม่มีงานซ่อม</p>
            </div>
            @endif
        </div>

        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-shopping-cart text-green-600 mr-2"></i>
                    การซื้อล่าสุด
                </h3>
            </div>

            @if($customer->sales->count() > 0)
            <div class="space-y-3">
                @foreach($customer->sales as $sale)
                <a href="{{ route('sales.show', $sale) }}" class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-900">{{ $sale->sale_number }}</span>
                        @php
                        $saleStatusColors = [
                        'completed' => 'bg-green-100 text-green-800',
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $saleStatusColors[$sale->status] ?? 'bg-gray-100' }}">
                            {{ $sale->status }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $sale->items_count ?? $sale->items->count() }} รายการ</p>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>{{ $sale->created_at->format('d/m/Y') }}</span>
                        <span class="font-medium text-green-600">฿{{ number_format($sale->total, 0) }}</span>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-2xl mb-2"></i>
                <p class="text-sm">ยังไม่มีการซื้อ</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection