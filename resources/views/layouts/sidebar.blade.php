<nav class="flex-1 px-4 space-y-1 overflow-y-auto overflow-x-hidden">
    <a href="{{ route('dashboard') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Dashboard Utama</span>
    </a>

    {{-- CORE BUSINESS GROUP (Admin & Employee) --}}
    @role('Owner|Admin|Employee')
    <div class="pt-4 pb-2 menu-text">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Operasional</p>
    </div>

    <!-- Data Pelanggan -->
    <a href="{{ route('clients.index') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('clients.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="users" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Data Pelanggan</span>
    </a>

    <!-- Layanan Aktif (Subscriptions) -->
    <a href="{{ route('subscriptions.index') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('subscriptions.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="zap" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Layanan Aktif</span>
    </a>

    <!-- Tagihan (Invoices) -->
    <a href="{{ route('invoices.index') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('invoices.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="receipt" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Tagihan</span>
    </a>

    <!-- Produk & Layanan -->
    <div class="pt-4 pb-2 menu-text">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Produk & Layanan</p>
    </div>

    <!-- Submenu: Produk & Paket -->
    @php
        $isProductActive = request()->routeIs('services.*') || request()->routeIs('packages.*');
    @endphp
    <div class="submenu-container {{ $isProductActive ? 'submenu-active' : '' }}" id="menu-produk"
        data-menu-title="Produk">
        <button onclick="toggleSubmenu('menu-produk')"
            class="w-full flex items-center justify-between px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all whitespace-nowrap group {{ $isProductActive ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
            <div class="flex items-center gap-3">
                <i data-lucide="box" class="w-5 h-5 shrink-0 transition-colors"></i>
                <span class="font-medium text-sm menu-text">Katalog Layanan</span>
            </div>
            <i data-lucide="chevron-down"
                class="chevron-icon w-4 h-4 transition-transform duration-200 {{ $isProductActive ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 overflow-hidden transition-all duration-300 {{ $isProductActive ? 'submenu-open' : '' }}"
            style="{{ $isProductActive ? 'max-height: 500px;' : 'max-height: 0;' }}">
            <a href="{{ route('services.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('services.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Daftar Layanan
            </a>
            <a href="{{ route('packages.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('packages.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Daftar Paket
            </a>
        </div>
    </div>
    @endrole

    {{-- MASTER DATA GROUP (Owner & Admin) --}}
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

    <!-- Submenu: Infrastruktur -->
    @php
        $isInfraActive = request()->routeIs('routers.*') || request()->routeIs('servers.*');
    @endphp
    <div class="submenu-container {{ $isInfraActive ? 'submenu-active' : '' }}" id="menu-infrastruktur"
        data-menu-title="Infrastruktur">
        <button onclick="toggleSubmenu('menu-infrastruktur')"
            class="w-full flex items-center justify-between px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all whitespace-nowrap group {{ $isInfraActive ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
            <div class="flex items-center gap-3">
                <i data-lucide="server" class="w-5 h-5 shrink-0 transition-colors"></i>
                <span class="font-medium text-sm menu-text">Infrastruktur</span>
            </div>
            <i data-lucide="chevron-down"
                class="chevron-icon w-4 h-4 transition-transform duration-200 {{ $isInfraActive ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 overflow-hidden transition-all duration-300 {{ $isInfraActive ? 'submenu-open' : '' }}"
            style="{{ $isInfraActive ? 'max-height: 500px;' : 'max-height: 0;' }}">
            <a href="{{ route('routers.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('routers.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Router MikroTik
            </a>
            <a href="{{ route('servers.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('servers.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Server Hosting
            </a>
            <a href="{{ route('vendors.index') }}"
                class="text-sm py-2 hover:text-blue-600 dark:hover:text-blue-400 text-left transition-colors px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ request()->routeIs('vendors.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                Vendor
            </a>
        </div>
    </div>

    <!-- Manajemen Role -->
    <a href="{{ route('roles.index') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('roles.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="shield" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Manajemen Role</span>
    </a>
    @endrole

    {{-- CLIENT GROUP (Future Phase) --}}
    @role('Client')
    <!-- Placeholder for client menu -->
    @endrole
</nav>