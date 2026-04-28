<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-bold uppercase tracking-[0.24em]">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Direct API
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-3">Zabbix Monitoring</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Monitoring bandwidth langsung dari Zabbix API tanpa penyimpanan ke database CRM.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Graph</p>
                        <p id="dynamicGraph" class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">-</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Updated</p>
                        <p id="updatedAt" class="text-sm font-mono text-slate-700 dark:text-slate-200 mt-1">-</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="statusBanner" class="hidden rounded-2xl border px-4 py-3 text-sm font-medium"></div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-4 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-5">Filter Monitor</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Group</label>
                            <select id="groupSelect"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500">
                                <option value="">Memuat group...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Host</label>
                            <select id="hostSelect"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500"
                                disabled>
                                <option value="">Pilih group terlebih dahulu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Graph</label>
                            <select id="graphSelect"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500"
                                disabled>
                                <option value="">Pilih host terlebih dahulu</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Item In</p>
                            <p id="metaItemIn" class="font-mono text-sm text-slate-700 dark:text-slate-200">-</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Item Out</p>
                            <p id="metaItemOut" class="font-mono text-sm text-slate-700 dark:text-slate-200">-</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Custom Range</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="date" id="dateFrom" max="{{ now()->format('Y-m-d') }}"
                                class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500">
                            <input type="date" id="dateTo" max="{{ now()->format('Y-m-d') }}"
                                class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button id="customApplyBtn"
                            class="mt-3 w-full bg-slate-900 dark:bg-blue-600 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors">
                            Tampilkan Range
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest">Live Refresh</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Perbarui chart otomatis untuk preset live.</p>
                        </div>
                        <div id="liveBadge" class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold">STATIC</div>
                    </div>
                    <div class="mt-5 flex items-center gap-3">
                        <select id="intervalSelect"
                            class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500">
                            <option value="30">30 detik</option>
                            <option value="60" selected>1 menit</option>
                            <option value="120">2 menit</option>
                            <option value="300">5 menit</option>
                        </select>
                        <button id="pauseBtn"
                            class="px-4 py-2.5 rounded-xl font-bold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Pause
                        </button>
                    </div>
                    <div class="mt-4 h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div id="progressFill" class="h-full w-0 bg-blue-600 rounded-full transition-all duration-100"></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs font-mono text-slate-500">
                        <span id="rangeInfo">-</span>
                        <span>Next: <span id="countdown">-</span></span>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-8 space-y-6">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="rounded-[1.75rem] bg-gradient-to-br from-sky-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Current IN</p>
                        <p id="s-curIn" class="mt-3 text-2xl font-mono font-bold text-sky-600 dark:text-sky-400">-</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-gradient-to-br from-sky-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Max IN</p>
                        <p id="s-maxIn" class="mt-3 text-2xl font-mono font-bold text-sky-600 dark:text-sky-400">-</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-gradient-to-br from-sky-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Avg IN</p>
                        <p id="s-avgIn" class="mt-3 text-2xl font-mono font-bold text-sky-600 dark:text-sky-400">-</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-gradient-to-br from-rose-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Current OUT</p>
                        <p id="s-curOut" class="mt-3 text-2xl font-mono font-bold text-rose-600 dark:text-rose-400">-</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-gradient-to-br from-rose-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Max OUT</p>
                        <p id="s-maxOut" class="mt-3 text-2xl font-mono font-bold text-rose-600 dark:text-rose-400">-</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-gradient-to-br from-rose-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Avg OUT</p>
                        <p id="s-avgOut" class="mt-3 text-2xl font-mono font-bold text-rose-600 dark:text-rose-400">-</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div>
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white">Bandwidth Usage</h4>
                            <p id="dynamicMeta" class="text-sm text-slate-500 dark:text-slate-400 mt-1">HOST: - | GRAPH: - | ITEM_IN: - | ITEM_OUT: -</p>
                        </div>
                        <div id="presetGroup" class="flex flex-wrap gap-2">
                            @foreach (['1h', '6h', '24h', '7d', '30d', '90d', '180d', '1y', '2y'] as $preset)
                                <button type="button" data-period="{{ $preset }}"
                                    class="preset-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $preset }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-2">
                        <button id="btnDragZoom" type="button"
                            class="zoom-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            Select Area
                        </button>
                        <button id="btnScrollZoom" type="button"
                            class="zoom-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            Scroll
                        </button>
                        <button id="btnPanMode" type="button"
                            class="zoom-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            Pan
                        </button>
                        <button id="zoomInBtn" type="button"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            +
                        </button>
                        <button id="zoomOutBtn" type="button"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            -
                        </button>
                        <button id="btnReset" type="button"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-red-400 hover:text-red-600 transition-colors">
                            Reset
                        </button>
                        <span id="dataModeBadge" class="hidden px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200"></span>
                    </div>

                    <div class="mt-6 relative">
                        <div id="chartOverlay" class="absolute inset-0 rounded-3xl bg-white/70 dark:bg-slate-900/70 hidden items-center justify-center z-10">
                            <div class="w-10 h-10 rounded-full border-4 border-slate-200 border-t-blue-600 animate-spin"></div>
                        </div>
                        <canvas id="bwChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
        <script>
            (function () {
                const endpoints = {
                    groups: '{{ route('zabbix-monitors.groups') }}',
                    hosts: '{{ route('zabbix-monitors.hosts') }}',
                    graphs: '{{ route('zabbix-monitors.graphs') }}',
                    chart: '{{ route('zabbix-monitors.chart-data') }}',
                };

                const presetButtons = Array.from(document.querySelectorAll('.preset-btn'));
                const groupSelect = document.getElementById('groupSelect');
                const hostSelect = document.getElementById('hostSelect');
                const graphSelect = document.getElementById('graphSelect');
                const statusBanner = document.getElementById('statusBanner');
                const overlay = document.getElementById('chartOverlay');
                const pauseBtn = document.getElementById('pauseBtn');
                const intervalSelect = document.getElementById('intervalSelect');
                const progressFill = document.getElementById('progressFill');
                const countdown = document.getElementById('countdown');
                const liveBadge = document.getElementById('liveBadge');

                let chart;
                let graphOptions = [];
                let currentPeriod = '24h';
                let currentMode = 'preset';
                let currentItems = { itemIn: '', itemOut: '' };
                let timer = null;
                let countdownTimer = null;
                let isPaused = false;

                function showBanner(message, type = 'error') {
                    statusBanner.textContent = message;
                    statusBanner.className = 'rounded-2xl border px-4 py-3 text-sm font-medium';
                    statusBanner.classList.remove('hidden');
                    if (type === 'error') {
                        statusBanner.classList.add('bg-red-50', 'border-red-200', 'text-red-700', 'dark:bg-red-900/20', 'dark:border-red-800', 'dark:text-red-300');
                    } else {
                        statusBanner.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700', 'dark:bg-blue-900/20', 'dark:border-blue-800', 'dark:text-blue-300');
                    }
                }

                function hideBanner() {
                    statusBanner.classList.add('hidden');
                }

                function setLoading(loading) {
                    overlay.classList.toggle('hidden', !loading);
                    overlay.classList.toggle('flex', loading);
                }

                function setSelectOptions(select, items, valueKey, labelKey, placeholder) {
                    select.innerHTML = `<option value="">${placeholder}</option>`;
                    items.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item[valueKey];
                        option.textContent = item[labelKey];
                        select.appendChild(option);
                    });
                }

                async function loadGroups() {
                    const response = await fetch(endpoints.groups, { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memuat group.');
                    }

                    setSelectOptions(groupSelect, data, 'groupid', 'name', 'Pilih group');
                }

                async function loadHosts(groupId) {
                    if (!groupId) {
                        setSelectOptions(hostSelect, [], 'hostid', 'name', 'Pilih group terlebih dahulu');
                        hostSelect.disabled = true;
                        return;
                    }

                    const url = new URL(endpoints.hosts);
                    url.searchParams.set('groupid', groupId);
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memuat host.');
                    }

                    setSelectOptions(hostSelect, data, 'hostid', 'name', 'Pilih host');
                    hostSelect.disabled = false;
                }

                async function loadGraphs(hostId) {
                    if (!hostId) {
                        setSelectOptions(graphSelect, [], 'graphid', 'name', 'Pilih host terlebih dahulu');
                        graphSelect.disabled = true;
                        return;
                    }

                    const url = new URL(endpoints.graphs);
                    url.searchParams.set('hostid', hostId);
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memuat graph.');
                    }

                    graphOptions = data;
                    setSelectOptions(graphSelect, data, 'graphid', 'name', data.length ? 'Pilih graph' : 'Tidak ada graph network');
                    graphSelect.disabled = false;
                }

                function applySelectedGraph() {
                    const selected = graphOptions.find(option => option.graphid === graphSelect.value);
                    if (!selected) {
                        currentItems = { itemIn: '', itemOut: '' };
                        document.getElementById('dynamicGraph').textContent = '-';
                        document.getElementById('metaItemIn').textContent = '-';
                        document.getElementById('metaItemOut').textContent = '-';
                        document.getElementById('dynamicMeta').textContent = 'HOST: - | GRAPH: - | ITEM_IN: - | ITEM_OUT: -';
                        return;
                    }

                    currentItems.itemIn = selected.itemIn;
                    currentItems.itemOut = selected.itemOut;
                    document.getElementById('dynamicGraph').textContent = selected.name;
                    document.getElementById('metaItemIn').textContent = selected.itemIn;
                    document.getElementById('metaItemOut').textContent = selected.itemOut;
                    document.getElementById('dynamicMeta').textContent = `HOST: ${hostSelect.options[hostSelect.selectedIndex]?.text || '-'} | GRAPH: ${selected.name} | ITEM_IN: ${selected.itemIn} | ITEM_OUT: ${selected.itemOut}`;
                }

                function createChart(payload) {
                    const ctx = document.getElementById('bwChart');
                    if (chart) {
                        chart.destroy();
                    }

                    chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: payload.labels,
                            datasets: [
                                {
                                    label: 'Download',
                                    data: payload.dataIn,
                                    borderColor: '#0284c7',
                                    backgroundColor: 'rgba(2,132,199,0.12)',
                                    fill: true,
                                    tension: 0.28,
                                    borderWidth: 2.5,
                                    pointRadius: 0,
                                    pointHoverRadius: 4
                                },
                                {
                                    label: 'Upload',
                                    data: payload.dataOut,
                                    borderColor: '#e11d48',
                                    backgroundColor: 'rgba(225,29,72,0.08)',
                                    fill: true,
                                    tension: 0.28,
                                    borderWidth: 2.5,
                                    pointRadius: 0,
                                    pointHoverRadius: 4
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#475569'
                                    }
                                },
                                zoom: {
                                    limits: { x: { min: 'original', max: 'original' } },
                                    pan: {
                                        enabled: false,
                                        mode: 'x'
                                    },
                                    zoom: {
                                        wheel: { enabled: false },
                                        pinch: { enabled: false },
                                        drag: {
                                            enabled: true,
                                            backgroundColor: 'rgba(37,99,235,0.12)',
                                            borderColor: 'rgba(37,99,235,0.45)',
                                            borderWidth: 1
                                        },
                                        mode: 'x'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b',
                                        maxRotation: 0
                                    },
                                    grid: {
                                        color: document.documentElement.classList.contains('dark') ? 'rgba(148,163,184,0.08)' : 'rgba(15,23,42,0.06)'
                                    }
                                },
                                y: {
                                    ticks: {
                                        color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b',
                                        callback: (value) => `${value} Mbps`
                                    },
                                    grid: {
                                        color: document.documentElement.classList.contains('dark') ? 'rgba(148,163,184,0.08)' : 'rgba(15,23,42,0.06)'
                                    }
                                }
                            }
                        }
                    });
                }

                function updateStat(id, value) {
                    document.getElementById(id).textContent = Number(value).toFixed(2);
                }

                function restartTimers(enableLive) {
                    clearInterval(timer);
                    clearInterval(countdownTimer);
                    progressFill.style.width = '0%';

                    if (!enableLive || isPaused) {
                        countdown.textContent = '-';
                        return;
                    }

                    const duration = Number(intervalSelect.value);
                    let elapsed = 0;
                    countdown.textContent = `${duration}s`;

                    countdownTimer = setInterval(() => {
                        if (isPaused) {
                            return;
                        }

                        elapsed += 0.1;
                        const remaining = Math.max(0, duration - elapsed);
                        countdown.textContent = `${remaining.toFixed(0)}s`;
                        progressFill.style.width = `${Math.min(100, (elapsed / duration) * 100)}%`;
                    }, 100);

                    timer = setInterval(() => {
                        if (!isPaused) {
                            fetchChartData();
                        }
                    }, duration * 1000);
                }

                function applyPayload(payload) {
                    hideBanner();
                    createChart(payload);
                    updateStat('s-curIn', payload.stats.curIn);
                    updateStat('s-maxIn', payload.stats.maxIn);
                    updateStat('s-avgIn', payload.stats.avgIn);
                    updateStat('s-curOut', payload.stats.curOut);
                    updateStat('s-maxOut', payload.stats.maxOut);
                    updateStat('s-avgOut', payload.stats.avgOut);
                    document.getElementById('updatedAt').textContent = payload.updatedAt;
                    document.getElementById('rangeInfo').textContent = `${payload.rangeLabel} | ${payload.points} titik`;
                    document.getElementById('dataModeBadge').textContent = payload.dataMode.toUpperCase();
                    document.getElementById('dataModeBadge').classList.remove('hidden');

                    const isLive = Boolean(payload.isLive) && currentMode === 'preset';
                    liveBadge.textContent = isLive ? 'LIVE' : 'STATIC';
                    liveBadge.className = isLive
                        ? 'inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-bold'
                        : 'inline-flex items-center px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold';

                    restartTimers(isLive);
                }

                async function fetchChartData() {
                    if (!currentItems.itemIn || !currentItems.itemOut) {
                        return;
                    }

                    setLoading(true);
                    try {
                        const url = new URL(endpoints.chart);
                        url.searchParams.set('itemin', currentItems.itemIn);
                        url.searchParams.set('itemout', currentItems.itemOut);
                        url.searchParams.set('mode', currentMode);

                        if (currentMode === 'custom') {
                            url.searchParams.set('from', document.getElementById('dateFrom').value);
                            url.searchParams.set('to', document.getElementById('dateTo').value);
                        } else {
                            url.searchParams.set('period', currentPeriod);
                        }

                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Gagal memuat data chart.');
                        }

                        applyPayload(data);
                    } catch (error) {
                        showBanner(error.message, 'error');
                        restartTimers(false);
                    } finally {
                        setLoading(false);
                    }
                }

                function setActivePreset(period) {
                    presetButtons.forEach(button => {
                        const active = button.dataset.period === period;
                        button.classList.toggle('bg-blue-600', active);
                        button.classList.toggle('text-white', active);
                        button.classList.toggle('border-blue-600', active);
                    });
                }

                function configureZoom(mode) {
                    if (!chart) {
                        return;
                    }

                    const zoomOptions = chart.options.plugins.zoom;
                    zoomOptions.pan.enabled = mode === 'pan';
                    zoomOptions.zoom.wheel.enabled = mode === 'scroll';
                    zoomOptions.zoom.pinch.enabled = mode === 'scroll';
                    zoomOptions.zoom.drag.enabled = mode === 'drag';
                    chart.update('none');

                    [['btnDragZoom', 'drag'], ['btnScrollZoom', 'scroll'], ['btnPanMode', 'pan']].forEach(([id, targetMode]) => {
                        const button = document.getElementById(id);
                        const active = mode === targetMode;
                        button.classList.toggle('bg-blue-600', active);
                        button.classList.toggle('text-white', active);
                        button.classList.toggle('border-blue-600', active);
                    });
                }

                groupSelect.addEventListener('change', async function () {
                    try {
                        await loadHosts(this.value);
                        graphOptions = [];
                        setSelectOptions(graphSelect, [], 'graphid', 'name', 'Pilih host terlebih dahulu');
                        graphSelect.disabled = true;
                        applySelectedGraph();
                    } catch (error) {
                        showBanner(error.message, 'error');
                    }
                });

                hostSelect.addEventListener('change', async function () {
                    try {
                        await loadGraphs(this.value);
                        applySelectedGraph();
                    } catch (error) {
                        showBanner(error.message, 'error');
                    }
                });

                graphSelect.addEventListener('change', function () {
                    applySelectedGraph();
                    fetchChartData();
                });

                presetButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        currentMode = 'preset';
                        currentPeriod = this.dataset.period;
                        setActivePreset(currentPeriod);
                        fetchChartData();
                    });
                });

                document.getElementById('customApplyBtn').addEventListener('click', function () {
                    const from = document.getElementById('dateFrom').value;
                    const to = document.getElementById('dateTo').value;
                    if (!from || !to) {
                        showBanner('Tanggal custom range wajib diisi.', 'error');
                        return;
                    }

                    currentMode = 'custom';
                    presetButtons.forEach(button => {
                        button.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                    });
                    fetchChartData();
                });

                intervalSelect.addEventListener('change', function () {
                    restartTimers(liveBadge.textContent === 'LIVE');
                });

                pauseBtn.addEventListener('click', function () {
                    isPaused = !isPaused;
                    pauseBtn.textContent = isPaused ? 'Resume' : 'Pause';
                    if (!isPaused) {
                        restartTimers(liveBadge.textContent === 'LIVE');
                    }
                });

                document.getElementById('zoomInBtn').addEventListener('click', () => chart?.zoom(1.15));
                document.getElementById('zoomOutBtn').addEventListener('click', () => chart?.zoom(0.9));
                document.getElementById('btnReset').addEventListener('click', () => chart?.resetZoom());
                document.getElementById('btnDragZoom').addEventListener('click', () => configureZoom('drag'));
                document.getElementById('btnScrollZoom').addEventListener('click', () => configureZoom('scroll'));
                document.getElementById('btnPanMode').addEventListener('click', () => configureZoom('pan'));

                async function boot() {
                    try {
                        setActivePreset(currentPeriod);
                        await loadGroups();
                        configureZoom('drag');
                    } catch (error) {
                        showBanner(error.message, 'error');
                    }
                }

                boot();
            })();
        </script>
    @endpush
</x-app-layout>
