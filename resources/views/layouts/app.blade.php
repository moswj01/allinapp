<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'All In Mobile') - ระบบบริหารงานซ่อมและสต๊อก</title>

    <!-- Google Font Thai -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
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

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Global API helpers -->
    <script>
        window.csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
        window.apiHeaders = (extra = {}) => Object.assign({
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken(),
        }, extra);
        window.apiJsonHeaders = () => window.apiHeaders({
            'Content-Type': 'application/json'
        });
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: true, darkMode: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-indigo-800 to-indigo-900 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Logo -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-indigo-700">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-mobile-alt text-2xl text-indigo-300"></i>
                    <span class="text-xl font-bold">{{ $currentTenant->name ?? 'All In Mobile' }}</span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-indigo-300 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- User Info -->
            <div class="px-6 py-4 border-b border-indigo-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-lg font-semibold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-indigo-300 truncate">{{ Auth::user()->role->name ?? 'Role' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="px-4 py-4 space-y-1 overflow-y-auto h-[calc(100vh-200px)] scrollbar-thin">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-home w-5 mr-3"></i>
                    แดชบอร์ด
                </a>

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">งานซ่อม</p>
                </div>
                <a href="{{ route('repairs.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('repairs.index') || request()->routeIs('repairs.create') || request()->routeIs('repairs.show') || request()->routeIs('repairs.edit') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-tools w-5 mr-3"></i>
                    งานซ่อม
                </a>
                <a href="{{ route('repairs.part-approvals') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('repairs.part-approvals') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-clipboard-check w-5 mr-3"></i>
                    อนุมัติเบิกอะไหล่
                </a>
                <a href="{{ route('repairs.part-report') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('repairs.part-report') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-chart-bar w-5 mr-3"></i>
                    รายงานเบิกอะไหล่
                </a>

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">รายงาน</p>
                </div>
                <a href="{{ route('reports.sales') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('reports.sales') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-chart-line w-5 mr-3"></i>
                    รายงานการขาย
                </a>
                <a href="{{ route('reports.repairs') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('reports.repairs') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-chart-pie w-5 mr-3"></i>
                    รายงานการซ่อม
                </a>
                <a href="{{ route('reports.stock') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('reports.stock') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-boxes w-5 mr-3"></i>
                    รายงานสต๊อก
                </a>
                <a href="{{ route('reports.finance') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('reports.finance') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-money-check-alt w-5 mr-3"></i>
                    รายงานการเงิน
                </a>
                <a href="{{ route('reports.purchasing') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('reports.purchasing') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-shopping-bag w-5 mr-3"></i>
                    รายงานจัดซื้อ
                </a>

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">สินค้า & คลัง</p>
                </div>
                <a href="{{ route('products.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-box w-5 mr-3"></i>
                    สินค้า
                </a>
                <a href="{{ route('categories.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-tags w-5 mr-3"></i>
                    หมวดหมู่
                </a>
                <a href="{{ route('suppliers.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-truck w-5 mr-3"></i>
                    ซัพพลายเออร์
                </a>
                <a href="{{ route('stocks.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('stocks.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-warehouse w-5 mr-3"></i>
                    คลังสินค้า (Warehouse)
                </a>
                <a href="{{ route('branch-orders.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('branch-orders.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-paper-plane w-5 mr-3"></i>
                    {{ Auth::user()->branch && Auth::user()->branch->is_main ? 'คำสั่งซื้อ' : 'สั่งซื้อจากสาขาใหญ่' }}
                </a>
                @if(isset($currentTenant) && $currentTenant->slug !== 'system-admin')
                <a href="{{ route('tenant-orders.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('tenant-orders.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-shopping-cart w-5 mr-3"></i>
                    สั่งสินค้าจากร้านกลาง
                </a>
                @endif

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">จัดซื้อ</p>
                </div>
                <a href="{{ route('purchase-orders.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('purchase-orders.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-file-invoice w-5 mr-3"></i>
                    ใบสั่งซื้อ
                </a>
                <a href="{{ route('goods-receipts.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('goods-receipts.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-dolly w-5 mr-3"></i>
                    รับสินค้า
                </a>
                <a href="{{ route('stock-takes.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('stock-takes.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-clipboard-list w-5 mr-3"></i>
                    ตรวจนับสต๊อก
                </a>
                <a href="{{ route('stock-transfers.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('stock-transfers.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-exchange-alt w-5 mr-3"></i>
                    โอนสต๊อก
                </a>

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">ขาย & ลูกค้า</p>
                </div>
                <a href="{{ route('pos') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('pos') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-cash-register w-5 mr-3"></i>
                    POS ขายสินค้า
                </a>
                <a href="{{ route('sales.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('sales.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-receipt w-5 mr-3"></i>
                    รายการขาย
                </a>
                <a href="{{ route('quotations.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('quotations.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-file-alt w-5 mr-3"></i>
                    ใบเสนอราคา
                </a>
                <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('customers.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-users w-5 mr-3"></i>
                    ลูกค้า
                </a>
                <a href="{{ route('accounts-receivable.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts-receivable.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-hand-holding-usd w-5 mr-3"></i>
                    บัญชีลูกหนี้
                </a>

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">การเงิน</p>
                </div>
                <a href="{{ route('finance.petty-cash.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('finance.petty-cash.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-coins w-5 mr-3"></i>
                    เงินสดย่อย
                </a>
                <a href="{{ route('finance.daily-settlement.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('finance.daily-settlement.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-calculator w-5 mr-3"></i>
                    ปิดยอดประจำวัน
                </a>

                @if(Auth::user() && Auth::user()->isManager())
                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">จัดการ</p>
                </div>
                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-user-cog w-5 mr-3"></i>
                    พนักงาน
                </a>
                <a href="{{ route('branches.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('branches.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-store w-5 mr-3"></i>
                    สาขา
                </a>
                <a href="{{ route('audit-logs.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-history w-5 mr-3"></i>
                    Audit Log
                </a>
                <a href="{{ route('notifications.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('notifications.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-bell w-5 mr-3"></i>
                    การแจ้งเตือน
                </a>

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">ตั้งค่า</p>
                </div>
                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    การตั้งค่าระบบ
                </a>
                <a href="{{ route('receipt-templates.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('receipt-templates.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-file-invoice w-5 mr-3"></i>
                    ออกแบบใบเสร็จ
                </a>
                <a href="{{ route('line-oa.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('line-oa.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
                    </svg>
                    LINE OA แชทบอท
                </a>
                <a href="{{ route('roles.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('roles.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-user-shield w-5 mr-3"></i>
                    จัดการสิทธิ์
                </a>
                @endif

                <!-- Billing -->
                <a href="{{ route('billing') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('billing*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-crown w-5 mr-3"></i>
                    แพ็กเกจ / Billing
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm z-40">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800">@yield('page-title', 'แดชบอร์ด')</h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Branch Selector -->
                        @if(Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isOwner()))
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                <i class="fas fa-store text-gray-500"></i>
                                <span class="text-sm font-medium text-gray-700">{{ Auth::user()->branch->name ?? 'ทุกสาขา' }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                        </div>
                        @endif

                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="fas fa-bell text-xl"></i>
                                @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                                @endif
                            </button>

                            <!-- Notification Dropdown -->
                            <div x-show="open"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                x-cloak
                                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg overflow-hidden z-50 border border-gray-100">
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900">การแจ้งเตือน</h3>
                                    @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">อ่านทั้งหมด</button>
                                    </form>
                                    @endif
                                </div>
                                <div class="max-h-72 overflow-y-auto">
                                    @if(isset($recentNotifications) && $recentNotifications->count() > 0)
                                    @foreach($recentNotifications as $notif)
                                    <a href="{{ $notif->link ? route('notifications.read', $notif) : '#' }}"
                                        @if(!$notif->link) onclick="event.preventDefault()" @endif
                                        class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 {{ !$notif->is_read ? 'bg-blue-50/50' : '' }}">
                                        <div class="flex items-start">
                                            @php
                                            $iconMap = [
                                            'info' => 'fas fa-info-circle text-blue-500',
                                            'success' => 'fas fa-check-circle text-green-500',
                                            'warning' => 'fas fa-exclamation-triangle text-yellow-500',
                                            'error' => 'fas fa-times-circle text-red-500',
                                            'repair_assigned' => 'fas fa-tools text-indigo-500',
                                            'repair_status_changed' => 'fas fa-sync text-blue-500',
                                            'repair_completed' => 'fas fa-check-circle text-green-500',
                                            'low_stock_alert' => 'fas fa-exclamation-triangle text-orange-500',
                                            'payment_received' => 'fas fa-money-bill text-green-500',
                                            ];
                                            $nIcon = $iconMap[$notif->type] ?? 'fas fa-bell text-gray-400';
                                            @endphp
                                            <i class="{{ $nIcon }} text-sm mt-0.5 mr-3 flex-shrink-0"></i>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $notif->title }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $notif->message }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if(!$notif->is_read)
                                            <span class="w-2 h-2 bg-blue-500 rounded-full mt-2 ml-2 flex-shrink-0"></span>
                                            @endif
                                        </div>
                                    </a>
                                    @endforeach
                                    @else
                                    <div class="px-4 py-8 text-center text-gray-400">
                                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                        <p class="text-sm">ไม่มีการแจ้งเตือน</p>
                                    </div>
                                    @endif
                                </div>
                                <a href="{{ route('notifications.index') }}" class="block text-center px-4 py-3 bg-gray-50 border-t border-gray-100 text-sm text-indigo-600 hover:text-indigo-800 font-medium hover:bg-gray-100 transition-colors">
                                    ดูทั้งหมด <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </a>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white">
                                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                                </div>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>

                            <div x-show="open"
                                @click.away="open = false"
                                x-transition
                                x-cloak
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> โปรไฟล์
                                </a>
                                <hr class="my-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> ออกจากระบบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Trial Warning Banner -->
            @if(isset($currentTenant) && $currentTenant && $currentTenant->isTrial())
            @php $daysLeft = $currentTenant->daysLeftInTrial(); @endphp
            <div class="px-6 py-2 flex items-center justify-between text-sm {{ $daysLeft <= 3 ? 'bg-red-500 text-white' : 'bg-indigo-500 text-white' }}">
                <span>
                    <i class="fas fa-clock mr-2"></i>
                    ช่วงทดลองใช้งาน: เหลืออีก <strong>{{ $daysLeft }} วัน</strong>
                    @if($daysLeft <= 3) — กรุณาอัพเกรดแพ็กเกจเพื่อใช้งานต่อ @endif
                        </span>
                        <a href="{{ route('billing') }}" class="px-3 py-1 bg-white/20 rounded-lg text-xs font-medium hover:bg-white/30">
                            <i class="fas fa-crown mr-1"></i>อัพเกรดเลย
                        </a>
            </div>
            @endif
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                    class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-700 hover:text-green-900">&times;</button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show"
                    class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center justify-between">
                    <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-700 hover:text-red-900">&times;</button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')

    <script>
        // Universal Table Sorting
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('table').forEach(function(table) {
                var thead = table.querySelector('thead');
                var tbody = table.querySelector('tbody');
                if (!thead || !tbody) return;
                // Skip tables inside modals or forms that have dynamic rows
                if (table.closest('[x-ref]') && table.closest('[x-ref]').getAttribute('x-ref') === 'modalContent') return;

                var headers = thead.querySelectorAll('th');
                headers.forEach(function(th, colIdx) {
                    var text = th.textContent.trim();
                    // Skip columns: จัดการ (actions), empty headers, or headers with no text
                    if (!text || text === 'จัดการ' || text === '#' || th.querySelector('input[type=checkbox]')) return;

                    th.style.cursor = 'pointer';
                    th.style.userSelect = 'none';
                    th.style.position = 'relative';
                    th.style.paddingRight = '24px';

                    // Add sort indicator
                    var indicator = document.createElement('span');
                    indicator.className = 'sort-indicator';
                    indicator.style.cssText = 'position:absolute;right:6px;top:50%;transform:translateY(-50%);font-size:10px;color:#9ca3af;transition:color 0.15s;';
                    indicator.innerHTML = '&#x25B2;&#x25BC;';
                    th.appendChild(indicator);

                    th.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var rows = Array.from(tbody.querySelectorAll('tr'));
                        if (rows.length < 2) return;

                        // Determine current sort state
                        var asc = th.getAttribute('data-sort') !== 'asc';

                        // Reset all headers in this table
                        headers.forEach(function(h) {
                            h.removeAttribute('data-sort');
                            var si = h.querySelector('.sort-indicator');
                            if (si) {
                                si.innerHTML = '&#x25B2;&#x25BC;';
                                si.style.color = '#9ca3af';
                            }
                        });

                        th.setAttribute('data-sort', asc ? 'asc' : 'desc');
                        indicator.innerHTML = asc ? '&#x25B2;' : '&#x25BC;';
                        indicator.style.color = '#6366f1';

                        rows.sort(function(a, b) {
                            var cellA = a.children[colIdx];
                            var cellB = b.children[colIdx];
                            if (!cellA || !cellB) return 0;

                            var valA = (cellA.getAttribute('data-sort-value') || cellA.textContent).trim();
                            var valB = (cellB.getAttribute('data-sort-value') || cellB.textContent).trim();

                            // Try numeric (handles ฿1,234.56 / 1,234 etc)
                            var numA = parseFloat(valA.replace(/[^\d.\-]/g, ''));
                            var numB = parseFloat(valB.replace(/[^\d.\-]/g, ''));
                            if (!isNaN(numA) && !isNaN(numB) && valA !== '' && valB !== '') {
                                return asc ? numA - numB : numB - numA;
                            }

                            // Try date (dd/mm/yyyy)
                            var dateRegex = /^(\d{1,2})\/(\d{1,2})\/(\d{4})/;
                            var matchA = valA.match(dateRegex);
                            var matchB = valB.match(dateRegex);
                            if (matchA && matchB) {
                                var dA = new Date(matchA[3], matchA[2] - 1, matchA[1]);
                                var dB = new Date(matchB[3], matchB[2] - 1, matchB[1]);
                                return asc ? dA - dB : dB - dA;
                            }

                            // String compare
                            return asc ? valA.localeCompare(valB, 'th') : valB.localeCompare(valA, 'th');
                        });

                        rows.forEach(function(row) {
                            tbody.appendChild(row);
                        });
                    });

                    // Hover effect
                    th.addEventListener('mouseenter', function() {
                        if (!th.getAttribute('data-sort')) indicator.style.color = '#6b7280';
                    });
                    th.addEventListener('mouseleave', function() {
                        if (!th.getAttribute('data-sort')) indicator.style.color = '#9ca3af';
                    });
                });
            });
        });
    </script>
</body>

</html>