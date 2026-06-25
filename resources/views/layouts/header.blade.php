<header
    class="sticky top-0 z-30 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 px-4 md:px-8 py-3 flex items-center justify-between">
    <!-- Hamburger & Search -->
    <div class="flex items-center gap-4 flex-1">
        <button onclick="toggleSidebar()"
            class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl text-slate-500 dark:text-slate-400 transition-colors">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- Global Search (Alpine.js) -->
        <div
            x-data="globalSearch()"
            x-init="init()"
            class="relative w-full max-w-xl hidden sm:block"
            @keydown.escape.window="close()"
            @keydown.ctrl.k.window.prevent="open()"
            @keydown.meta.k.window.prevent="open()"
            @click.outside="close()"
        >
            <!-- Search Input Trigger -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                </div>
                <input
                    id="global-search-input"
                    type="text"
                    x-model="query"
                    @input.debounce.300ms="search()"
                    @focus="open()"
                    @keydown.arrow-down.prevent="focusNext()"
                    @keydown.arrow-up.prevent="focusPrev()"
                    @keydown.enter.prevent="goToFocused()"
                    placeholder="Pencarian Global... (Ctrl+K)"
                    class="block w-full pl-10 pr-16 py-2.5 border border-slate-200 dark:border-slate-600 rounded-2xl leading-5 bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all sm:text-sm cursor-pointer"
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                    <!-- Loading Spinner -->
                    <svg x-show="loading" class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <!-- Kbd shortcut hint -->
                    <span x-show="!loading && !isOpen" class="text-slate-400 text-xs font-semibold border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-0.5">Ctrl K</span>
                    <!-- Clear button -->
                    <button x-show="query && !loading" @click="clearSearch()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Results Dropdown -->
            <div
                x-show="isOpen && (results.length > 0 || (query.length >= 2 && !loading))"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden z-50"
                style="display: none;"
            >
                <!-- No results -->
                <div x-show="results.length === 0 && query.length >= 2 && !loading" class="px-4 py-8 text-center">
                    <i data-lucide="search-x" class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2"></i>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Tidak ada hasil untuk <strong x-text="'&quot;' + query + '&quot;'"></strong></p>
                </div>

                <!-- Grouped Results -->
                <div x-show="results.length > 0" class="py-2 max-h-[420px] overflow-y-auto">
                    <template x-for="(group, gIdx) in results" :key="gIdx">
                        <div>
                            <!-- Group Header -->
                            <div class="px-4 py-2 flex items-center gap-2">
                                <i :data-lucide="group.icon" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400" x-text="group.group"></span>
                            </div>

                            <!-- Group Items -->
                            <template x-for="(item, iIdx) in group.items" :key="item.id">
                                <a
                                    :href="item.url"
                                    :data-search-index="getFlatIndex(gIdx, iIdx)"
                                    @mouseenter="focusedIndex = getFlatIndex(gIdx, iIdx)"
                                    class="flex items-center justify-between px-4 py-2.5 mx-2 rounded-xl transition-colors group cursor-pointer"
                                    :class="focusedIndex === getFlatIndex(gIdx, iIdx) ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                >
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="item.title"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5" x-text="item.subtitle"></p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3 shrink-0">
                                        <span
                                            x-show="item.badge"
                                            x-text="item.badge"
                                            class="text-[10px] font-bold px-2 py-0.5 rounded-full capitalize"
                                            :class="getBadgeClass(item.badge)"
                                        ></span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-300 group-hover:text-blue-500 transition-colors"></i>
                                    </div>
                                </a>
                            </template>

                            <!-- Divider between groups -->
                            <div x-show="gIdx < results.length - 1" class="mx-4 my-1 border-t border-slate-100 dark:border-slate-700"></div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div x-show="results.length > 0" class="px-4 py-2 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 text-[10px] text-slate-400">
                        <span>
                            <span x-text="total"></span> hasil cepat ditampilkan
                        </span>
                        <a :href="resultsUrl()"
                            class="font-semibold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Lihat semua hasil
                        </a>
                    </div>
                    <div class="flex items-center gap-3 text-[10px] text-slate-400">
                        <span><kbd class="font-mono bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-600">↑↓</kbd> navigasi</span>
                        <span><kbd class="font-mono bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-600">Enter</kbd> buka</span>
                        <span><kbd class="font-mono bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-600">Esc</kbd> tutup</span>
                    </div>
                </div>
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

<script>
function globalSearch() {
    return {
        query: '',
        results: [],
        total: 0,
        isOpen: false,
        loading: false,
        focusedIndex: -1,
        _flatItems: [],

        init() {
            // Re-initialize lucide icons after Alpine updates the DOM
            this.$watch('results', () => {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            });
        },

        open() {
            this.isOpen = true;
            this.$nextTick(() => {
                document.getElementById('global-search-input')?.focus();
            });
        },

        close() {
            this.isOpen = false;
            this.focusedIndex = -1;
        },

        clearSearch() {
            this.query = '';
            this.results = [];
            this.total = 0;
            this._flatItems = [];
            this.focusedIndex = -1;
            document.getElementById('global-search-input')?.focus();
        },

        async search() {
            if (this.query.length < 2) {
                this.results = [];
                this.total = 0;
                this._flatItems = [];
                this.focusedIndex = -1;
                return;
            }

            this.loading = true;
            this.isOpen = true;

            try {
                const url = new URL('{{ route("search") }}', window.location.origin);
                url.searchParams.set('q', this.query);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Search failed');

                const data = await response.json();
                this.results = data.results || [];
                this.total = data.total || 0;
                this.focusedIndex = -1;

                // Build flat list for keyboard navigation
                this._flatItems = [];
                this.results.forEach(group => {
                    group.items.forEach(item => {
                        this._flatItems.push(item);
                    });
                });
            } catch (e) {
                console.error('Global search error:', e);
            } finally {
                this.loading = false;
            }
        },

        getFlatIndex(groupIndex, itemIndex) {
            let idx = 0;
            for (let g = 0; g < groupIndex; g++) {
                idx += this.results[g].items.length;
            }
            return idx + itemIndex;
        },

        focusNext() {
            if (this._flatItems.length === 0) return;
            this.focusedIndex = (this.focusedIndex + 1) % this._flatItems.length;
        },

        focusPrev() {
            if (this._flatItems.length === 0) return;
            this.focusedIndex = this.focusedIndex <= 0
                ? this._flatItems.length - 1
                : this.focusedIndex - 1;
        },

        goToFocused() {
            if (this.focusedIndex >= 0 && this._flatItems[this.focusedIndex]) {
                window.location.href = this._flatItems[this.focusedIndex].url;
                return;
            }

            if (this.query.length >= 2) {
                window.location.href = this.resultsUrl();
            }
        },

        resultsUrl() {
            const url = new URL('{{ route("search.results") }}', window.location.origin);
            url.searchParams.set('q', this.query);
            return url.toString();
        },

        getBadgeClass(badge) {
            const map = {
                // Client status
                active:     'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                inactive:   'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                // Invoice status
                paid:       'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                unpaid:     'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                overdue:    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                // Ticket status
                open:       'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                resolved:   'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                closed:     'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                pending:    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                // Subscription status
                suspended:  'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400',
                terminated: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            };
            return map[(badge || '').toLowerCase()] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
        },
    };
}
</script>
