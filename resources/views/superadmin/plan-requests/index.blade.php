@extends('superadmin.layout')

@section('title', 'คำขอเปลี่ยนแพ็กเกจ')
@section('page-title', 'คำขอเปลี่ยนแพ็กเกจ')

@section('content')
<div class="space-y-6" x-data="{ showRejectModal: false, rejectId: null, showNoteModal: false, approveId: null }">
    <!-- Stats -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if($pendingCount > 0)
            <span class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-full text-sm font-medium">
                <i class="fas fa-clock mr-1"></i>{{ $pendingCount }} รออนุมัติ
            </span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.plan-requests.index') }}" class="px-3 py-2 text-sm rounded-lg {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">ทั้งหมด</a>
            <a href="{{ route('superadmin.plan-requests.index', ['status' => 'pending']) }}" class="px-3 py-2 text-sm rounded-lg {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">รออนุมัติ</a>
            <a href="{{ route('superadmin.plan-requests.index', ['status' => 'approved']) }}" class="px-3 py-2 text-sm rounded-lg {{ request('status') === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">อนุมัติแล้ว</a>
            <a href="{{ route('superadmin.plan-requests.index', ['status' => 'rejected']) }}" class="px-3 py-2 text-sm rounded-lg {{ request('status') === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">ปฏิเสธ</a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ร้านค้า</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">แพ็กเกจเดิม</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">แพ็กเกจใหม่</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ยอดชำระ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ชำระเงิน</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่ขอ</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $req)
                    @php
                    $statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-100 text-gray-500'];
                    $statusLabels = ['pending' => 'รออนุมัติ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ปฏิเสธ', 'cancelled' => 'ยกเลิก'];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-800">{{ $req->tenant->name }}</p>
                            <p class="text-xs text-gray-500">{{ $req->requestedByUser->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $req->currentPlan->name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $req->type === 'upgrade' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                <i class="fas fa-arrow-{{ $req->type === 'upgrade' ? 'up text-indigo-500' : 'down text-gray-400' }} mr-1"></i>
                                {{ $req->type === 'upgrade' ? 'อัพเกรด' : 'ดาวน์เกรด' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-indigo-600">{{ $req->requestedPlan->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                            ฿{{ number_format($req->total_amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($req->is_paid)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                <i class="fas fa-check mr-1"></i>ชำระแล้ว
                            </span>
                            @elseif($req->status === 'pending')
                            <form action="{{ route('superadmin.plan-requests.mark-paid', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันว่าร้านค้าชำระเงินแล้ว?')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="payment_method" value="bank_transfer">
                                <button type="submit" class="px-2 py-1 bg-amber-100 text-amber-700 hover:bg-amber-200 rounded-full text-xs font-medium transition">
                                    <i class="fas fa-money-bill-wave mr-1"></i>ยืนยันชำระ
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$req->status] ?? '' }}">
                                {{ $statusLabels[$req->status] ?? $req->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $req->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($req->status === 'pending')
                            <div class="flex items-center justify-center gap-1">
                                @if($req->is_paid)
                                <form action="{{ route('superadmin.plan-requests.approve', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('อนุมัติคำขอเปลี่ยนแพ็กเกจนี้?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1.5 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700 transition">
                                        <i class="fas fa-check mr-1"></i>อนุมัติ
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400">รอชำระ</span>
                                @endif
                                <button type="button" @click="rejectId = {{ $req->id }}; showRejectModal = true" class="px-2.5 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs hover:bg-red-200 transition">
                                    <i class="fas fa-times mr-1"></i>ปฏิเสธ
                                </button>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">
                                @if($req->admin_note)
                                <span title="{{ $req->admin_note }}"><i class="fas fa-comment-alt text-gray-400"></i></span>
                                @endif
                                {{ $req->approved_at?->format('d/m/Y') ?? '-' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @if($req->tenant_note || $req->admin_note)
                    <tr class="bg-gray-50">
                        <td colspan="9" class="px-4 py-2 text-xs">
                            @if($req->tenant_note)
                            <span class="text-gray-500"><i class="fas fa-comment mr-1"></i>หมายเหตุร้านค้า: {{ $req->tenant_note }}</span>
                            @endif
                            @if($req->admin_note)
                            <span class="text-indigo-600 ml-4"><i class="fas fa-reply mr-1"></i>ตอบกลับ: {{ $req->admin_note }}</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-exchange-alt text-4xl mb-3"></i>
                            <p>ยังไม่มีคำขอเปลี่ยนแพ็กเกจ</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showRejectModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-times-circle text-red-500 mr-2"></i>ปฏิเสธคำขอ</h3>
            <form :action="'/superadmin/plan-requests/' + rejectId + '/reject'" method="POST">
                @csrf @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">เหตุผล <span class="text-red-500">*</span></label>
                    <textarea name="admin_note" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500" placeholder="กรุณาระบุเหตุผลในการปฏิเสธ..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">ยกเลิก</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                        <i class="fas fa-times mr-1"></i>ปฏิเสธ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection