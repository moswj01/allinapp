@extends('layouts.app')

@section('title', 'บัญชีลูกหนี้ ' . $accountsReceivable->invoice_number)
@section('page-title', 'รายละเอียดบัญชีลูกหนี้')

@section('content')
<div x-data="{ showPayment: false }" class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('accounts-receivable.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $accountsReceivable->invoice_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">บัญชีลูกหนี้</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ลูกค้า</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ชื่อ:</span><span class="font-medium">{{ $accountsReceivable->customer->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">โทร:</span><span>{{ $accountsReceivable->customer->phone ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">สาขา:</span><span>{{ $accountsReceivable->branch->name ?? '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ยอดเงิน</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ยอดรวม:</span><span class="font-medium">฿{{ number_format($accountsReceivable->total_amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">ชำระแล้ว:</span><span class="text-green-600 font-medium">฿{{ number_format($accountsReceivable->paid_amount, 2) }}</span></div>
                <div class="flex justify-between border-t pt-2"><span class="font-semibold">คงเหลือ:</span><span class="font-bold text-lg {{ $accountsReceivable->balance > 0 ? 'text-orange-600' : 'text-green-600' }}">฿{{ number_format($accountsReceivable->balance, 2) }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">วันที่</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">วันที่สร้าง:</span><span>{{ $accountsReceivable->created_at->format('d/m/Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">ครบกำหนด:</span>
                    @php $isOverdue = $accountsReceivable->due_date && $accountsReceivable->due_date < now() && $accountsReceivable->status !== 'paid'; @endphp
                        <span class="{{ $isOverdue ? 'text-red-600 font-semibold' : '' }}">{{ $accountsReceivable->due_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                @if($isOverdue)
                <div class="bg-red-50 border border-red-200 rounded-lg p-2 text-center"><span class="text-red-600 text-xs font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i>เกินกำหนด {{ now()->diffInDays($accountsReceivable->due_date) }} วัน</span></div>
                @endif
            </div>
            @if($accountsReceivable->balance > 0)
            <button @click="showPayment = true" class="mt-3 w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg"><i class="fas fa-money-bill mr-1"></i>บันทึกการชำระ</button>
            @endif
        </div>
    </div>

    {{-- Payment History --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-history mr-2 text-green-500"></i>ประวัติการชำระ</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">วันที่ชำระ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">จำนวนเงิน</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">วิธีชำระ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">อ้างอิง</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($accountsReceivable->payments as $i => $payment)
                @php $methods = ['cash' => 'เงินสด', 'transfer' => 'โอน', 'qr' => 'QR', 'card' => 'บัตร']; @endphp
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 text-sm">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : $payment->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-right text-sm font-semibold text-green-600">฿{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-3 text-center text-sm">{{ $methods[$payment->payment_method] ?? $payment->payment_method }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $payment->reference_number ?? '-' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $payment->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">ยังไม่มีประวัติการชำระ</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPayment" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-full max-w-md m-4" @click.away="showPayment = false">
            <h3 class="text-lg font-semibold mb-4"><i class="fas fa-money-bill text-green-500 mr-2"></i>บันทึกการชำระเงิน</h3>
            <form method="POST" action="{{ route('accounts-receivable.add-payment', $accountsReceivable) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนเงิน <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" required min="0.01" max="{{ $accountsReceivable->balance }}" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="฿">
                    <p class="text-xs text-gray-500 mt-1">ยอดค้างชำระ: ฿{{ number_format($accountsReceivable->balance, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">วิธีชำระ <span class="text-red-500">*</span></label>
                    <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="cash">เงินสด</option>
                        <option value="transfer">โอนเงิน</option>
                        <option value="qr">QR Code</option>
                        <option value="card">บัตรเครดิต</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">วันที่ชำระ <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขอ้างอิง</label>
                    <input type="text" name="reference_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="เลขอ้างอิง / เลขสลิป">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showPayment = false" class="px-4 py-2 border border-gray-300 rounded-lg">ยกเลิก</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">บันทึกการชำระ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection