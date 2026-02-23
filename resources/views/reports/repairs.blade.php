@extends('layouts.app')

@section('title', 'รายงานการซ่อม')
@section('page-title', 'รายงานการซ่อม')

@section('content')
<div class="space-y-4">
    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 bg-white rounded-xl shadow-sm px-4 py-2">
        <a href="{{ route('reports.sales', request()->query()) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
            <i class="fas fa-receipt mr-1"></i>การขาย
        </a>
        <a href="{{ route('reports.repairs', request()->query()) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white">
            <i class="fas fa-tools mr-1"></i>การซ่อม
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">จากวันที่</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">ถึงวันที่</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">สถานะงาน</label>
                <select name="status"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">ทั้งหมด</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>กำลังซ่อม
                    </option>
                    <option value="waiting_parts" {{ request('status') === 'waiting_parts' ? 'selected' : '' }}>รออะไหล่
                    </option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>ซ่อมเสร็จ
                    </option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>ส่งมอบแล้ว
                    </option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">สถานะชำระ</label>
                <select name="payment_status"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">ทั้งหมด</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>ยังไม่ชำระ
                    </option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>ชำระบางส่วน
                    </option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>ชำระครบ</option>
                </select>
            </div>
            <div>
                <button type="submit"
                    class="w-full px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-1"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">รายได้รวม</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">
                        ฿{{ number_format($summary['total_revenue'] ?? 0, 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-coins text-green-600"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs">
                <span class="text-green-600">ชำระแล้ว ฿{{ number_format($summary['total_paid'] ?? 0, 0) }}</span>
                <span class="text-red-600">ค้าง ฿{{ number_format($summary['total_unpaid'] ?? 0, 0) }}</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">จำนวนงาน</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_count'] ?? 0) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wrench text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">ค่าบริการเฉลี่ย</p>
                    <p class="text-xl font-bold text-purple-700 mt-1">
                        ฿{{ number_format($summary['avg_service_cost'] ?? 0, 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-2">สถานะงาน</p>
            <div class="grid grid-cols-2 gap-1 text-xs">
                @php
                $statusLabels = [
                'pending' => ['รอดำเนินการ', 'text-yellow-700'],
                'in_progress' => ['กำลังซ่อม', 'text-blue-700'],
                'waiting_parts' => ['รออะไหล่', 'text-orange-700'],
                'completed' => ['เสร็จ', 'text-green-700'],
                'delivered' => ['ส่งมอบ', 'text-emerald-700'],
                'cancelled' => ['ยกเลิก', 'text-red-700'],
                ];
                @endphp
                @foreach($summary['status_counts'] ?? [] as $s => $cnt)
                @if($cnt > 0)
                <span
                    class="{{ $statusLabels[$s][1] ?? 'text-gray-600' }} font-medium">{{ $statusLabels[$s][0] ?? $s }}:
                    {{ $cnt }}</span>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Daily Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><i
                    class="fas fa-chart-line text-indigo-500 mr-1"></i>งานซ่อมรายวัน</h3>
            <div class="space-y-1" style="max-height: 200px; overflow-y: auto;">
                @foreach($dailyRepairs as $day)
                @php $maxVal = $dailyRepairs->max('count') ?: 1; @endphp
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-14 text-gray-500">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-5 relative overflow-hidden">
                        <div class="bg-purple-500 h-5 rounded-full" style="width: {{ ($day->count / $maxVal) * 100 }}%">
                        </div>
                        <span
                            class="absolute inset-0 flex items-center justify-center text-xs font-medium {{ ($day->count / $maxVal) > 0.5 ? 'text-white' : 'text-gray-700' }}">
                            {{ $day->count }} งาน — ฿{{ number_format($day->total ?? 0, 0) }}
                        </span>
                    </div>
                </div>
                @endforeach
                @if($dailyRepairs->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4">ไม่มีข้อมูล</p>
                @endif
            </div>
        </div>

        <!-- Top Technicians -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><i
                    class="fas fa-user-cog text-blue-500 mr-1"></i>ช่างที่ซ่อมมากสุด</h3>
            <div class="space-y-2" style="max-height: 200px; overflow-y: auto;">
                @foreach($topTechnicians as $i => $tech)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                        <span class="text-gray-700">{{ $tech->name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-medium text-gray-800">{{ $tech->total_jobs }} งาน</span>
                        <span
                            class="text-xs text-gray-400 block">฿{{ number_format($tech->total_revenue ?? 0, 0) }}</span>
                    </div>
                </div>
                @endforeach
                @if($topTechnicians->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4">ไม่มีข้อมูล</p>
                @endif
            </div>
        </div>

        <!-- Device Brands -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><i
                    class="fas fa-mobile-alt text-orange-500 mr-1"></i>ยี่ห้อที่ซ่อมบ่อย</h3>
            <div class="space-y-2" style="max-height: 200px; overflow-y: auto;">
                @foreach($deviceBrands as $brand)
                @php $maxBrand = $deviceBrands->max('count') ?: 1; @endphp
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-700 w-20 truncate">{{ $brand->device_brand }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-4 relative overflow-hidden">
                        <div class="bg-orange-400 h-4 rounded-full"
                            style="width: {{ ($brand->count / $maxBrand) * 100 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500 w-8 text-right">{{ $brand->count }}</span>
                </div>
                @endforeach
                @if($deviceBrands->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4">ไม่มีข้อมูล</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Repair Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-list mr-1"></i>รายการซ่อม</h3>
            <span class="text-xs text-gray-500">{{ $repairs->total() }} รายการ</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">วันที่</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">เลขที่งาน</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ลูกค้า</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">อุปกรณ์</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ช่าง</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">ค่าบริการ</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">ชำระแล้ว</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">สถานะ</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">ชำระเงิน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($repairs as $repair)
                    @php
                    $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'waiting_parts' => 'bg-orange-100 text-orange-800',
                    'quoted' => 'bg-cyan-100 text-cyan-800',
                    'confirmed' => 'bg-blue-100 text-blue-800',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'qc' => 'bg-purple-100 text-purple-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'delivered' => 'bg-emerald-100 text-emerald-800',
                    'cancelled' => 'bg-red-100 text-red-800',
                    'claim' => 'bg-pink-100 text-pink-800',
                    ];
                    $statusNames = [
                    'pending' => 'รอดำเนินการ',
                    'waiting_parts' => 'รออะไหล่',
                    'quoted' => 'เสนอราคา',
                    'confirmed' => 'ยืนยัน',
                    'in_progress' => 'กำลังซ่อม',
                    'qc' => 'ตรวจสอบ',
                    'completed' => 'เสร็จ',
                    'delivered' => 'ส่งมอบ',
                    'cancelled' => 'ยกเลิก',
                    'claim' => 'เคลม',
                    ];
                    $payColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'partial' => 'bg-orange-100 text-orange-800',
                    'paid' => 'bg-green-100 text-green-800',
                    ];
                    $payNames = [
                    'pending' => 'ยังไม่ชำระ',
                    'partial' => 'บางส่วน',
                    'paid' => 'ชำระครบ',
                    ];
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $repair->status === 'cancelled' ? 'bg-red-50 opacity-60' : '' }}">
                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                            {{ $repair->created_at->format('d/m/y H:i') }}
                        </td>
                        <td class="px-3 py-2">
                            <a href="{{ route('repairs.show', $repair) }}"
                                class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $repair->repair_number }}
                            </a>
                        </td>
                        <td class="px-3 py-2 text-gray-700 truncate max-w-[100px]">
                            {{ $repair->customer->name ?? $repair->customer_name ?? '-' }}
                        </td>
                        <td class="px-3 py-2 text-gray-600 truncate max-w-[120px]">
                            {{ $repair->device_brand }} {{ $repair->device_model }}
                        </td>
                        <td class="px-3 py-2 text-gray-600 truncate max-w-[80px]">{{ $repair->technician->name ?? '-' }}
                        </td>
                        <td
                            class="px-3 py-2 text-right font-medium {{ $repair->status === 'cancelled' ? 'line-through text-gray-400' : 'text-gray-800' }}">
                            ฿{{ number_format($repair->total_cost ?? 0, 0) }}
                        </td>
                        <td class="px-3 py-2 text-right text-green-700">
                            ฿{{ number_format($repair->paid_amount ?? 0, 0) }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span
                                class="px-2 py-0.5 text-xs rounded-full {{ $statusColors[$repair->status] ?? 'bg-gray-100' }}">
                                {{ $statusNames[$repair->status] ?? $repair->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span
                                class="px-2 py-0.5 text-xs rounded-full {{ $payColors[$repair->payment_status] ?? 'bg-gray-100' }}">
                                {{ $payNames[$repair->payment_status] ?? $repair->payment_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                            <i class="fas fa-tools text-3xl mb-2 block"></i>
                            ไม่พบรายการซ่อมในช่วงเวลาที่เลือก
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($repairs->count() > 0)
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-right text-sm text-gray-600">รวมหน้านี้:</td>
                        <td class="px-3 py-2 text-right text-indigo-700">
                            ฿{{ number_format($repairs->where('status', '!=', 'cancelled')->sum('total_cost'), 0) }}
                        </td>
                        <td class="px-3 py-2 text-right text-green-700">
                            ฿{{ number_format($repairs->sum('paid_amount'), 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($repairs->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $repairs->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection