<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครใช้งาน - All In Mobile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex">
        <!-- Left decorative panel -->
        <div class="hidden lg:flex lg:w-5/12 bg-gradient-to-br from-indigo-600 to-purple-700 p-12 flex-col justify-between relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-10 w-40 h-40 bg-white rounded-full"></div>
                <div class="absolute bottom-32 right-10 w-60 h-60 bg-white rounded-full"></div>
                <div class="absolute top-1/2 left-1/3 w-20 h-20 bg-white rounded-full"></div>
            </div>
            <div class="relative z-10">
                <a href="{{ route('landing') }}" class="flex items-center gap-3 mb-12">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-white">All In Mobile</span>
                </a>
                <h2 class="text-3xl font-bold text-white mb-4">เริ่มต้นบริหารร้านของคุณ</h2>
                <p class="text-indigo-200 text-lg">ครบจบในที่เดียว ทดลองใช้ฟรี {{ $selectedPlan->trial_days ?? 14 }} วัน</p>
            </div>
            <div class="relative z-10 space-y-4">
                <div class="flex items-center gap-3 text-white/80">
                    <i class="fas fa-check-circle text-indigo-300"></i>
                    <span>ไม่ต้องใช้บัตรเครดิต</span>
                </div>
                <div class="flex items-center gap-3 text-white/80">
                    <i class="fas fa-check-circle text-indigo-300"></i>
                    <span>ตั้งค่าง่าย ใช้งานได้ทันที</span>
                </div>
                <div class="flex items-center gap-3 text-white/80">
                    <i class="fas fa-check-circle text-indigo-300"></i>
                    <span>ยกเลิกได้ตลอดเวลา</span>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex-1 flex items-center justify-center p-8" x-data="registerForm()">
            <div class="w-full max-w-lg">
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-800">All In Mobile</span>
                    </a>
                </div>

                <h1 class="text-2xl font-bold text-gray-800 mb-1">สมัครใช้งาน</h1>
                <p class="text-gray-500 mb-8">กรอกข้อมูลเพื่อเริ่มต้นใช้งานระบบ</p>

                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-2 text-red-700 text-sm font-medium mb-1">
                        <i class="fas fa-exclamation-circle"></i> พบข้อผิดพลาด
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Shop Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-store mr-1"></i> ข้อมูลร้านค้า
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อร้าน <span class="text-red-400">*</span></label>
                            <input type="text" name="shop_name" value="{{ old('shop_name') }}"
                                x-model="shopName" @input="generateSlug()"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="เช่น ร้านซ่อมมือถือ ABC" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Slug (URL ร้าน) <span class="text-red-400">*</span>
                            </label>
                            <div class="flex items-center">
                                <input type="text" name="slug" value="{{ old('slug') }}"
                                    x-model="slug" @input="checkSlug()" @blur="checkSlug()"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="slugStatus === 'taken' ? 'border-red-300 bg-red-50' : (slugStatus === 'available' ? 'border-green-300 bg-green-50' : '')"
                                    placeholder="shop-abc" required pattern="[a-z0-9\-]+">
                            </div>
                            <p class="mt-1 text-xs" :class="slugStatus === 'taken' ? 'text-red-500' : (slugStatus === 'available' ? 'text-green-600' : 'text-gray-400')">
                                <template x-if="slugStatus === 'checking'">
                                    <span><i class="fas fa-spinner fa-spin mr-1"></i>กำลังตรวจสอบ...</span>
                                </template>
                                <template x-if="slugStatus === 'available'">
                                    <span><i class="fas fa-check-circle mr-1"></i>สามารถใช้ได้</span>
                                </template>
                                <template x-if="slugStatus === 'taken'">
                                    <span><i class="fas fa-times-circle mr-1"></i>ถูกใช้แล้ว กรุณาเลือกชื่ออื่น</span>
                                </template>
                                <template x-if="slugStatus === 'idle'">
                                    <span>ใช้ตัวอักษรภาษาอังกฤษ ตัวเลข และขีด (-) เท่านั้น</span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <!-- Owner Info -->
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-user mr-1"></i> ข้อมูลเจ้าของ
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ-นามสกุล <span class="text-red-400">*</span></label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="ชื่อผู้ดูแลระบบ" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล <span class="text-red-400">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="you@example.com" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน <span class="text-red-400">*</span></label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="password"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                                        placeholder="••••••••" required minlength="6">
                                    <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่าน <span class="text-red-400">*</span></label>
                                <input type="password" name="password_confirmation"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="••••••••" required minlength="6">
                            </div>
                        </div>
                    </div>

                    <!-- Plan Selection -->
                    <div class="space-y-3 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-tag mr-1"></i> เลือกแพ็กเกจ
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($plans as $plan)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                    class="peer sr-only"
                                    {{ (old('plan_id', $selectedPlan->id ?? '') == $plan->id) ? 'checked' : '' }}>
                                <div class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 transition">
                                    <div class="font-semibold text-gray-800 text-sm">{{ $plan->name }}</div>
                                    <div class="text-indigo-600 font-bold mt-1">
                                        {{ $plan->price > 0 ? '฿' . number_format($plan->price, 0) . '/เดือน' : 'ฟรี' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">{{ $plan->max_users == -1 ? 'ไม่จำกัด' : $plan->max_users }} ผู้ใช้</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit"
                        :disabled="slugStatus === 'taken'"
                        class="w-full py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-indigo-600/20">
                        <i class="fas fa-rocket mr-2"></i>เริ่มทดลองใช้ฟรี
                    </button>

                    <p class="text-center text-sm text-gray-500">
                        มีบัญชีอยู่แล้ว?
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">เข้าสู่ระบบ</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    {{-- @formatter:off --}}
    <script>
        function registerForm() {
            return {
                shopName: @json(old('shop_name', '')),
                slug: @json(old('slug', '')),
                slugStatus: 'idle',
                slugTimer: null,

                generateSlug() {
                    this.slug = this.shopName
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');
                    this.checkSlug();
                },

                checkSlug() {
                    clearTimeout(this.slugTimer);
                    if (!this.slug || this.slug.length < 3) {
                        this.slugStatus = 'idle';
                        return;
                    }
                    this.slugStatus = 'checking';
                    this.slugTimer = setTimeout(() => {
                        fetch('{{ route("check-slug") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    slug: this.slug
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.slugStatus = data.available ? 'available' : 'taken';
                            })
                            .catch(() => {
                                this.slugStatus = 'idle';
                            });
                    }, 400);
                }
            };
        }
    </script>
    {{-- @formatter:on --}}
</body>

</html>