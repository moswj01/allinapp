@extends('layouts.app')

@section('title', 'เงินสดย่อย')
@section('page-title', 'เงินสดย่อย (Petty Cash)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">เงินสดย่อย</h2>
            <p class="text-sm text-gray-500 mt-1">บันทึกรายรับ-รายจ่ายเงินสดย่อย</p>
        </div>
        <a href="{{ route('finance.petty-cash.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"><i class="fas fa-plus mr-2"></i>บันทึกรายการ</a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">รายรับ</p>
                    <p class="text-xl font-bold text-green-600 mt-1">฿{{ number_format($summary['total_in'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"><i class="fas fa-arrow-down text-green-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">รายจ่าย</p>
                    <p class="text-xl font-bold text-red-600 mt-1">฿{{ number_format($summary['total_out'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-arrow-up text-red-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">คงเหลือ</p>
                    <p class="text-xl font-bold {{ $summary['balance'] >= 0 ? 'text-indigo-700' : 'text-red-600' }} mt-1">฿{{ number_format($summary['balance'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center"><i class="fas fa-wallet text-indigo-600"></i></div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">จาก</label>
                <input type="date" name="from" value="{{ $from }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">ถึง</label>
                <input type="date" name="to" value="{{ $to }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกประเภท</option>
                    <option value="in" @selected(request('type')==='in' )>รายรับ</option>
                    <option value="out" @selected(request('type')==='out' )>รายจ่าย</option>
                </select>
            </div>
            <div>
                <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">ทุกหมวดหมู่</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}" @selected(request('category')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>ค้นหา</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">วันที่</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">ประเภท</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">หมวดหมู่</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">รายละเอียด</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">จำนวนเงิน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ผู้บันทึก</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pettyCash as $pc)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-center text-sm text-gray-500">{{ \Carbon\Carbon::parse($pc->transaction_date)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $pc->type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $pc->type === 'in' ? 'รับ' : 'จ่าย' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $categories[$pc->category] ?? $pc->category }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $pc->description }}</td>
                    <td class="px-6 py-4 text-right text-sm font-semibold {{ $pc->type === 'in' ? 'text-green-600' : 'text-red-600' }}">{{ $pc->type === 'in' ? '+' : '-' }}฿{{ number_format($pc->amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $pc->createdBy->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">ไม่พบรายการ</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($pettyCash->hasPages())
        <div class="px-6 py-4 border-t">{{ $pettyCash->links() }}</div>
        @endif
    </div>
</div>
@endsection