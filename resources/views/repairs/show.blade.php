@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม - ' . $repair->repair_number)
@section('page-title', 'รายละเอียดงานซ่อม')

@section('content')
@php
$statusColors = [
'pending' => 'bg-gray-100 text-gray-800 border-gray-300',
'waiting_parts' => 'bg-orange-100 text-orange-800 border-orange-300',
'quoted' => 'bg-purple-100 text-purple-800 border-purple-300',
'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
'in_progress' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
'qc' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
'completed' => 'bg-green-100 text-green-800 border-green-300',
'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
];
$statusNames = \App\Models\Repair::getStatuses();
@endphp

<div class="space-y-6" x-data="{ editing: {{ $errors->any() ? 'true' : 'false' }} }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $repair->repair_number }}</h2>
            <p class="text-gray-500">
                สร้างเมื่อ {{ $repair->created_at->format('d/m/Y H:i') }}
                โดย {{ $repair->receivedBy->name ?? 'N/A' }}
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-4 py-2 text-sm font-semibold rounded-full border-2 {{ $statusColors[$repair->status] ?? 'bg-gray-100' }}">
                {{ $statusNames[$repair->status] ?? $repair->status }}
            </span>
            <a href="{{ route('repairs.receipt', $repair) }}" target="_blank" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-print mr-2"></i>ใบรับเครื่อง
            </a>
            @if(in_array($repair->status, ['completed', 'delivered']))
            <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'receipt']) }}" target="_blank" class="px-4 py-2 border border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors">
                <i class="fas fa-receipt mr-2"></i>ใบเสร็จ
            </a>
            <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'tax_invoice']) }}" target="_blank" class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors">
                <i class="fas fa-file-invoice mr-2"></i>ใบกำกับภาษี
            </a>
            @endif
            <!-- Toggle edit mode -->
            <button x-show="!editing" x-on:click="editing = true" type="button"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>แก้ไข
            </button>
            <button x-show="editing" x-on:click="editing = false" type="button"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-times mr-2"></i>ยกเลิกแก้ไข
            </button>
            <form action="{{ route('repairs.destroy', $repair) }}" method="POST" onsubmit="return confirm('ยืนยันลบงานซ่อม {{ $repair->repair_number }}? ข้อมูลทั้งหมดจะถูกลบถาวร')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i>ลบ
                </button>
            </form>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <ul class="list-disc list-inside text-red-600 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('repairs.update', $repair) }}" method="POST" x-ref="editForm">
                @csrf
                @method('PUT')

                <!-- Customer & Device -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Customer Info - VIEW -->
                        <div x-show="!editing">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user text-indigo-600 mr-2"></i>
                                ข้อมูลลูกค้า
                            </h3>
                            <dl class="space-y-2">
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">ชื่อ:</dt>
                                    <dd class="font-medium text-gray-900">{{ $repair->customer_name }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">โทร:</dt>
                                    <dd class="font-medium text-gray-900">
                                        <a href="tel:{{ $repair->customer_phone }}" class="text-indigo-600 hover:underline">
                                            {{ $repair->customer_phone }}
                                        </a>
                                    </dd>
                                </div>
                                @if($repair->customer_line_id)
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">LINE:</dt>
                                    <dd class="font-medium text-green-600">{{ $repair->customer_line_id }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Customer Info - EDIT -->
                        <div x-show="editing" class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user text-indigo-600 mr-2"></i>
                                ข้อมูลลูกค้า
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ลูกค้าเดิม</label>
                                    <select name="customer_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- เลือกลูกค้าเดิม --</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $repair->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->phone }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500 text-center my-2">- หรือกรอกข้อมูลลูกค้าใหม่ -</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อลูกค้า <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name', $repair->customer_name) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="ชื่อ-นามสกุล" x-bind:required="editing">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร <span class="text-red-500">*</span></label>
                                    <input type="tel" name="customer_phone" value="{{ old('customer_phone', $repair->customer_phone) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="08X-XXX-XXXX" x-bind:required="editing">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">LINE ID</label>
                                    <input type="text" name="customer_line_id" value="{{ old('customer_line_id', $repair->customer_line_id) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="@lineid">
                                </div>
                            </div>
                        </div>

                        <!-- Device Info - VIEW -->
                        <div x-show="!editing">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-mobile-alt text-indigo-600 mr-2"></i>
                                ข้อมูลเครื่อง
                            </h3>
                            <dl class="space-y-2">
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">ประเภท:</dt>
                                    <dd class="font-medium text-gray-900">{{ $repair->device_type }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">ยี่ห้อ/รุ่น:</dt>
                                    <dd class="font-medium text-gray-900">{{ $repair->device_brand }} {{ $repair->device_model }}</dd>
                                </div>
                                @if($repair->device_color)
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">สี:</dt>
                                    <dd class="font-medium text-gray-900">{{ $repair->device_color }}</dd>
                                </div>
                                @endif
                                @if($repair->device_imei)
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">IMEI:</dt>
                                    <dd class="font-medium text-gray-900 font-mono text-sm">{{ $repair->device_imei }}</dd>
                                </div>
                                @endif
                                @if($repair->device_password)
                                <div class="flex">
                                    <dt class="w-24 text-gray-500 text-sm">รหัส:</dt>
                                    <dd class="font-medium text-gray-900">{{ $repair->device_password }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Device Info - EDIT -->
                    <div x-show="editing" class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-mobile-alt text-indigo-600 mr-2"></i>
                            ข้อมูลเครื่อง
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทเครื่อง <span class="text-red-500">*</span></label>
                                <select name="device_type" x-bind:required="editing"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="มือถือ" {{ old('device_type', $repair->device_type) == 'มือถือ' ? 'selected' : '' }}>มือถือ</option>
                                    <option value="แท็บเล็ต" {{ old('device_type', $repair->device_type) == 'แท็บเล็ต' ? 'selected' : '' }}>แท็บเล็ต</option>
                                    <option value="โน๊ตบุ๊ค" {{ old('device_type', $repair->device_type) == 'โน๊ตบุ๊ค' ? 'selected' : '' }}>โน๊ตบุ๊ค</option>
                                    <option value="คอมพิวเตอร์" {{ old('device_type', $repair->device_type) == 'คอมพิวเตอร์' ? 'selected' : '' }}>คอมพิวเตอร์</option>
                                    <option value="นาฬิกา" {{ old('device_type', $repair->device_type) == 'นาฬิกา' ? 'selected' : '' }}>นาฬิกา</option>
                                    <option value="อื่นๆ" {{ old('device_type', $repair->device_type) == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ยี่ห้อ <span class="text-red-500">*</span></label>
                                <input type="text" name="device_brand" value="{{ old('device_brand', $repair->device_brand) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    list="brandList" placeholder="Apple, Samsung, OPPO..." x-bind:required="editing">
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">รุ่น <span class="text-red-500">*</span></label>
                                <input type="text" name="device_model" value="{{ old('device_model', $repair->device_model) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="iPhone 15 Pro Max" x-bind:required="editing">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">สี</label>
                                <input type="text" name="device_color" value="{{ old('device_color', $repair->device_color) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Black, White, Gold...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">IMEI / Serial Number</label>
                                <input type="text" name="device_imei" value="{{ old('device_imei', $repair->device_imei) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="IMEI หรือ S/N">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านเครื่อง</label>
                                <input type="text" name="device_password" value="{{ old('device_password', $repair->device_password) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="รหัส PIN / Pattern (ถ้ามี)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Problem & Solution -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        อาการเสีย / การซ่อม
                    </h3>

                    <!-- VIEW mode -->
                    <div x-show="!editing" class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">อาการเสีย:</h4>
                            <p class="text-gray-900 bg-gray-50 rounded-lg p-3">{{ $repair->problem_description }}</p>
                        </div>

                        @if($repair->diagnosis)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">การวินิจฉัย:</h4>
                            <p class="text-gray-900 bg-blue-50 rounded-lg p-3">{{ $repair->diagnosis }}</p>
                        </div>
                        @endif

                        @if($repair->solution)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">วิธีแก้ไข:</h4>
                            <p class="text-gray-900 bg-green-50 rounded-lg p-3">{{ $repair->solution }}</p>
                        </div>
                        @endif

                        @if($repair->device_condition)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">สภาพเครื่อง:</h4>
                            <p class="text-gray-900 bg-orange-50 rounded-lg p-3">{{ $repair->device_condition }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- EDIT mode -->
                    <div x-show="editing" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">อาการเสีย / ปัญหา <span class="text-red-500">*</span></label>
                            <textarea name="problem_description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="อธิบายอาการเสียหรือปัญหาที่ลูกค้าแจ้ง" x-bind:required="editing">{{ old('problem_description', $repair->problem_description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">การวินิจฉัย</label>
                            <textarea name="diagnosis" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="การวินิจฉัยจากช่าง">{{ old('diagnosis', $repair->diagnosis) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">วิธีแก้ไข / ผลการซ่อม</label>
                            <textarea name="solution" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="รายละเอียดการแก้ไข">{{ old('solution', $repair->solution) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">สภาพเครื่องก่อนซ่อม</label>
                            <textarea name="device_condition" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="รอยขีดข่วน, ฝาหลังแตก, อุปกรณ์ที่รับมาพร้อม">{{ old('device_condition', $repair->device_condition) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Warranty - EDIT ONLY -->
                <div x-show="editing" class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calculator text-green-600 mr-2"></i>
                        ค่าใช้จ่าย / การรับประกัน
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ค่าบริการประเมิน</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">฿</span>
                                <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $repair->estimated_cost ?? 0) }}" min="0"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ค่าบริการ</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">฿</span>
                                <input type="number" name="service_cost" value="{{ old('service_cost', $repair->service_cost ?? 0) }}" min="0"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ส่วนลด</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">฿</span>
                                <input type="number" name="discount" value="{{ old('discount', $repair->discount ?? 0) }}" min="0"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">มัดจำ</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">฿</span>
                                <input type="number" name="deposit" value="{{ old('deposit', $repair->deposit ?? 0) }}" min="0"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ระยะประกัน (วัน)</label>
                            <input type="number" name="warranty_days" value="{{ old('warranty_days', $repair->warranty_days ?? 30) }}" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">เงื่อนไขการรับประกัน</label>
                            <textarea name="warranty_conditions" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="ไม่รับประกันหากเครื่องตกน้ำ, แกะเครื่องเอง ฯลฯ">{{ old('warranty_conditions', $repair->warranty_conditions) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ระดับความเร่งด่วน</label>
                            <select name="priority"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="low" {{ old('priority', $repair->priority) === 'low' ? 'selected' : '' }}>ปกติ</option>
                                <option value="medium" {{ old('priority', $repair->priority) === 'medium' ? 'selected' : '' }}>เร่งด่วน</option>
                                <option value="high" {{ old('priority', $repair->priority) === 'high' ? 'selected' : '' }}>ด่วนมาก</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">กำหนดนัดรับ</label>
                            <input type="datetime-local" name="estimated_completion"
                                value="{{ old('estimated_completion', $repair->estimated_completion ? $repair->estimated_completion->format('Y-m-d\TH:i') : '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Internal Notes - EDIT ONLY -->
                <div x-show="editing" class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                        หมายเหตุภายใน
                    </h3>

                    <textarea name="internal_notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="หมายเหตุสำหรับพนักงาน (ไม่แสดงให้ลูกค้า)">{{ old('internal_notes', $repair->internal_notes) }}</textarea>
                </div>

                <!-- Save Button - EDIT ONLY -->
                <div x-show="editing" class="flex items-center justify-end space-x-3">
                    <button type="button" x-on:click="editing = false"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        ยกเลิก
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>

            <!-- Parts Used (always visible) -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-microchip text-purple-600 mr-2"></i>
                        อะไหล่ที่ใช้
                    </h3>
                    <button type="button"
                        onclick="document.getElementById('addPartModal').classList.remove('hidden')"
                        class="text-sm text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-plus mr-1"></i>เบิกอะไหล่
                    </button>
                </div>

                @if($repair->parts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">อะไหล่</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">จำนวน</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">ราคา/หน่วย</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">รวม</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ผู้เบิก / ผู้อนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($repair->parts as $part)
                            <tr class="{{ $part->status === 'rejected' ? 'bg-red-50' : '' }}">
                                <td class="px-3 py-3 text-sm text-gray-900">
                                    {{ $part->part_name }}
                                    @if($part->notes)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $part->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 text-center">{{ $part->quantity }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 text-right">฿{{ number_format($part->unit_price ?? 0, 0) }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 text-right font-medium">฿{{ number_format(($part->unit_price ?? 0) * $part->quantity, 0) }}</td>
                                <td class="px-3 py-3 text-center">
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
                                    <span class="px-2 py-1 text-xs rounded-full {{ $partStatusColors[$part->status] ?? 'bg-gray-100' }}">
                                        {{ $partStatusLabels[$part->status] ?? $part->status }}
                                    </span>
                                    @if($part->status === 'rejected' && $part->reject_reason)
                                    <p class="text-xs text-red-500 mt-1">{{ $part->reject_reason }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-600">
                                    <div><i class="fas fa-user text-gray-400 mr-1"></i>{{ $part->requestedBy->name ?? '-' }}</div>
                                    @if($part->approvedBy)
                                    <div class="text-green-600 mt-0.5"><i class="fas fa-check-circle mr-1"></i>{{ $part->approvedBy->name }}</div>
                                    @endif
                                    @if($part->rejectedBy)
                                    <div class="text-red-600 mt-0.5"><i class="fas fa-times-circle mr-1"></i>{{ $part->rejectedBy->name }}</div>
                                    @endif
                                    @if($part->status === 'pending')
                                    <form action="{{ route('repairs.cancel-part', [$repair, $part]) }}" method="POST" class="mt-1" onsubmit="return confirm('ยืนยันยกเลิกรายการนี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                            <i class="fas fa-trash-alt mr-1"></i>ยกเลิก
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-right font-medium text-gray-700">รวมค่าอะไหล่ (อนุมัติแล้ว):</td>
                                <td class="px-3 py-2 text-right font-bold text-gray-900">฿{{ number_format($repair->parts->where('status', 'approved')->sum(fn($p) => ($p->unit_price ?? 0) * $p->quantity), 0) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-box-open text-3xl mb-2"></i>
                    <p>ยังไม่มีอะไหล่</p>
                </div>
                @endif
            </div>

            <!-- Activity Log (always visible) -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-history text-gray-600 mr-2"></i>
                    ประวัติการดำเนินการ
                </h3>

                <div class="space-y-4">
                    @foreach($repair->logs->sortByDesc('created_at') as $log)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-indigo-600">{{ substr($log->user->name ?? '?', 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                                <span class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $log->description }}</p>
                            @if($log->old_value && $log->new_value)
                            <p class="text-xs text-gray-500 mt-1">
                                สถานะ: {{ $statusNames[$log->old_value] ?? $log->old_value }} → {{ $statusNames[$log->new_value] ?? $log->new_value }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar (always visible) -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">เปลี่ยนสถานะ</h3>

                <form action="{{ route('repairs.status', $repair) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach($statusNames as $key => $name)
                        <option value="{{ $key }}" {{ $repair->status === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>

                    <textarea name="notes" rows="2" placeholder="หมายเหตุ (ถ้ามี)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>

                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>อัปเดตสถานะ
                    </button>
                </form>
            </div>

            <!-- Assign Technician -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ช่างซ่อม</h3>

                @if($repair->technician)
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="font-medium text-indigo-600">{{ substr($repair->technician->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $repair->technician->name }}</p>
                        <p class="text-sm text-gray-500">{{ $repair->technician->phone ?? '' }}</p>
                    </div>
                </div>
                @endif

                <form action="{{ route('repairs.assign', $repair) }}" method="POST">
                    @csrf
                    <select name="technician_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 mb-3">
                        <option value="">-- เลือกช่าง --</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ $repair->technician_id === $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-user-check mr-2"></i>มอบหมายช่าง
                    </button>
                </form>
            </div>

            <!-- Cost Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">สรุปค่าใช้จ่าย</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ค่าบริการ:</dt>
                        <dd class="font-medium">฿{{ number_format($repair->service_cost ?? 0, 0) }}</dd>
                    </div>
                    @if($repair->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <dt>ส่วนลด:</dt>
                        <dd class="font-medium">-฿{{ number_format($repair->discount ?? 0, 0) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between pt-3 border-t border-gray-200">
                        <dt class="text-lg font-semibold text-gray-900">รวมทั้งสิ้น:</dt>
                        <dd class="text-lg font-bold text-green-600">฿{{ number_format($repair->total_cost ?? 0, 0) }}</dd>
                    </div>

                    @if($repair->deposit > 0 || $repair->paid_amount > 0)
                    <div class="flex justify-between text-blue-600">
                        <dt>ชำระแล้ว:</dt>
                        <dd class="font-medium">฿{{ number_format($repair->paid_amount ?? 0, 0) }}</dd>
                    </div>
                    <div class="flex justify-between text-orange-600">
                        <dt>คงเหลือ:</dt>
                        <dd class="font-bold">฿{{ number_format($repair->balance, 0) }}</dd>
                    </div>
                    @endif
                </dl>

                @if($repair->balance > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <button type="button"
                        onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i>รับชำระเงิน
                    </button>
                </div>
                @endif
            </div>

            <!-- Warranty -->
            @if($repair->warranty_days > 0)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                    การรับประกัน
                </h3>

                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ระยะประกัน:</dt>
                        <dd class="font-medium">{{ $repair->warranty_days }} วัน</dd>
                    </div>
                    @if($repair->warranty_expires_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">หมดประกัน:</dt>
                        <dd class="font-medium {{ $repair->warranty_expires_at->isPast() ? 'text-red-600' : 'text-green-600' }}">
                            {{ $repair->warranty_expires_at->format('d/m/Y') }}
                        </dd>
                    </div>
                    @endif
                    @if($repair->warranty_conditions)
                    <div class="pt-2">
                        <dt class="text-gray-500 text-sm">เงื่อนไข:</dt>
                        <dd class="text-sm text-gray-700 mt-1">{{ $repair->warranty_conditions }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Internal Notes (VIEW only in sidebar) -->
            @if($repair->internal_notes)
            <div x-show="!editing" class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                    หมายเหตุภายใน
                </h3>
                <p class="text-sm text-gray-700 bg-yellow-50 rounded-lg p-3">{{ $repair->internal_notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Part Modal -->
<div id="addPartModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('addPartModal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">เพิ่มสินค้า/อะไหล่</h3>

            <form action="{{ route('repairs.add-part', $repair) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลือกสินค้า</label>
                    <select name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกสินค้า --</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->name }} - ฿{{ number_format($part->retail_price ?? 0, 0) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวน</label>
                    <input type="number" name="quantity" value="1" min="1" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                        onclick="document.getElementById('addPartModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>เพิ่ม
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('paymentModal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">รับชำระเงิน</h3>

            <form action="{{ route('repairs.payment', $repair) }}" method="POST" class="space-y-4">
                @csrf

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">ยอดคงเหลือ:</span>
                        <span class="font-bold text-xl text-orange-600">฿{{ number_format($repair->balance, 0) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนเงิน</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="amount" value="{{ $repair->balance }}" min="1" max="{{ $repair->balance }}" required
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">วิธีชำระ</label>
                    <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="cash">เงินสด</option>
                        <option value="transfer">โอนเงิน</option>
                        <option value="qr">QR Payment</option>
                        <option value="card">บัตรเครดิต</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลขอ้างอิง (ถ้ามี)</label>
                    <input type="text" name="payment_ref"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                        onclick="document.getElementById('paymentModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>รับชำระ
                    </button>
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