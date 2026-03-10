@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม - ' . $repair->repair_number)
@section('page-title', 'รายละเอียดงานซ่อม')

@push('styles')
<style>
    /* ========== Animations ========== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulseDot {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .4;
        }
    }

    .anim-in {
        animation: fadeInUp .35s ease-out both;
    }

    .anim-in-1 {
        animation: fadeInUp .35s ease-out .06s both;
    }

    .anim-in-2 {
        animation: fadeInUp .35s ease-out .12s both;
    }

    /* ========== Card ========== */
    .card {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .03), 0 1px 2px rgba(0, 0, 0, .04);
        transition: box-shadow .25s ease;
    }

    .card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .06), 0 1px 3px rgba(0, 0, 0, .04);
    }

    /* ========== Glass header ========== */
    .glass-header {
        background: rgba(249, 250, 251, .88);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    /* ========== Buttons ========== */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.625rem;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.25rem;
        cursor: pointer;
        transition: all .2s ease;
        border: 1px solid transparent;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn:active {
        transform: scale(.97);
    }

    .btn-primary {
        background: #4f46e5;
        color: #fff;
        border-color: #4f46e5;
    }

    .btn-primary:hover {
        background: #4338ca;
        border-color: #4338ca;
    }

    .btn-success {
        background: #059669;
        color: #fff;
        border-color: #059669;
    }

    .btn-success:hover {
        background: #047857;
        border-color: #047857;
    }

    .btn-outline {
        background: #fff;
        color: #374151;
        border-color: #e5e7eb;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }

    .btn-danger-outline {
        background: #fff;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-danger-outline:hover {
        background: #fef2f2;
    }

    .btn-green-outline {
        background: #fff;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .btn-green-outline:hover {
        background: #f0fdf4;
    }

    .btn-red-outline {
        background: #fff;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .btn-red-outline:hover {
        background: #fef2f2;
    }

    /* ========== Status dot ========== */
    .status-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        margin-right: 6px;
        flex-shrink: 0;
    }

    .status-dot-pulse {
        animation: pulseDot 1.5s ease-in-out infinite;
    }

    /* ========== Form ========== */
    .form-input {
        width: 100%;
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: #111827;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.625rem;
        outline: none;
        transition: all .2s ease;
        -webkit-appearance: none;
    }

    .form-input:focus {
        background: #fff;
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
    }

    .form-input::placeholder {
        color: #9ca3af;
    }

    .form-label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.375rem;
    }

    /* ========== Section header ========== */
    .sec-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        margin-right: 0.625rem;
        flex-shrink: 0;
    }

    .sec-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.01em;
    }

    /* ========== Sidebar card ========== */
    .sidebar-card-header {
        padding: 0.75rem 1.25rem;
        font-size: 0.8125rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ========== Timeline ========== */
    .tl-item {
        position: relative;
        padding-left: 2.75rem;
        padding-bottom: 1.25rem;
    }

    .tl-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 2rem;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .tl-dot {
        position: absolute;
        left: 0;
        top: 2px;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6875rem;
        font-weight: 700;
        box-shadow: 0 0 0 3px #fff;
    }

    /* ========== Table ========== */
    .dtable th {
        padding: 0.625rem 1rem;
        text-align: left;
        font-size: 0.625rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: #f8fafc;
    }

    .dtable td {
        padding: 0.75rem 1rem;
        font-size: 0.8125rem;
        vertical-align: middle;
    }

    .dtable tbody tr {
        border-top: 1px solid #f1f5f9;
        transition: background .15s ease;
    }

    .dtable tbody tr:hover {
        background: #f8fafc;
    }

    /* ========== Modal ========== */
    .modal-bg {
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    /* ========== Data row ========== */
    .data-row {
        display: flex;
        align-items: flex-start;
        padding: 0.5rem 0;
    }

    .data-label {
        width: 5rem;
        font-size: 0.75rem;
        color: #94a3b8;
        flex-shrink: 0;
        padding-top: 1px;
    }

    .data-value {
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 500;
    }

    /* ========== Divider ========== */
    .divider-or {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0.5rem 0;
    }

    .divider-or::before,
    .divider-or::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .divider-or span {
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        font-weight: 600;
    }

    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
@php
$statusConfig = [
'pending' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
'waiting_parts' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400'],
'quoted' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'border' => 'border-violet-200','dot' => 'bg-violet-400'],
'confirmed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-400'],
'in_progress' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200','dot' => 'bg-yellow-500'],
'qc' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'border' => 'border-cyan-200', 'dot' => 'bg-cyan-400'],
'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200','dot' => 'bg-emerald-500'],
'delivered' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
];
$sc = $statusConfig[$repair->status] ?? $statusConfig['pending'];
$statusNames = \App\Models\Repair::getStatuses();
@endphp

<div class="max-w-7xl mx-auto space-y-4" x-data="{ editing: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center text-sm text-gray-400 anim-in">
        <a href="{{ route('repairs.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
            <i class="fas fa-wrench text-xs"></i>งานซ่อม
        </a>
        <i class="fas fa-chevron-right mx-2 text-[10px] text-gray-300"></i>
        <span class="text-gray-700 font-semibold">{{ $repair->repair_number }}</span>
    </nav>

    {{-- ==================== Sticky Header ==================== --}}
    <div class="glass-header sticky top-0 z-30 -mx-6 px-6 py-3 border-b border-gray-200/50 anim-in">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 max-w-7xl mx-auto">
            {{-- Left --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('repairs.index') }}"
                    class="w-9 h-9 rounded-lg border border-gray-200 bg-white flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-300 transition-all"
                    title="กลับ">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight">{{ $repair->repair_number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                            <span class="status-dot {{ $sc['dot'] }} {{ in_array($repair->status, ['in_progress','qc']) ? 'status-dot-pulse' : '' }}"></span>
                            {{ $statusNames[$repair->status] ?? $repair->status }}
                        </span>
                    </div>
                    <p class="text-gray-400 text-xs mt-0.5">
                        {{ $repair->created_at->format('d M Y, H:i') }} &middot; {{ $repair->receivedBy->name ?? 'N/A' }}
                    </p>
                </div>
            </div>
            {{-- Right --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('repairs.receipt', $repair) }}" target="_blank" class="btn btn-outline">
                    <i class="fas fa-print text-xs"></i>ใบรับเครื่อง
                </a>
                @if(in_array($repair->status, ['completed', 'delivered']))
                <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'receipt']) }}" target="_blank" class="btn btn-green-outline">
                    <i class="fas fa-receipt text-xs"></i>ใบเสร็จ
                </a>
                <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'tax_invoice']) }}" target="_blank" class="btn btn-red-outline">
                    <i class="fas fa-file-invoice text-xs"></i>ใบกำกับภาษี
                </a>
                @endif

                <span class="hidden md:block w-px h-6 bg-gray-200 mx-0.5"></span>

                <button x-show="!editing" x-on:click="editing = true" type="button" class="btn btn-primary">
                    <i class="fas fa-pen text-xs"></i>แก้ไข
                </button>
                <button x-show="editing" x-cloak x-on:click="editing = false" type="button" class="btn btn-outline">
                    <i class="fas fa-times text-xs"></i>ยกเลิก
                </button>
                <button x-show="editing" x-cloak type="button" x-on:click="$refs.editForm.submit()" class="btn btn-success">
                    <i class="fas fa-check text-xs"></i>บันทึก
                </button>
                <form action="{{ route('repairs.destroy', $repair) }}" method="POST" class="inline"
                    onsubmit="return confirm('ยืนยันลบงานซ่อม {{ $repair->repair_number }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline">
                        <i class="fas fa-trash-alt text-xs"></i>ลบ
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 anim-in">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-red-400 mt-0.5"></i>
            <ul class="text-red-600 text-sm space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ==================== Main Grid ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ===== LEFT ===== --}}
        <div class="lg:col-span-2 space-y-5">
            <form action="{{ route('repairs.update', $repair) }}" method="POST" x-ref="editForm">
                @csrf @method('PUT')

                {{-- Customer & Device --}}
                <div class="card overflow-hidden anim-in">
                    {{-- VIEW mode --}}
                    <div x-show="!editing" class="grid grid-cols-1 md:grid-cols-2">
                        {{-- Customer --}}
                        <div class="p-5 md:border-r border-gray-100">
                            <div class="flex items-center mb-4">
                                <div class="sec-icon bg-indigo-50 text-indigo-500"><i class="fas fa-user"></i></div>
                                <h3 class="sec-title">ข้อมูลลูกค้า</h3>
                            </div>
                            <div class="space-y-1">
                                <div class="data-row">
                                    <span class="data-label">ชื่อ</span>
                                    <span class="data-value font-semibold">{{ $repair->customer_name }}</span>
                                </div>
                                <div class="data-row">
                                    <span class="data-label">โทร</span>
                                    <a href="tel:{{ $repair->customer_phone }}" class="data-value text-indigo-600 hover:text-indigo-800 transition-colors">
                                        <i class="fas fa-phone-alt text-[10px] mr-1 opacity-50"></i>{{ $repair->customer_phone }}
                                    </a>
                                </div>
                                @if($repair->customer_line_id)
                                <div class="data-row">
                                    <span class="data-label">LINE</span>
                                    <span class="data-value text-green-600"><i class="fab fa-line text-xs mr-1"></i>{{ $repair->customer_line_id }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Device --}}
                        <div class="p-5 border-t md:border-t-0 border-gray-100">
                            <div class="flex items-center mb-4">
                                <div class="sec-icon bg-violet-50 text-violet-500"><i class="fas fa-mobile-alt"></i></div>
                                <h3 class="sec-title">ข้อมูลเครื่อง</h3>
                            </div>
                            <div class="space-y-1">
                                <div class="data-row">
                                    <span class="data-label">ประเภท</span>
                                    <span class="data-value">{{ $repair->device_type }}</span>
                                </div>
                                <div class="data-row">
                                    <span class="data-label">ยี่ห้อ/รุ่น</span>
                                    <span class="data-value font-semibold">{{ $repair->device_brand }} {{ $repair->device_model }}</span>
                                </div>
                                @if($repair->device_color)
                                <div class="data-row">
                                    <span class="data-label">สี</span>
                                    <span class="data-value">{{ $repair->device_color }}</span>
                                </div>
                                @endif
                                @if($repair->device_imei)
                                <div class="data-row">
                                    <span class="data-label">IMEI</span>
                                    <span class="font-mono text-xs text-gray-500 bg-gray-50 rounded px-1.5 py-0.5">{{ $repair->device_imei }}</span>
                                </div>
                                @endif
                                @if($repair->device_password)
                                <div class="data-row">
                                    <span class="data-label">รหัส</span>
                                    <span class="data-value font-mono">{{ $repair->device_password }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- EDIT mode --}}
                    <div x-show="editing" x-cloak class="p-5 space-y-6">
                        {{-- Customer Edit --}}
                        <div>
                            <div class="flex items-center mb-4">
                                <div class="sec-icon bg-indigo-50 text-indigo-500"><i class="fas fa-user-edit"></i></div>
                                <h3 class="sec-title">ข้อมูลลูกค้า</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="md:col-span-2">
                                    <label class="form-label">ลูกค้าเดิม</label>
                                    <select name="customer_id" class="form-input">
                                        <option value="">-- เลือกลูกค้าเดิม --</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $repair->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->phone }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="divider-or"><span>หรือกรอกข้อมูลใหม่</span></div>
                                </div>
                                <div>
                                    <label class="form-label">ชื่อลูกค้า <span class="text-red-400">*</span></label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name', $repair->customer_name) }}"
                                        class="form-input" placeholder="ชื่อ-นามสกุล" x-bind:required="editing">
                                </div>
                                <div>
                                    <label class="form-label">เบอร์โทร <span class="text-red-400">*</span></label>
                                    <input type="tel" name="customer_phone" value="{{ old('customer_phone', $repair->customer_phone) }}"
                                        class="form-input" placeholder="08X-XXX-XXXX" x-bind:required="editing">
                                </div>
                                <div>
                                    <label class="form-label">LINE ID</label>
                                    <input type="text" name="customer_line_id" value="{{ old('customer_line_id', $repair->customer_line_id) }}"
                                        class="form-input" placeholder="@lineid">
                                </div>
                            </div>
                        </div>

                        {{-- Device Edit --}}
                        <div class="border-t border-gray-100 pt-5">
                            <div class="flex items-center mb-4">
                                <div class="sec-icon bg-violet-50 text-violet-500"><i class="fas fa-mobile-alt"></i></div>
                                <h3 class="sec-title">ข้อมูลเครื่อง</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">ประเภทเครื่อง <span class="text-red-400">*</span></label>
                                    <select name="device_type" x-bind:required="editing" class="form-input">
                                        <option value="smartphone" {{ old('device_type', $repair->device_type) == 'smartphone' ? 'selected' : '' }}>สมาร์ทโฟน</option>
                                        <option value="tablet" {{ old('device_type', $repair->device_type) == 'tablet' ? 'selected' : '' }}>แท็บเล็ต</option>
                                        <option value="smartwatch" {{ old('device_type', $repair->device_type) == 'smartwatch' ? 'selected' : '' }}>Smart Watch</option>
                                        <option value="laptop" {{ old('device_type', $repair->device_type) == 'laptop' ? 'selected' : '' }}>โน้ตบุ๊ก</option>
                                        <option value="other" {{ old('device_type', $repair->device_type) == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">ยี่ห้อ <span class="text-red-400">*</span></label>
                                    <input type="text" name="device_brand" value="{{ old('device_brand', $repair->device_brand) }}"
                                        class="form-input" list="brandList" placeholder="Apple, Samsung, OPPO..." x-bind:required="editing">
                                    <datalist id="brandList">
                                        <option value="Apple">
                                        <option value="Samsung">
                                        <option value="OPPO">
                                        <option value="Vivo">
                                        <option value="Xiaomi">
                                        <option value="Huawei">
                                        <option value="Realme">
                                        <option value="Asus">
                                        <option value="Lenovo">
                                        <option value="HP">
                                        <option value="Dell">
                                    </datalist>
                                </div>
                                <div>
                                    <label class="form-label">รุ่น <span class="text-red-400">*</span></label>
                                    <input type="text" name="device_model" value="{{ old('device_model', $repair->device_model) }}"
                                        class="form-input" placeholder="iPhone 15 Pro Max" x-bind:required="editing">
                                </div>
                                <div>
                                    <label class="form-label">สี</label>
                                    <input type="text" name="device_color" value="{{ old('device_color', $repair->device_color) }}"
                                        class="form-input" placeholder="Black, White, Gold...">
                                </div>
                                <div>
                                    <label class="form-label">IMEI / Serial Number</label>
                                    <input type="text" name="device_imei" value="{{ old('device_imei', $repair->device_imei) }}"
                                        class="form-input" placeholder="IMEI หรือ S/N">
                                </div>
                                <div>
                                    <label class="form-label">รหัสผ่านเครื่อง</label>
                                    <input type="text" name="device_password" value="{{ old('device_password', $repair->device_password) }}"
                                        class="form-input" placeholder="PIN / Pattern (ถ้ามี)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Problem & Solution --}}
                <div class="card p-5 anim-in-1 mt-5">
                    <div class="flex items-center mb-4">
                        <div class="sec-icon bg-amber-50 text-amber-500"><i class="fas fa-tools"></i></div>
                        <h3 class="sec-title">อาการเสีย / การซ่อม</h3>
                    </div>

                    {{-- VIEW --}}
                    <div x-show="!editing" class="space-y-3">
                        <div>
                            <div class="form-label">อาการเสีย</div>
                            <p class="text-sm text-gray-800 bg-slate-50 rounded-lg p-3 leading-relaxed border border-slate-100">{{ $repair->problem_description }}</p>
                        </div>
                        @if($repair->diagnosis)
                        <div>
                            <div class="form-label">การวินิจฉัย</div>
                            <p class="text-sm text-gray-800 bg-blue-50 rounded-lg p-3 leading-relaxed border border-blue-100">{{ $repair->diagnosis }}</p>
                        </div>
                        @endif
                        @if($repair->solution)
                        <div>
                            <div class="form-label">วิธีแก้ไข</div>
                            <p class="text-sm text-gray-800 bg-emerald-50 rounded-lg p-3 leading-relaxed border border-emerald-100">{{ $repair->solution }}</p>
                        </div>
                        @endif
                        @if($repair->device_condition)
                        <div>
                            <div class="form-label">สภาพเครื่อง</div>
                            <p class="text-sm text-gray-800 bg-orange-50 rounded-lg p-3 leading-relaxed border border-orange-100">{{ $repair->device_condition }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- EDIT --}}
                    <div x-show="editing" x-cloak class="space-y-3">
                        <div>
                            <label class="form-label">อาการเสีย / ปัญหา <span class="text-red-400">*</span></label>
                            <textarea name="problem_description" rows="3" class="form-input" placeholder="อธิบายอาการเสียหรือปัญหาที่ลูกค้าแจ้ง" x-bind:required="editing">{{ old('problem_description', $repair->problem_description) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">การวินิจฉัย</label>
                            <textarea name="diagnosis" rows="3" class="form-input" placeholder="การวินิจฉัยจากช่าง">{{ old('diagnosis', $repair->diagnosis) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">วิธีแก้ไข / ผลการซ่อม</label>
                            <textarea name="solution" rows="3" class="form-input" placeholder="รายละเอียดการแก้ไข">{{ old('solution', $repair->solution) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">สภาพเครื่องก่อนซ่อม</label>
                            <textarea name="device_condition" rows="2" class="form-input" placeholder="รอยขีดข่วน, ฝาหลังแตก, อุปกรณ์ที่รับมาพร้อม">{{ old('device_condition', $repair->device_condition) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Pricing & Warranty (EDIT) --}}
                <div x-show="editing" x-cloak class="card p-5 mt-5">
                    <div class="flex items-center mb-4">
                        <div class="sec-icon bg-emerald-50 text-emerald-500"><i class="fas fa-calculator"></i></div>
                        <h3 class="sec-title">ค่าใช้จ่าย / การรับประกัน</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="form-label">ค่าบริการประเมิน</label>
                            <div class="relative">
                                <span class="absolute left-3 top-[0.6rem] text-gray-400 text-sm pointer-events-none">฿</span>
                                <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $repair->estimated_cost ?? 0) }}" min="0" class="form-input" style="padding-left:1.75rem">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">ค่าบริการ</label>
                            <div class="relative">
                                <span class="absolute left-3 top-[0.6rem] text-gray-400 text-sm pointer-events-none">฿</span>
                                <input type="number" name="service_cost" value="{{ old('service_cost', $repair->service_cost ?? 0) }}" min="0" class="form-input" style="padding-left:1.75rem">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">ส่วนลด</label>
                            <div class="relative">
                                <span class="absolute left-3 top-[0.6rem] text-gray-400 text-sm pointer-events-none">฿</span>
                                <input type="number" name="discount" value="{{ old('discount', $repair->discount ?? 0) }}" min="0" class="form-input" style="padding-left:1.75rem">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">มัดจำ</label>
                            <div class="relative">
                                <span class="absolute left-3 top-[0.6rem] text-gray-400 text-sm pointer-events-none">฿</span>
                                <input type="number" name="deposit" value="{{ old('deposit', $repair->deposit ?? 0) }}" min="0" class="form-input" style="padding-left:1.75rem">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">ระยะประกัน (วัน)</label>
                            <input type="number" name="warranty_days" value="{{ old('warranty_days', $repair->warranty_days ?? 30) }}" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">ระดับความเร่งด่วน</label>
                            <select name="priority" class="form-input">
                                <option value="low" {{ old('priority', $repair->priority) === 'low' ? 'selected' : '' }}>ปกติ</option>
                                <option value="medium" {{ old('priority', $repair->priority) === 'medium' ? 'selected' : '' }}>เร่งด่วน</option>
                                <option value="high" {{ old('priority', $repair->priority) === 'high' ? 'selected' : '' }}>ด่วนมาก</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">กำหนดนัดรับ</label>
                            <input type="datetime-local" name="estimated_completion"
                                value="{{ old('estimated_completion', $repair->estimated_completion ? $repair->estimated_completion->format('Y-m-d\TH:i') : '') }}"
                                class="form-input">
                        </div>
                        <div class="md:col-span-3">
                            <label class="form-label">เงื่อนไขการรับประกัน</label>
                            <textarea name="warranty_conditions" rows="2" class="form-input" placeholder="ไม่รับประกันหากเครื่องตกน้ำ, แกะเครื่องเอง ฯลฯ">{{ old('warranty_conditions', $repair->warranty_conditions) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Internal Notes (EDIT) --}}
                <div x-show="editing" x-cloak class="card p-5 mt-5">
                    <div class="flex items-center mb-4">
                        <div class="sec-icon bg-yellow-50 text-yellow-500"><i class="fas fa-sticky-note"></i></div>
                        <h3 class="sec-title">หมายเหตุภายใน</h3>
                    </div>
                    <textarea name="internal_notes" rows="3" class="form-input" placeholder="หมายเหตุสำหรับพนักงาน (ไม่แสดงให้ลูกค้า)">{{ old('internal_notes', $repair->internal_notes) }}</textarea>
                </div>

                {{-- Bottom Save --}}
                <div x-show="editing" x-cloak class="flex items-center justify-end gap-2 pt-3 mt-5">
                    <button type="button" x-on:click="editing = false" class="btn btn-outline">
                        <i class="fas fa-times text-xs"></i>ยกเลิกแก้ไข
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check text-xs"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>

            {{-- ==================== Parts ==================== --}}
            <div class="card overflow-hidden anim-in-1">
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center">
                        <div class="sec-icon bg-purple-50 text-purple-500"><i class="fas fa-microchip"></i></div>
                        <h3 class="sec-title">อะไหล่ที่ใช้</h3>
                    </div>
                    <button type="button"
                        onclick="document.getElementById('addPartModal').classList.remove('hidden')"
                        class="btn btn-primary" style="font-size:.75rem; padding:.375rem .75rem;">
                        <i class="fas fa-plus text-[10px]"></i>เบิกอะไหล่
                    </button>
                </div>

                @if($repair->parts->count() > 0)
                <div class="overflow-x-auto border-t border-gray-100">
                    <table class="w-full dtable">
                        <thead>
                            <tr>
                                <th>อะไหล่</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-right">ราคา/หน่วย</th>
                                <th class="text-right">รวม</th>
                                <th class="text-center">สถานะ</th>
                                <th>ผู้เบิก</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repair->parts as $part)
                            @php
                            $partSC = [
                            'pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800', 'issued' => 'bg-blue-100 text-blue-800',
                            'used' => 'bg-emerald-100 text-emerald-800', 'returned' => 'bg-orange-100 text-orange-800',
                            ];
                            $partSL = [
                            'pending' => 'รออนุมัติ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ปฏิเสธ',
                            'issued' => 'จ่ายแล้ว', 'used' => 'ใช้แล้ว', 'returned' => 'คืนแล้ว',
                            ];
                            @endphp
                            <tr class="{{ $part->status === 'rejected' ? 'bg-red-50/40' : '' }}">
                                <td class="text-gray-900 font-medium">
                                    {{ $part->part_name }}
                                    @if($part->notes)<p class="text-xs text-gray-400 mt-0.5 font-normal">{{ $part->notes }}</p>@endif
                                </td>
                                <td class="text-gray-600 text-center">{{ $part->quantity }}</td>
                                <td class="text-gray-600 text-right font-mono">฿{{ number_format($part->unit_price ?? 0, 0) }}</td>
                                <td class="text-gray-900 text-right font-semibold font-mono">฿{{ number_format(($part->unit_price ?? 0) * $part->quantity, 0) }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-bold rounded-full {{ $partSC[$part->status] ?? 'bg-gray-100' }}">
                                        {{ $partSL[$part->status] ?? $part->status }}
                                    </span>
                                    @if($part->status === 'rejected' && $part->reject_reason)
                                    <p class="text-[11px] text-red-400 mt-1">{{ $part->reject_reason }}</p>
                                    @endif
                                </td>
                                <td class="text-xs text-gray-500">
                                    <div><i class="fas fa-user text-gray-300 mr-1"></i>{{ $part->requestedBy->name ?? '-' }}</div>
                                    @if($part->approvedBy)
                                    <div class="text-green-600 mt-0.5"><i class="fas fa-check-circle mr-1"></i>{{ $part->approvedBy->name }}</div>
                                    @endif
                                    @if($part->rejectedBy)
                                    <div class="text-red-500 mt-0.5"><i class="fas fa-times-circle mr-1"></i>{{ $part->rejectedBy->name }}</div>
                                    @endif
                                    @if($part->status === 'pending')
                                    <form action="{{ route('repairs.cancel-part', [$repair, $part]) }}" method="POST" class="mt-1" onsubmit="return confirm('ยืนยันยกเลิกรายการนี้?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs transition-colors"><i class="fas fa-trash-alt mr-1"></i>ยกเลิก</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200">
                                <td colspan="3" class="text-right font-semibold text-gray-500 text-xs" style="background:#f8fafc">รวมค่าอะไหล่ (อนุมัติแล้ว)</td>
                                <td class="text-right font-bold text-gray-900 font-mono" style="background:#f8fafc">฿{{ number_format($repair->parts->where('status', 'approved')->sum(fn($p) => ($p->unit_price ?? 0) * $p->quantity), 0) }}</td>
                                <td colspan="2" style="background:#f8fafc"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-10 border-t border-gray-100">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-box-open text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">ยังไม่มีอะไหล่</p>
                    <p class="text-xs text-gray-300 mt-1">กดปุ่ม "เบิกอะไหล่" เพื่อเพิ่ม</p>
                </div>
                @endif
            </div>

            {{-- ==================== Activity Log ==================== --}}
            <div class="card p-5 anim-in-2">
                <div class="flex items-center mb-5">
                    <div class="sec-icon bg-gray-100 text-gray-400"><i class="fas fa-history"></i></div>
                    <h3 class="sec-title">ประวัติการดำเนินการ</h3>
                </div>
                <div>
                    @foreach($repair->logs->sortByDesc('created_at') as $log)
                    <div class="tl-item">
                        <div class="tl-dot bg-indigo-100 text-indigo-600">
                            {{ mb_substr($log->user->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-800">{{ $log->user->name ?? 'System' }}</span>
                                <span class="text-[11px] text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $log->description }}</p>
                            @if($log->old_value && $log->new_value)
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded bg-gray-100 text-gray-600">
                                    {{ $statusNames[$log->old_value] ?? $log->old_value }}
                                </span>
                                <i class="fas fa-arrow-right text-[8px] text-gray-300"></i>
                                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded bg-indigo-50 text-indigo-700">
                                    {{ $statusNames[$log->new_value] ?? $log->new_value }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Sidebar ===== --}}
        <div class="space-y-5">

            {{-- Status Change --}}
            <div class="card overflow-hidden anim-in">
                <div class="sidebar-card-header text-indigo-700">
                    <i class="fas fa-exchange-alt text-indigo-400 text-xs"></i>
                    <span>เปลี่ยนสถานะ</span>
                </div>
                <div class="p-4 space-y-3">
                    <form action="{{ route('repairs.status', $repair) }}" method="POST" class="space-y-3">
                        @csrf @method('PATCH')
                        <select name="status" class="form-input">
                            @foreach($statusNames as $key => $name)
                            <option value="{{ $key }}" {{ $repair->status === $key ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <textarea name="notes" rows="2" placeholder="หมายเหตุ (ถ้ามี)" class="form-input"></textarea>
                        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                            <i class="fas fa-check text-xs"></i>อัปเดตสถานะ
                        </button>
                    </form>
                </div>
            </div>

            {{-- Technician --}}
            <div class="card overflow-hidden anim-in-1">
                <div class="sidebar-card-header text-emerald-700">
                    <i class="fas fa-user-cog text-emerald-400 text-xs"></i>
                    <span>ช่างซ่อม</span>
                </div>
                <div class="p-4">
                    @if($repair->technician)
                    <div class="flex items-center gap-3 mb-3 bg-emerald-50 rounded-lg p-2.5 border border-emerald-100">
                        <div class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="font-bold text-emerald-700 text-sm">{{ mb_substr($repair->technician->name, 0, 1) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $repair->technician->name }}</p>
                            @if($repair->technician->phone)
                            <p class="text-xs text-gray-400">{{ $repair->technician->phone }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    <form action="{{ route('repairs.assign', $repair) }}" method="POST" class="space-y-3">
                        @csrf
                        <select name="technician_id" class="form-input">
                            <option value="">-- เลือกช่าง --</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $repair->technician_id === $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                            <i class="fas fa-user-check text-xs"></i>มอบหมายช่าง
                        </button>
                    </form>
                </div>
            </div>

            {{-- Cost Summary --}}
            <div class="card overflow-hidden anim-in-1">
                <div class="sidebar-card-header text-cyan-700">
                    <i class="fas fa-coins text-cyan-400 text-xs"></i>
                    <span>สรุปค่าใช้จ่าย</span>
                </div>
                <div class="p-4">
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-400">ค่าบริการ</dt>
                            <dd class="font-semibold text-gray-700 font-mono">฿{{ number_format($repair->service_cost ?? 0, 0) }}</dd>
                        </div>
                        @if($repair->discount > 0)
                        <div class="flex justify-between">
                            <dt class="text-red-400">ส่วนลด</dt>
                            <dd class="font-semibold text-red-500 font-mono">-฿{{ number_format($repair->discount ?? 0, 0) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between pt-3 border-t border-gray-100">
                            <dt class="font-bold text-gray-800">รวมทั้งสิ้น</dt>
                            <dd class="text-lg font-bold text-emerald-600 font-mono">฿{{ number_format($repair->total_cost ?? 0, 0) }}</dd>
                        </div>
                        @if($repair->deposit > 0 || $repair->paid_amount > 0)
                        <div class="flex justify-between text-blue-600">
                            <dt>ชำระแล้ว</dt>
                            <dd class="font-semibold font-mono">฿{{ number_format($repair->paid_amount ?? 0, 0) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-orange-500 font-semibold">คงเหลือ</dt>
                            <dd class="font-bold text-orange-600 font-mono text-lg">฿{{ number_format($repair->balance, 0) }}</dd>
                        </div>
                        @endif
                    </dl>
                    @if($repair->balance > 0)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <button type="button"
                            onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                            class="btn btn-success" style="width:100%; justify-content:center;">
                            <i class="fas fa-money-bill-wave text-xs"></i>รับชำระเงิน
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Warranty --}}
            @if($repair->warranty_days > 0)
            <div class="card overflow-hidden anim-in-2">
                <div class="sidebar-card-header text-blue-700">
                    <i class="fas fa-shield-alt text-blue-400 text-xs"></i>
                    <span>การรับประกัน</span>
                </div>
                <div class="p-4">
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-400">ระยะประกัน</dt>
                            <dd class="font-semibold text-gray-700">{{ $repair->warranty_days }} วัน</dd>
                        </div>
                        @if($repair->warranty_expires_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-400">หมดประกัน</dt>
                            <dd class="font-semibold {{ $repair->warranty_expires_at->isPast() ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $repair->warranty_expires_at->format('d/m/Y') }}
                                @if($repair->warranty_expires_at->isPast())
                                <span class="text-[10px]">(หมดแล้ว)</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($repair->warranty_conditions)
                        <div class="pt-2 border-t border-gray-100">
                            <div class="form-label mb-1">เงื่อนไข</div>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $repair->warranty_conditions }}</p>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif

            {{-- Internal Notes sidebar --}}
            @if($repair->internal_notes)
            <div x-show="!editing" class="card overflow-hidden anim-in-2">
                <div class="sidebar-card-header text-amber-700">
                    <i class="fas fa-sticky-note text-amber-400 text-xs"></i>
                    <span>หมายเหตุภายใน</span>
                </div>
                <div class="p-4">
                    <p class="text-sm text-gray-700 leading-relaxed bg-amber-50 rounded-lg p-3 border border-amber-100">{{ $repair->internal_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ==================== Add Part Modal ==================== --}}
<div id="addPartModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 modal-bg transition-opacity" onclick="document.getElementById('addPartModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 border border-gray-100">
            <div class="flex items-center mb-4">
                <div class="sec-icon bg-purple-50 text-purple-500"><i class="fas fa-plus-circle"></i></div>
                <h3 class="sec-title">เพิ่มสินค้า / อะไหล่</h3>
            </div>
            <form action="{{ route('repairs.add-part', $repair) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label">เลือกสินค้า</label>
                    <select name="product_id" required class="form-input">
                        <option value="">-- เลือกสินค้า --</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->name }} - ฿{{ number_format($part->retail_price ?? 0, 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">จำนวน</label>
                    <input type="number" name="quantity" value="1" min="1" required class="form-input">
                </div>
                <div>
                    <label class="form-label">หมายเหตุ</label>
                    <textarea name="notes" rows="2" class="form-input"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addPartModal').classList.add('hidden')" class="btn btn-outline" style="flex:1; justify-content:center;">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;"><i class="fas fa-plus text-xs"></i>เพิ่ม</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== Payment Modal ==================== --}}
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 modal-bg transition-opacity" onclick="document.getElementById('paymentModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 border border-gray-100">
            <div class="flex items-center mb-4">
                <div class="sec-icon bg-emerald-50 text-emerald-500"><i class="fas fa-money-bill-wave"></i></div>
                <h3 class="sec-title">รับชำระเงิน</h3>
            </div>
            <form action="{{ route('repairs.payment', $repair) }}" method="POST" class="space-y-3">
                @csrf
                <div class="bg-orange-50 rounded-lg p-3 border border-orange-100 flex justify-between items-center">
                    <span class="text-sm text-gray-500">ยอดคงเหลือ</span>
                    <span class="font-bold text-xl text-orange-600 font-mono">฿{{ number_format($repair->balance, 0) }}</span>
                </div>
                <div>
                    <label class="form-label">จำนวนเงิน</label>
                    <div class="relative">
                        <span class="absolute left-3 top-[0.6rem] text-gray-400 text-sm pointer-events-none">฿</span>
                        <input type="number" name="amount" value="{{ $repair->balance }}" min="1" max="{{ $repair->balance }}" required class="form-input" style="padding-left:1.75rem">
                    </div>
                </div>
                <div>
                    <label class="form-label">วิธีชำระ</label>
                    <select name="payment_method" required class="form-input">
                        <option value="cash">เงินสด</option>
                        <option value="transfer">โอนเงิน</option>
                        <option value="qr">QR Payment</option>
                        <option value="card">บัตรเครดิต</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">เลขอ้างอิง (ถ้ามี)</label>
                    <input type="text" name="payment_ref" class="form-input">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="btn btn-outline" style="flex:1; justify-content:center;">ยกเลิก</button>
                    <button type="submit" class="btn btn-success" style="flex:1; justify-content:center;"><i class="fas fa-check text-xs"></i>รับชำระ</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('open_receipt'))
<div id="autoOpenReceipt" data-url="{{ session('open_receipt') }}"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('autoOpenReceipt');
        if (el) window.open(el.dataset.url, '_blank');
    });
</script>
@endif
@endsection