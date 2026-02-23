<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All In Mobile - ระบบบริหารจัดการร้านซ่อมมือถือ SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delay': 'float 6s ease-in-out 2s infinite',
                        'fade-up': 'fadeUp 0.8s ease-out forwards',
                        'fade-up-delay': 'fadeUp 0.8s ease-out 0.2s forwards',
                        'fade-up-delay-2': 'fadeUp 0.8s ease-out 0.4s forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-20px)'
                            },
                        },
                        fadeUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(30px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .hero-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(99, 102, 241, 0.08) 1px, transparent 0);
            background-size: 40px 40px;
        }

        .glow-indigo {
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.15), 0 0 120px rgba(99, 102, 241, 0.05);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .pricing-card:hover {
            transform: translateY(-4px);
        }

        .stat-number {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="bg-white text-gray-800 antialiased" x-data="{ mobileMenu: false }">

    <!-- ========== NAVBAR ========== -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        x-data="{ scrolled: false }"
        @scroll.window="scrolled = (window.scrollY > 20)"
        :class="scrolled ? 'bg-white/90 backdrop-blur-xl shadow-sm border-b border-gray-100' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/40 transition-shadow">
                        <i class="fas fa-mobile-alt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-extrabold text-gray-900">All In <span class="text-indigo-600">Mobile</span></span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <a href="#features" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600 rounded-lg hover:bg-indigo-50/50 transition-all">ฟีเจอร์</a>
                    <a href="#how-it-works" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600 rounded-lg hover:bg-indigo-50/50 transition-all">วิธีใช้งาน</a>
                    <a href="#pricing" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600 rounded-lg hover:bg-indigo-50/50 transition-all">ราคา</a>
                    <div class="w-px h-6 bg-gray-200 mx-2"></div>
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">เข้าสู่ระบบ</a>
                    <a href="{{ route('register') }}" class="ml-1 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all duration-200">
                        ทดลองใช้ฟรี
                    </a>
                </div>

                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-gray-600">
                    <i :class="mobileMenu ? 'fas fa-times' : 'fas fa-bars'" class="text-xl"></i>
                </button>
            </div>
        </div>

        <div x-show="mobileMenu" x-transition.opacity class="md:hidden bg-white border-t border-gray-100 shadow-xl">
            <div class="px-6 py-4 space-y-1">
                <a href="#features" @click="mobileMenu = false" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg">ฟีเจอร์</a>
                <a href="#how-it-works" @click="mobileMenu = false" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg">วิธีใช้งาน</a>
                <a href="#pricing" @click="mobileMenu = false" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg">ราคา</a>
                <div class="border-t border-gray-100 pt-3 mt-3 space-y-2">
                    <a href="{{ route('login') }}" class="block px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-lg text-center">เข้าสู่ระบบ</a>
                    <a href="{{ route('register') }}" class="block px-4 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-xl text-center">ทดลองใช้ฟรี</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========== HERO ========== -->
    <section class="relative min-h-screen flex items-center overflow-hidden pt-20 hero-grid">
        <div class="absolute top-20 right-0 w-96 h-96 bg-gradient-to-br from-indigo-200/40 to-purple-200/40 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-0 w-80 h-80 bg-gradient-to-tr from-blue-200/30 to-cyan-200/30 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-full text-sm font-medium mb-8 animate-fade-up">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        ทดลองใช้ฟรี 14 วัน — ไม่ต้องใช้บัตรเครดิต
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6 opacity-0 animate-fade-up-delay">
                        บริหารร้านซ่อม<br>
                        <span class="gradient-text">ครบจบในที่เดียว</span>
                    </h1>

                    <p class="text-lg lg:text-xl text-gray-500 mb-10 max-w-lg mx-auto lg:mx-0 leading-relaxed opacity-0 animate-fade-up-delay-2">
                        ระบบคลาวด์สำหรับจัดการงานซ่อม สต๊อกสินค้า ขายหน้าร้าน จัดซื้อ และการเงิน — ใช้ได้ทุกที่ ทุกอุปกรณ์
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start opacity-0 animate-fade-up-delay-2">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-1 transition-all duration-300 text-center">
                            เริ่มต้นใช้งานฟรี <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-white text-gray-700 rounded-2xl font-semibold border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/50 transition-all duration-300 text-center">
                            <i class="fas fa-play-circle mr-2 text-indigo-500"></i>ดูฟีเจอร์ทั้งหมด
                        </a>
                    </div>

                    <div class="mt-12 flex items-center gap-6 justify-center lg:justify-start text-sm text-gray-400">
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-shield-alt text-green-500"></i>
                            <span>ข้อมูลปลอดภัย</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-cloud text-blue-500"></i>
                            <span>ระบบ Cloud</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-headset text-purple-500"></i>
                            <span>ซัพพอร์ตไทย</span>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Preview -->
                <div class="relative hidden lg:block">
                    <div class="relative animate-float">
                        <div class="glass-card rounded-3xl border border-gray-200/50 shadow-2xl p-6 glow-indigo">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                                <div class="flex-1 h-7 bg-gray-100 rounded-lg flex items-center px-3">
                                    <i class="fas fa-lock text-gray-300 text-xs mr-2"></i>
                                    <span class="text-xs text-gray-400">app.mjdigitals.com/dashboard</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-4 text-white">
                                        <div class="text-xs opacity-80">งานซ่อมวันนี้</div>
                                        <div class="text-2xl font-bold mt-1">12</div>
                                        <div class="text-xs mt-2 text-indigo-200"><i class="fas fa-arrow-up mr-1"></i>+3 จากเมื่อวาน</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-4 text-white">
                                        <div class="text-xs opacity-80">ยอดขาย</div>
                                        <div class="text-2xl font-bold mt-1">฿8.5K</div>
                                        <div class="text-xs mt-2 text-emerald-200"><i class="fas fa-arrow-up mr-1"></i>+12%</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl p-4 text-white">
                                        <div class="text-xs opacity-80">สินค้าคงเหลือ</div>
                                        <div class="text-2xl font-bold mt-1">245</div>
                                        <div class="text-xs mt-2 text-amber-200"><i class="fas fa-exclamation-triangle mr-1"></i>3 ใกล้หมด</div>
                                    </div>
                                </div>
                                <div class="bg-gray-50/80 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-gray-700">รายได้รายสัปดาห์</span>
                                        <span class="text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded-full">+18.2%</span>
                                    </div>
                                    <div class="flex items-end gap-1 h-16">
                                        <div class="flex-1 bg-indigo-200 rounded-t-sm" style="height: 40%"></div>
                                        <div class="flex-1 bg-indigo-200 rounded-t-sm" style="height: 55%"></div>
                                        <div class="flex-1 bg-indigo-300 rounded-t-sm" style="height: 45%"></div>
                                        <div class="flex-1 bg-indigo-300 rounded-t-sm" style="height: 70%"></div>
                                        <div class="flex-1 bg-indigo-400 rounded-t-sm" style="height: 60%"></div>
                                        <div class="flex-1 bg-indigo-500 rounded-t-sm" style="height: 85%"></div>
                                        <div class="flex-1 bg-indigo-600 rounded-t-sm" style="height: 100%"></div>
                                    </div>
                                </div>
                                <div class="bg-gray-50/80 rounded-xl p-4">
                                    <div class="text-sm font-semibold text-gray-700 mb-3">งานซ่อมล่าสุด</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-gray-100">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-mobile-alt text-blue-500 text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-gray-700">iPhone 15 Pro Max</div>
                                                    <div class="text-[10px] text-gray-400">RP-2026001 • เปลี่ยนจอ</div>
                                                </div>
                                            </div>
                                            <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">กำลังซ่อม</span>
                                        </div>
                                        <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-gray-100">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-laptop text-green-500 text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-gray-700">MacBook Air M2</div>
                                                    <div class="text-[10px] text-gray-400">RP-2026002 • เปลี่ยนแบต</div>
                                                </div>
                                            </div>
                                            <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">เสร็จแล้ว</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Cards -->
                        <div class="absolute -left-12 top-1/4 glass-card rounded-2xl border border-gray-200/50 shadow-xl p-4 animate-float-delay w-56">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-800">ซ่อมเสร็จแล้ว!</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">แจ้งลูกค้าผ่าน LINE</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -right-8 bottom-20 glass-card rounded-2xl border border-gray-200/50 shadow-xl p-4 animate-float w-48">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-chart-line text-purple-500"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-800">รายได้เดือนนี้</div>
                                    <div class="text-sm font-bold text-purple-600 mt-0.5">฿128,500</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== STATS ========== -->
    <section class="py-16 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <div class="text-center" x-data="{ count: 0 }" x-intersect.once="let i = setInterval(() => { count++; if(count >= 500) { count = 500; clearInterval(i); }}, 2)">
                    <div class="text-4xl lg:text-5xl font-extrabold stat-number" x-text="count + '+'">500+</div>
                    <div class="text-sm text-gray-500 mt-2 font-medium">ร้านค้าที่ไว้วางใจ</div>
                </div>
                <div class="text-center" x-data="{ count: 0 }" x-intersect.once="let i = setInterval(() => { count += 10; if(count >= 50000) { count = 50000; clearInterval(i); }}, 1)">
                    <div class="text-4xl lg:text-5xl font-extrabold stat-number" x-text="(count/1000).toFixed(0) + 'K+'">50K+</div>
                    <div class="text-sm text-gray-500 mt-2 font-medium">งานซ่อมที่จัดการ</div>
                </div>
                <div class="text-center" x-data="{ count: 0 }" x-intersect.once="let i = setInterval(() => { count++; if(count >= 99) { count = 99; clearInterval(i); }}, 30)">
                    <div class="text-4xl lg:text-5xl font-extrabold stat-number" x-text="count + '%'">99%</div>
                    <div class="text-sm text-gray-500 mt-2 font-medium">ความพึงพอใจ</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl lg:text-5xl font-extrabold stat-number">24/7</div>
                    <div class="text-sm text-gray-500 mt-2 font-medium">เข้าถึงได้ตลอด</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FEATURES ========== -->
    <section id="features" class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-semibold uppercase tracking-wider mb-4">
                    <i class="fas fa-puzzle-piece"></i> Features
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">ฟีเจอร์ครบครัน ตอบโจทย์ทุกการใช้งาน</h2>
                <p class="text-gray-500 text-lg">ออกแบบมาเพื่อร้านซ่อมมือถือโดยเฉพาะ ครบทุกฟังก์ชันที่ต้องการ</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                $features = [
                ['icon' => 'fas fa-tools', 'gradient' => 'from-indigo-500 to-blue-500', 'bg' => 'bg-indigo-50', 'title' => 'ระบบงานซ่อม', 'desc' => 'Kanban Board ติดตามสถานะ เบิกอะไหล่ แจ้งลูกค้าอัตโนมัติ พิมพ์ใบรับเครื่อง'],
                ['icon' => 'fas fa-cash-register', 'gradient' => 'from-emerald-500 to-green-500', 'bg' => 'bg-emerald-50', 'title' => 'POS ขายหน้าร้าน', 'desc' => 'ระบบขายครบวงจร ออกใบเสร็จ รองรับหลายช่องทางชำระเงิน'],
                ['icon' => 'fas fa-boxes-stacked', 'gradient' => 'from-amber-500 to-orange-500', 'bg' => 'bg-amber-50', 'title' => 'สต๊อกสินค้า', 'desc' => 'จัดการคลัง โอนสต๊อก ตรวจนับ แจ้งเตือนสินค้าใกล้หมด'],
                ['icon' => 'fas fa-truck-field', 'gradient' => 'from-purple-500 to-violet-500', 'bg' => 'bg-purple-50', 'title' => 'ระบบจัดซื้อ', 'desc' => 'สร้างใบสั่งซื้อ รับสินค้า จัดการ Supplier อัตโนมัติ'],
                ['icon' => 'fas fa-coins', 'gradient' => 'from-rose-500 to-pink-500', 'bg' => 'bg-rose-50', 'title' => 'บัญชี & การเงิน', 'desc' => 'สรุปยอดประจำวัน เงินสดย่อย ลูกหนี้ รายงานกำไร-ขาดทุน'],
                ['icon' => 'fas fa-chart-column', 'gradient' => 'from-cyan-500 to-blue-500', 'bg' => 'bg-cyan-50', 'title' => 'รายงานวิเคราะห์', 'desc' => 'Dashboard มองเห็นภาพรวม รายงานยอดขาย ซ่อม สต๊อก ครบทุกมิติ'],
                ['icon' => 'fas fa-store', 'gradient' => 'from-orange-500 to-red-500', 'bg' => 'bg-orange-50', 'title' => 'หลายสาขา', 'desc' => 'บริหารหลายสาขา โอนสินค้าข้ามสาขา ดูรายงานรวม/แยก'],
                ['icon' => 'fab fa-line', 'gradient' => 'from-green-500 to-emerald-500', 'bg' => 'bg-green-50', 'title' => 'LINE OA Chatbot', 'desc' => 'ลูกค้าเช็คสถานะซ่อม ค้นหาสินค้า ผ่าน LINE อัตโนมัติ'],
                ];
                @endphp

                @foreach($features as $f)
                <div class="feature-card group relative bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-gray-200/50 hover:-translate-y-1 transition-all duration-300">
                    <div class="feature-icon w-14 h-14 {{ $f['bg'] }} rounded-2xl flex items-center justify-center mb-5 transition-transform duration-300">
                        <i class="{{ $f['icon'] }} text-xl bg-gradient-to-r {{ $f['gradient'] }} bg-clip-text" style="-webkit-text-fill-color: transparent;"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $f['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r {{ $f['gradient'] }} rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== HOW IT WORKS ========== -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-semibold uppercase tracking-wider mb-4">
                    <i class="fas fa-rocket"></i> Get Started
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">เริ่มต้นง่ายๆ ใน 3 ขั้นตอน</h2>
                <p class="text-gray-500 text-lg">ไม่ต้องติดตั้งอะไร สมัครแล้วใช้งานได้ทันที</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="relative text-center group">
                    <div class="relative inline-flex">
                        <div class="w-20 h-20 bg-indigo-100 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 relative z-10">
                            <i class="fas fa-user-plus text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold z-20">01</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mt-6 mb-3">สมัครสมาชิก</h3>
                    <p class="text-gray-500 max-w-xs mx-auto">กรอกข้อมูลร้านค้า เลือกแพ็กเกจ ใช้เวลาไม่ถึง 2 นาที</p>
                </div>

                <div class="relative text-center group">
                    <div class="relative inline-flex">
                        <div class="w-20 h-20 bg-purple-100 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 relative z-10">
                            <i class="fas fa-cogs text-purple-600 text-2xl"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-purple-600 text-white rounded-lg flex items-center justify-center text-xs font-bold z-20">02</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mt-6 mb-3">ตั้งค่าร้าน</h3>
                    <p class="text-gray-500 max-w-xs mx-auto">เพิ่มสินค้า ตั้งค่าสาขา พนักงาน ระบบช่วย Setup ให้</p>
                </div>

                <div class="relative text-center group">
                    <div class="relative inline-flex">
                        <div class="w-20 h-20 bg-emerald-100 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 relative z-10">
                            <i class="fas fa-rocket text-emerald-600 text-2xl"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center text-xs font-bold z-20">03</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mt-6 mb-3">เริ่มใช้งาน!</h3>
                    <p class="text-gray-500 max-w-xs mx-auto">พร้อมรับงานซ่อม ขายสินค้า จัดการสต๊อก ได้ทันที</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== PRICING ========== -->
    <section id="pricing" class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-semibold uppercase tracking-wider mb-4">
                    <i class="fas fa-tags"></i> Pricing
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">เลือกแพ็กเกจที่เหมาะกับคุณ</h2>
                <p class="text-gray-500 text-lg">เริ่มต้นฟรี อัปเกรดเมื่อพร้อม ยกเลิกได้ตลอด</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($plans), 4) }} gap-6 max-w-5xl mx-auto">
                @foreach($plans as $plan)
                <div class="pricing-card relative bg-white rounded-3xl border-2 transition-all duration-300
                    {{ $plan->slug === 'pro' ? 'border-indigo-500 shadow-xl shadow-indigo-500/10' : 'border-gray-100 hover:border-gray-200 shadow-sm' }}">
                    @if($plan->slug === 'pro')
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <div class="px-5 py-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-bold rounded-full shadow-lg">
                            <i class="fas fa-fire mr-1"></i>ยอดนิยม
                        </div>
                    </div>
                    @endif
                    <div class="p-8 {{ $plan->slug === 'pro' ? 'pt-10' : '' }}">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                            <p class="text-sm text-gray-400 mt-1">{{ $plan->description ?? '' }}</p>
                        </div>

                        <div class="mb-8">
                            <div class="flex items-baseline gap-1">
                                @if($plan->price > 0)
                                <span class="text-5xl font-extrabold text-gray-900">฿{{ number_format($plan->price, 0) }}</span>
                                <span class="text-gray-400 text-sm font-medium">/เดือน</span>
                                @else
                                <span class="text-5xl font-extrabold gradient-text">ฟรี</span>
                                @endif
                            </div>
                            @if($plan->yearly_price > 0 && $plan->price > 0)
                            <p class="text-xs text-gray-400 mt-2">
                                <span class="text-green-600 font-semibold">ประหยัด {{ round((1 - $plan->yearly_price / ($plan->price * 12)) * 100) }}%</span>
                                เมื่อจ่ายรายปี ฿{{ number_format($plan->yearly_price, 0) }}/ปี
                            </p>
                            @endif
                        </div>

                        <a href="{{ route('register', ['plan' => $plan->slug]) }}"
                            class="block w-full py-3.5 text-center rounded-xl text-sm font-bold transition-all duration-200
                            {{ $plan->slug === 'pro'
                                ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $plan->trial_days > 0 ? 'ทดลองใช้ฟรี ' . $plan->trial_days . ' วัน' : 'เริ่มต้นใช้งาน' }}
                        </a>

                        <div class="mt-8 space-y-3.5">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">รวมฟีเจอร์:</div>

                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-indigo-600 text-[10px]"></i>
                                </div>
                                <span>ผู้ใช้ {{ $plan->max_users == -1 ? 'ไม่จำกัด' : 'สูงสุด ' . $plan->max_users . ' คน' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-indigo-600 text-[10px]"></i>
                                </div>
                                <span>{{ $plan->max_branches == -1 ? 'ไม่จำกัดสาขา' : $plan->max_branches . ' สาขา' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-indigo-600 text-[10px]"></i>
                                </div>
                                <span>สินค้า {{ $plan->max_products == -1 ? 'ไม่จำกัด' : 'สูงสุด ' . number_format($plan->max_products) }}</span>
                            </div>

                            @if($plan->features)
                            @php
                            $featureLabels = [
                            'repairs' => 'ระบบงานซ่อม',
                            'pos' => 'ขายหน้าร้าน (POS)',
                            'stock' => 'สต๊อกสินค้า',
                            'purchasing' => 'ระบบจัดซื้อ',
                            'finance' => 'บัญชี & การเงิน',
                            'reports' => 'รายงานวิเคราะห์',
                            'line_oa' => 'LINE OA Chatbot',
                            'api' => 'API Access',
                            'quotations' => 'ใบเสนอราคา',
                            'multi_branch' => 'หลายสาขา',
                            ];
                            @endphp
                            @foreach($plan->features as $feature)
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-green-600 text-[10px]"></i>
                                </div>
                                <span>{{ $featureLabels[$feature] ?? $feature }}</span>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== TESTIMONIALS ========== -->
    <section class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-semibold uppercase tracking-wider mb-4">
                    <i class="fas fa-heart"></i> Testimonials
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">ลูกค้าพูดถึงเรา</h2>
                <p class="text-gray-500 text-lg">ร้านซ่อมมือถือทั่วประเทศที่ไว้วางใจใช้งาน</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @php
                $testimonials = [
                ['name' => 'คุณสมชาย', 'shop' => 'ร้าน Fix Phone สยาม', 'text' => 'ใช้มา 6 เดือน ระบบดีมาก จัดการงานซ่อมได้ง่ายขึ้นเยอะ ลูกค้าพอใจที่เช็คสถานะผ่าน LINE ได้', 'rating' => 5, 'avatar' => 'S'],
                ['name' => 'คุณมานี', 'shop' => 'Mobile Care บางแค', 'text' => 'สต๊อกสินค้าไม่เคยหายอีกเลย ระบบแจ้งเตือนสินค้าใกล้หมดช่วยได้มาก ดีกว่าจดในสมุดเยอะ', 'rating' => 5, 'avatar' => 'M'],
                ['name' => 'คุณวิชัย', 'shop' => 'DropFix 3 สาขา', 'text' => 'มี 3 สาขา โอนสินค้าข้ามสาขาสะดวก ดูรายงานรวมได้ในที่เดียว คุ้มค่ามากครับ', 'rating' => 5, 'avatar' => 'W'],
                ];
                @endphp

                @foreach($testimonials as $t)
                <div class="bg-gray-50 rounded-2xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < $t['rating']; $i++)
                            <i class="fas fa-star text-amber-400 text-sm"></i>
                            @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">"{{ $t['text'] }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            {{ $t['avatar'] }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">{{ $t['name'] }}</div>
                            <div class="text-xs text-gray-400">{{ $t['shop'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== FAQ ========== -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-3xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold uppercase tracking-wider mb-4">
                    <i class="fas fa-question-circle"></i> FAQ
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900">คำถามที่พบบ่อย</h2>
            </div>

            <div class="space-y-3" x-data="{ openFaq: null }">
                @php
                $faqs = [
                ['q' => 'ต้องติดตั้งโปรแกรมอะไรไหม?', 'a' => 'ไม่ต้องติดตั้งอะไรเลย! All In Mobile เป็นระบบ Cloud (SaaS) ใช้งานผ่านเว็บเบราว์เซอร์ได้ทันที ทั้งคอมพิวเตอร์ แท็บเล็ต และมือถือ'],
                ['q' => 'ข้อมูลปลอดภัยหรือไม่?', 'a' => 'ปลอดภัย 100% ข้อมูลถูกเข้ารหัส (SSL) และ backup อัตโนมัติทุกวัน ข้อมูลแต่ละร้านแยกกันโดยสมบูรณ์'],
                ['q' => 'ยกเลิกได้ตลอดเวลาไหม?', 'a' => 'ได้เลย! ไม่มีสัญญาผูกมัด สามารถยกเลิกหรือเปลี่ยนแพ็กเกจได้ตลอดเวลา'],
                ['q' => 'รองรับหลายสาขาไหม?', 'a' => 'รองรับ! แพ็กเกจ Pro ขึ้นไปรองรับหลายสาขา โอนสินค้าข้ามสาขา ดูรายงานรวมหรือแยกสาขาได้'],
                ['q' => 'มีทีมซัพพอร์ตไหม?', 'a' => 'มีทีมซัพพอร์ตภาษาไทย พร้อมช่วยเหลือผ่าน LINE และอีเมล'],
                ];
                @endphp

                @foreach($faqs as $idx => $faq)
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden hover:shadow-md transition-shadow"
                    :class="openFaq === {{ $idx }} ? 'shadow-md' : ''">
                    <button @click="openFaq = openFaq === {{ $idx }} ? null : {{ $idx }}"
                        class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-semibold text-gray-900">{{ $faq['q'] }}</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                            :class="openFaq === {{ $idx }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === {{ $idx }}" x-collapse>
                        <div class="px-6 pb-5 text-gray-500 leading-relaxed">{{ $faq['a'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== CTA ========== -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-40 h-40 bg-white rounded-full blur-xl"></div>
            <div class="absolute bottom-10 right-10 w-60 h-60 bg-white rounded-full blur-xl"></div>
            <div class="absolute top-1/2 left-1/3 w-32 h-32 bg-white rounded-full blur-xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-5xl font-extrabold text-white mb-6 leading-tight">
                พร้อมยกระดับ<br>การบริหารร้านของคุณ?
            </h2>
            <p class="text-indigo-200 text-lg mb-10 max-w-2xl mx-auto">
                เริ่มต้นใช้งานวันนี้ ทดลองฟรี 14 วัน ไม่ต้องใช้บัตรเครดิต<br>
                ยกเลิกได้ตลอดเวลา ไม่มีข้อผูกมัด
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-10 py-4 bg-white text-indigo-600 rounded-2xl font-bold text-lg hover:bg-gray-50 hover:-translate-y-1 transition-all duration-300 shadow-xl shadow-black/10">
                    เริ่มต้นใช้งานฟรี <i class="fas fa-arrow-right ml-2"></i>
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-10 py-4 border-2 border-white/30 text-white rounded-2xl font-semibold hover:bg-white/10 hover:-translate-y-1 transition-all duration-300 text-center">
                    เข้าสู่ระบบ
                </a>
            </div>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer class="bg-gray-950 text-gray-400 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-extrabold text-white">All In <span class="text-indigo-400">Mobile</span></span>
                    </div>
                    <p class="text-gray-500 max-w-sm leading-relaxed">
                        ระบบบริหารจัดการร้านซ่อมมือถือและสต๊อกสินค้า แบบ Cloud SaaS ครบจบในที่เดียว
                    </p>
                    <div class="flex gap-3 mt-6">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-indigo-600 rounded-xl flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-xl flex items-center justify-center transition-colors">
                            <i class="fab fa-line text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-sky-500 rounded-xl flex items-center justify-center transition-colors">
                            <i class="fas fa-envelope text-sm"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">ผลิตภัณฑ์</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#features" class="hover:text-white transition">ฟีเจอร์</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">ราคา</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">สมัครใช้งาน</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">เข้าสู่ระบบ</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">ช่วยเหลือ</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#how-it-works" class="hover:text-white transition">วิธีใช้งาน</a></li>
                        <li><a href="#" class="hover:text-white transition">คู่มือการใช้งาน</a></li>
                        <li><a href="#" class="hover:text-white transition">ติดต่อเรา</a></li>
                        <li><a href="#" class="hover:text-white transition">นโยบายความเป็นส่วนตัว</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-600">&copy; {{ date('Y') }} All In Mobile. All rights reserved.</p>
                <p class="text-xs text-gray-700">Made with <i class="fas fa-heart text-red-500 mx-1"></i> in Thailand</p>
            </div>
        </div>
    </footer>

</body>

</html>