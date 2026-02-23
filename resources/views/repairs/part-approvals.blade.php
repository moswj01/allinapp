@extends('layouts.app')

@section('title', 'อนุมัติเบิกอะไหล่')
@section('page-title', 'อนุมัติเบิกอะไหล่ซ่อม')

@section('content')
<div x-data="{ showRejectModal: false, rejectPartId: null, rejectPartName: '' }" class="space-y-4">
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
                    <tr class="{{ $part->status === 'pending' ? 'bg-yellow-50' : ($part->status === 'rejected' ? 'bg-red-50' : '') }}">
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('repairs.show', $part->repair) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $part->repair->repair_number }}
                            </a>
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
                        <td class="px-4 py-3 text-center">
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
                                    @click="showRejectModal = true; rejectPartId = {{ $part->id }}; rejectPartName = '{{ $part->part_name }}'"
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

    <!-- Reject Modal -->
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