<x-app-layout>
    @php
        $greeting = 'Halo, '.(Auth::user()->name ?? 'User').'! 👋';
        $periods = $prefs['widget_periods'] ?? [];
    @endphp
    <div x-data="dashboardCustom(@js($prefs), @js($registry))" x-init="init()" class="space-y-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-white">{{ $greeting }}</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Ringkasan operasional BMPnet — <span class="text-slate-400 text-sm">per {{ now()->format('d M Y H:i') }}</span></p>
            </div>
            <button @click="openCustomize()" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700">
                <i data-lucide="settings-2" class="w-4 h-4"></i> Kustomisasi
            </button>
        </div>

        <!-- Dashboard Grid (drag urutan) -->
        <div id="dashboard-grid" class="grid grid-cols-12 gap-6">
            @forelse($visible as $item)
                @php $id = $item['id']; $w = $registry[$id]['w'] ?? 3; $col = $w === 6 ? 'col-span-12 lg:col-span-6' : ($w === 12 ? 'col-span-12' : 'col-span-12 md:col-span-6 lg:col-span-3'); $s = $stats[$id] ?? ['empty'=>true]; @endphp

                {{-- Clients Count --}}
                @if($id === 'clients_count')
                @can('clients.view')
                <div data-id="clients_count" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="users" class="w-6 h-6"></i></div>
                        <span class="text-xs font-bold text-slate-400">Total</span>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Pelanggan</p>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400 mt-2">Belum ada data</p>
                    @else
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($s['total']) }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Aktif: {{ $s['by_status']['active'] ?? 0 }} • +{{ $s['recent_7d'] }} 7 hari</p>
                    @endif
                    <a href="{{ route('clients.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Lihat pelanggan →</a>
                </div>
                @endcan
                @endif

                {{-- Subscriptions Status --}}
                @if($id === 'subscriptions_status')
                @can('subscriptions.view')
                <div data-id="subscriptions_status" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm group">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Langganan per Status</p>
                        <i data-lucide="wifi" class="w-5 h-5 text-slate-400"></i>
                    </div>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400">Belum ada data</p>
                    @else
                        <div class="flex gap-2 flex-wrap">
                            @foreach($s['by_status'] as $st=>$c)
                                <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $st==='active'?'bg-emerald-100 text-emerald-700':'bg-slate-100 text-slate-600' }}">{{ $st }}: {{ $c }}</span>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Total {{ $s['total'] }}</p>
                    @endif
                    <a href="{{ route('subscriptions.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Lihat langganan →</a>
                </div>
                @endcan
                @endif

                {{-- Outstanding Invoice --}}
                @if($id === 'outstanding_invoice')
                @can('invoices.view')
                <div data-id="outstanding_invoice" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm group">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Outstanding Invoice</p>
                        <i data-lucide="file-text" class="w-5 h-5 text-amber-500"></i>
                    </div>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400">Belum ada data</p>
                    @else
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Rp {{ number_format($s['total'],0,',','.') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $s['count'] }} tagihan</p>
                        <div class="text-[10px] text-slate-400 mt-1">0-30: Rp{{ number_format($s['aging']['0-30']??0,0,',','.') }}</div>
                    @endif
                    <a href="{{ route('invoices.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Lihat invoice →</a>
                </div>
                @endcan
                @endif

                {{-- Revenue --}}
                @if($id === 'revenue')
                @can('financial_reports.view')
                <div data-id="revenue" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm group">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Revenue Bulan Ini</p>
                        <select x-model="widgetPeriods['revenue']" @change="changePeriod('revenue', $event.target.value)" class="text-xs border rounded-lg px-2 py-1 bg-slate-50 dark:bg-slate-700">
                            <option value="1M" :selected="widgetPeriods['revenue']==='1M'">Bulan ini</option>
                            <option value="30d" :selected="widgetPeriods['revenue']==='30d'">30 hari</option>
                        </select>
                    </div>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400">Belum ada data</p>
                    @else
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Rp {{ number_format($s['current'],0,',','.') }}</h3>
                        @if($s['pct'] !== null)
                            <p class="text-xs {{ $s['pct']>=0?'text-emerald-600':'text-red-600' }} mt-1">{{ $s['pct']>=0?'+':'' }}{{ $s['pct'] }}% vs bulan lalu</p>
                        @endif
                    @endif
                    <a href="{{ route('reports.financial.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Laporan keuangan →</a>
                </div>
                @endcan
                @endif

                {{-- Pending Payments --}}
                @if($id === 'pending_payments')
                @can('payments.verify')
                <div data-id="pending_payments" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Perlu Verifikasi</p>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400 mt-2">Belum ada data</p>
                    @else
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $s['count'] }} <span class="text-sm font-normal text-slate-400">pembayaran</span></h3>
                    @endif
                    <a href="{{ route('payments.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Verifikasi →</a>
                </div>
                @endcan
                @endif

                {{-- Due Invoices --}}
                @if($id === 'due_invoices')
                @can('invoices.view')
                <div data-id="due_invoices" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Jatuh Tempo 7 Hari</p>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400 mt-2">Belum ada data</p>
                    @else
                        <div class="mt-2 space-y-1">
                            @foreach($s['items'] as $inv)
                                <a href="{{ route('invoices.show', $inv['id']) }}" class="flex justify-between text-xs hover:bg-slate-50 dark:hover:bg-slate-700/50 p-1 rounded">
                                    <span>{{ $inv['invoice_number'] }}</span><span>{{ $inv['due_date'] ? \Carbon\Carbon::parse($inv['due_date'])->format('d M') : '-' }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endcan
                @endif

                {{-- Tickets Open --}}
                @if($id === 'tickets_open')
                @can('tickets.view')
                <div data-id="tickets_open" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tiket Terbuka</p>
                    @if($s['empty'])
                        <p class="text-sm text-slate-400 mt-2">Belum ada data</p>
                    @else
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $s['total'] }} <span class="text-sm font-normal text-slate-400">terbuka</span></h3>
                        <div class="flex gap-1 mt-2 flex-wrap">
                            @foreach($s['by_priority'] as $p=>$c)
                                <span class="text-xs px-2 py-0.5 rounded {{ $p==='high'||$p==='urgent'?'bg-red-100 text-red-700':'bg-slate-100' }}">{{ $p }}: {{ $c }}</span>
                            @endforeach
                        </div>
                    @endif
                    <a href="{{ route('tickets.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Lihat tiket →</a>
                </div>
                @endcan
                @endif

                {{-- Tickets Unresponded --}}
                @if($id === 'tickets_unresponded')
                @can('tickets.view')
                <div data-id="tickets_unresponded" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Belum Respon 24j</p>
                    @if($s['empty'])
                        <p class="text-sm text-emerald-600 mt-2">Semua sudah direspon</p>
                    @else
                        <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $s['count'] }}</h3>
                        <p class="text-xs text-slate-400">perlu respon</p>
                    @endif
                    <a href="{{ route('tickets.index') }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Lihat tiket →</a>
                </div>
                @endcan
                @endif

                {{-- Notifications --}}
                @if($id === 'notifications_unread' || $id === 'notifications_action')
                @can('notifications.view')
                <div data-id="{{ $id }}" class="{{ $col }} bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">{{ $id==='notifications_action'?'Perlu Tindakan':'Notifikasi Belum Dibaca' }}</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $id==='notifications_action'? ($stats['notifications']['action_required'] ?? 0) : ($stats['notifications']['unread'] ?? 0) }}</h3>
                    <a href="{{ route('notifications.index', $id==='notifications_action'?['filter'=>'action_required']:[]) }}" class="text-xs text-blue-600 hover:underline mt-3 inline-block">Buka inbox →</a>
                </div>
                @endcan
                @endif

            @empty
                <div class="col-span-12 p-8 text-center text-slate-400 bg-white dark:bg-slate-800 rounded-3xl border">Belum ada widget aktif. Klik Kustomisasi untuk mengaktifkan.</div>
            @endforelse

            {{-- Growth Chart --}}
            @if(collect($visible)->pluck('id')->contains('growth'))
            @can('clients.view')
            <div data-id="growth" class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-800 p-6 md:p-8 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-white">Pertumbuhan Pelanggan</h4>
                        <p class="text-xs text-slate-500">Per bulan</p>
                    </div>
                    <select @change="changePeriod('growth', $event.target.value)" class="text-xs border rounded-lg px-2 py-1 bg-slate-50 dark:bg-slate-700">
                        <option value="30d" :selected="(widgetPeriods['growth']||'30d')==='30d'">6 bulan</option>
                        <option value="7d" :selected="widgetPeriods['growth']==='7d'">2 bulan</option>
                        <option value="1y" :selected="widgetPeriods['growth']==='1y'">12 bulan</option>
                    </select>
                </div>
                @php $g = $stats['growth'] ?? ['labels'=>[],'data'=>[],'empty'=>true]; @endphp
                @if($g['empty'])
                    <p class="text-sm text-slate-400">Belum ada data</p>
                @else
                    <div class="h-[300px]"><canvas id="growthChart"></canvas></div>
                    <script type="application/json" id="growthData">@json($g)</script>
                @endif
            </div>
            @endcan
            @endif

            {{-- Recent Activity --}}
            @if(collect($visible)->pluck('id')->contains('recent_activity'))
            @can('logs.view')
            <div data-id="recent_activity" class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h4 class="font-bold text-slate-800 dark:text-white">Aktivitas Terakhir</h4>
                    <a href="{{ route('activity-logs.index') }}" class="text-sm font-bold text-blue-600 hover:underline">Lihat Log Lengkap</a>
                </div>
                @php $ra = $stats['recent_activity'] ?? ['items'=>[],'empty'=>true]; @endphp
                @if($ra['empty'])
                    <p class="p-6 text-sm text-slate-400">Belum ada data</p>
                @else
                    <div class="divide-y divide-slate-50 dark:divide-slate-700">
                        @foreach($ra['items'] as $log)
                            <div class="px-6 py-3 flex justify-between text-sm">
                                <div>
                                    <span class="font-semibold">{{ $log['causer']['name'] ?? 'System' }}</span>
                                    <span class="text-slate-500"> — {{ $log['description'] ?? $log['log_name'] }}</span>
                                </div>
                                <span class="text-slate-400 text-xs">{{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endcan
            @endif

            {{-- Additional list widgets --}}
            @if(collect($visible)->pluck('id')->contains('top_packages'))
            @can('packages.view')
            @php $tp = $stats['top_packages'] ?? ['items'=>[],'empty'=>true]; @endphp
            <div data-id="top_packages" class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-800 p-6 rounded-[2rem] border shadow-sm">
                <h4 class="font-bold text-slate-800 dark:text-white mb-3">Paket Terlaris Top 5</h4>
                @if($tp['empty'])
                    <p class="text-sm text-slate-400">Belum ada data</p>
                @else
                    <div class="space-y-2">
                        @foreach($tp['items'] as $pkg)
                            <div class="flex justify-between text-sm"><span>{{ $pkg['name'] }} <span class="text-slate-400">({{ $pkg['service']['name'] ?? '' }})</span></span><span class="font-bold">{{ $pkg['subscriptions_count'] }}</span></div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endcan
            @endif

        </div>

        <!-- Customize Modal -->
        <div x-show="customizeOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center" style="display:none;">
            <div @click="customizeOpen=false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-[2rem] p-6 w-full max-w-lg max-h-[80vh] overflow-y-auto border shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg">Kustomisasi Dashboard</h3>
                    <button @click="customizeOpen=false" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <p class="text-xs text-slate-400 mb-3">Centang untuk menampilkan, drag untuk ubah urutan.</p>
                <div id="customize-list" class="space-y-2">
                    <template x-for="(item, idx) in layout" :key="item.id">
                        <div class="flex items-center gap-3 p-3 border rounded-xl bg-slate-50 dark:bg-slate-700/30" :data-id="item.id">
                            <span class="cursor-move text-slate-400">≡</span>
                            <input type="checkbox" :checked="item.visible" @change="item.visible = $event.target.checked">
                            <span class="text-sm font-semibold flex-1" x-text="registry[item.id]?.title || item.id"></span>
                            <span class="text-xs text-slate-400" x-text="registry[item.id]?.group || ''"></span>
                        </div>
                    </template>
                </div>
                <div class="mt-6 flex gap-2">
                    <button @click="save()" :disabled="saving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold disabled:opacity-50" x-text="saving ? 'Menyimpan...' : 'Simpan'"></button>
                    <button @click="customizeOpen=false" class="px-4 py-2 border rounded-xl text-sm">Batal</button>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        function dashboardCustom(prefs, registry) {
            return {
                layout: prefs.layout || [],
                widgetPeriods: prefs.widget_periods || {},
                registry: registry,
                customizeOpen: false,
                saving: false,
                init() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        this.initCharts();
                        this.initSortable();
                    });
                },
                openCustomize() { this.customizeOpen = true; this.$nextTick(() => this.initSortable()); },
                initSortable() {
                    const el = document.getElementById('customize-list');
                    if (!el || el._sortable) return;
                    el._sortable = Sortable.create(el, {
                        animation: 150,
                        handle: '.cursor-move',
                        onEnd: (evt) => {
                            const ids = Array.from(el.children).map(c => c.dataset.id);
                            const map = Object.fromEntries(this.layout.map(i => [i.id, i]));
                            this.layout = ids.map(id => map[id]).filter(Boolean);
                        }
                    });
                    // dashboard-grid drag untuk urutan (optional — simpan juga)
                    const grid = document.getElementById('dashboard-grid');
                    if (grid && !grid._sortable) {
                        grid._sortable = Sortable.create(grid, {
                            animation: 150,
                            handle: '[data-id]',
                            onEnd: (evt) => {
                                const ids = Array.from(grid.querySelectorAll('[data-id]')).map(c => c.dataset.id);
                                const map = Object.fromEntries(this.layout.map(i => [i.id, i]));
                                // reorder layout sesuai DOM
                                const ordered = ids.map(id => map[id]).filter(Boolean);
                                // tambahkan yang hidden di akhir
                                const hidden = this.layout.filter(i => !ids.includes(i.id));
                                this.layout = [...ordered, ...hidden];
                                this.save();
                            }
                        });
                    }
                },
                async changePeriod(widget, period) {
                    this.widgetPeriods[widget] = period;
                    // fetch stats partial
                    try {
                        const res = await fetch(`{{ route('dashboard.stats') }}?widget=${widget}&period=${period}`, {headers:{'Accept':'application/json'}});
                        if (!res.ok) return;
                        const json = await res.json();
                        // untuk growth, update chart
                        if (widget === 'growth' && json.data) {
                            const g = json.data;
                            const el = document.getElementById('growthData');
                            if (el) el.textContent = JSON.stringify(g);
                            this.initCharts();
                        }
                    } catch(e) {}
                    this.save();
                },
                async save() {
                    this.saving = true;
                    try {
                        const res = await fetch(`{{ route('dashboard.preferences') }}`, {
                            method: 'PUT',
                            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept':'application/json'},
                            body: JSON.stringify({layout: this.layout, widget_periods: this.widgetPeriods})
                        });
                        if (res.ok) {
                            localStorage.setItem('dashboard:layout:{{ Auth::id() }}', JSON.stringify({layout: this.layout, widget_periods: this.widgetPeriods}));
                            location.reload();
                        }
                    } finally { this.saving = false; }
                },
                initCharts() {
                    const gEl = document.getElementById('growthChart');
                    const gDataEl = document.getElementById('growthData');
                    if (gEl && gDataEl && typeof Chart !== 'undefined') {
                        try {
                            const g = JSON.parse(gDataEl.textContent);
                            if (g.labels && g.data) {
                                const ctx = gEl.getContext('2d');
                                if (gEl._chart) gEl._chart.destroy();
                                gEl._chart = new Chart(ctx, {
                                    type: 'line',
                                    data: { labels: g.labels, datasets: [{ label: 'Pelanggan Baru', data: g.data, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', tension: 0.4, fill: true }]},
                                    options: { responsive: true, maintainAspectRatio: false }
                                });
                            }
                        } catch(e) {}
                    }
                }
            }
        }
    </script>
</x-app-layout>