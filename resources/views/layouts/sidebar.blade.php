<nav class="flex-1 px-4 space-y-1 overflow-y-auto overflow-x-hidden">
    <a href="{{ route('dashboard') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Dashboard Utama</span>
    </a>

    @role('Owner|Admin')
    <div class="pt-4 pb-2 menu-text">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Master Data</p>
    </div>

    <!-- Submenu: Organisasi -->
    @php
        $isOrgActive = request()->routeIs('branches.*') || request()->routeIs('divisions.*') || request()->routeIs('employees.*');
    @endphp
    <div class="submenu-container {{ $isOrgActive ? 'submenu-active' : '' }}" id="menu-organisasi"
        data-menu-title="Organisasi">
        <button onclick="toggleSubmenu('menu-organisasi')"
            class="w-full flex items-center justify-between px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all whitespace-nowrap group {{ $isOrgActive ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
            <div class="flex items-center gap-3">
                <i data-lucide="building-2" class="w-5 h-5 shrink-0 transition-colors"></i>
                <span class="font-medium text-sm menu-text">Organisasi</span>
            </div>
            <i data-lucide="chevron-down"
                class="chevron-icon w-4 h-4 transition-transform duration-200 {{ $isOrgActive ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 overflow-hidden transition-all duration-300 {{ $isOrgActive ? 'submenu-open' : '' }}"
            style="{{ $isOrgActive ? 'max-height: 500px;' : 'max-height: 0;' }}">
            <a href="{{ route('branches.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('branches.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Kantor Cabang
            </a>
            <a href="{{ route('divisions.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('divisions.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Divisi
            </a>
            <a href="{{ route('employees.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('employees.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Karyawan
            </a>
        </div>
    </div>
    @endrole

    @role('Owner|Admin|Employee')
    <!-- Submenu: Layanan -->
    <div class="submenu-container" id="menu-layanan" data-menu-title="Layanan & NOC">
        <button onclick="toggleSubmenu('menu-layanan')"
            class="w-full flex items-center justify-between px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all whitespace-nowrap group">
            <div class="flex items-center gap-3">
                <i data-lucide="globe" class="w-5 h-5 shrink-0 transition-colors"></i>
                <span class="font-medium text-sm menu-text">Layanan & NOC</span>
            </div>
            <i data-lucide="chevron-down" class="chevron-icon w-4 h-4 transition-transform duration-200"></i>
        </button>
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 overflow-hidden transition-all duration-300"
            style="max-height: 0;">
            <a href="#"
                class="text-sm py-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50">Monitoring
                OLT</a>
            <a href="#"
                class="text-sm py-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50">Manajemen
                ODP</a>
            <a href="#"
                class="text-sm py-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50">Bandwidth
                Usage</a>
        </div>
    </div>

    <!-- Submenu: Pelanggan -->
    <div class="submenu-container" id="menu-pelanggan" data-menu-title="Manajemen Pelanggan">
        <button onclick="toggleSubmenu('menu-pelanggan')"
            class="w-full flex items-center justify-between px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all whitespace-nowrap group">
            <div class="flex items-center gap-3">
                <i data-lucide="users" class="w-5 h-5 shrink-0 transition-colors"></i>
                <span class="font-medium text-sm menu-text">Pelanggan</span>
            </div>
            <i data-lucide="chevron-down" class="chevron-icon w-4 h-4 transition-transform duration-200"></i>
        </button>
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 overflow-hidden transition-all duration-300"
            style="max-height: 0;">
            <a href="#"
                class="text-sm py-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50">Pelanggan
                Aktif</a>
            <a href="#"
                class="text-sm py-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50">Registrasi
                Baru</a>
            <a href="#"
                class="text-sm py-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50">Isolir
                & Terminasi</a>
        </div>
    </div>

    <a href="#"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap">
        <i data-lucide="ticket" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text">Troubleshoot</span>
        <span
            class="badge ml-auto bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full">12</span>
    </a>

    <div class="pt-6 pb-2 menu-text">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Finance & Billing</p>
    </div>

    <a href="#"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap">
        <i data-lucide="receipt" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text">Invoicing</span>
    </a>
    @endrole

    @role('Client')
    <div class="pt-6 pb-2 menu-text">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menu Pelanggan</p>
    </div>
    <a href="#"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap">
        <i data-lucide="package" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text">Langganan Saya</span>
    </a>
    <a href="#"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap">
        <i data-lucide="credit-card" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text">Tagihan & Bayar</span>
    </a>
    @endrole
</nav>