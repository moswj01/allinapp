<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - SaaS Management</title>
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
    @stack('styles')
</head>

<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: true }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-gray-900 text-white transition-all duration-300 flex-shrink-0 flex flex-col">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center" x-show="sidebarOpen">
                        <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-crown text-white text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-base font-bold">Super Admin</h1>
                            <p class="text-xs text-gray-400">SaaS Management</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
                        <i :class="sidebarOpen ? 'fas fa-chevron-left' : 'fas fa-chevron-right'"></i>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="{{ route('superadmin.dashboard') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.dashboard') ? 'bg-amber-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span x-show="sidebarOpen">แดชบอร์ด</span>
                </a>
                <a href="{{ route('superadmin.tenants.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.tenants.*') ? 'bg-amber-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <i class="fas fa-store w-5 mr-3"></i>
                    <span x-show="sidebarOpen">จัดการร้านค้า</span>
                </a>
                <a href="{{ route('superadmin.plans.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.plans.*') ? 'bg-amber-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <i class="fas fa-box-open w-5 mr-3"></i>
                    <span x-show="sidebarOpen">จัดการแพ็กเกจ</span>
                </a>
                <a href="{{ route('superadmin.tenant-orders.index') }}" class="flex items-center px-4 py-3 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.tenant-orders.*') ? 'bg-amber-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <i class="fas fa-shopping-cart w-5 mr-3"></i>
                    <span x-show="sidebarOpen">ออเดอร์จากร้านค้า</span>
                </a>

                <div class="pt-3 border-t border-gray-800 mt-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left w-5 mr-3"></i>
                        <span x-show="sidebarOpen">กลับหน้าหลัก</span>
                    </a>
                </div>
            </nav>

            <!-- User -->
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center" x-show="sidebarOpen">
                    <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center text-sm font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-amber-400"><i class="fas fa-crown mr-1"></i>Super Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">@yield('page-title', 'Super Admin')</h2>
                </div>
                <div class="flex items-center gap-4">
                    @if(session('super_admin_id'))
                    <a href="{{ route('superadmin.switch-back') }}" class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-sm hover:bg-amber-200">
                        <i class="fas fa-user-shield mr-1"></i>กลับ Super Admin
                    </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-green-700">{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4 flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <span class="text-red-700">{{ session('error') }}</span>
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

            var headers = thead.querySelectorAll('th');
            headers.forEach(function(th, colIdx) {
                var text = th.textContent.trim();
                if (!text || text === 'จัดการ' || text === '#' || th.querySelector('input[type=checkbox]')) return;

                th.style.cursor = 'pointer';
                th.style.userSelect = 'none';
                th.style.position = 'relative';
                th.style.paddingRight = '24px';

                var indicator = document.createElement('span');
                indicator.className = 'sort-indicator';
                indicator.style.cssText = 'position:absolute;right:6px;top:50%;transform:translateY(-50%);font-size:10px;color:#9ca3af;transition:color 0.15s;';
                indicator.innerHTML = '&#x25B2;&#x25BC;';
                th.appendChild(indicator);

                th.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var rows = Array.from(tbody.querySelectorAll('tr'));
                    if (rows.length < 2) return;

                    var asc = th.getAttribute('data-sort') !== 'asc';

                    headers.forEach(function(h) {
                        h.removeAttribute('data-sort');
                        var si = h.querySelector('.sort-indicator');
                        if (si) { si.innerHTML = '&#x25B2;&#x25BC;'; si.style.color = '#9ca3af'; }
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

                        var numA = parseFloat(valA.replace(/[^\d.\-]/g, ''));
                        var numB = parseFloat(valB.replace(/[^\d.\-]/g, ''));
                        if (!isNaN(numA) && !isNaN(numB) && valA !== '' && valB !== '') {
                            return asc ? numA - numB : numB - numA;
                        }

                        var dateRegex = /^(\d{1,2})\/(\d{1,2})\/(\d{4})/;
                        var matchA = valA.match(dateRegex);
                        var matchB = valB.match(dateRegex);
                        if (matchA && matchB) {
                            var dA = new Date(matchA[3], matchA[2]-1, matchA[1]);
                            var dB = new Date(matchB[3], matchB[2]-1, matchB[1]);
                            return asc ? dA - dB : dB - dA;
                        }

                        return asc ? valA.localeCompare(valB, 'th') : valB.localeCompare(valA, 'th');
                    });

                    rows.forEach(function(row) { tbody.appendChild(row); });
                });

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