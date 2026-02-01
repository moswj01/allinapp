@extends('layouts.app')

@section('title', 'จัดการลูกค้า')
@section('page-title', 'ลูกค้า')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ลูกค้าทั้งหมด</h2>
            <p class="text-gray-500">{{ $customers->total() }} ราย</p>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>เพิ่มลูกค้าใหม่
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อ, เบอร์โทร, อีเมล, LINE...">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภท</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="retail" {{ request('type') === 'retail' ? 'selected' : '' }}>ลูกค้าทั่วไป</option>
                    <option value="wholesale" {{ request('type') === 'wholesale' ? 'selected' : '' }}>ลูกค้าส่ง</option>
                    <option value="technician" {{ request('type') === 'technician' ? 'selected' : '' }}>ช่าง</option>
                    <option value="vip" {{ request('type') === 'vip' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>ใช้งาน</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                </select>
            </div>

            <div class="md:col-span-4 flex justify-end space-x-3">
                <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    ล้างตัวกรอง
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i>กรอง
                </button>
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลูกค้า</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เบอร์โทร</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ประเภท</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">งานซ่อม</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">การซื้อ</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">{{ mb_substr($customer->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('customers.show', $customer) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $customer->name }}
                                    </a>
                                    @if($customer->line_id)
                                    <p class="text-xs text-green-600"><i class="fab fa-line mr-1"></i>{{ $customer->line_id }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="tel:{{ $customer->phone }}" class="text-indigo-600 hover:underline">{{ $customer->phone }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <span class="font-medium text-gray-900">{{ $customer->repairs_count ?? 0 }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <span class="font-medium text-gray-900">{{ $customer->sales_count ?? 0 }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($customer->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">ใช้งาน</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">ไม่ใช้งาน</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('customers.show', $customer) }}" class="text-gray-400 hover:text-gray-600" title="ดู">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="text-blue-400 hover:text-blue-600" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                                    onsubmit="return confirm('ต้องการลบลูกค้านี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600" title="ลบ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4"></i>
                            <p>ยังไม่มีลูกค้า</p>
                            <a href="{{ route('customers.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline">
                                เพิ่มลูกค้าใหม่
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection