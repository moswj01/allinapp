@extends('layouts.app')

@section('title', $supplier->name)
@section('page-title', 'รายละเอียดซัพพลายเออร์')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h2>
            <p class="text-gray-500">รหัส: {{ $supplier->code ?? '-' }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </a>
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Supplier Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-truck text-indigo-600 mr-2"></i>
                    ข้อมูลซัพพลายเออร์
                </h3>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">ชื่อ</dt>
                        <dd class="font-medium text-gray-900">{{ $supplier->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">รหัส</dt>
                        <dd class="font-mono font-medium">{{ $supplier->code ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">ผู้ติดต่อ</dt>
                        <dd class="font-medium">{{ $supplier->contact_person ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">เบอร์โทร</dt>
                        <dd class="font-medium">
                            @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" class="text-indigo-600 hover:underline">{{ $supplier->phone }}</a>
                            @else
                            -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">อีเมล</dt>
                        <dd class="font-medium">
                            @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="text-indigo-600 hover:underline">{{ $supplier->email }}</a>
                            @else
                            -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">เลขประจำตัวผู้เสียภาษี</dt>
                        <dd class="font-mono font-medium">{{ $supplier->tax_id ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">เครดิต</dt>
                        <dd class="font-medium">{{ $supplier->credit_days ? $supplier->credit_days . ' วัน' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">สถานะ</dt>
                        <dd>
                            @if($supplier->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">ใช้งาน</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">ไม่ใช้งาน</span>
                            @endif
                        </dd>
                    </div>
                    @if($supplier->address)
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">ที่อยู่</dt>
                        <dd class="font-medium">{{ $supplier->address }}</dd>
                    </div>
                    @endif
                    @if($supplier->notes)
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">หมายเหตุ</dt>
                        <dd class="text-gray-700">{{ $supplier->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Products from this Supplier -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-box text-green-600 mr-2"></i>
                    สินค้าจากซัพพลายเออร์นี้
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">{{ $supplier->products->count() }}</span>
                </h3>

                @if($supplier->products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ชื่อสินค้า</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">ต้นทุน</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">ราคาขาย</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($supplier->products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-mono text-gray-500">{{ $product->sku }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:underline">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-right text-gray-900">฿{{ number_format($product->cost ?? 0, 0) }}</td>
                                <td class="px-4 py-2 text-right font-medium text-green-600">฿{{ number_format($product->retail_price ?? 0, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p>ยังไม่มีสินค้าจากซัพพลายเออร์นี้</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ข้อมูลสรุป</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">จำนวนสินค้า:</dt>
                        <dd class="font-bold text-gray-900">{{ $supplier->products->count() }} รายการ</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">เครดิต:</dt>
                        <dd class="font-bold text-gray-900">{{ $supplier->credit_days ?? 0 }} วัน</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">สร้างเมื่อ:</dt>
                        <dd class="text-gray-700">{{ $supplier->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">อัปเดตล่าสุด:</dt>
                        <dd class="text-gray-700">{{ $supplier->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">การดำเนินการ</h3>
                <div class="space-y-2">
                    <a href="{{ route('suppliers.edit', $supplier) }}"
                        class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>แก้ไข
                    </a>
                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                        onsubmit="return confirm('ต้องการลบซัพพลายเออร์นี้หรือไม่?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>ลบ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection