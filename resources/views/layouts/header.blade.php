<header
    class="sticky top-0 z-30 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 px-4 md:px-8 py-3 flex items-center justify-between">
    <!-- Hamburger & Search -->
    <div class="flex items-center gap-4 flex-1">
        <button onclick="toggleSidebar()"
            class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl text-slate-500 dark:text-slate-400 transition-colors">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <!-- Global Search -->
        <div class="relative w-full max-w-xl hidden sm:block">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
            </div>
            <input type="text" placeholder="Pencarian Global (Pelanggan, Invoice, Tiket, atau ODP)..."
                class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-2xl leading-5 bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all sm:text-sm">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <span
                    class="text-slate-400 text-xs font-semibold border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-0.5">Ctrl
                    + K</span>
            </div>
        </div>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-2 md:gap-4">
        <!-- Dark Mode Toggle -->
        <button onclick="toggleDarkMode()"
            class="p-2.5 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
            <i data-lucide="moon" id="dark-icon" class="w-5 h-5 hidden dark:block"></i>
            <i data-lucide="sun" id="light-icon" class="w-5 h-5 block dark:hidden"></i>
        </button>

        <!-- Notifications -->
        <div class="relative">
            <button onclick="toggleDropdown('notification-menu')"
                class="p-2.5 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl relative transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span
                    class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-800"></span>
            </button>

            <!-- Notification Dropdown -->
            <div id="notification-menu"
                class="hidden absolute right-0 mt-3 w-80 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl overflow-hidden glass-card z-50">
                <div
                    class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Notifikasi</h3>
                    <span
                        class="text-[10px] bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 font-bold px-2 py-0.5 rounded-full">3
                        Baru</span>
                </div>
                <div class="p-4 text-center text-sm text-slate-400">Belum ada notifikasi baru</div>
            </div>
        </div>

        <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 mx-1 hidden md:block"></div>

        <!-- Profile Dropdown -->
        <div class="relative">
            <button onclick="toggleDropdown('profile-menu')"
                class="flex items-center gap-3 p-1 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-2xl transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-600">
                <div class="w-9 h-9 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-600">
                    @if (Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar"
                            class="w-full h-full object-cover">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff"
                            alt="Avatar" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="hidden md:block text-left pr-2">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-none">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">{{ Auth::user()->email }}</p>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden md:block"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="profile-menu"
                class="hidden absolute right-0 mt-3 w-60 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl py-2 overflow-hidden glass-card z-50">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Login Sebagai</p>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
                        {{ Auth::user()->email }}
                    </p>
                </div>
                <div class="p-2">
                    <a href="{{ route('profile.edit') }}"
                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-all">
                        <i data-lucide="user" class="w-4 h-4"></i> Pengaturan Profil
                    </a>
                </div>
                <div class="p-2 border-t border-slate-100 dark:border-slate-700">
                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all font-semibold">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Keluar Aplikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>