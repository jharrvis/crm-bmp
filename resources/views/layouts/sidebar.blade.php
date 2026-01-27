<nav class="flex-1 px-4 space-y-1 overflow-y-auto overflow-x-hidden">
    <a href="{{ route('dashboard') }}"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all group whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
        <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0 transition-colors"></i>
        <span class="font-medium text-sm menu-text transition-opacity duration-200">Dashboard Utama</span>
    </a>

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
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 hidden">
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
        <div class="submenu-content flex flex-col pl-12 lg:group-hover:pl-2 space-y-1 mt-1 hidden">
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