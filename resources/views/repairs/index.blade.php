@extends('layouts.app')

@section('title', 'งานซ่อม')
@section('page-title', 'รายการงานซ่อม')

@section('content')
<div x-data="{
    showStatusModal: false,
    selectedRepairId: null,
    selectedStatus: '',
    statusAction: ''
}" class="space-y-4">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('repairs.export-csv', request()->only('status', 'from', 'to')) }}"
                class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-download mr-2"></i>Export CSV
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                class="inline-flex items-center px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors">
                <i class="fas fa-file-csv mr-2"></i>Import CSV
            </button>
            <a href="{{ route('repairs.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                รับงานซ่อมใหม่
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="เลขที่งาน, ลูกค้า, อุปกรณ์..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    @foreach($statuses as $key => $name)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทเครื่อง</label>
                <select name="device_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทั้งหมด</option>
                    <option value="smartphone" {{ request('device_type') === 'smartphone' ? 'selected' : '' }}>สมาร์ทโฟน</option>
                    <option value="tablet" {{ request('device_type') === 'tablet' ? 'selected' : '' }}>แท็บเล็ต</option>
                    <option value="smartwatch" {{ request('device_type') === 'smartwatch' ? 'selected' : '' }}>Smart Watch</option>
                    <option value="laptop" {{ request('device_type') === 'laptop' ? 'selected' : '' }}>โน้ตบุ๊ก</option>
                    <option value="other" {{ request('device_type') === 'other' ? 'selected' : '' }}>อื่นๆ</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-1"></i> ค้นหา
                </button>
                <a href="{{ route('repairs.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    ล้าง
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่งาน</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ลูกค้า</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">อุปกรณ์</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">อาการ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ช่าง</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ค่าซ่อม</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่รับ</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
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
                    'cancelled' => 'bg-red-100 text-red-800',
                    'claim' => 'bg-pink-100 text-pink-800',
                    ];
                    $priorityLabels = [
                    'urgent' => ['label' => 'ด่วน', 'class' => 'text-red-600'],
                    'high' => ['label' => 'สูง', 'class' => 'text-orange-600'],
                    'normal' => ['label' => '', 'class' => ''],
                    'low' => ['label' => 'ต่ำ', 'class' => 'text-gray-400'],
                    ];
                    @endphp
                    @forelse($repairs as $repair)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('repairs.show', $repair) }}'">>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm font-bold text-indigo-600">
                                {{ $repair->repair_number }}
                            </span>
                            @if(isset($priorityLabels[$repair->priority]) && $priorityLabels[$repair->priority]['label'])
                            <span class="ml-1 text-xs font-semibold {{ $priorityLabels[$repair->priority]['class'] }}">
                                <i class="fas fa-{{ $repair->priority === 'urgent' ? 'exclamation-circle' : 'arrow-up' }}"></i>
                                {{ $priorityLabels[$repair->priority]['label'] }}
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $repair->customer->name ?? $repair->customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $repair->customer_phone }}</p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            {{ $repair->device_brand }} {{ $repair->device_model }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">
                            {{ Str::limit($repair->problem_description, 40) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <button type="button"
                                onclick="event.stopPropagation(); " x-data x-on:click="selectedRepairId = {{ $repair->id }}; selectedStatus = '{{ $repair->status }}'; statusAction = '{{ route('repairs.status', $repair) }}'; showStatusModal = true"
                                class="px-2 py-1 text-xs font-medium rounded-full cursor-pointer hover:opacity-80 transition-opacity {{ $statusColors[$repair->status] ?? 'bg-gray-100' }}">
                                {{ $statuses[$repair->status] ?? $repair->status }}
                            </button>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                            @if($repair->technician)
                            <span class="text-gray-700">{{ $repair->technician->name }}</span>
                            @else
                            <span class="text-orange-500 text-xs"><i class="fas fa-user-slash mr-1"></i>ยังไม่มอบหมาย</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                            @if(($repair->total_cost ?? 0) > 0)
                            <span class="font-semibold text-green-600">฿{{ number_format($repair->total_cost ?? 0, 0) }}</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $repair->received_at->format('d/m/Y') }}
                            <span class="text-xs text-gray-400 block">{{ $repair->received_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right" onclick="event.stopPropagation()">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('repairs.show', $repair) }}" class="text-indigo-400 hover:text-indigo-600" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(in_array($repair->status, ['completed', 'delivered']))
                                <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'receipt']) }}" target="_blank" class="text-green-400 hover:text-green-600" title="ใบเสร็จ">
                                    <i class="fas fa-receipt"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-tools text-4xl mb-4 text-gray-300"></i>
                            <p>ไม่พบงานซ่อม</p>
                            <a href="{{ route('repairs.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline">
                                รับงานซ่อมใหม่
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($repairs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $repairs->links() }}
        </div>
        @endif
    </div>
    <!-- Status Update Modal -->
    <div x-show="showStatusModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showStatusModal = false"
        @keydown.escape.window="showStatusModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-exchange-alt text-indigo-600 mr-2"></i>เปลี่ยนสถานะ
                </h3>
                <button @click="showStatusModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="statusAction" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สถานะใหม่</label>
                    <select name="status" x-model="selectedStatus"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach(\App\Models\Repair::getStatuses() as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">โน้ต / หมายเหตุ</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="เพิ่มหมายเหตุ (ถ้ามี)..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showStatusModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-check mr-1"></i>อัปเดต
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Repair Detail Modal removed — moved to part-approvals -->
</div>
<div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-file-csv text-green-600 mr-2"></i>Import งานซ่อมจาก CSV
                </h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('repairs.import-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">เลือกไฟล์ CSV</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 p-2 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">รองรับไฟล์ .csv ขนาดไม่เกิน 5MB</p>
                </div>

                <div class="mb-4 bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">คอลัมน์ที่รองรับ:</p>
                    <div class="grid grid-cols-2 gap-1 text-xs text-gray-600">
                        <span><strong class="text-red-500">*</strong> customer_name</span>
                        <span><strong class="text-red-500">*</strong> customer_phone</span>
                        <span>customer_line_id</span>
                        <span>customer_email</span>
                        <span>customer_address</span>
                        <span>device_type</span>
                        <span>device_brand</span>
                        <span>device_model</span>
                        <span>device_color</span>
                        <span>device_serial</span>
                        <span>device_imei</span>
                        <span>device_password</span>
                        <span>device_condition</span>
                        <span>device_accessories</span>
                        <span><strong class="text-red-500">*</strong> problem_description</span>
                        <span>priority (normal/urgent/critical)</span>
                        <span>estimated_cost</span>
                        <span>deposit</span>
                        <span>internal_notes</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <strong class="text-red-500">*</strong> จำเป็น | ลูกค้าใหม่จะถูกสร้างอัตโนมัติ | สถานะเริ่มต้น = รอซ่อม
                    </p>
                </div>

                <div class="mb-4">
                    <a href="{{ route('repairs.import-template') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-download mr-1"></i>ดาวน์โหลดไฟล์ตัวอย่าง
                    </a>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">ยกเลิก</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection