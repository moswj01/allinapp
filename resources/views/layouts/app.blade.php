<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'All In Mobile') - ระบบบริหารงานซ่อมและสต๊อก</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
                    <span class="text-xl font-bold">All In Mobile</span>
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
                <a href="{{ route('repairs.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('repairs.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-tools w-5 mr-3"></i>
                    งานซ่อม (Kanban)
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
                <a href="{{ route('stocks.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('stocks.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-warehouse w-5 mr-3"></i>
                    คลังสินค้า (Warehouse)
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
                <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('customers.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-users w-5 mr-3"></i>
                    ลูกค้า
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

                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">ตั้งค่า</p>
                </div>
                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    การตั้งค่าระบบ
                </a>
                <a href="{{ route('roles.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('roles.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700/50' }}">
                    <i class="fas fa-user-shield w-5 mr-3"></i>
                    จัดการสิทธิ์
                </a>
                @endif
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
                                <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                            </button>
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

            <!-- Page Content -->
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
</body>

</html>