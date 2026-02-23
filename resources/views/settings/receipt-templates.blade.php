@extends('layouts.app')

@section('title', 'ออกแบบใบเสร็จ')
@section('page-title', 'ออกแบบใบเสร็จ / ใบรับซ่อม')

@section('content')
<div x-data="receiptDesigner()" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ออกแบบเทมเพลตเอกสาร</h2>
            <p class="text-gray-500">ปรับแต่ง Layout และข้อความสำหรับใบรับซ่อม / ใบเสร็จ</p>
        </div>
        <a href="{{ route('settings.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>กลับไปตั้งค่า
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- Tab Switcher --}}
    <div class="bg-white rounded-xl shadow-sm p-1 inline-flex gap-1">
        <button @click="activeTab = 'repair'"
            :class="activeTab === 'repair' ? 'bg-indigo-600 text-white shadow' : 'text-gray-600 hover:bg-gray-100'"
            class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
            <i class="fas fa-wrench mr-2"></i>ใบรับซ่อม
        </button>
        <button @click="activeTab = 'sales'"
            :class="activeTab === 'sales' ? 'bg-indigo-600 text-white shadow' : 'text-gray-600 hover:bg-gray-100'"
            class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
            <i class="fas fa-receipt mr-2"></i>ใบเสร็จ
        </button>
    </div>

    {{-- ===== REPAIR RECEIPT TAB ===== --}}
    <div x-show="activeTab === 'repair'" x-cloak>
        <form action="{{ route('receipt-templates.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="repair">

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                {{-- Left: Settings --}}
                <div class="space-y-6">

                    {{-- ข้อมูลร้าน --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-store text-indigo-600 mr-2"></i>ข้อมูลร้าน
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อร้าน/บริษัท <span class="text-gray-400 text-xs">(ว่างไว้ = ใช้ชื่อสาขา)</span></label>
                                <input type="text" name="repair_receipt_shop_name" x-model="repair.shop_name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="All in Service Mac">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ข้อมูลร้าน <span class="text-gray-400 text-xs">(ที่อยู่, เบอร์โทร)</span></label>
                                <textarea name="repair_receipt_shop_info" x-model="repair.shop_info" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="123 ถ.สุขุมวิท กรุงเทพฯ&#10;โทร: 02-123-4567"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL <span class="text-gray-400 text-xs">(ไม่บังคับ)</span></label>
                                <input type="url" name="repair_receipt_logo_url" x-model="repair.logo_url"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="https://example.com/logo.png">
                            </div>
                        </div>
                    </div>

                    {{-- หัวเอกสาร --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-heading text-blue-600 mr-2"></i>หัวเอกสาร & ข้อความ
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเอกสาร</label>
                                <input type="text" name="repair_receipt_title" x-model="repair.title"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ลายเซ็นซ้าย</label>
                                    <input type="text" name="repair_receipt_sign_left" x-model="repair.sign_left"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ลายเซ็นขวา</label>
                                    <input type="text" name="repair_receipt_sign_right" x-model="repair.sign_right"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">หัวข้อเงื่อนไข</label>
                                <input type="text" name="repair_receipt_terms_title" x-model="repair.terms_title"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">เงื่อนไข/ข้อตกลง <span class="text-gray-400 text-xs">(แต่ละข้อขึ้นบรรทัดใหม่)</span></label>
                                <textarea name="repair_receipt_terms" x-model="repair.terms" rows="5"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- สี & เลย์เอาท์ --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-palette text-pink-600 mr-2"></i>สี & เลย์เอาท์
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">สีหลัก</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="repair_receipt_brand_color" x-model="repair.brand_color"
                                            class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                        <span class="text-xs text-gray-500" x-text="repair.brand_color"></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">สีเน้น</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="repair_receipt_accent_color" x-model="repair.accent_color"
                                            class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                        <span class="text-xs text-gray-500" x-text="repair.accent_color"></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">สีพื้นหลัง</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="repair_receipt_bg_color" x-model="repair.bg_color"
                                            class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                                        <span class="text-xs text-gray-500" x-text="repair.bg_color"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ขนาดกระดาษ</label>
                                    <select name="repair_receipt_paper_size" x-model="repair.paper_size"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        <option value="A5">A5 แนวนอน (210×148mm)</option>
                                        <option value="A4">A4 (210×297mm)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนสำเนา</label>
                                    <select name="repair_receipt_copies" x-model="repair.copies"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        <option value="1">1 ฉบับ (ต้นฉบับ)</option>
                                        <option value="2">2 ฉบับ (ต้นฉบับ + สำเนา)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- แสดง/ซ่อน ส่วนต่าง ๆ --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-eye text-green-600 mr-2"></i>แสดง / ซ่อน ส่วนต่างๆ
                        </h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-image mr-2 text-gray-400"></i>โลโก้ร้าน</span>
                                <input type="hidden" name="repair_receipt_show_logo" value="0">
                                <input type="checkbox" name="repair_receipt_show_logo" value="1" x-model="repair.show_logo"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-qrcode mr-2 text-gray-400"></i>QR Code ติดตามสถานะ</span>
                                <input type="hidden" name="repair_receipt_show_qr" value="0">
                                <input type="checkbox" name="repair_receipt_show_qr" value="1" x-model="repair.show_qr"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-barcode mr-2 text-gray-400"></i>บาร์โค้ดรหัสอ้างอิง</span>
                                <input type="hidden" name="repair_receipt_show_barcode" value="0">
                                <input type="checkbox" name="repair_receipt_show_barcode" value="1" x-model="repair.show_barcode"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-box mr-2 text-gray-400"></i>อุปกรณ์ที่ติดมาด้วย</span>
                                <input type="hidden" name="repair_receipt_show_accessories" value="0">
                                <input type="checkbox" name="repair_receipt_show_accessories" value="1" x-model="repair.show_accessories"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-lock mr-2 text-gray-400"></i>รหัสปลดล็อค</span>
                                <input type="hidden" name="repair_receipt_show_password" value="0">
                                <input type="checkbox" name="repair_receipt_show_password" value="1" x-model="repair.show_password"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="flex items-center justify-end gap-3">
                        <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            บันทึกเทมเพลตใบรับซ่อม
                        </button>
                    </div>
                </div>

                {{-- Right: Live Preview --}}
                <div class="space-y-4">
                    <div class="sticky top-4">
                        <div class="bg-white rounded-xl shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-600">
                                    <i class="fas fa-eye mr-1"></i> ตัวอย่าง (Preview)
                                </h3>
                                <span class="text-xs text-gray-400">ปรับแต่งด้านซ้ายเพื่อดูผลทันที</span>
                            </div>

                            {{-- Scaled Preview --}}
                            <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-100 p-3">
                                <div class="transform origin-top-left" style="transform: scale(0.55); width: 182%; height: 0; padding-bottom: 65%;">
                                    <div class="bg-white rounded-lg shadow p-3 border" style="width: 210mm; font-family: ui-sans-serif, system-ui, sans-serif;"
                                        :style="{ color: repair.brand_color }">

                                        {{-- Header --}}
                                        <div class="flex justify-between items-start gap-2">
                                            <div>
                                                <div class="inline-block text-white rounded-lg px-3 py-1 text-sm font-extrabold"
                                                    :style="{ background: repair.brand_color }">
                                                    <span x-text="repair.title"></span> • RPR-2025-0001
                                                </div>
                                                <div class="mt-1 text-xs font-semibold" style="color: #374151;">
                                                    <template x-if="repair.show_logo && repair.logo_url">
                                                        <img :src="repair.logo_url" class="h-8 mb-1" alt="logo">
                                                    </template>
                                                    <span x-text="repair.shop_name || 'ชื่อสาขา'"></span> • สมชาย ใจดี • 081-234-5678
                                                </div>
                                                <div class="text-xs" style="color: #6b7280;" x-show="repair.shop_info">
                                                    <span x-text="repair.shop_info"></span>
                                                </div>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <div class="inline-block border rounded-lg px-2 py-1 text-xs" style="color: #374151; border-color: #e5e7eb; background: #fff;">
                                                    เอกสารฉบับจริง
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Device Info --}}
                                        <div class="mt-2 p-2 border rounded-lg bg-white" style="border-color: #e5e7eb;">
                                            <div class="grid grid-cols-2 gap-1 text-xs">
                                                <div class="flex">
                                                    <span class="w-24 flex-shrink-0" style="color: #6b7280;">อุปกรณ์</span>
                                                    <span class="font-semibold">iPhone 15 Pro Max</span>
                                                </div>
                                                <div class="flex">
                                                    <span class="w-24 flex-shrink-0" style="color: #6b7280;">วันที่รับ</span>
                                                    <span class="font-semibold">{{ date('d/m/Y') }}</span>
                                                </div>
                                                <div class="flex">
                                                    <span class="w-24 flex-shrink-0" style="color: #6b7280;">ประเภท</span>
                                                    <span class="font-semibold">สมาร์ทโฟน</span>
                                                </div>
                                                <div class="flex">
                                                    <span class="w-24 flex-shrink-0" style="color: #6b7280;">ราคาประเมิน</span>
                                                    <span class="font-semibold">฿ 3,500</span>
                                                </div>
                                                <div class="flex" x-show="repair.show_password">
                                                    <span class="w-24 flex-shrink-0" style="color: #6b7280;">รหัสปลดล็อค</span>
                                                    <span class="font-semibold">1234</span>
                                                </div>
                                                <div class="flex">
                                                    <span class="w-24 flex-shrink-0" style="color: #6b7280;">อาการเสีย</span>
                                                    <span class="font-semibold">จอแตก เปลี่ยนจอ</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Accessories --}}
                                        <div x-show="repair.show_accessories" class="mt-2 p-2 border rounded-lg bg-white" style="border-color: #e5e7eb;">
                                            <div class="text-xs font-bold mb-1" :style="{ color: repair.brand_color }">อุปกรณ์ที่ติดมาด้วย</div>
                                            <div class="text-xs">ที่ชาร์จ, เคส</div>
                                        </div>

                                        {{-- Terms --}}
                                        <div class="mt-2 p-2 border rounded-lg" :style="{ background: repair.bg_color, borderColor: '#e5e7eb' }">
                                            <div class="text-xs font-bold mb-1" :style="{ color: repair.brand_color }" x-text="repair.terms_title"></div>
                                            <div class="text-xs" style="color: #374151; line-height: 1.5;">
                                                <template x-for="(line, i) in repair.terms.split('\n')" :key="i">
                                                    <div x-text="line" class="ml-2"></div>
                                                </template>
                                            </div>
                                            {{-- Signature --}}
                                            <div class="grid grid-cols-2 gap-3 mt-2">
                                                <div>
                                                    <div class="h-6 border-b border-dashed" style="border-color: #9ca3af;"></div>
                                                    <div class="text-center mt-1" style="font-size: 8px; color: #6b7280;" x-text="repair.sign_left"></div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="h-6 border-b border-dashed" style="border-color: #9ca3af;"></div>
                                                    <div class="text-center mt-1" style="font-size: 8px; color: #6b7280;" x-text="repair.sign_right"></div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Bottom --}}
                                        <div class="flex justify-between items-end mt-2">
                                            <div x-show="repair.show_barcode">
                                                <div style="font-size: 8px; color: #6b7280;">รหัสอ้างอิง</div>
                                                <div class="inline-block px-2 py-1 border border-dashed rounded text-xs font-mono" style="border-color: #e5e7eb;">RPR-2025-0001</div>
                                            </div>
                                            <div x-show="repair.show_qr" class="text-right">
                                                <div class="inline-block w-12 h-12 border rounded bg-white" style="border-color: #e5e7eb;">
                                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                        <i class="fas fa-qrcode text-2xl"></i>
                                                    </div>
                                                </div>
                                                <div style="font-size: 8px; color: #6b7280;">สแกนติดตามสถานะ</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ===== SALES RECEIPT TAB ===== --}}
    <div x-show="activeTab === 'sales'" x-cloak>
        <form action="{{ route('receipt-templates.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="sales">

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                {{-- Left: Settings --}}
                <div class="space-y-6">

                    {{-- ข้อมูลร้าน --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-store text-indigo-600 mr-2"></i>ข้อมูลร้าน
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อร้าน <span class="text-gray-400 text-xs">(ว่างไว้ = ใช้ชื่อสาขา)</span></label>
                                <input type="text" name="sales_receipt_shop_name" x-model="sales.shop_name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="All in Service Mac">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ข้อมูลร้าน <span class="text-gray-400 text-xs">(ที่อยู่, เบอร์โทร)</span></label>
                                <textarea name="sales_receipt_shop_info" x-model="sales.shop_info" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="123 ถ.สุขุมวิท กรุงเทพฯ&#10;โทร: 02-123-4567"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL <span class="text-gray-400 text-xs">(ไม่บังคับ)</span></label>
                                <input type="url" name="sales_receipt_logo_url" x-model="sales.logo_url"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="https://example.com/logo.png">
                            </div>
                        </div>
                    </div>

                    {{-- เลย์เอาท์ --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-ruler-combined text-blue-600 mr-2"></i>เลย์เอาท์
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ความกว้างกระดาษ (mm)</label>
                                    <select name="sales_receipt_paper_width" x-model="sales.paper_width"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        <option value="58">58mm (กระดาษเล็ก)</option>
                                        <option value="80">80mm (มาตรฐาน)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ขนาดฟอนต์ (px)</label>
                                    <select name="sales_receipt_font_size" x-model="sales.font_size"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        <option value="10">10px (เล็ก)</option>
                                        <option value="11">11px</option>
                                        <option value="12">12px (มาตรฐาน)</option>
                                        <option value="13">13px</option>
                                        <option value="14">14px (ใหญ่)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ข้อความท้ายใบเสร็จ --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-comment-dots text-green-600 mr-2"></i>ข้อความท้ายใบเสร็จ
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ข้อความขอบคุณ</label>
                                <input type="text" name="sales_receipt_footer_thank" x-model="sales.footer_thank"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ข้อความเพิ่มเติม</label>
                                <textarea name="sales_receipt_footer_text" x-model="sales.footer_text" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- แสดง/ซ่อน --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-eye text-green-600 mr-2"></i>แสดง / ซ่อน ส่วนต่างๆ
                        </h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-image mr-2 text-gray-400"></i>โลโก้ร้าน</span>
                                <input type="hidden" name="sales_receipt_show_logo" value="0">
                                <input type="checkbox" name="sales_receipt_show_logo" value="1" x-model="sales.show_logo"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-user mr-2 text-gray-400"></i>ข้อมูลลูกค้า</span>
                                <input type="hidden" name="sales_receipt_show_customer" value="0">
                                <input type="checkbox" name="sales_receipt_show_customer" value="1" x-model="sales.show_customer"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-user-tie mr-2 text-gray-400"></i>ข้อมูลผู้ขาย</span>
                                <input type="hidden" name="sales_receipt_show_seller" value="0">
                                <input type="checkbox" name="sales_receipt_show_seller" value="1" x-model="sales.show_seller"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-percent mr-2 text-gray-400"></i>ภาษี/VAT</span>
                                <input type="hidden" name="sales_receipt_show_vat" value="0">
                                <input type="checkbox" name="sales_receipt_show_vat" value="1" x-model="sales.show_vat"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-credit-card mr-2 text-gray-400"></i>วิธีชำระเงิน</span>
                                <input type="hidden" name="sales_receipt_show_payment" value="0">
                                <input type="checkbox" name="sales_receipt_show_payment" value="1" x-model="sales.show_payment"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            </label>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="flex items-center justify-end gap-3">
                        <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            บันทึกเทมเพลตใบเสร็จ
                        </button>
                    </div>
                </div>

                {{-- Right: Live Preview --}}
                <div class="space-y-4">
                    <div class="sticky top-4">
                        <div class="bg-white rounded-xl shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-600">
                                    <i class="fas fa-eye mr-1"></i> ตัวอย่าง (Preview)
                                </h3>
                                <span class="text-xs text-gray-400">ปรับแต่งด้านซ้ายเพื่อดูผลทันที</span>
                            </div>

                            {{-- Thermal Receipt Preview --}}
                            <div class="flex justify-center">
                                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-100 p-4">
                                    <div class="bg-white shadow-md mx-auto"
                                        :style="{ width: sales.paper_width + 'mm', padding: '5mm', fontFamily: 'sans-serif', fontSize: sales.font_size + 'px' }">

                                        {{-- Header --}}
                                        <div class="text-center pb-2 mb-2" style="border-bottom: 1px dashed #000;">
                                            <template x-if="sales.show_logo && sales.logo_url">
                                                <img :src="sales.logo_url" class="h-10 mx-auto mb-1" alt="logo">
                                            </template>
                                            <div class="font-bold" :style="{ fontSize: (parseInt(sales.font_size) + 6) + 'px' }"
                                                x-text="sales.shop_name || 'ชื่อร้านค้า'"></div>
                                            <div class="text-gray-500" :style="{ fontSize: (parseInt(sales.font_size) - 2) + 'px' }"
                                                x-html="(sales.shop_info || 'ที่อยู่ร้าน').replace(/\n/g, '<br>')"></div>
                                        </div>

                                        {{-- Info --}}
                                        <div class="pb-2 mb-2" style="border-bottom: 1px dashed #000;">
                                            <table class="w-full" :style="{ fontSize: (parseInt(sales.font_size) - 1) + 'px' }">
                                                <tr>
                                                    <td>เลขที่:</td>
                                                    <td class="text-right font-bold">INV-2025-0001</td>
                                                </tr>
                                                <tr>
                                                    <td>วันที่:</td>
                                                    <td class="text-right">{{ date('d/m/Y H:i') }}</td>
                                                </tr>
                                                <tr x-show="sales.show_seller">
                                                    <td>ผู้ขาย:</td>
                                                    <td class="text-right">พนักงาน ก.</td>
                                                </tr>
                                                <tr x-show="sales.show_customer">
                                                    <td>ลูกค้า:</td>
                                                    <td class="text-right">สมชาย ใจดี</td>
                                                </tr>
                                            </table>
                                        </div>

                                        {{-- Items --}}
                                        <div class="mb-2">
                                            <table class="w-full" :style="{ fontSize: (parseInt(sales.font_size) - 1) + 'px' }">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid #000;">
                                                        <th class="text-left py-1">รายการ</th>
                                                        <th class="text-right py-1">รวม</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="py-1">
                                                            <div class="font-medium">เคส iPhone 15 Pro</div>
                                                            <div class="text-gray-500" :style="{ fontSize: (parseInt(sales.font_size) - 2) + 'px' }">1 x ฿590</div>
                                                        </td>
                                                        <td class="text-right py-1">฿590</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="py-1">
                                                            <div class="font-medium">ฟิล์มกระจก</div>
                                                            <div class="text-gray-500" :style="{ fontSize: (parseInt(sales.font_size) - 2) + 'px' }">2 x ฿250</div>
                                                        </td>
                                                        <td class="text-right py-1">฿500</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Totals --}}
                                        <div class="pt-2 mb-2" style="border-top: 1px dashed #000;">
                                            <table class="w-full" :style="{ fontSize: (parseInt(sales.font_size) - 1) + 'px' }">
                                                <tr>
                                                    <td>รวม (2 รายการ)</td>
                                                    <td class="text-right">฿1,090</td>
                                                </tr>
                                                <tr x-show="sales.show_vat">
                                                    <td>ภาษี 7%</td>
                                                    <td class="text-right">฿76</td>
                                                </tr>
                                                <tr class="font-bold" :style="{ fontSize: (parseInt(sales.font_size) + 4) + 'px' }">
                                                    <td>รวมสุทธิ</td>
                                                    <td class="text-right">฿1,166</td>
                                                </tr>
                                                <tr x-show="sales.show_payment">
                                                    <td>ชำระโดย</td>
                                                    <td class="text-right">เงินสด</td>
                                                </tr>
                                            </table>
                                        </div>

                                        {{-- Footer --}}
                                        <div class="text-center pt-2" style="border-top: 1px dashed #000;">
                                            <div class="font-bold" :style="{ fontSize: (parseInt(sales.font_size) + 2) + 'px' }"
                                                x-text="sales.footer_thank"></div>
                                            <div class="text-gray-500 mt-1" :style="{ fontSize: (parseInt(sales.font_size) - 2) + 'px' }"
                                                x-html="sales.footer_text.replace(/\n/g, '<br>')"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- @formatter:off --}}
<script>
    function receiptDesigner() {
        return {
            activeTab: 'repair',
            repair: {
                title: @json($repair['repair_receipt_title']),
                brand_color: @json($repair['repair_receipt_brand_color']),
                accent_color: @json($repair['repair_receipt_accent_color']),
                bg_color: @json($repair['repair_receipt_bg_color']),
                shop_name: @json($repair['repair_receipt_shop_name']),
                shop_info: @json($repair['repair_receipt_shop_info']),
                logo_url: @json($repair['repair_receipt_logo_url']),
                show_logo: @json((bool) $repair['repair_receipt_show_logo']),
                copies: @json($repair['repair_receipt_copies']),
                paper_size: @json($repair['repair_receipt_paper_size']),
                sign_left: @json($repair['repair_receipt_sign_left']),
                sign_right: @json($repair['repair_receipt_sign_right']),
                show_qr: @json((bool) $repair['repair_receipt_show_qr']),
                show_accessories: @json((bool) $repair['repair_receipt_show_accessories']),
                show_barcode: @json((bool) $repair['repair_receipt_show_barcode']),
                show_password: @json((bool) $repair['repair_receipt_show_password']),
                terms: @json($repair['repair_receipt_terms']),
                terms_title: @json($repair['repair_receipt_terms_title']),
            },
            sales: {
                shop_name: @json($sales['sales_receipt_shop_name']),
                shop_info: @json($sales['sales_receipt_shop_info']),
                logo_url: @json($sales['sales_receipt_logo_url']),
                show_logo: @json((bool) $sales['sales_receipt_show_logo']),
                paper_width: @json($sales['sales_receipt_paper_width']),
                font_size: @json($sales['sales_receipt_font_size']),
                show_customer: @json((bool) $sales['sales_receipt_show_customer']),
                show_seller: @json((bool) $sales['sales_receipt_show_seller']),
                show_vat: @json((bool) $sales['sales_receipt_show_vat']),
                show_payment: @json((bool) $sales['sales_receipt_show_payment']),
                footer_thank: @json($sales['sales_receipt_footer_thank']),
                footer_text: @json($sales['sales_receipt_footer_text']),
            },
        }
    }
</script>
{{-- @formatter:on --}}
@endpush