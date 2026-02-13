@extends('layouts.app')

@section('title', 'รับงานซ่อมใหม่')
@section('page-title', 'รับงานซ่อมใหม่')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('repairs.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Customer Information -->
        <div class="bg-white rounded-xl shadow-sm p-6" x-data="customerLookup()">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user text-indigo-600 mr-2"></i>
                ข้อมูลลูกค้า
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อลูกค้า <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" x-model="name" @input.debounce.300ms="onNameInput"
                        value="{{ old('customer_name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <template x-if="nameSuggestions.length && !matchedExact">
                        <div class="mt-2 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="px-3 py-2 text-xs text-gray-500">พบลูกค้าที่ใกล้เคียง</div>
                            <ul>
                                <template x-for="c in nameSuggestions" :key="c.id">
                                    <li>
                                        <button type="button" @click="applyCustomer(c)"
                                            class="w-full text-left px-3 py-2 hover:bg-indigo-50">
                                            <span class="font-medium" x-text="c.name"></span>
                                            <span class="text-sm text-gray-500">(<span x-text="c.phone"></span>)</span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์ <span
                            class="text-red-500">*</span></label>
                    <input type="tel" name="customer_phone" x-model="phone" @input.debounce.300ms="onPhoneInput"
                        value="{{ old('customer_phone') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="mt-1 text-xs" :class="matchedExact ? 'text-green-600' : 'text-gray-500'">
                        <template x-if="matchedExact">
                            <span>พบลูกค้าเดิมและเติมข้อมูลให้แล้ว</span>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LINE ID</label>
                    <input type="text" name="customer_line_id" x-model="lineId" value="{{ old('customer_line_id') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                    <input type="email" name="customer_email" x-model="email" value="{{ old('customer_email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่</label>
                    <input type="text" name="customer_address" x-model="address" value="{{ old('customer_address') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>


            </div>
            <template x-if="error">
                <div class="mt-3 text-sm text-red-600" x-text="error"></div>
            </template>
        </div>

        <!-- Device Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-mobile-alt text-indigo-600 mr-2"></i>
                ข้อมูลเครื่อง
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทเครื่อง <span
                            class="text-red-500">*</span></label>
                    <select name="device_type" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="smartphone">สมาร์ทโฟน</option>
                        <option value="tablet">แท็บเล็ต</option>
                        <option value="smartwatch">Smart Watch</option>
                        <option value="laptop">โน้ตบุ๊ก</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ยี่ห้อ <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="device_brand" value="{{ old('device_brand') }}" required list="brand-list"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <datalist id="brand-list">
                        <option value="Apple">
                        <option value="Samsung">
                        <option value="OPPO">
                        <option value="Vivo">
                        <option value="Xiaomi">
                        <option value="Huawei">
                        <option value="Realme">
                        <option value="OnePlus">
                        <option value="Google">
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รุ่น <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="device_model" value="{{ old('device_model') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สี</label>
                    <input type="text" name="device_color" value="{{ old('device_color') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                    <input type="text" name="device_serial" value="{{ old('device_serial') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IMEI</label>
                    <input type="text" name="device_imei" value="{{ old('device_imei') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านเครื่อง</label>
                    <input type="text" name="device_password" value="{{ old('device_password') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="สำหรับทดสอบเครื่องหลังซ่อม">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สภาพเครื่อง</label>
                    <input type="text" name="device_condition" value="{{ old('device_condition') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="รอยขีดข่วน, บุบ, แตก">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">อุปกรณ์ที่รับมาพร้อมเครื่อง</label>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="device_accessories[]" value="charger"
                            class="rounded text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">ที่ชาร์จ</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="device_accessories[]" value="case" class="rounded text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">เคส</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="device_accessories[]" value="sim_card"
                            class="rounded text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">ซิมการ์ด</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="device_accessories[]" value="memory_card"
                            class="rounded text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">เมมโมรี่การ์ด</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Problem Description -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                อาการเสีย / ปัญหา
            </h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดอาการเสีย <span
                        class="text-red-500">*</span></label>
                <textarea name="problem_description" rows="4" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="อธิบายอาการเสียหรือปัญหาที่ลูกค้าแจ้ง">{{ old('problem_description') }}</textarea>
            </div>
        </div>

        <!-- Service Details -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-coins text-green-600 mr-2"></i>
                รายละเอียดการซ่อม
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ค่าซ่อมประมาณ</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="estimated_cost" value="{{ old('estimated_cost') }}" min="0" step="1"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">มัดจำ</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">฿</span>
                        <input type="number" name="deposit" value="{{ old('deposit') }}" min="0" step="1"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">กำหนดส่ง</label>
                    <input type="datetime-local" name="estimated_completion" value="{{ old('estimated_completion') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ความเร่งด่วน</label>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="priority" value="normal" checked class="text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">ปกติ</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="priority" value="urgent" class="text-red-600">
                        <span class="ml-2 text-sm text-red-700">ด่วน</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="priority" value="vip" class="text-yellow-600">
                        <span class="ml-2 text-sm text-yellow-700">VIP</span>
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุภายใน (ไม่แสดงให้ลูกค้า)</label>
                <textarea name="internal_notes" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="บันทึกสำหรับพนักงาน">{{ old('internal_notes') }}</textarea>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('repairs.index') }}"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                ยกเลิก
            </a>
            <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-save mr-2"></i>
                บันทึกงานซ่อม
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function customerLookup() {
    return {
        name: "{{ old('customer_name') ?? '' }}",
        phone: "{{ old('customer_phone') ?? '' }}",
        lineId: "{{ old('customer_line_id') ?? '' }}",
        email: "{{ old('customer_email') ?? '' }}",
        address: "{{ old('customer_address') ?? '' }}",
        matchedExact: false,
        nameSuggestions: [],
        error: '',
        normalizePhone(p) {
            return (p || '').replace(/[^0-9]/g, '');
        },
        isPartialPhoneMatch(input, candidate) {
            const a = this.normalizePhone(input);
            const b = this.normalizePhone(candidate);
            if (!a || !b) return false;
            // Require at least 6 digits typed to consider partial match
            if (a.length < 6) return false;
            // Match suffix or substring
            return b.endsWith(a) || b.includes(a);
        },
        async search(q) {
            if (!q || q.length < 2) return [];
            try {
                const res = await fetch(`{{ \Illuminate\Support\Facades\Route::has('api.customer-search') 
                    ? route('api.customer-search') 
                    : (\Illuminate\Support\Facades\Route::has('api.customers.search') 
                        ? route('api.customers.search') 
                        : url('/api/customer-search')) }}?q=${encodeURIComponent(q)}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('ค้นหาลูกค้าไม่สำเร็จ');
                const data = await res.json();
                return Array.isArray(data) ? data : [];
            } catch (e) {
                this.error = e.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                return [];
            }
        },
        async onPhoneInput() {
            this.error = '';
            this.matchedExact = false;
            const results = await this.search(this.phone);
            // Exact phone match -> auto-apply
            const exact = results.find(c => this.normalizePhone(c.phone) === this.normalizePhone(this.phone));
            if (exact) {
                this.applyCustomer(exact);
                this.matchedExact = true;
                this.nameSuggestions = [];
            } else {
                // If only one candidate and partial match on phone (last 6+ digits), auto-apply
                if (results.length === 1 && this.isPartialPhoneMatch(this.phone, results[0].phone)) {
                    this.applyCustomer(results[0]);
                    this.matchedExact = true;
                    this.nameSuggestions = [];
                } else {
                    this.matchedExact = false;
                }
            }
        },
        async onNameInput() {
            this.error = '';
            this.matchedExact = false;
            const results = await this.search(this.name);
            // If only one result, apply; otherwise show suggestions
            if (results.length === 1) {
                this.applyCustomer(results[0]);
                this.matchedExact = true;
                this.nameSuggestions = [];
            } else {
                this.nameSuggestions = results.slice(0, 5);
            }
        },
        applyCustomer(c) {
            this.name = c.name || this.name;
            this.phone = c.phone || this.phone;
            this.lineId = c.line_id || this.lineId;
            this.email = c.email || this.email;
            this.address = c.address || this.address;
            // Set the customer select value
            if (this.$refs.customerSelect) {
                this.$refs.customerSelect.value = c.id;
            }
        },
        clearCustomer() {
            this.matchedExact = false;
            this.nameSuggestions = [];
            this.error = '';
            this.name = '';
            this.phone = '';
            this.lineId = '';
            this.email = '';
            this.address = '';
            if (this.$refs.customerSelect) {
                this.$refs.customerSelect.value = '';
            }
        }
    }
}
</script>
@endpush