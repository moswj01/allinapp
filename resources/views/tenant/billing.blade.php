@extends('layouts.app')

@section('title', 'แพ็กเกจและการเรียกเก็บเงิน')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">แพ็กเกจและการเรียกเก็บเงิน</h1>
            <p class="text-gray-500 mt-1">จัดการแพ็กเกจและดูประวัติการชำระเงิน</p>
        </div>
    </div>

    <!-- Current Plan -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-crown text-amber-500 mr-2"></i>แพ็กเกจปัจจุบัน</h2>
        </div>
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $tenant->plan->name }}</h3>
                        @if($tenant->isTrial())
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                            <i class="fas fa-clock mr-1"></i>ทดลองใช้ เหลือ {{ $tenant->daysLeftInTrial() }} วัน
                        </span>
                        @elseif($tenant->isActive())
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            <i class="fas fa-check-circle mr-1"></i>ใช้งานอยู่
                        </span>
                        @endif
                    </div>
                    <p class="text-gray-500">
                        {{ $tenant->plan->description ?? 'แพ็กเกจสำหรับร้านของคุณ' }}
                    </p>
                    @if($tenant->subscription_ends_at)
                    <p class="text-sm text-gray-400 mt-2">
                        <i class="fas fa-calendar-alt mr-1"></i>หมดอายุ: {{ $tenant->subscription_ends_at->format('d/m/Y') }}
                    </p>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-indigo-600">
                        {{ $tenant->plan->price > 0 ? '฿' . number_format($tenant->plan->price, 0) : 'ฟรี' }}
                    </div>
                    @if($tenant->plan->price > 0)
                    <div class="text-sm text-gray-400">/เดือน</div>
                    @endif
                </div>
            </div>

            <!-- Usage -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100">
                @php $usage = $tenant->getUsageSummary(); @endphp

                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600"><i class="fas fa-users mr-1"></i>ผู้ใช้</span>
                        <span class="font-medium text-gray-800">
                            {{ $usage['users']['current'] }} / {{ $usage['users']['max'] == -1 ? '∞' : $usage['users']['max'] }}
                        </span>
                    </div>
                    @if($usage['users']['max'] != -1)
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ min(100, ($usage['users']['current'] / max(1, $usage['users']['max'])) * 100) }}%"></div>
                    </div>
                    @else
                    <div class="w-full bg-green-100 rounded-full h-2">
                        <div class="bg-green-400 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600"><i class="fas fa-store mr-1"></i>สาขา</span>
                        <span class="font-medium text-gray-800">
                            {{ $usage['branches']['current'] }} / {{ $usage['branches']['max'] == -1 ? '∞' : $usage['branches']['max'] }}
                        </span>
                    </div>
                    @if($usage['branches']['max'] != -1)
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ min(100, ($usage['branches']['current'] / max(1, $usage['branches']['max'])) * 100) }}%"></div>
                    </div>
                    @else
                    <div class="w-full bg-green-100 rounded-full h-2">
                        <div class="bg-green-400 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600"><i class="fas fa-box mr-1"></i>สินค้า</span>
                        <span class="font-medium text-gray-800">
                            {{ $usage['products']['current'] }} / {{ $usage['products']['max'] == -1 ? '∞' : $usage['products']['max'] }}
                        </span>
                    </div>
                    @if($usage['products']['max'] != -1)
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-rose-500 h-2 rounded-full" style="width: {{ min(100, ($usage['products']['current'] / max(1, $usage['products']['max'])) * 100) }}%"></div>
                    </div>
                    @else
                    <div class="w-full bg-green-100 rounded-full h-2">
                        <div class="bg-green-400 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Request Banner -->
    @if($pendingRequest)
    <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-5">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-amber-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-amber-800">คำขอเปลี่ยนแพ็กเกจรออนุมัติ</h3>
                <p class="text-sm text-amber-700 mt-1">
                    {{ $pendingRequest->type === 'upgrade' ? 'อัพเกรด' : 'ดาวน์เกรด' }}จาก
                    <b>{{ $pendingRequest->currentPlan->name }}</b> เป็น
                    <b>{{ $pendingRequest->requestedPlan->name }}</b>
                    · ยอดชำระ <b>฿{{ number_format($pendingRequest->total_amount, 2) }}</b>
                </p>
                <p class="text-xs text-amber-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>กรุณารอการอนุมัติจากผู้ดูแลระบบ กรุณาชำระเงินก่อนเพื่อให้การอนุมัติรวดเร็วขึ้น
                </p>
                @if(!$pendingRequest->is_paid)
                <div class="mt-3 p-3 bg-white rounded-lg border border-amber-200">
                    <p class="text-sm font-semibold text-gray-800 mb-1"><i class="fas fa-university text-indigo-500 mr-1"></i>ข้อมูลการชำระเงิน</p>
                    <p class="text-xs text-gray-600">ธนาคาร: กสิกรไทย (KBank)</p>
                    <p class="text-xs text-gray-600">เลขบัญชี: XXX-X-XXXXX-X</p>
                    <p class="text-xs text-gray-600">ชื่อบัญชี: บจก. ออลอินเซอร์วิส</p>
                    <p class="text-xs text-gray-500 mt-1">* หลังโอนเงินแล้ว กรุณาแจ้งผู้ดูแลระบบ</p>
                </div>
                @else
                <p class="text-sm text-green-600 mt-2"><i class="fas fa-check-circle mr-1"></i>ชำระเงินแล้ว · รออนุมัติ</p>
                @endif
            </div>
            <form action="{{ route('billing.cancel-plan-request', $pendingRequest->id) }}" method="POST" onsubmit="return confirm('ยกเลิกคำขอเปลี่ยนแพ็กเกจนี้?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 text-xs text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                    <i class="fas fa-times mr-1"></i>ยกเลิกคำขอ
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Change Plan -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-exchange-alt text-indigo-500 mr-2"></i>เปลี่ยนแพ็กเกจ</h2>
            <p class="text-sm text-gray-500 mt-1">เลือกแพ็กเกจที่ต้องการ คำขอจะถูกส่งให้ผู้ดูแลระบบอนุมัติ</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($plans), 4) }} gap-4">
                @foreach($plans as $plan)
                <div class="border-2 rounded-xl p-5 {{ $tenant->plan_id === $plan->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} transition relative">
                    @if($tenant->plan_id === $plan->id)
                    <div class="absolute -top-3 left-4 px-3 py-0.5 bg-indigo-600 text-white text-xs font-medium rounded-full">
                        ปัจจุบัน
                    </div>
                    @endif

                    <h3 class="text-lg font-bold text-gray-800">{{ $plan->name }}</h3>
                    <div class="text-2xl font-bold text-indigo-600 mt-2">
                        {{ $plan->price > 0 ? '฿' . number_format($plan->price, 0) : 'ฟรี' }}
                        @if($plan->price > 0)
                        <span class="text-sm text-gray-400 font-normal">/เดือน</span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500 w-4"></i>
                            {{ $plan->max_users == -1 ? 'ไม่จำกัดผู้ใช้' : $plan->max_users . ' ผู้ใช้' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500 w-4"></i>
                            {{ $plan->max_branches == -1 ? 'ไม่จำกัดสาขา' : $plan->max_branches . ' สาขา' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500 w-4"></i>
                            {{ $plan->max_products == -1 ? 'ไม่จำกัดสินค้า' : number_format($plan->max_products) . ' สินค้า' }}
                        </div>
                    </div>

                    @if($tenant->plan_id !== $plan->id)
                    @if($pendingRequest)
                    <div class="mt-4">
                        <button disabled class="w-full py-2 px-4 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                            <i class="fas fa-clock mr-1"></i>มีคำขอรออนุมัติ
                        </button>
                    </div>
                    @else
                    <form action="{{ route('billing.change-plan') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="w-full py-2 px-4 rounded-lg text-sm font-medium transition
                                {{ $plan->price > $tenant->plan->price ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            onclick="return confirm('{{ $plan->price > $tenant->plan->price ? 'ส่งคำขออัพเกรดเป็น ' . $plan->name . '? (ต้องรอการอนุมัติจากผู้ดูแลระบบ)' : 'ส่งคำขอดาวน์เกรดเป็น ' . $plan->name . '? (ต้องรอการอนุมัติจากผู้ดูแลระบบ)' }}')">
                            <i class="fas fa-paper-plane mr-1"></i>
                            {{ $plan->price > $tenant->plan->price ? 'ขออัพเกรด' : 'ขอดาวน์เกรด' }}
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Invoices -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-file-invoice text-green-500 mr-2"></i>ประวัติการเรียกเก็บเงิน</h2>
        </div>
        @if($invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลขที่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">แพ็กเกจ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ช่วงเวลา</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">จำนวนเงิน</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">ชำระเมื่อ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $invoice->invoice_number }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $invoice->plan->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $invoice->period_start?->format('d/m/Y') }} - {{ $invoice->period_end?->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-gray-800">฿{{ number_format($invoice->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                            $statusColors = [
                            'paid' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'overdue' => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-gray-100 text-gray-500',
                            ];
                            $statusLabels = [
                            'paid' => 'ชำระแล้ว',
                            'pending' => 'รอชำระ',
                            'overdue' => 'เลยกำหนด',
                            'cancelled' => 'ยกเลิก',
                            ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">
                            {{ $invoice->paid_at?->format('d/m/Y') ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $invoices->links() }}
        </div>
        @endif
        @else
        <div class="p-12 text-center text-gray-400">
            <i class="fas fa-file-invoice text-4xl mb-3"></i>
            <p>ยังไม่มีประวัติการเรียกเก็บเงิน</p>
        </div>
        @endif
    </div>
</div>
@endsection