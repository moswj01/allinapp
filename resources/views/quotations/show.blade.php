@extends('layouts.app')

@section('title', 'ใบเสนอราคา ' . $quotation->quotation_number)
@section('page-title', 'รายละเอียดใบเสนอราคา')

@section('content')
<div x-data="{ showStatusModal: false, newStatus: '' }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('quotations.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $quotation->quotation_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">ใบเสนอราคา</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php $color = \App\Models\Quotation::getStatusColor($quotation->status); $statuses = \App\Models\Quotation::getStatuses(); @endphp
            @if($quotation->canBeEdited())
            <a href="{{ route('quotations.edit', $quotation) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg"><i class="fas fa-edit mr-1"></i>แก้ไข</a>
            @endif
            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg"><i class="fas fa-print mr-1"></i>พิมพ์</a>
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">{{ $statuses[$quotation->status] ?? $quotation->status }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ข้อมูลลูกค้า</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ชื่อ:</span><span class="font-medium">{{ $quotation->customer_name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">เบอร์โทร:</span><span>{{ $quotation->customer_phone ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">อีเมล:</span><span>{{ $quotation->customer_email ?? '-' }}</span></div>
                @if($quotation->subject)
                <div class="flex justify-between"><span class="text-gray-500">หัวข้อ:</span><span>{{ $quotation->subject }}</span></div>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">มูลค่า</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">รวมก่อนส่วนลด:</span><span>฿{{ number_format($quotation->subtotal, 2) }}</span></div>
                @if($quotation->discount_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">ส่วนลด:</span><span class="text-red-600">-฿{{ number_format($quotation->discount_amount, 2) }}</span></div>
                @endif
                @if($quotation->tax_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">ภาษี:</span><span>฿{{ number_format($quotation->tax_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between border-t pt-2"><span class="text-gray-700 font-semibold">รวมทั้งหมด:</span><span class="font-bold text-lg text-indigo-700">฿{{ number_format($quotation->total, 2) }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">ข้อมูลเอกสาร</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">สร้างโดย:</span><span>{{ $quotation->createdBy->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">สาขา:</span><span>{{ $quotation->branch->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">วันที่สร้าง:</span><span>{{ $quotation->created_at->format('d/m/Y H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">วันหมดอายุ:</span>
                    <span class="{{ $quotation->isExpired() ? 'text-red-500 font-semibold' : '' }}">{{ $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '-' }}</span>
                </div>
                @if($quotation->approvedBy)
                <div class="flex justify-between"><span class="text-gray-500">อนุมัติโดย:</span><span>{{ $quotation->approvedBy->name }}</span></div>
                @endif
            </div>
        </div>
    </div>

    @if($quotation->terms)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-blue-800 mb-1"><i class="fas fa-file-contract mr-1"></i>เงื่อนไข</h3>
        <p class="text-sm text-blue-700 whitespace-pre-line">{{ $quotation->terms }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-list mr-2 text-indigo-500"></i>รายการ</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">รายการ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">จำนวน</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">ราคาต่อหน่วย</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">รวม</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($quotation->items as $i => $item)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3"><span class="text-sm font-medium text-gray-800">{{ $item->item_name }}</span>
                        @if($item->description)<p class="text-xs text-gray-400 mt-0.5">{{ $item->description }}</p>@endif
                    </td>
                    <td class="px-6 py-3 text-center text-sm">{{ $item->quantity }}</td>
                    <td class="px-6 py-3 text-right text-sm">฿{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-6 py-3 text-right text-sm font-medium">฿{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right font-semibold">รวมทั้งหมด:</td>
                    <td class="px-6 py-3 text-right font-bold text-lg text-indigo-700">฿{{ number_format($quotation->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if(in_array($quotation->status, ['draft', 'sent']))
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
            @if($quotation->status === 'draft')
            <form method="POST" action="{{ route('quotations.update-status', $quotation) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="sent">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg"><i class="fas fa-paper-plane mr-1"></i>ส่งให้ลูกค้า</button>
            </form>
            @endif
            <form method="POST" action="{{ route('quotations.update-status', $quotation) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg"><i class="fas fa-check mr-1"></i>อนุมัติ</button>
            </form>
            <form method="POST" action="{{ route('quotations.update-status', $quotation) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg" onclick="return confirm('ต้องการปฏิเสธใบเสนอราคานี้?')"><i class="fas fa-times mr-1"></i>ปฏิเสธ</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection