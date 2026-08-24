<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <style>
        /* Fallback agar map tetap memiliki viewport stabil bila stylesheet CDN terlambat. */
        #operationalMap {
            position: relative;
            min-height: 600px;
            background: #e2e8f0;
        }

        #operationalMap.leaflet-container {
            overflow: hidden;
        }
    </style>
    @endpush

    <div class="space-y-6" x-data="operationalMap()" x-init="init()">
        <!-- Header + Summary -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Peta Operasional</h1>
                <p class="text-sm text-slate-500">Sebaran pelanggan & cabang — clustering, filter, dan popup ringkas.</p>
            </div>
            <div class="flex gap-2">
                <button @click="fitBounds()" class="px-4 py-2 bg-white dark:bg-slate-800 border rounded-xl text-sm font-bold">Fit Bounds</button>
                <button @click="locateMe()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold">Lokasi Saya</button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border"><div class="text-xs text-slate-400 uppercase font-bold">Total Pelanggan</div><div class="text-xl font-black" x-text="summary.total ?? '-'"></div></div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border"><div class="text-xs text-slate-400 uppercase font-bold">Terpetakan</div><div class="text-xl font-black text-emerald-600" x-text="summary.mapped ?? '-'"></div></div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border"><div class="text-xs text-slate-400 uppercase font-bold">Belum Koordinat</div><div class="text-xl font-black text-amber-600" x-text="summary.unmapped ?? '-'"></div></div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border"><div class="text-xs text-slate-400 uppercase font-bold">Cabang</div><div class="text-xl font-black" x-text="summary.total_branches ?? '-'"></div></div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border hidden lg:block"><div class="text-xs text-slate-400 uppercase font-bold">By Branch</div><div class="text-xs mt-1" x-text="(summary.by_branch||[]).slice(0,1).map(b=>b.branch_name+':'+b.count).join(', ')||'-'"></div></div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border hidden lg:block"><div class="text-xs text-slate-400 uppercase font-bold">By Status</div><div class="text-xs mt-1" x-text="(summary.by_status||[]).map(s=>s.status+':'+s.count).join(' ')||'-'"></div></div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border flex flex-wrap gap-3">
            <select x-model="filters.branch_id" @change="reload()" class="text-sm border rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-700">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select>
            <select x-model="filters.status" @change="reload()" class="text-sm border rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-700">
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
                <option value="prospect">Prospect</option>
            </select>
            <select x-model="filters.service_id" @change="reload()" class="text-sm border rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-700">
                <option value="">Semua Layanan</option>
                @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
            <select x-model="filters.mapped" @change="reload()" class="text-sm border rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-700">
                <option value="">Hanya Terpetakan</option>
                <option value="only">Terpetakan</option>
                <option value="unmapped">Belum Koordinat</option>
            </select>
            <input x-model="filters.q" @keydown.enter="reload()" placeholder="Cari nama/kode/kota" class="text-sm border rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-700 flex-1 min-w-[180px]">
            <button @click="reload()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold">Filter</button>
            <button @click="resetFilters()" class="px-4 py-2 border rounded-xl text-sm">Reset</button>
        </div>

        <!-- Map -->
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border shadow-sm overflow-hidden">
            <div id="operationalMap" class="w-full h-[600px]"></div>
            <div class="p-3 flex flex-wrap gap-3 text-xs text-slate-500 border-t">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span> Pelanggan</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-slate-800 dark:bg-slate-200 inline-block"></span> Cabang</span>
                <span>Attribution: © OpenStreetMap contributors</span>
                <span x-show="meta.count !== undefined" x-text="meta.count + ' pelanggan • ' + meta.branch_count + ' cabang'"></span>
            </div>
        </div>

        <!-- Empty state -->
        <div x-show="summary.mapped===0 && summary.total>0" class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-sm text-amber-700">Tidak ada pelanggan terpetakan untuk filter ini. Cek pelanggan tanpa koordinat via filter “Belum Koordinat”.</div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script>
        function operationalMap() {
            return {
                map: null,
                cluster: null,
                filters: { branch_id: '', status: '', service_id: '', mapped: '', q: '' },
                summary: {},
                meta: {},
                initializing: false,
                init() {
                    // Alpine dapat memanggil x-init lebih dari sekali saat DOM
                    // di-hydrate ulang. Leaflet hanya boleh menginisialisasi
                    // satu instance untuk satu container.
                    if (this.map || this.initializing) return;
                    this.initializing = true;
                    this.$nextTick(() => {
                        const container = document.getElementById('operationalMap');
                        if (!container || container._leaflet_id) {
                            this.initializing = false;
                            return;
                        }
                        const tileUrl = @json(config('maps.tile_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'));
                        const attribution = @json(config('maps.attribution', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'));
                        this.map = L.map(container).setView([-7.0, 110.0], 8);
                        L.tileLayer(tileUrl, { attribution: attribution, maxZoom: 18 }).addTo(this.map);
                        this.cluster = L.markerClusterGroup();
                        this.map.addLayer(this.cluster);
                        this.initializing = false;
                        this.reload();
                    });
                },
                async reload() {
                    // summary
                    try {
                        const sRes = await fetch(`{{ route('operational-map.summary') }}?` + new URLSearchParams(this.filters).toString(), {headers:{'Accept':'application/json'}});
                        if (sRes.ok) this.summary = await sRes.json();
                    } catch(e) {}
                    // locations
                    try {
                        const lRes = await fetch(`{{ route('operational-map.locations') }}?` + new URLSearchParams(this.filters).toString(), {headers:{'Accept':'application/json'}});
                        if (!lRes.ok) return;
                        const data = await lRes.json();
                        this.meta = data.meta || {};
                        this.renderMarkers(data.data || []);
                        if (data.meta?.bounds) {
                            const b = data.meta.bounds;
                            this.map.fitBounds([[b.minLat, b.minLng],[b.maxLat, b.maxLng]], {padding:[20,20]});
                        }
                    } catch(e) { console.error(e); }
                },
                renderMarkers(items) {
                    this.cluster.clearLayers();
                    items.forEach(it => {
                        if (it.latitude == null || it.longitude == null) return;
                        const isBranch = it.type === 'branch';
                        const icon = L.divIcon({
                            html: `<div style="background:${isBranch?'#1e293b':'#2563eb'};width:12px;height:12px;border-radius:9999px;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3)"></div>`,
                            className: '', iconSize: [12,12]
                        });
                        const m = L.marker([it.latitude, it.longitude], {icon});
                        const popup = `
                            <div style="min-width:180px">
                                <div style="font-weight:700">${this.esc(it.name)}</div>
                                <div style="font-size:11px;color:#64748b">${this.esc(it.client_code)} • ${this.esc(it.status)}</div>
                                <div style="font-size:12px;margin-top:4px">${this.esc(it.branch_name||'')} ${this.esc(it.city||'')}</div>
                                <div style="font-size:11px;color:#475569">${it.subscriptions_count||0} layanan • ${this.esc(it.service_name||'-')}</div>
                                ${it.type==='client' ? `<a href="/clients/${it.id}" style="display:inline-block;margin-top:8px;padding:6px 10px;background:#0f172a;color:white;border-radius:8px;font-size:12px;text-decoration:none">Lihat Detail Pelanggan</a>` : ''}
                            </div>`;
                        m.bindPopup(popup);
                        this.cluster.addLayer(m);
                    });
                },
                fitBounds() {
                    if (this.meta?.bounds) {
                        const b=this.meta.bounds;
                        this.map.fitBounds([[b.minLat,b.minLng],[b.maxLat,b.maxLng]],{padding:[20,20]});
                    }
                },
                locateMe() {
                    if (!navigator.geolocation) { alert('Geolocation tidak didukung'); return; }
                    navigator.geolocation.getCurrentPosition(pos => {
                        this.map.setView([pos.coords.latitude, pos.coords.longitude], 13);
                        L.marker([pos.coords.latitude,pos.coords.longitude]).addTo(this.map).bindPopup('Lokasi Anda').openPopup();
                    });
                },
                resetFilters(){ this.filters={branch_id:'',status:'',service_id:'',mapped:'',q:''}; this.reload(); },
                esc(s){ const d=document.createElement('div'); d.textContent=s??''; return d.innerHTML; }
            }
        }
    </script>
    @endpush
</x-app-layout>
