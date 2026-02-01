@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม - ' . $repair->repair_number)
@section('page-title', 'รายละเอียดงานซ่อม')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $repair->repair_number }}</h2>
            <p class="text-gray-500">
                สร้างเมื่อ {{ $repair->created_at->format('d/m/Y H:i') }}
                โดย {{ $repair->receivedBy->name ?? 'N/A' }}
            </p>
        </div>
        <div class="flex items-center space-x-3">
            @php
            $statusColors = [
            'pending' => 'bg-gray-100 text-gray-800 border-gray-300',
            'waiting_parts' => 'bg-orange-100 text-orange-800 border-orange-300',
            'quoted' => 'bg-purple-100 text-purple-800 border-purple-300',
            'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
            'in_progress' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'qc' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
            'completed' => 'bg-green-100 text-green-800 border-green-300',
            'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            ];
            $statusNames = \App\Models\Repair::getStatuses();
            @endphp
            <span class="px-4 py-2 text-sm font-semibold rounded-full border-2 {{ $statusColors[$repair->status] ?? 'bg-gray-100' }}">
                {{ $statusNames[$repair->status] ?? $repair->status }}
            </span>
            <a href="{{ route('repairs.receipt', $repair) }}" target="_blank" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-print mr-2"></i>พิมพ์ใบรับเครื่อง
            </a>
            <a href="{{ route('repairs.edit', $repair) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer & Device -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user text-indigo-600 mr-2"></i>
                            ข้อมูลลูกค้า
                        </h3>
                        <dl class="space-y-2">
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">ชื่อ:</dt>
                                <dd class="font-medium text-gray-900">{{ $repair->customer_name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">โทร:</dt>
                                <dd class="font-medium text-gray-900">
                                    <a href="tel:{{ $repair->customer_phone }}" class="text-indigo-600 hover:underline">
                                        {{ $repair->customer_phone }}
                                    </a>
                                </dd>
                            </div>
                            @if($repair->customer_line_id)
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">LINE:</dt>
                                <dd class="font-medium text-green-600">{{ $repair->customer_line_id }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Device Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-mobile-alt text-indigo-600 mr-2"></i>
                            ข้อมูลเครื่อง
                        </h3>
                        <dl class="space-y-2">
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">ประเภท:</dt>
                                <dd class="font-medium text-gray-900">{{ $repair->device_type }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">ยี่ห้อ/รุ่น:</dt>
                                <dd class="font-medium text-gray-900">{{ $repair->device_brand }} {{ $repair->device_model }}</dd>
                            </div>
                            @if($repair->device_color)
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">สี:</dt>
                                <dd class="font-medium text-gray-900">{{ $repair->device_color }}</dd>
                            </div>
                            @endif
                            @if($repair->device_imei)
                            <div class="flex">
                                <dt class="w-24 text-gray-500 text-sm">IMEI:</dt>
                                <dd class="font-medium text-gray-900 font-mono text-sm">{{ $repair->device_imei }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Problem & Solution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    อาการเสีย / การซ่อม
                </h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">อาการเสีย:</h4>
                        <p class="text-gray-900 bg-gray-50 rounded-lg p-3">{{ $repair->problem_description }}</p>
                    </div>

                    @if($repair->diagnosis)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">การวินิจฉัย:</h4>
                        <p class="text-gray-900 bg-blue-50 rounded-lg p-3">{{ $repair->diagnosis }}</p>
                    </div>
                    @endif

                    @if($repair->solution)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">วิธีแก้ไข:</h4>
                        <p class="text-gray-900 bg-green-50 rounded-lg p-3">{{ $repair->solution }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Parts Used -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-microchip text-purple-600 mr-2"></i>
                        อะไหล่ที่ใช้
                    </h3>
                    <button type="button"
                        onclick="document.getElementById('addPartModal').classList.remove('hidden')"
                        class="text-sm text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-plus mr-1"></i>เพิ่มอะไหล่
                    </button>
                </div>

                @if($repair->parts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">อะไหล่</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">ราคา/หน่วย</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">รวม</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($repair->parts as $part)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $part->part_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $part->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">฿{{ number_format($part->unit_price, 0) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">฿{{ number_format($part->total_price, 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                    $partStatusColors = [
                                    'pending' => 'bg-gray-100 text-gray-800',
                                    'approved' => 'bg-blue-100 text-blue-800',
                                    'issued' => 'bg-yellow-100 text-yellow-800',
                                    'used' => 'bg-green-100 text-green-800',
                                    'returned' => 'bg-orange-100 text-orange-800',
                                    ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $partStatusColors[$part->status] ?? 'bg-gray-100' }}">
                                        {{ $part->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right font-medium text-gray-700">รวมค่าอะไหล่:</td>
                                <td class="px-4 py-2 text-right font-bold text-gray-900">฿{{ number_format($repair->parts_cost, 0) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-box-open text-3xl mb-2"></i>
                    <p>ยังไม่มีอะไหล่</p>
                </div>
                @endif
            </div>

            <!-- Activity Log -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-history text-gray-600 mr-2"></i>
                    ประวัติการดำเนินการ
                </h3>

                <div class="space-y-4">
                    @foreach($repair->logs->sortByDesc('created_at') as $log)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-indigo-600">{{ substr($log->user->name ?? '?', 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                                <span class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $log->description }}</p>
                            @if($log->old_value && $log->new_value)
                            <p class="text-xs text-gray-500 mt-1">
                                สถานะ: {{ $statusNames[$log->old_value] ?? $log->old_value }} → {{ $statusNames[$log->new_value] ?? $log->new_value }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">เปลี่ยนสถานะ</h3>

                <form action="{{ route('repairs.status', $repair) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach($statusNames as $key => $name)
                        <option value="{{ $key }}" {{ $repair->status === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>

                    <textarea name="notes" rows="2" placeholder="หมายเหตุ (ถ้ามี)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>

                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>อัปเดตสถานะ
                    </button>
                </form>
            </div>

            <!-- Assign Technician -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ช่างซ่อม</h3>

                @if($repair->technician)
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="font-medium text-indigo-600">{{ substr($repair->technician->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $repair->technician->name }}</p>
                        <p class="text-sm text-gray-500">{{ $repair->technician->phone ?? '' }}</p>
                    </div>
                </div>
                @endif

                <form action="{{ route('repairs.assign', $repair) }}" method="POST">
                    @csrf
                    <select name="technician_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 mb-3">
                        <option value="">-- เลือกช่าง --</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ $repair->technician_id === $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-user-check mr-2"></i>มอบหมายช่าง
                    </button>
                </form>
            </div>

            <!-- Cost Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">สรุปค่าใช้จ่าย</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ค่าบริการ:</dt>
                        <dd class="font-medium">฿{{ number_format($repair->service_cost, 0) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ค่าอะไหล่:</dt>
                        <dd class="font-medium">฿{{ number_format($repair->parts_cost, 0) }}</dd>
                    </div>
                    @if($repair->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <dt>ส่วนลด:</dt>
                        <dd class="font-medium">-฿{{ number_format($repair->discount, 0) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between pt-3 border-t border-gray-200">
                        <dt class="text-lg font-semibold text-gray-900">รวมทั้งสิ้น:</dt>
                        <dd class="text-lg font-bold text-green-600">฿{{ number_format($repair->total_cost, 0) }}</dd>
                    </div>

                    @if($repair->deposit > 0 || $repair->paid_amount > 0)
                    <div class="flex justify-between text-blue-600">
                        <dt>ชำระแล้ว:</dt>
                        <dd class="font-medium">฿{{ number_format($repair->paid_amount, 0) }}</dd>
                    </div>
                    <div class="flex justify-between text-orange-600">
                        <dt>คงเหลือ:</dt>
                        <dd class="font-bold">฿{{ number_format($repair->balance, 0) }}</dd>
                    </div>
                    @endif
                </dl>

                @if($repair->balance > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <button type="button"
                        onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i>รับชำระเงิน
                    </button>
                </div>
                @endif
            </div>

            <!-- Warranty -->
            @if($repair->warranty_days > 0)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                    การรับประกัน
                </h3>

                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ระยะประกัน:</dt>
                        <dd class="font-medium">{{ $repair->warranty_days }} วัน</dd>
                    </div>
                    @if($repair->warranty_expires_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">หมดประกัน:</dt>
                        <dd class="font-medium {{ $repair->warranty_expires_at->isPast() ? 'text-red-600' : 'text-green-600' }}">
                            {{ $repair->warranty_expires_at->format('d/m/Y') }}
                        </dd>
                    </div>
                    @endif
                    @if($repair->warranty_conditions)
                    <div class="pt-2">
                        <dt class="text-gray-500 text-sm">เงื่อนไข:</dt>
                        <dd class="text-sm text-gray-700 mt-1">{{ $repair->warranty_conditions }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Part Modal -->
<div id="addPartModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('addPartModal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">เพิ่มอะไหล่</h3>

            <form action="{{ route('repairs.add-part', $repair) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลือกอะไหล่</label>
                    <select name="part_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกอะไหล่ --</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->name }} - ฿{{ number_format($part->price, 0) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวน</label>
                    <input type="number" name="quantity" value="1" min="1" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                        onclick="document.getElementById('addPartModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>เพิ่ม
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('paymentModal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">รับชำระเงิน</h3>

            <form action="{{ route('repairs.payment', $repair) }}" method="POST" class="space-y-4">
                @csrf

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">ยอดคงเหลือ:</span>
                        <span class="font-bold text-xl text-orange-600">฿{{ number_format($repair->balance, 0) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนเงิน</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="amount" value="{{ $repair->balance }}" min="1" max="{{ $repair->balance }}" required
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">วิธีชำระ</label>
                    <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="cash">เงินสด</option>
                        <option value="transfer">โอนเงิน</option>
                        <option value="qr">QR Payment</option>
                        <option value="card">บัตรเครดิต</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขอ้างอิง (ถ้ามี)</label>
                    <input type="text" name="payment_ref"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                        onclick="document.getElementById('paymentModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>รับชำระ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection