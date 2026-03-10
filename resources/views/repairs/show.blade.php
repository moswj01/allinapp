@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม - ' . $repair->repair_number)
@section('page-title', 'รายละเอียดงานซ่อม')

@push('styles')
<style>
    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseRing {
        0% { box-shadow: 0 0 0 0 rgba(99,102,241,0.4); }
        70% { box-shadow: 0 0 0 8px rgba(99,102,241,0); }
        100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
    }
    .animate-fade-in { animation: fadeInUp .4s ease-out both; }
    .animate-fade-in-delay { animation: fadeInUp .4s ease-out .1s both; }
    .animate-fade-in-delay-2 { animation: fadeInUp .4s ease-out .2s both; }
    .pulse-ring { animation: pulseRing 2s infinite; }

    /* Cards */
    .card {
        @apply bg-white rounded-2xl border border-gray-100 transition-all duration-300;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.06);
    }
    .card:hover {
        box-shadow: 0 10px 25px -5px rgba(0,0,0,.08), 0 4px 10px -5px rgba(0,0,0,.04);
    }

    /* Sticky header glass */
    .glass-header {
        background: rgba(249,250,251,.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    /* Buttons */
    .btn {
        @apply inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200;
    }
    .btn:active { transform: scale(.97); }
    .btn-primary { @apply bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-200; }
    .btn-success { @apply bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-200; }
    .btn-danger-outline { @apply border border-red-200 text-red-600 hover:bg-red-50; }
    .btn-outline { @apply border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300; }
    .btn-ghost { @apply text-gray-500 hover:text-gray-700 hover:bg-gray-100; }

    /* Status dot */
    .status-dot { @apply inline-block w-2 h-2 rounded-full mr-2; }
    .status-dot-pulse { animation: pulseRing 2s infinite; }

    /* Form inputs */
    .form-input {
        @apply w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50/50 transition-all duration-200;
    }
    .form-input:focus {
        @apply border-indigo-400 ring-4 ring-indigo-50 bg-white outline-none;
    }
    .form-label {
        @apply block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5;
    }

    /* Section title */
    .section-icon {
        @apply w-9 h-9 rounded-xl flex items-center justify-center text-sm mr-3 flex-shrink-0;
    }
    .section-title {
        @apply text-base font-bold text-gray-800;
    }

    /* Timeline */
    .timeline-item { @apply relative pl-10 pb-6; }
    .timeline-item:not(:last-child)::before {
        content: '';
        @apply absolute left-[15px] top-8 w-0.5 bg-gray-200;
        bottom: 0;
    }
    .timeline-dot {
        @apply absolute left-0 top-0.5 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold ring-4 ring-white;
    }

    /* Sidebar card header */
    .sidebar-header {
        @apply px-5 py-3.5 rounded-t-2xl text-white text-sm font-bold flex items-center;
    }

    /* Table */
    .pro-table th {
        @apply px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest;
    }
    .pro-table td {
        @apply px-4 py-3.5 text-sm;
    }
    .pro-table tbody tr {
        @apply border-t border-gray-50 hover:bg-indigo-50/30 transition-colors duration-150;
    }

    /* Modal overlay */
    .modal-overlay {
        background: rgba(17,24,39,.5);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@php
$statusConfig = [
    'pending'       => ['bg' => 'bg-slate-100',   'text' => 'text-slate-700',   'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
    'waiting_parts' => ['bg' => 'bg-amber-50',    'text' => 'text-amber-700',   'border' => 'border-amber-200', 'dot' => 'bg-amber-400'],
    'quoted'        => ['bg' => 'bg-violet-50',   'text' => 'text-violet-700',  'border' => 'border-violet-200','dot' => 'bg-violet-400'],
    'confirmed'     => ['bg' => 'bg-blue-50',     'text' => 'text-blue-700',    'border' => 'border-blue-200',  'dot' => 'bg-blue-400'],
    'in_progress'   => ['bg' => 'bg-yellow-50',   'text' => 'text-yellow-700',  'border' => 'border-yellow-200','dot' => 'bg-yellow-500'],
    'qc'            => ['bg' => 'bg-cyan-50',     'text' => 'text-cyan-700',    'border' => 'border-cyan-200',  'dot' => 'bg-cyan-400'],
    'completed'     => ['bg' => 'bg-emerald-50',  'text' => 'text-emerald-700', 'border' => 'border-emerald-200','dot' => 'bg-emerald-500'],
    'delivered'     => ['bg' => 'bg-green-50',    'text' => 'text-green-700',   'border' => 'border-green-200', 'dot' => 'bg-green-500'],
];
$sc = $statusConfig[$repair->status] ?? $statusConfig['pending'];
$statusNames = \App\Models\Repair::getStatuses();
@endphp

<div class="space-y-5 max-w-7xl mx-auto" x-data="{ editing: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center text-sm text-gray-400 animate-fade-in">
        <a href="{{ route('repairs.index') }}" class="hover:text-indigo-600 transition-colors duration-200 flex items-center">
            <i class="fas fa-wrench mr-1.5 text-xs"></i>งานซ่อม
        </a>
        <i class="fas fa-chevron-right mx-2.5 text-[10px] text-gray-300"></i>
        <span class="text-gray-700 font-semibold">{{ $repair->repair_number }}</span>
    </nav>

    {{-- Sticky Header --}}
    <div class="glass-header sticky top-0 z-30 -mx-6 px-6 py-4 border-b border-gray-200/60 animate-fade-in">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 max-w-7xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="{{ route('repairs.index') }}"
                   class="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm transition-all duration-200"
                   title="กลับไปรายการงานซ่อม">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">{{ $repair->repair_number }}</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                            <span class="status-dot {{ $sc['dot'] }} {{ in_array($repair->status, ['in_progress','qc']) ? 'status-dot-pulse' : '' }}"></span>
                            {{ $statusNames[$repair->status] ?? $repair->status }}
                        </span>
                    </div>
                    <p class="text-gray-400 text-xs mt-0.5">
                        สร้าง {{ $repair->created_at->format('d M Y, H:i') }}
                        &middot; {{ $repair->receivedBy->name ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Print buttons --}}
                <a href="{{ route('repairs.receipt', $repair) }}" target="_blank" class="btn btn-outline">
                    <i class="fas fa-print mr-1.5 text-xs"></i>ใบรับเครื่อง
                </a>
                @if(in_array($repair->status, ['completed', 'delivered']))
                <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'receipt']) }}" target="_blank" class="btn btn-outline" style="border-color: rgb(167,243,208); color: rgb(21,128,61);">
                    <i class="fas fa-receipt mr-1.5 text-xs"></i>ใบเสร็จ
                </a>
                <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'tax_invoice']) }}" target="_blank" class="btn btn-outline" style="border-color: rgb(254,202,202); color: rgb(185,28,28);">
                    <i class="fas fa-file-invoice mr-1.5 text-xs"></i>ใบกำกับภาษี
                </a>
                @endif

                <div class="w-px h-8 bg-gray-200 mx-1 hidden md:block"></div>

                {{-- Edit / Cancel / Save --}}
                <button x-show="!editing" x-on:click="editing = true" type="button" class="btn btn-primary">
                    <i class="fas fa-pen mr-1.5 text-xs"></i>แก้ไข
                </button>
                <button x-show="editing" x-cloak x-on:click="editing = false" type="button" class="btn btn-outline">
                    <i class="fas fa-times mr-1.5 text-xs"></i>ยกเลิก
                </button>
                <button x-show="editing" x-cloak type="button" x-on:click="$refs.editForm.submit()" class="btn btn-success">
                    <i class="fas fa-check mr-1.5 text-xs"></i>บันทึก
                </button>

                <form action="{{ route('repairs.destroy', $repair) }}" method="POST"
                      onsubmit="return confirm('ยืนยันลบงานซ่อม {{ $repair->repair_number }}? ข้อมูลทั้งหมดจะถูกลบถาวร')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline">
                        <i class="fas fa-trash-alt mr-1.5 text-xs"></i>ลบ
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 animate-fade-in">
        <div class="flex items-start">
            <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center mr-3 flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
            </div>
            <ul class="text-red-600 text-sm space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ===== LEFT: Main Content ===== --}}
        <div class="lg:col-span-2 space-y-5">
            <form action="{{ route('repairs.update', $repair) }}" method="POST" x-ref="editForm">
                @csrf @method('PUT')

                {{-- Customer & Device Card --}}
                <div class="card p-0 overflow-hidden animate-fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">

                        {{-- Customer Info - VIEW --}}
                        <div x-show="!editing" class="p-6">
                            <div class="flex items-center mb-5">
                                <div class="section-icon bg-indigo-50 text-indigo-600"><i class="fas fa-user"></i></div>
                                <h3 class="section-title">ข้อมูลลูกค้า</h3>
                            </div>
                            <dl class="space-y-3">
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">ชื่อ</dt>
                                    <dd class="font-semibold text-gray-900">{{ $repair->customer_name }}</dd>
                                </div>
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">โทร</dt>
                                    <dd>
                                        <a href="tel:{{ $repair->customer_phone }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                                            <i class="fas fa-phone-alt text-xs mr-1"></i>{{ $repair->customer_phone }}
                                        </a>
                                    </dd>
                                </div>
                                @if($repair->customer_line_id)
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">LINE</dt>
                                    <dd class="font-medium text-emerald-600"><i class="fab fa-line text-xs mr-1"></i>{{ $repair->customer_line_id }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Device Info - VIEW --}}
                        <div x-show="!editing" class="p-6">
                            <div class="flex items-center mb-5">
                                <div class="section-icon bg-purple-50 text-purple-600"><i class="fas fa-mobile-alt"></i></div>
                                <h3 class="section-title">ข้อมูลเครื่อง</h3>
                            </div>
                            <dl class="space-y-3">
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">ประเภท</dt>
                                    <dd class="font-medium text-gray-900">{{ $repair->device_type }}</dd>
                                </div>
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">ยี่ห้อ/รุ่น</dt>
                                    <dd class="font-semibold text-gray-900">{{ $repair->device_brand }} {{ $repair->device_model }}</dd>
                                </div>
                                @if($repair->device_color)
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">สี</dt>
                                    <dd class="text-gray-700">{{ $repair->device_color }}</dd>
                                </div>
                                @endif
                                @if($repair->device_imei)
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">IMEI</dt>
                                    <dd class="font-mono text-xs text-gray-600 bg-gray-50 rounded-lg px-2 py-1">{{ $repair->device_imei }}</dd>
                                </div>
                                @endif
                                @if($repair->device_password)
                                <div class="flex items-start">
                                    <dt class="w-20 text-xs text-gray-400 uppercase tracking-wider pt-0.5 flex-shrink-0">รหัส</dt>
                                    <dd class="font-mono text-sm text-gray-700">{{ $repair->device_password }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Customer & Device - EDIT --}}
                    <div x-show="editing" x-cloak class="p-6">
                        <div class="flex items-center mb-5">
                            <div class="section-icon bg-indigo-50 text-indigo-600"><i class="fas fa-user-edit"></i></div>
                            <h3 class="section-title">ข้อมูลลูกค้า</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
                                <div class="flex items-center gap-3 my-1">
                                    <div class="flex-1 h-px bg-gray-200"></div>
                                    <span class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold">หรือกรอกข้อมูลใหม่</span>
                                    <div class="flex-1 h-px bg-gray-200"></div>
                                </div>
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

                        <div class="border-t border-gray-100 pt-6">
                            <div class="flex items-center mb-5">
                                <div class="section-icon bg-purple-50 text-purple-600"><i class="fas fa-mobile-alt"></i></div>
                                <h3 class="section-title">ข้อมูลเครื่อง</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">ประเภทเครื่อง <span class="text-red-400">*</span></label>
                                    <select name="device_type" x-bind:required="editing" class="form-input">
                                        <option value="มือถือ" {{ old('device_type', $repair->device_type) == 'มือถือ' ? 'selected' : '' }}>มือถือ</option>
                                        <option value="แท็บเล็ต" {{ old('device_type', $repair->device_type) == 'แท็บเล็ต' ? 'selected' : '' }}>แท็บเล็ต</option>
                                        <option value="โน๊ตบุ๊ค" {{ old('device_type', $repair->device_type) == 'โน๊ตบุ๊ค' ? 'selected' : '' }}>โน๊ตบุ๊ค</option>
                                        <option value="คอมพิวเตอร์" {{ old('device_type', $repair->device_type) == 'คอมพิวเตอร์' ? 'selected' : '' }}>คอมพิวเตอร์</option>
                                        <option value="นาฬิกา" {{ old('device_type', $repair->device_type) == 'นาฬิกา' ? 'selected' : '' }}>นาฬิกา</option>
                                        <option value="อื่นๆ" {{ old('device_type', $repair->device_type) == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">ยี่ห้อ <span class="text-red-400">*</span></label>
                                    <input type="text" name="device_brand" value="{{ old('device_brand', $repair->device_brand) }}"
                                        class="form-input" list="brandList" placeholder="Apple, Samsung, OPPO..." x-bind:required="editing">
                                    <datalist id="brandList">
                                        <option value="Apple"><option value="Samsung"><option value="OPPO"><option value="Vivo">
                                        <option value="Xiaomi"><option value="Huawei"><option value="Realme"><option value="Asus">
                                        <option value="Lenovo"><option value="HP"><option value="Dell">
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

                {{-- Problem & Solution Card --}}
                <div class="card p-6 animate-fade-in-delay">
                    <div class="flex items-center mb-5">
                        <div class="section-icon bg-amber-50 text-amber-600"><i class="fas fa-tools"></i></div>
                        <h3 class="section-title">อาการเสีย / การซ่อม</h3>
                    </div>

                    {{-- VIEW --}}
                    <div x-show="!editing" class="space-y-4">
                        <div>
                            <h4 class="form-label">อาการเสีย</h4>
                            <p class="text-gray-900 bg-gray-50 rounded-xl p-4 text-sm leading-relaxed border border-gray-100">{{ $repair->problem_description }}</p>
                        </div>
                        @if($repair->diagnosis)
                        <div>
                            <h4 class="form-label">การวินิจฉัย</h4>
                            <p class="text-gray-900 bg-blue-50/60 rounded-xl p-4 text-sm leading-relaxed border border-blue-100">{{ $repair->diagnosis }}</p>
                        </div>
                        @endif
                        @if($repair->solution)
                        <div>
                            <h4 class="form-label">วิธีแก้ไข</h4>
                            <p class="text-gray-900 bg-emerald-50/60 rounded-xl p-4 text-sm leading-relaxed border border-emerald-100">{{ $repair->solution }}</p>
                        </div>
                        @endif
                        @if($repair->device_condition)
                        <div>
                            <h4 class="form-label">สภาพเครื่อง</h4>
                            <p class="text-gray-900 bg-orange-50/60 rounded-xl p-4 text-sm leading-relaxed border border-orange-100">{{ $repair->device_condition }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- EDIT --}}
                    <div x-show="editing" x-cloak class="space-y-4">
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

                {{-- Pricing & Warranty - EDIT ONLY --}}
                <div x-show="editing" x-cloak class="card p-6">
                    <div class="flex items-center mb-5">
                        <div class="section-icon bg-emerald-50 text-emerald-600"><i class="fas fa-calculator"></i></div>
                        <h3 class="section-title">ค่าใช้จ่าย / การรับประกัน</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">ค่าบริการประเมิน</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400 text-sm">฿</span>
                                <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $repair->estimated_cost ?? 0) }}" min="0" class="form-input pl-8">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">ค่าบริการ</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400 text-sm">฿</span>
                                <input type="number" name="service_cost" value="{{ old('service_cost', $repair->service_cost ?? 0) }}" min="0" class="form-input pl-8">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">ส่วนลด</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400 text-sm">฿</span>
                                <input type="number" name="discount" value="{{ old('discount', $repair->discount ?? 0) }}" min="0" class="form-input pl-8">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">มัดจำ</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400 text-sm">฿</span>
                                <input type="number" name="deposit" value="{{ old('deposit', $repair->deposit ?? 0) }}" min="0" class="form-input pl-8">
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

                {{-- Internal Notes - EDIT ONLY --}}
                <div x-show="editing" x-cloak class="card p-6">
                    <div class="flex items-center mb-5">
                        <div class="section-icon bg-yellow-50 text-yellow-600"><i class="fas fa-sticky-note"></i></div>
                        <h3 class="section-title">หมายเหตุภายใน</h3>
                    </div>
                    <textarea name="internal_notes" rows="3" class="form-input" placeholder="หมายเหตุสำหรับพนักงาน (ไม่แสดงให้ลูกค้า)">{{ old('internal_notes', $repair->internal_notes) }}</textarea>
                </div>

                {{-- Bottom Save --}}
                <div x-show="editing" x-cloak class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" x-on:click="editing = false" class="btn btn-outline">
                        <i class="fas fa-times mr-1.5 text-xs"></i>ยกเลิกแก้ไข
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1.5 text-xs"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>

            {{-- Parts Used --}}
            <div class="card p-0 overflow-hidden animate-fade-in-delay">
                <div class="flex items-center justify-between px-6 pt-6 pb-4">
                    <div class="flex items-center">
                        <div class="section-icon bg-violet-50 text-violet-600"><i class="fas fa-microchip"></i></div>
                        <h3 class="section-title">อะไหล่ที่ใช้</h3>
                    </div>
                    <button type="button"
                        onclick="document.getElementById('addPartModal').classList.remove('hidden')"
                        class="btn btn-primary text-xs !px-3 !py-2">
                        <i class="fas fa-plus mr-1.5"></i>เบิกอะไหล่
                    </button>
                </div>

                @if($repair->parts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full pro-table">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th>อะไหล่</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-right">ราคา/หน่วย</th>
                                <th class="text-right">รวม</th>
                                <th class="text-center">สถานะ</th>
                                <th>ผู้เบิก / ผู้อนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repair->parts as $part)
                            @php
                            $partStatusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'issued' => 'bg-blue-100 text-blue-800',
                                'used' => 'bg-emerald-100 text-emerald-800',
                                'returned' => 'bg-orange-100 text-orange-800',
                            ];
                            $partStatusLabels = [
                                'pending' => 'รออนุมัติ',
                                'approved' => 'อนุมัติแล้ว',
                                'rejected' => 'ปฏิเสธ',
                                'issued' => 'จ่ายแล้ว',
                                'used' => 'ใช้แล้ว',
                                'returned' => 'คืนแล้ว',
                            ];
                            @endphp
                            <tr class="{{ $part->status === 'rejected' ? 'bg-red-50/50' : '' }}">
                                <td class="text-gray-900 font-medium">
                                    {{ $part->part_name }}
                                    @if($part->notes)
                                    <p class="text-xs text-gray-400 mt-0.5 font-normal">{{ $part->notes }}</p>
                                    @endif
                                </td>
                                <td class="text-gray-700 text-center">{{ $part->quantity }}</td>
                                <td class="text-gray-700 text-right font-mono text-xs">฿{{ number_format($part->unit_price ?? 0, 0) }}</td>
                                <td class="text-gray-900 text-right font-bold font-mono text-xs">฿{{ number_format(($part->unit_price ?? 0) * $part->quantity, 0) }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold rounded-full {{ $partStatusColors[$part->status] ?? 'bg-gray-100' }}">
                                        {{ $partStatusLabels[$part->status] ?? $part->status }}
                                    </span>
                                    @if($part->status === 'rejected' && $part->reject_reason)
                                    <p class="text-[11px] text-red-400 mt-1">{{ $part->reject_reason }}</p>
                                    @endif
                                </td>
                                <td class="text-xs text-gray-500">
                                    <div><i class="fas fa-user text-gray-300 mr-1"></i>{{ $part->requestedBy->name ?? '-' }}</div>
                                    @if($part->approvedBy)
                                    <div class="text-emerald-600 mt-0.5"><i class="fas fa-check-circle mr-1"></i>{{ $part->approvedBy->name }}</div>
                                    @endif
                                    @if($part->rejectedBy)
                                    <div class="text-red-500 mt-0.5"><i class="fas fa-times-circle mr-1"></i>{{ $part->rejectedBy->name }}</div>
                                    @endif
                                    @if($part->status === 'pending')
                                    <form action="{{ route('repairs.cancel-part', [$repair, $part]) }}" method="POST" class="mt-1" onsubmit="return confirm('ยืนยันยกเลิกรายการนี้?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs transition-colors">
                                            <i class="fas fa-trash-alt mr-1"></i>ยกเลิก
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50/80 border-t-2 border-gray-200">
                                <td colspan="3" class="text-right font-semibold text-gray-600 text-xs">รวมค่าอะไหล่ (อนุมัติแล้ว)</td>
                                <td class="text-right font-extrabold text-gray-900 font-mono">฿{{ number_format($repair->parts->where('status', 'approved')->sum(fn($p) => ($p->unit_price ?? 0) * $p->quantity), 0) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-12 text-gray-400">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-box-open text-xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium">ยังไม่มีอะไหล่</p>
                    <p class="text-xs text-gray-300 mt-1">กดปุ่ม "เบิกอะไหล่" เพื่อเพิ่มรายการ</p>
                </div>
                @endif
            </div>

            {{-- Activity Log --}}
            <div class="card p-6 animate-fade-in-delay-2">
                <div class="flex items-center mb-6">
                    <div class="section-icon bg-gray-100 text-gray-500"><i class="fas fa-history"></i></div>
                    <h3 class="section-title">ประวัติการดำเนินการ</h3>
                </div>

                <div>
                    @foreach($repair->logs->sortByDesc('created_at') as $log)
                    <div class="timeline-item">
                        <div class="timeline-dot bg-indigo-100 text-indigo-600">
                            {{ mb_substr($log->user->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-gray-800">{{ $log->user->name ?? 'System' }}</span>
                                <span class="text-[11px] text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-0.5 leading-relaxed">{{ $log->description }}</p>
                            @if($log->old_value && $log->new_value)
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-gray-100 text-gray-600">
                                    {{ $statusNames[$log->old_value] ?? $log->old_value }}
                                </span>
                                <i class="fas fa-arrow-right text-[8px] text-gray-300"></i>
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-indigo-100 text-indigo-700">
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
            <div class="card overflow-hidden animate-fade-in">
                <div class="sidebar-header bg-gradient-to-r from-indigo-500 to-indigo-600">
                    <i class="fas fa-exchange-alt mr-2 text-indigo-200"></i>
                    เปลี่ยนสถานะ
                </div>
                <div class="p-5">
                    <form action="{{ route('repairs.status', $repair) }}" method="POST" class="space-y-3">
                        @csrf @method('PATCH')
                        <select name="status" class="form-input">
                            @foreach($statusNames as $key => $name)
                            <option value="{{ $key }}" {{ $repair->status === $key ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <textarea name="notes" rows="2" placeholder="หมายเหตุ (ถ้ามี)" class="form-input"></textarea>
                        <button type="submit" class="btn btn-primary w-full justify-center">
                            <i class="fas fa-check mr-1.5 text-xs"></i>อัปเดตสถานะ
                        </button>
                    </form>
                </div>
            </div>

            {{-- Assign Technician --}}
            <div class="card overflow-hidden animate-fade-in-delay">
                <div class="sidebar-header bg-gradient-to-r from-emerald-500 to-emerald-600">
                    <i class="fas fa-user-cog mr-2 text-emerald-200"></i>
                    ช่างซ่อม
                </div>
                <div class="p-5">
                    @if($repair->technician)
                    <div class="flex items-center gap-3 mb-4 bg-emerald-50/50 rounded-xl p-3 border border-emerald-100">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="font-bold text-emerald-600">{{ mb_substr($repair->technician->name, 0, 1) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $repair->technician->name }}</p>
                            @if($repair->technician->phone)
                            <p class="text-xs text-gray-400">{{ $repair->technician->phone }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    <form action="{{ route('repairs.assign', $repair) }}" method="POST">
                        @csrf
                        <select name="technician_id" class="form-input mb-3">
                            <option value="">-- เลือกช่าง --</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $repair->technician_id === $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-success w-full justify-center">
                            <i class="fas fa-user-check mr-1.5 text-xs"></i>มอบหมายช่าง
                        </button>
                    </form>
                </div>
            </div>

            {{-- Cost Summary --}}
            <div class="card overflow-hidden animate-fade-in-delay">
                <div class="sidebar-header bg-gradient-to-r from-cyan-500 to-teal-500">
                    <i class="fas fa-coins mr-2 text-cyan-200"></i>
                    สรุปค่าใช้จ่าย
                </div>
                <div class="p-5">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-400">ค่าบริการ</dt>
                            <dd class="font-semibold text-gray-700 font-mono">฿{{ number_format($repair->service_cost ?? 0, 0) }}</dd>
                        </div>
                        @if($repair->discount > 0)
                        <div class="flex justify-between items-center">
                            <dt class="text-red-400">ส่วนลด</dt>
                            <dd class="font-semibold text-red-500 font-mono">-฿{{ number_format($repair->discount ?? 0, 0) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-gray-100">
                            <dt class="font-bold text-gray-900">รวมทั้งสิ้น</dt>
                            <dd class="text-xl font-extrabold text-emerald-600 font-mono">฿{{ number_format($repair->total_cost ?? 0, 0) }}</dd>
                        </div>

                        @if($repair->deposit > 0 || $repair->paid_amount > 0)
                        <div class="flex justify-between items-center text-blue-600">
                            <dt>ชำระแล้ว</dt>
                            <dd class="font-semibold font-mono">฿{{ number_format($repair->paid_amount ?? 0, 0) }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-orange-500 font-semibold">คงเหลือ</dt>
                            <dd class="font-extrabold text-orange-600 font-mono text-lg">฿{{ number_format($repair->balance, 0) }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($repair->balance > 0)
                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <button type="button"
                            onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                            class="btn btn-success w-full justify-center">
                            <i class="fas fa-money-bill-wave mr-1.5 text-xs"></i>รับชำระเงิน
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Warranty --}}
            @if($repair->warranty_days > 0)
            <div class="card overflow-hidden animate-fade-in-delay-2">
                <div class="sidebar-header bg-gradient-to-r from-blue-500 to-blue-600">
                    <i class="fas fa-shield-alt mr-2 text-blue-200"></i>
                    การรับประกัน
                </div>
                <div class="p-5">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-400">ระยะประกัน</dt>
                            <dd class="font-semibold text-gray-700">{{ $repair->warranty_days }} วัน</dd>
                        </div>
                        @if($repair->warranty_expires_at)
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-400">หมดประกัน</dt>
                            <dd class="font-semibold {{ $repair->warranty_expires_at->isPast() ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $repair->warranty_expires_at->format('d/m/Y') }}
                                @if($repair->warranty_expires_at->isPast())
                                <span class="text-[10px] text-red-400">(หมดแล้ว)</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($repair->warranty_conditions)
                        <div class="pt-2 border-t border-gray-100">
                            <dt class="form-label mb-1">เงื่อนไข</dt>
                            <dd class="text-sm text-gray-600 leading-relaxed">{{ $repair->warranty_conditions }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif

            {{-- Internal Notes (VIEW only in sidebar) --}}
            @if($repair->internal_notes)
            <div x-show="!editing" class="card overflow-hidden animate-fade-in-delay-2">
                <div class="sidebar-header bg-gradient-to-r from-amber-400 to-yellow-500">
                    <i class="fas fa-sticky-note mr-2 text-yellow-100"></i>
                    หมายเหตุภายใน
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-700 leading-relaxed bg-amber-50/50 rounded-xl p-3 border border-amber-100">{{ $repair->internal_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ===== Add Part Modal ===== --}}
<div id="addPartModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 modal-overlay transition-opacity" onclick="document.getElementById('addPartModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-100">
            <div class="flex items-center mb-5">
                <div class="section-icon bg-violet-50 text-violet-600"><i class="fas fa-plus-circle"></i></div>
                <h3 class="section-title">เพิ่มสินค้า / อะไหล่</h3>
            </div>
            <form action="{{ route('repairs.add-part', $repair) }}" method="POST" class="space-y-4">
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
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addPartModal').classList.add('hidden')" class="btn btn-outline flex-1 justify-center">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="fas fa-plus mr-1.5 text-xs"></i>เพิ่ม</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Payment Modal ===== --}}
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 modal-overlay transition-opacity" onclick="document.getElementById('paymentModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-100">
            <div class="flex items-center mb-5">
                <div class="section-icon bg-emerald-50 text-emerald-600"><i class="fas fa-money-bill-wave"></i></div>
                <h3 class="section-title">รับชำระเงิน</h3>
            </div>
            <form action="{{ route('repairs.payment', $repair) }}" method="POST" class="space-y-4">
                @csrf
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl p-4 border border-orange-100">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">ยอดคงเหลือ</span>
                        <span class="font-extrabold text-2xl text-orange-600 font-mono">฿{{ number_format($repair->balance, 0) }}</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">จำนวนเงิน</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">฿</span>
                        <input type="number" name="amount" value="{{ $repair->balance }}" min="1" max="{{ $repair->balance }}" required class="form-input pl-8">
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
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="btn btn-outline flex-1 justify-center">ยกเลิก</button>
                    <button type="submit" class="btn btn-success flex-1 justify-center"><i class="fas fa-check mr-1.5 text-xs"></i>รับชำระ</button>
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
