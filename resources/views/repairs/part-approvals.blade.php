@extends('layouts.app')

@section('title', 'อนุมัติเบิกอะไหล่')
@section('page-title', 'อนุมัติเบิกอะไหล่ซ่อม')

@section('content')
<div x-data="{
    showDetailModal: false,
    showRejectModal: false,
    detail: null,
    rejectPartId: null,
    rejectPartName: '',
    deviceTypeMap: { smartphone: 'สมาร์ทโฟน', tablet: 'แท็บเล็ต', smartwatch: 'Smart Watch', laptop: 'โน้ตบุ๊ก', other: 'อื่นๆ' },
    openDetail(d) { this.detail = d; this.showDetailModal = true; },
    openReject(id, name) { this.rejectPartId = id; this.rejectPartName = name; this.showDetailModal = false; this.showRejectModal = true; }
}" class="space-y-4">
    <!-- Status Filter Tabs -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-2">
            @php
            $statuses = [
            'pending' => ['label' => 'รออนุมัติ', 'color' => 'yellow', 'icon' => 'clock'],
            'approved' => ['label' => 'อนุมัติแล้ว', 'color' => 'green', 'icon' => 'check-circle'],
            'rejected' => ['label' => 'ปฏิเสธ', 'color' => 'red', 'icon' => 'times-circle'],
            'all' => ['label' => 'ทั้งหมด', 'color' => 'gray', 'icon' => 'list'],
            ];
            @endphp
            @foreach($statuses as $key => $s)
            <a href="{{ route('repairs.part-approvals', ['status' => $key]) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center
                {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <i class="fas fa-{{ $s['icon'] }} mr-2"></i>{{ $s['label'] }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Parts Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">งานซ่อม</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">อะไหล่</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ราคา/หน่วย</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">รวม</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้เบิก</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">วันที่เบิก</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($parts as $part)
                    @php
                    $partJson = [
                    'id' => $part->id,
                    'part_name' => $part->part_name,
                    'quantity' => $part->quantity,
                    'unit_price' => $part->unit_price ?? 0,
                    'total' => ($part->unit_price ?? 0) * $part->quantity,
                    'status' => $part->status,
                    'notes' => $part->notes,
                    'requested_by' => $part->requestedBy->name ?? '-',
                    'requested_at' => $part->created_at->format('d/m/Y H:i'),
                    'approved_by' => $part->approvedBy->name ?? null,
                    'approved_at' => $part->approved_at?->format('d/m/Y H:i'),
                    'rejected_by' => $part->rejectedBy->name ?? null,
                    'rejected_at' => $part->rejected_at?->format('d/m/Y H:i'),
                    'reject_reason' => $part->reject_reason,
                    'repair_number' => $part->repair->repair_number,
                    'repair_id' => $part->repair->id,
                    'device_type' => $part->repair->device_type,
                    'device_brand' => $part->repair->device_brand,
                    'device_model' => $part->repair->device_model,
                    'customer_name' => $part->repair->customer->name ?? $part->repair->customer_name,
                    'customer_phone' => $part->repair->customer_phone,
                    'problem_description' => $part->repair->problem_description,
                    'diagnosis' => $part->repair->diagnosis,
                    'technician_name' => $part->repair->technician->name ?? null,
                    'show_url' => route('repairs.show', $part->repair),
                    'approve_url' => route('repairs.approve-part', $part),
                    ];
                    @endphp
                    <tr class="{{ $part->status === 'pending' ? 'bg-yellow-50' : ($part->status === 'rejected' ? 'bg-red-50' : '') }} hover:bg-gray-50 cursor-pointer"
                        @click="openDetail({{ Js::from($partJson) }})">
                        <td class="px-4 py-3 text-sm">
                            <span class="text-indigo-600 font-medium">{{ $part->repair->repair_number }}</span>
                            <div class="text-xs text-gray-500">{{ $part->repair->device_brand }} {{ $part->repair->device_model }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $part->part_name }}</div>
                            @if($part->notes)
                            <div class="text-xs text-gray-500">{{ $part->notes }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center">{{ $part->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-right">฿{{ number_format($part->unit_price ?? 0, 0) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium">฿{{ number_format(($part->unit_price ?? 0) * $part->quantity, 0) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-user text-indigo-600 text-xs"></i>
                                </div>
                                <span>{{ $part->requestedBy->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">
                            {{ $part->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                            $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabels = [
                            'pending' => 'รออนุมัติ',
                            'approved' => 'อนุมัติแล้ว',
                            'rejected' => 'ปฏิเสธ',
                            ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$part->status] ?? 'bg-gray-100' }}">
                                {{ $statusLabels[$part->status] ?? $part->status }}
                            </span>
                            @if($part->status === 'approved' && $part->approvedBy)
                            <div class="text-xs text-green-600 mt-1">
                                <i class="fas fa-check mr-1"></i>{{ $part->approvedBy->name }}
                                <br>{{ $part->approved_at?->format('d/m H:i') }}
                            </div>
                            @endif
                            @if($part->status === 'rejected')
                            <div class="text-xs text-red-500 mt-1">
                                <i class="fas fa-times mr-1"></i>{{ $part->rejectedBy->name ?? '' }}
                                @if($part->reject_reason)
                                <br>{{ $part->reject_reason }}
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            @if($part->status === 'pending')
                            <div class="flex justify-center space-x-2">
                                <form action="{{ route('repairs.approve-part', $part) }}" method="POST"
                                    onsubmit="return confirm('ยืนยันอนุมัติเบิกอะไหล่ {{ $part->part_name }} x {{ $part->quantity }}?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition-colors"
                                        title="อนุมัติ">
                                        <i class="fas fa-check mr-1"></i>อนุมัติ
                                    </button>
                                </form>
                                <button type="button"
                                    @click="openReject({{ $part->id }}, '{{ $part->part_name }}')"
                                    class="px-3 py-1.5 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition-colors"
                                    title="ปฏิเสธ">
                                    <i class="fas fa-times mr-1"></i>ปฏิเสธ
                                </button>
                            </div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-box-open text-3xl mb-2"></i>
                            <p>ไม่มีรายการเบิกอะไหล่</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($parts->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $parts->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- Part Detail Modal                       -->
    <!-- ═══════════════════════════════════════ -->
    <div x-show="showDetailModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showDetailModal = false"
        @keydown.escape.window="showDetailModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col" @click.stop>

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border-radius: 1rem 1rem 0 0;">
                <div>
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-cogs mr-2"></i>เบิกอะไหล่
                    </h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-white/20 text-white" x-text="detail?.repair_number"></span>
                        <span class="text-xs text-indigo-200" x-text="detail?.requested_at"></span>
                    </div>
                </div>
                <button @click="showDetailModal = false" class="text-white/70 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                <!-- ข้อมูลอะไหล่ -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        <i class="fas fa-puzzle-piece text-indigo-500 mr-1"></i> ข้อมูลอะไหล่
                    </h4>
                    <div class="bg-indigo-50 rounded-xl p-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <p class="text-xs text-gray-400">ชื่ออะไหล่</p>
                                <p class="text-base font-bold text-gray-900" x-text="detail?.part_name"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">สถานะ</p>
                                <span class="px-2 py-0.5 text-xs rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': detail?.status === 'pending',
                                        'bg-green-100 text-green-800': detail?.status === 'approved',
                                        'bg-red-100 text-red-800': detail?.status === 'rejected'
                                    }"
                                    x-text="detail?.status === 'pending' ? 'รออนุมัติ' : (detail?.status === 'approved' ? 'อนุมัติแล้ว' : 'ปฏิเสธ')"></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-3">
                            <div>
                                <p class="text-xs text-gray-400">จำนวน</p>
                                <p class="text-lg font-bold text-indigo-600" x-text="detail?.quantity"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">ราคา/หน่วย</p>
                                <p class="text-sm font-medium text-gray-900" x-text="'฿' + Number(detail?.unit_price || 0).toLocaleString()"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">รวม</p>
                                <p class="text-sm font-bold text-gray-900" x-text="'฿' + Number(detail?.total || 0).toLocaleString()"></p>
                            </div>
                        </div>
                        <template x-if="detail?.notes">
                            <div class="mt-3 pt-3 border-t border-indigo-200">
                                <p class="text-xs text-gray-400">หมายเหตุ</p>
                                <p class="text-sm text-gray-700" x-text="detail?.notes"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ผู้เบิก / ผู้อนุมัติ -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        <i class="fas fa-user-check text-green-500 mr-1"></i> ผู้ดำเนินการ
                    </h4>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                        <div>
                            <p class="text-xs text-gray-400">ผู้เบิก</p>
                            <p class="text-sm font-medium text-gray-900" x-text="detail?.requested_by"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">วันที่เบิก</p>
                            <p class="text-sm text-gray-700" x-text="detail?.requested_at"></p>
                        </div>
                        <template x-if="detail?.approved_by">
                            <div>
                                <p class="text-xs text-gray-400">ผู้อนุมัติ</p>
                                <p class="text-sm font-medium text-green-600" x-text="detail?.approved_by"></p>
                            </div>
                        </template>
                        <template x-if="detail?.approved_at">
                            <div>
                                <p class="text-xs text-gray-400">วันที่อนุมัติ</p>
                                <p class="text-sm text-green-600" x-text="detail?.approved_at"></p>
                            </div>
                        </template>
                        <template x-if="detail?.rejected_by">
                            <div>
                                <p class="text-xs text-gray-400">ผู้ปฏิเสธ</p>
                                <p class="text-sm font-medium text-red-600" x-text="detail?.rejected_by"></p>
                            </div>
                        </template>
                        <template x-if="detail?.reject_reason">
                            <div class="col-span-2">
                                <p class="text-xs text-gray-400">เหตุผลปฏิเสธ</p>
                                <p class="text-sm text-red-600 bg-red-50 p-2 rounded-lg mt-1" x-text="detail?.reject_reason"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- ข้อมูลงานซ่อม -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        <i class="fas fa-wrench text-orange-500 mr-1"></i> ข้อมูลงานซ่อม
                    </h4>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                        <div>
                            <p class="text-xs text-gray-400">เลขที่งาน</p>
                            <p class="text-sm font-bold text-indigo-600" x-text="detail?.repair_number"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">ช่างซ่อม</p>
                            <p class="text-sm font-medium" x-text="detail?.technician_name || 'ยังไม่มอบหมาย'" :class="detail?.technician_name ? 'text-gray-900' : 'text-orange-500'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">ลูกค้า</p>
                            <p class="text-sm font-medium text-gray-900" x-text="detail?.customer_name || '-'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">เบอร์โทร</p>
                            <p class="text-sm text-gray-700" x-text="detail?.customer_phone || '-'"></p>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลเครื่อง -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        <i class="fas fa-mobile-alt text-blue-500 mr-1"></i> ข้อมูลเครื่อง
                    </h4>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                        <div>
                            <p class="text-xs text-gray-400">ประเภท</p>
                            <p class="text-sm font-medium text-gray-900" x-text="deviceTypeMap[detail?.device_type] || detail?.device_type || '-'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">ยี่ห้อ / รุ่น</p>
                            <p class="text-sm font-medium text-gray-900" x-text="(detail?.device_brand || '') + ' ' + (detail?.device_model || '')"></p>
                        </div>
                    </div>
                </div>

                <!-- อาการ -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        <i class="fas fa-stethoscope text-red-500 mr-1"></i> อาการ / วินิจฉัย
                    </h4>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-400">อาการเสีย</p>
                            <p class="text-sm text-gray-900 whitespace-pre-line" x-text="detail?.problem_description || '-'"></p>
                        </div>
                        <template x-if="detail?.diagnosis">
                            <div>
                                <p class="text-xs text-gray-400">วินิจฉัย</p>
                                <p class="text-sm text-gray-700 whitespace-pre-line" x-text="detail?.diagnosis"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between" style="border-radius: 0 0 1rem 1rem;">
                <div class="flex items-center gap-2">
                    <!-- ปุ่มอนุมัติ/ปฏิเสธ (เฉพาะ pending) -->
                    <template x-if="detail?.status === 'pending'">
                        <form :action="detail?.approve_url" method="POST"
                            onsubmit="return confirm('ยืนยันอนุมัติเบิกอะไหล่?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-check mr-1"></i> อนุมัติ
                            </button>
                        </form>
                    </template>
                    <template x-if="detail?.status === 'pending'">
                        <button type="button" @click="openReject(detail?.id, detail?.part_name)"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-1"></i> ปฏิเสธ
                        </button>
                    </template>
                    <a :href="detail?.show_url" class="px-4 py-2 text-sm border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i> ดูงานซ่อม
                    </a>
                </div>
                <button @click="showDetailModal = false" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    ปิด
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- Reject Modal                            -->
    <!-- ═══════════════════════════════════════ -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    <i class="fas fa-times-circle mr-2"></i>ปฏิเสธเบิกอะไหล่
                </h3>
                <p class="text-sm text-gray-600 mb-4">อะไหล่: <span class="font-medium" x-text="rejectPartName"></span></p>

                <form :action="`{{ url('repairs/parts') }}/${rejectPartId}/reject`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผลที่ปฏิเสธ <span class="text-red-500">*</span></label>
                        <textarea name="reject_reason" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                            placeholder="ระบุเหตุผล..."></textarea>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" @click="showRejectModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            ยกเลิก
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class="fas fa-times mr-2"></i>ปฏิเสธ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection