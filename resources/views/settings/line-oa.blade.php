{{-- @formatter:off --}}
@extends('layouts.app')

@section('title', 'ตั้งค่า LINE OA')
@section('page-title', 'ตั้งค่า LINE OA Chatbot')

@section('content')
<div x-data="lineOaSettings()" class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">LINE Official Account</h2>
            <p class="text-gray-500">เชื่อมต่อ LINE OA เพื่อทำแชทบอทสอบถามสินค้าคงเหลือ / ราคา / ติดตามงานซ่อม</p>
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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Left: Settings Form --}}
        <div class="xl:col-span-2 space-y-6">
            <form action="{{ route('line-oa.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- สถานะ --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">เปิด/ปิด LINE Chatbot</h3>
                                <p class="text-sm text-gray-500">เปิดใช้งานบอทตอบข้อความอัตโนมัติ</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="line_oa_enabled" value="0">
                            <input type="checkbox" name="line_oa_enabled" value="1" x-model="enabled"
                                class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                </div>

                {{-- API Keys --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-key text-amber-600 mr-2"></i>
                        การเชื่อมต่อ LINE Messaging API
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Channel ID
                                <span class="text-gray-400 text-xs ml-1">(จาก LINE Developers Console)</span>
                            </label>
                            <input type="text" name="line_oa_channel_id" x-model="channelId"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-mono text-sm"
                                placeholder="1234567890">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Channel Secret
                                <span class="text-gray-400 text-xs ml-1">(ใช้ตรวจสอบ signature)</span>
                            </label>
                            <div class="relative">
                                <input :type="showSecret ? 'text' : 'password'" name="line_oa_channel_secret" x-model="channelSecret"
                                    class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-mono text-sm"
                                    placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                <button type="button" @click="showSecret = !showSecret"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i :class="showSecret ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Channel Access Token (Long-lived)
                                <span class="text-gray-400 text-xs ml-1">(ใช้ส่งข้อความ)</span>
                            </label>
                            <div class="relative">
                                <textarea name="line_oa_access_token" x-model="accessToken" rows="3"
                                    class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-mono text-sm"
                                    placeholder="Long-lived access token..."></textarea>
                                <button type="button" @click="testConnection()"
                                    class="absolute right-3 top-3 px-3 py-1 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition"
                                    :disabled="testing">
                                    <span x-show="!testing"><i class="fas fa-plug mr-1"></i>ทดสอบ</span>
                                    <span x-show="testing"><i class="fas fa-spinner fa-spin mr-1"></i>กำลังทดสอบ...</span>
                                </button>
                            </div>
                        </div>

                        {{-- Test result --}}
                        <div x-show="testResult" x-cloak
                            :class="testSuccess ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                            class="border rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <template x-if="testSuccess && botPicture">
                                    <img :src="botPicture" class="w-10 h-10 rounded-full">
                                </template>
                                <div>
                                    <div :class="testSuccess ? 'text-green-700' : 'text-red-700'" class="font-semibold text-sm" x-text="testResult"></div>
                                    <div x-show="botName" class="text-sm text-gray-600">Bot: <span x-text="botName" class="font-semibold"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Webhook URL --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-link text-blue-600 mr-2"></i>
                        Webhook URL
                    </h3>
                    <p class="text-sm text-gray-500 mb-3">คัดลอก URL ด้านล่างไปตั้งค่าใน LINE Developers Console → Messaging API → Webhook URL</p>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $webhookUrl }}"
                            id="webhookUrl"
                            class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg font-mono text-sm text-gray-700">
                        <button type="button" @click="copyWebhook()"
                            class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm whitespace-nowrap">
                            <i class="fas fa-copy mr-1"></i>
                            <span x-text="copied ? 'คัดลอกแล้ว!' : 'คัดลอก'"></span>
                        </button>
                    </div>
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>สำคัญ:</strong> ต้องตั้ง Webhook URL ใน LINE Developers Console และเปิด "Use webhook" ด้วย
                            <br>หากใช้บน localhost ให้ใช้ ngrok หรือ tunnel เพื่อให้ LINE เข้าถึงได้
                        </p>
                    </div>
                </div>

                {{-- ตัวเลือกบอท --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-robot text-purple-600 mr-2"></i>
                        ตัวเลือกแชทบอท
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                            <div>
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-tag mr-2 text-gray-400"></i>แสดงราคาสินค้า</span>
                                <p class="text-xs text-gray-400 ml-6">แสดงราคาปลีก/ส่งเมื่อสอบถามสินค้า</p>
                            </div>
                            <input type="hidden" name="line_oa_show_price" value="0">
                            <input type="checkbox" name="line_oa_show_price" value="1" x-model="showPrice"
                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                            <div>
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-store mr-2 text-gray-400"></i>แสดงสต๊อกแยกสาขา</span>
                                <p class="text-xs text-gray-400 ml-6">แสดงจำนวนสินค้าแต่ละสาขา</p>
                            </div>
                            <input type="hidden" name="line_oa_show_branch" value="0">
                            <input type="checkbox" name="line_oa_show_branch" value="1" x-model="showBranch"
                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                            <div>
                                <span class="text-sm font-medium text-gray-700"><i class="fas fa-search mr-2 text-gray-400"></i>ค้นหาอัตโนมัติ</span>
                                <p class="text-xs text-gray-400 ml-6">พิมพ์ชื่อสินค้าโดยไม่ต้องใช้คำสั่ง "สต๊อก"</p>
                            </div>
                            <input type="hidden" name="line_oa_auto_search" value="0">
                            <input type="checkbox" name="line_oa_auto_search" value="1" x-model="autoSearch"
                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                    </div>
                </div>

                {{-- ข้อความต้อนรับ --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-comment-dots text-green-600 mr-2"></i>
                        ข้อความต้อนรับ
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ข้อความเมื่อมีคนเพิ่มเพื่อน
                            <span class="text-gray-400 text-xs ml-1">(ว่างไว้ = ใช้ข้อความเริ่มต้น)</span>
                        </label>
                        <textarea name="line_oa_welcome_msg" x-model="welcomeMsg" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm"
                            placeholder="สวัสดีค่ะ ยินดีต้อนรับ! พิมพ์ สต๊อก ตามด้วยชื่อสินค้า เพื่อเช็คสินค้าคงเหลือได้เลยค่ะ"></textarea>
                    </div>
                </div>

                {{-- Save --}}
                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        บันทึกการตั้งค่า LINE OA
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Guide & Preview --}}
        <div class="space-y-6">
            {{-- Setup Guide --}}
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-book text-indigo-600 mr-2"></i>
                    วิธีตั้งค่า LINE OA
                </h3>
                <ol class="space-y-3 text-sm text-gray-600">
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-bold">1</span>
                        <span>เข้า <a href="https://developers.line.biz/console/" target="_blank" class="text-indigo-600 underline">LINE Developers Console</a></span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-bold">2</span>
                        <span>สร้าง Provider → Channel (Messaging API)</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-bold">3</span>
                        <span>คัดลอก Channel ID, Channel Secret มาใส่</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-bold">4</span>
                        <span>ไปที่ Messaging API → Issue Channel Access Token (long-lived) แล้วคัดลอกมาใส่</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-bold">5</span>
                        <span>ตั้ง Webhook URL ด้วย URL ที่ระบบแสดง และเปิด "Use webhook"</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-bold">6</span>
                        <span>ปิด "Auto-reply messages" ใน LINE Official Account Manager</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-700 rounded-full text-xs flex items-center justify-center font-bold">✓</span>
                        <span class="text-green-700 font-semibold">ทดสอบส่งข้อความใน LINE!</span>
                    </li>
                </ol>
            </div>

            {{-- Chat Preview --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-comment-alt text-green-600 mr-2"></i>
                    ตัวอย่างการใช้งาน (Chat Preview)
                </h3>
                <div class="bg-[#7494C0] rounded-xl p-4 space-y-3" style="min-height: 300px;">
                    {{-- User message --}}
                    <div class="flex justify-end">
                        <div class="bg-[#A8D86E] rounded-2xl rounded-tr-sm px-3 py-2 max-w-[75%] text-sm">
                            สต๊อก iPhone 15
                        </div>
                    </div>
                    {{-- Bot reply --}}
                    <div class="flex justify-start">
                        <div class="bg-white rounded-2xl rounded-tl-sm px-3 py-2 max-w-[85%] text-sm whitespace-pre-line text-gray-800">📦 ผลค้นหา "iPhone 15" (2 รายการ)
                            ────────────────────

                            📌 เคส iPhone 15 Pro
                            SKU: CASE-IP15P
                            คงเหลือ: 25 ชิ้น
                            สถานะ: ✅ มีสินค้า
                            📍 สาขาหลัก: 15 ชิ้น
                            📍 สาขา 2: 10 ชิ้น

                            📌 ฟิล์ม iPhone 15
                            คงเหลือ: 0 ชิ้น
                            สถานะ: ❌ สินค้าหมด</div>
                    </div>
                    {{-- User message --}}
                    <div class="flex justify-end">
                        <div class="bg-[#A8D86E] rounded-2xl rounded-tr-sm px-3 py-2 max-w-[75%] text-sm">
                            ติดตาม RPR-2025-0001
                        </div>
                    </div>
                    {{-- Bot reply --}}
                    <div class="flex justify-start">
                        <div class="bg-white rounded-2xl rounded-tl-sm px-3 py-2 max-w-[85%] text-sm whitespace-pre-line text-gray-800">🔧 สถานะงานซ่อม
                            ────────────────────

                            📋 เลขที่: RPR-2025-0001
                            📱 อุปกรณ์: Apple iPhone 15 Pro
                            🔧 อาการ: จอแตก
                            📊 สถานะ: 🔧 กำลังซ่อม
                            📍 สาขา: สาขาหลัก</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- @formatter:off --}}
<script>
    function lineOaSettings() {
        return {
            enabled: @json((bool) $settings['line_oa_enabled']),
            channelId: @json($settings['line_oa_channel_id']),
            channelSecret: @json($settings['line_oa_channel_secret']),
            accessToken: @json($settings['line_oa_access_token']),
            welcomeMsg: @json($settings['line_oa_welcome_msg']),
            showPrice: @json((bool) $settings['line_oa_show_price']),
            showBranch: @json((bool) $settings['line_oa_show_branch']),
            autoSearch: @json((bool) $settings['line_oa_auto_search']),
            showSecret: false,
            copied: false,
            testing: false,
            testResult: '',
            testSuccess: false,
            botName: '',
            botPicture: '',

            copyWebhook() {
                const el = document.getElementById('webhookUrl');
                el.select();
                navigator.clipboard.writeText(el.value);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            },

            async testConnection() {
                this.testing = true;
                this.testResult = '';
                try {
                    const res = await fetch('{{ route("line-oa.test") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            access_token: this.accessToken
                        }),
                    });
                    const data = await res.json();
                    this.testSuccess = data.success;
                    this.testResult = data.message;
                    this.botName = data.bot_name || '';
                    this.botPicture = data.picture || '';
                } catch (e) {
                    this.testSuccess = false;
                    this.testResult = 'เกิดข้อผิดพลาด: ' + e.message;
                }
                this.testing = false;
            }
        }
    }
</script>
{{-- @formatter:on --}}
@endpush