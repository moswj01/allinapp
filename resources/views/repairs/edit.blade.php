@extends('layouts.app')

@section('title', 'แก้ไขงานซ่อม - ' . $repair->repair_number)
@section('page-title', 'แก้ไขงานซ่อม')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('repairs.update', $repair) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">แก้ไขงานซ่อม {{ $repair->repair_number }}</h2>
                <p class="text-gray-500">แก้ไขข้อมูลงานซ่อม</p>
            </div>
            <a href="{{ route('repairs.show', $repair) }}" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
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

        <!-- Customer Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
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
                    <input type="text" name="customer_name" value="{{ old('customer_name', $repair->customer_name) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ชื่อ-นามสกุล">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร <span class="text-red-500">*</span></label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone', $repair->customer_phone) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="08X-XXX-XXXX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LINE ID</label>
                    <input type="text" name="customer_line_id" value="{{ old('customer_line_id', $repair->customer_line_id) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="@lineid">
                </div>
            </div>
        </div>

        <!-- Device Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-mobile-alt text-indigo-600 mr-2"></i>
                ข้อมูลเครื่อง
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทเครื่อง <span class="text-red-500">*</span></label>
                    <select name="device_type" required
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
                    <input type="text" name="device_brand" value="{{ old('device_brand', $repair->device_brand) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        list="brandList" placeholder="Apple, Samsung, OPPO...">
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
                    <input type="text" name="device_model" value="{{ old('device_model', $repair->device_model) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="iPhone 15 Pro Max">
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

        <!-- Problem & Service -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-tools text-yellow-500 mr-2"></i>
                อาการ / การซ่อม
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">อาการเสีย / ปัญหา <span class="text-red-500">*</span></label>
                    <textarea name="problem_description" rows="3" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="อธิบายอาการเสียหรือปัญหาที่ลูกค้าแจ้ง">{{ old('problem_description', $repair->problem_description) }}</textarea>
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
                    <textarea name="accessories" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="รอยขีดข่วน, ฝาหลังแตก, อุปกรณ์ที่รับมาพร้อม">{{ old('accessories', $repair->accessories) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Warranty -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calculator text-green-600 mr-2"></i>
                ค่าใช้จ่าย / การรับประกัน
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ค่าบริการประเมิน</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $repair->estimated_cost) }}" min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ค่าบริการจริง</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="service_cost" value="{{ old('service_cost', $repair->service_cost) }}" min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ค่าอะไหล่</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="parts_cost" value="{{ old('parts_cost', $repair->parts_cost) }}" min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-gray-50"
                            readonly>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">* คำนวณจากอะไหล่อัตโนมัติ</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ส่วนลด</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="discount" value="{{ old('discount', $repair->discount) }}" min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">มัดจำ</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="deposit" value="{{ old('deposit', $repair->deposit) }}" min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ระยะประกัน (วัน)</label>
                    <input type="number" name="warranty_days" value="{{ old('warranty_days', $repair->warranty_days ?? 30) }}" min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="30">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">เงื่อนไขการรับประกัน</label>
                    <textarea name="warranty_conditions" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="ไม่รับประกันหากเครื่องตกน้ำ, แกะเครื่องเอง ฯลฯ">{{ old('warranty_conditions', $repair->warranty_conditions) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Assignment -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-cog text-purple-600 mr-2"></i>
                การมอบหมายงาน
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ช่างซ่อม</label>
                    <select name="technician_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกช่าง --</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ old('technician_id', $repair->technician_id) == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                    <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach(\App\Models\Repair::getStatuses() as $key => $name)
                        <option value="{{ $key }}" {{ old('status', $repair->status) === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">กำหนดนัดรับ</label>
                    <input type="datetime-local" name="due_date"
                        value="{{ old('due_date', $repair->due_date ? $repair->due_date->format('Y-m-d\TH:i') : '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Internal Notes -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                หมายเหตุภายใน
            </h3>

            <textarea name="internal_notes" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                placeholder="หมายเหตุสำหรับพนักงาน (ไม่แสดงให้ลูกค้า)">{{ old('internal_notes', $repair->internal_notes) }}</textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('repairs.show', $repair) }}"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                ยกเลิก
            </a>
            <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                บันทึกการเปลี่ยนแปลง
            </button>
        </div>
    </form>
</div>
@endsection