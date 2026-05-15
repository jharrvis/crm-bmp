<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('subscriptions.index') }}"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        <i data-lucide="arrow-left" class="w-5 h-5 text-slate-500"></i>
                    </a>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Detail Layanan</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-mono">
                            {{ $subscription->subscription_code }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $statusStyles = [
                            'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'terminated' => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400'
                        ];
                        $statusLabels = [
                            'active' => 'Aktif',
                            'pending' => 'Pending',
                            'suspended' => 'Suspend',
                            'terminated' => 'Berhenti'
                        ];
                    @endphp
                    <span
                        class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold {{ $statusStyles[$subscription->status] ?? $statusStyles['pending'] }}">
                        {{ $statusLabels[$subscription->status] ?? $subscription->status }}
                    </span>
                    <button onclick="window.editData({{ $subscription->id }})"
                        class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-yellow-200 dark:shadow-none transition-all">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        <span>Edit</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-2">
            <div class="flex gap-2">
                <button type="button" id="tabInfo" onclick="switchTab('info')"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Informasi</span>
                </button>
                @if($subscription->connectivity)
                    <button type="button" id="tabTeknis" onclick="switchTab('teknis')"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="wifi" class="w-4 h-4"></i>
                        <span>Detail Koneksi</span>
                    </button>
                    @if(!empty($subscription->connectivity->zabbix_interfaces))
                        <button type="button" id="tabMonitoring" onclick="switchTab('monitoring')"
                            class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                            <span>Monitoring</span>
                        </button>
                    @endif
                    <button type="button" id="tabTopologi" onclick="switchTab('topologi')"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="git-branch" class="w-4 h-4"></i>
                        <span>Topologi Jaringan</span>
                    </button>
                @endif
                @if($subscription->hosting)
                    <button type="button" id="tabHosting" onclick="switchTab('hosting')"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="server" class="w-4 h-4"></i>
                        <span>Hosting</span>
                    </button>
                @endif
                @if($subscription->domain)
                    <button type="button" id="tabDomain" onclick="switchTab('domain')"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="globe" class="w-4 h-4"></i>
                        <span>Domain</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Tab Content -->
        <!-- Info Tab -->
        <div id="panelInfo" class="tab-panel">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer Info Card -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4
                            class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            Data Pelanggan
                        </h4>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl">
                                {{ strtoupper(substr($subscription->client->name ?? 'C', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <h5 class="text-lg font-bold text-slate-800 dark:text-white">
                                    {{ $subscription->client->name ?? '-' }}
                                </h5>
                                <p class="text-sm text-slate-500 font-mono">
                                    {{ $subscription->client->client_code ?? '-' }}
                                </p>
                                @if($subscription->client->address)
                                    <p class="text-sm text-slate-500 mt-2">{{ $subscription->client->address }}</p>
                                @endif
                            </div>
                            <button type="button" onclick="openClientModal()"
                                class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                                Lihat Profil <i data-lucide="user-circle" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Package & Service Info -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4
                            class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="package" class="w-4 h-4"></i>
                            Paket Layanan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Layanan</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->package->service->name ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Paket</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->package->name ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tanggal Pasang</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->installed_at ? $subscription->installed_at->format('d M Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tanggal Billing
                                    Berikutnya
                                </p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->next_billing_date ? $subscription->next_billing_date->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($subscription->notes)
                        <div
                            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                            <h4
                                class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                Catatan
                            </h4>
                            <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $subscription->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Right Column: Billing Info -->
                <div class="space-y-6">
                    <!-- Pricing Card -->
                    <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-[2rem] shadow-lg p-6 text-white">
                        <h4 class="text-sm font-bold uppercase tracking-widest mb-4 opacity-80">Informasi Harga</h4>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Harga Paket</p>
                                <p class="text-lg font-mono">Rp
                                    {{ number_format($subscription->package->price ?? 0, 0, ',', '.') }}
                                </p>
                            </div>

                            @if($subscription->custom_price)
                                <div class="pt-3 border-t border-white/20">
                                    <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Harga Khusus (Deal)</p>
                                    <p class="text-2xl font-bold font-mono">Rp
                                        {{ number_format($subscription->custom_price, 0, ',', '.') }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <p class="text-xs opacity-70 uppercase tracking-wider mb-1">PPN 11%</p>
                                <p class="text-lg font-bold">
                                    {{ $subscription->uses_ppn ? 'Rp ' . number_format((float) $subscription->ppn_amount, 0, ',', '.') : 'Tidak digunakan' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs opacity-70 uppercase tracking-wider mb-1">PPh23</p>
                                <p class="text-lg font-bold">
                                    {{ $subscription->uses_pph23 ? 'Rp ' . number_format((float) $subscription->pph23_amount, 0, ',', '.') : 'Tidak digunakan' }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-white/20">
                                <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Total Tagihan</p>
                                <p class="text-2xl font-bold font-mono">Rp
                                    {{ number_format($subscription->effective_price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Cycle -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4
                            class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            Siklus Billing
                        </h4>
                        @php
                            $cycleLabels = [
                                1 => 'Bulanan',
                                3 => 'Triwulan',
                                6 => 'Semester',
                                12 => 'Tahunan'
                            ];
                        @endphp
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Periode</p>
                                <p class="text-slate-800 dark:text-white font-bold text-lg">
                                    {{ $cycleLabels[$subscription->billing_period_months] ?? ($subscription->billing_period_months . ' Bulan') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Hari Tagihan</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    Tanggal {{ $subscription->billing_cycle_day ?? '-' }} setiap bulan
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4
                            class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                            Aksi Cepat
                        </h4>
                        <div class="space-y-3">
                            <a href="#"
                                class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                <i data-lucide="file-text" class="w-5 h-5 text-blue-500"></i>
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Buat Invoice</span>
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                <i data-lucide="ticket" class="w-5 h-5 text-orange-500"></i>
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Buat Tiket</span>
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                <i data-lucide="history" class="w-5 h-5 text-purple-500"></i>
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Riwayat Pembayaran</span>
                            </a>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] p-6">
                        <div class="space-y-2 text-sm text-slate-500">
                            <p>Dibuat: {{ $subscription->created_at?->format('d M Y H:i') }}</p>
                            <p>Diupdate: {{ $subscription->updated_at?->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teknis Tab (Connectivity Details) -->
        @if($subscription->connectivity)
                <div id="panelTeknis" class="tab-panel hidden">
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <i data-lucide="wifi" class="w-4 h-4"></i>
                            Detail Koneksi Internet
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">IP Address</p>
                                <p class="text-slate-800 dark:text-white font-mono text-lg">
                                    {{ $subscription->connectivity->ip_address ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">PPPoE Username</p>
                                <p class="text-slate-800 dark:text-white font-mono text-lg">
                                    {{ $subscription->connectivity->pppoe_user ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Router</p>
                                <p class="text-slate-800 dark:text-white font-medium text-lg">
                                    {{ $subscription->connectivity->router->name ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Modem SN (ONT)</p>
                                <p class="text-slate-800 dark:text-white font-mono text-lg">
                                    {{ $subscription->connectivity->ont_sn ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">VLAN ID</p>
                                <p class="text-slate-800 dark:text-white font-mono text-lg">
                                    {{ $subscription->connectivity->vlan_id ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 md:col-span-2 lg:col-span-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-3">Monitoring Zabbix</p>
                                @if($subscription->connectivity->zabbix_host_name)
                                    <div class="space-y-3">
                                        <p class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ $subscription->connectivity->zabbix_group_name ?: '-' }} / {{ $subscription->connectivity->zabbix_host_name }}
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            @forelse(($subscription->connectivity->zabbix_interfaces ?? []) as $interface)
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-semibold">
                                                    {{ $interface['name'] ?? '-' }}
                                                </span>
                                            @empty
                                                <span class="text-sm text-slate-500 dark:text-slate-400">Belum ada interface dipilih.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada konfigurasi interface Zabbix.</p>
                                @endif
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Signal RX</p>
                                <p class="text-slate-800 dark:text-white font-mono text-lg">
                                    {{ $subscription->connectivity->signal_rx ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    <!-- Metro Ethernet Details (If Available) -->
    @if($subscription->connectivity && $subscription->connectivity->metroEthernet)
        <div
            class="mt-6 bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i data-lucide="network" class="w-4 h-4 text-blue-500"></i>
                Detail Metro Ethernet
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Nama Metro Ethernet</p>
                    <p class="font-bold text-slate-800 dark:text-white text-lg">
                        {{ $subscription->connectivity->metroEthernet->display_name }}
                    </p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                    <p class="text-xs font-bold text-blue-500 uppercase mb-1">Vendor Backbone</p>
                    <p class="font-bold text-slate-800 dark:text-white text-lg">
                        {{ $subscription->connectivity->metroEthernet->vendor->name ?? 'Unknown Vendor' }}
                    </p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Circuit ID (CID)</p>
                    <p class="font-mono text-slate-800 dark:text-white text-lg">
                        {{ $subscription->connectivity->metroEthernet->cid ?? '-' }}
                    </p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">IP Address (Metro)</p>
                    <p class="font-mono text-slate-800 dark:text-white text-lg">
                        {{ $subscription->connectivity->metroEthernet->ip_address ?? '-' }}
                    </p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Kapasitas Bandwidth</p>
                    <p class="font-mono text-slate-800 dark:text-white text-lg">
                        {{ $subscription->connectivity->metroEthernet->bandwidth ?? 0 }} Mbps
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Topologi Tab (Full Width Editor) -->
    @if($subscription->connectivity)
        <div id="panelTopologi" class="tab-panel hidden mt-6">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                {{-- React Flow will mount here - FULL WIDTH --}}
                <div id="topology-editor-root" data-subscription-id="{{ $subscription->id }}"
                    data-api-base-url="{{ url('/') }}" data-can-edit="true" style="height: 75vh; min-height: 600px;">
                </div>
            </div>
        </div>
    @endif

    @if($subscription->connectivity && !empty($subscription->connectivity->zabbix_interfaces))
        <div id="panelMonitoring" class="tab-panel hidden mt-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-5 space-y-5">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-widest text-slate-500">Monitoring</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                            {{ $subscription->connectivity->zabbix_group_name ?: '-' }} / {{ $subscription->connectivity->zabbix_host_name ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Interface</label>
                        <select id="monitoringInterfaceSelect"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            @foreach(($subscription->connectivity->zabbix_interfaces ?? []) as $interface)
                                <option value="{{ $interface['graphid'] ?? '' }}"
                                    data-name="{{ $interface['name'] ?? '' }}"
                                    data-item-in="{{ $interface['itemIn'] ?? '' }}"
                                    data-item-out="{{ $interface['itemOut'] ?? '' }}">
                                    {{ $interface['name'] ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Preset Range</label>
                        <div class="grid grid-cols-3 gap-2" id="monitoringPresetGroup">
                            @foreach (['1h', '6h', '24h', '7d', '30d', '90d'] as $preset)
                                <button type="button" data-period="{{ $preset }}"
                                    class="monitoring-preset-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                    {{ $preset }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Custom Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" id="monitoringDateFrom" max="{{ now()->format('Y-m-d') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-3 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500">
                            <input type="date" id="monitoringDateTo" max="{{ now()->format('Y-m-d') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-3 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" id="monitoringApplyCustom"
                            class="w-full px-4 py-2.5 rounded-xl font-bold bg-slate-900 dark:bg-blue-600 text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors">
                            Tampilkan
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-sky-50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-900/30 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Current IN</p>
                            <p id="monitorCurIn" class="mt-2 text-lg font-mono font-bold text-sky-600 dark:text-sky-400">-</p>
                        </div>
                        <div class="rounded-2xl bg-sky-50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-900/30 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Max IN</p>
                            <p id="monitorMaxIn" class="mt-2 text-lg font-mono font-bold text-sky-600 dark:text-sky-400">-</p>
                        </div>
                        <div class="rounded-2xl bg-rose-50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-900/30 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Current OUT</p>
                            <p id="monitorCurOut" class="mt-2 text-lg font-mono font-bold text-rose-600 dark:text-rose-400">-</p>
                        </div>
                        <div class="rounded-2xl bg-rose-50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-900/30 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Max OUT</p>
                            <p id="monitorMaxOut" class="mt-2 text-lg font-mono font-bold text-rose-600 dark:text-rose-400">-</p>
                        </div>
                    </div>
                </div>
                </div>

                <div class="lg:col-span-8 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-5 md:p-6 min-w-0">
                    <div class="flex flex-col gap-1">
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Bandwidth Monitoring</h4>
                        <p id="monitoringMeta" class="text-sm text-slate-500 dark:text-slate-400">GRAPH: - | ITEM_IN: - | ITEM_OUT: -</p>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <button id="monitoringBtnDragZoom" type="button"
                            class="monitoring-zoom-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            Select Area
                        </button>
                        <button id="monitoringBtnScrollZoom" type="button"
                            class="monitoring-zoom-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            Scroll
                        </button>
                        <button id="monitoringBtnPanMode" type="button"
                            class="monitoring-zoom-btn px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            Pan
                        </button>
                        <button id="monitoringZoomInBtn" type="button"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            +
                        </button>
                        <button id="monitoringZoomOutBtn" type="button"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors">
                            -
                        </button>
                        <button id="monitoringResetZoomBtn" type="button"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:border-red-400 hover:text-red-600 transition-colors">
                            Reset
                        </button>
                    </div>

                    <div class="mt-5 relative min-h-[420px]">
                        <div id="monitoringChartOverlay" class="hidden absolute inset-0 rounded-3xl bg-white/70 dark:bg-slate-900/70 items-center justify-center z-10">
                            <div class="w-10 h-10 rounded-full border-4 border-slate-200 border-t-blue-600 animate-spin"></div>
                        </div>
                        <canvas id="monitoringChart" height="120"></canvas>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3 flex-wrap text-sm">
                        <div id="monitoringRangeInfo" class="text-slate-500 dark:text-slate-400">-</div>
                        <div id="monitoringUpdatedAt" class="font-mono text-slate-500 dark:text-slate-400">-</div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Hosting Tab -->
    @if($subscription->hosting)
        <div id="panelHosting" class="tab-panel hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i data-lucide="server" class="w-4 h-4"></i>
                    Detail Hosting
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Domain</p>
                        <p class="text-slate-800 dark:text-white font-mono text-lg">
                            {{ $subscription->hosting->domain ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Username</p>
                        <p class="text-slate-800 dark:text-white font-mono text-lg">
                            {{ $subscription->hosting->username ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Server</p>
                        <p class="text-slate-800 dark:text-white font-medium text-lg">
                            {{ $subscription->hosting->hostingServer->name ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Disk Quota</p>
                        <p class="text-slate-800 dark:text-white font-medium text-lg">
                            {{ $subscription->hosting->disk_quota_gb ?? 0 }} GB
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Email Accounts</p>
                        <p class="text-slate-800 dark:text-white font-medium text-lg">
                            {{ $subscription->hosting->email_accounts ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Databases</p>
                        <p class="text-slate-800 dark:text-white font-medium text-lg">
                            {{ $subscription->hosting->databases ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Domain Tab -->
    @if($subscription->domain)
        <div id="panelDomain" class="tab-panel hidden">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i data-lucide="globe" class="w-4 h-4"></i>
                    Detail Domain
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Nama Domain</p>
                        <p class="text-slate-800 dark:text-white font-mono text-lg">
                            {{ $subscription->domain->domain_name ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Registrar</p>
                        <p class="text-slate-800 dark:text-white font-medium text-lg">
                            {{ $subscription->domain->registrar ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tanggal Expired</p>
                        <p class="text-slate-800 dark:text-white font-medium text-lg">
                            {{ $subscription->domain->expires_at ? $subscription->domain->expires_at->format('d M Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div>

    <!-- Client Profile Modal -->
    <div id="clientModal" class="fixed inset-0 z-50 hidden">
        <div id="clientModalBackdrop" class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity opacity-0">
        </div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div id="clientModalPanel"
                class="bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all scale-95 opacity-0">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($subscription->client->name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <span>{{ $subscription->client->name ?? '-' }}</span>
                            <p class="text-sm font-normal text-slate-500 font-mono">
                                {{ $subscription->client->client_code ?? '-' }}
                            </p>
                        </div>
                    </h3>
                    <button type="button" onclick="closeClientModal()"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
                    </button>
                </div>

                <!-- Modal Tabs -->
                <div class="flex border-b border-slate-200 dark:border-slate-700 px-6">
                    <button type="button" id="clientTabInfo" onclick="switchClientTab('info')"
                        class="px-4 py-2 rounded-t-xl text-sm font-bold transition-colors bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i> Info
                    </button>
                    <button type="button" id="clientTabContacts" onclick="switchClientTab('contacts')"
                        class="px-4 py-2 rounded-t-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="phone" class="w-4 h-4 inline mr-1"></i> Kontak
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <!-- Info Panel -->
                    <div id="clientPanelInfo">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tipe Pelanggan</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->client->type === 'individual' ? 'Perorangan' : 'Perusahaan' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">NIK / NPWP</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->client->nik_npwp ?? '-' }}
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Alamat</p>
                                <p class="text-slate-800 dark:text-white">
                                    {{ $subscription->client->address ?? '-' }}
                                </p>
                            </div>
                            @if($subscription->client->type === 'company')
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Nama PIC</p>
                                    <p class="text-slate-800 dark:text-white font-medium">
                                        {{ $subscription->client->pic_name ?? '-' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contacts Panel -->
                    <div id="clientPanelContacts" class="hidden">
                        <div class="space-y-4">
                            @if($subscription->client->contacts && $subscription->client->contacts->count() > 0)
                                @foreach($subscription->client->contacts as $contact)
                                    <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                        <div
                                            class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                            <i data-lucide="user" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-slate-800 dark:text-white">{{ $contact->name }}</p>
                                            <div class="flex items-center gap-4 text-sm text-slate-500">
                                                @if($contact->phone)
                                                    <span class="flex items-center gap-1">
                                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                                        {{ $contact->phone }}
                                                    </span>
                                                @endif
                                                @if($contact->email)
                                                    <span class="flex items-center gap-1">
                                                        <i data-lucide="mail" class="w-3 h-3"></i>
                                                        {{ $contact->email }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-slate-500 text-center py-4">Tidak ada kontak tersimpan</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end p-6 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('clients.show', $subscription->client_id) }}"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold transition-colors">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Lihat Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tab) {
            const tabs = ['Info', 'Teknis', 'Monitoring', 'Topologi', 'Hosting', 'Domain'];
            const activeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
            const inactiveClass = 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700';

            tabs.forEach(t => {
                const tabBtn = document.getElementById('tab' + t);
                const panel = document.getElementById('panel' + t);

                if (tabBtn && panel) {
                    if (t.toLowerCase() === tab) {
                        tabBtn.className = `flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors ${activeClass}`;
                        panel.classList.remove('hidden');
                    } else {
                        tabBtn.className = `flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-colors ${inactiveClass}`;
                        panel.classList.add('hidden');
                    }
                }
            });

            lucide.createIcons();
        }

        @if($subscription->connectivity && !empty($subscription->connectivity->zabbix_interfaces))
            let monitoringChart;
            let monitoringPeriod = '24h';
            let monitoringMode = 'preset';

            function setMonitoringLoading(isLoading) {
                const overlay = document.getElementById('monitoringChartOverlay');
                if (!overlay) return;
                overlay.classList.toggle('hidden', !isLoading);
                overlay.classList.toggle('flex', isLoading);
            }

            function setActiveMonitoringPreset(period) {
                document.querySelectorAll('.monitoring-preset-btn').forEach(button => {
                    const active = button.dataset.period === period;
                    button.classList.toggle('bg-blue-600', active);
                    button.classList.toggle('text-white', active);
                    button.classList.toggle('border-blue-600', active);
                });
            }

            function configureMonitoringZoom(mode) {
                if (!monitoringChart) {
                    return;
                }

                const zoomOptions = monitoringChart.options.plugins.zoom;
                zoomOptions.pan.enabled = mode === 'pan';
                zoomOptions.zoom.wheel.enabled = mode === 'scroll';
                zoomOptions.zoom.pinch.enabled = mode === 'scroll';
                zoomOptions.zoom.drag.enabled = mode === 'drag';
                monitoringChart.update('none');

                [
                    ['monitoringBtnDragZoom', 'drag'],
                    ['monitoringBtnScrollZoom', 'scroll'],
                    ['monitoringBtnPanMode', 'pan']
                ].forEach(([id, targetMode]) => {
                    const button = document.getElementById(id);
                    const active = mode === targetMode;
                    button?.classList.toggle('bg-blue-600', active);
                    button?.classList.toggle('text-white', active);
                    button?.classList.toggle('border-blue-600', active);
                });
            }

            function buildMonitoringChart(payload) {
                const ctx = document.getElementById('monitoringChart');
                if (!ctx) return;

                if (monitoringChart) {
                    monitoringChart.destroy();
                }

                monitoringChart = new Chart(ctx, {
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

                configureMonitoringZoom('drag');
            }

            function updateMonitoringStats(payload) {
                document.getElementById('monitorCurIn').textContent = Number(payload.stats.curIn).toFixed(2);
                document.getElementById('monitorMaxIn').textContent = Number(payload.stats.maxIn).toFixed(2);
                document.getElementById('monitorCurOut').textContent = Number(payload.stats.curOut).toFixed(2);
                document.getElementById('monitorMaxOut').textContent = Number(payload.stats.maxOut).toFixed(2);
                document.getElementById('monitoringRangeInfo').textContent = `${payload.rangeLabel} | ${payload.points} titik | ${payload.dataMode.toUpperCase()}`;
                document.getElementById('monitoringUpdatedAt').textContent = payload.updatedAt;
            }

            async function loadMonitoringChart() {
                const select = document.getElementById('monitoringInterfaceSelect');
                if (!select || !select.value) return;

                const option = select.options[select.selectedIndex];
                const itemIn = option.getAttribute('data-item-in');
                const itemOut = option.getAttribute('data-item-out');
                const graphName = option.getAttribute('data-name');

                document.getElementById('monitoringMeta').textContent = `GRAPH: ${graphName} | ITEM_IN: ${itemIn} | ITEM_OUT: ${itemOut}`;

                const url = new URL(@json(route('zabbix-monitors.chart-data')));
                url.searchParams.set('itemin', itemIn);
                url.searchParams.set('itemout', itemOut);
                url.searchParams.set('mode', monitoringMode);

                if (monitoringMode === 'custom') {
                    url.searchParams.set('from', document.getElementById('monitoringDateFrom').value);
                    url.searchParams.set('to', document.getElementById('monitoringDateTo').value);
                } else {
                    url.searchParams.set('period', monitoringPeriod);
                }

                setMonitoringLoading(true);
                try {
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'Gagal memuat chart monitoring.');
                    }

                    buildMonitoringChart(payload);
                    updateMonitoringStats(payload);
                } catch (error) {
                    alert(error.message);
                } finally {
                    setMonitoringLoading(false);
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const select = document.getElementById('monitoringInterfaceSelect');
                const customButton = document.getElementById('monitoringApplyCustom');

                if (select) {
                    setActiveMonitoringPreset(monitoringPeriod);
                    select.addEventListener('change', () => {
                        monitoringMode = 'preset';
                        setActiveMonitoringPreset(monitoringPeriod);
                        loadMonitoringChart();
                    });

                    document.querySelectorAll('.monitoring-preset-btn').forEach(button => {
                        button.addEventListener('click', () => {
                            monitoringMode = 'preset';
                            monitoringPeriod = button.dataset.period;
                            setActiveMonitoringPreset(monitoringPeriod);
                            loadMonitoringChart();
                        });
                    });

                    customButton?.addEventListener('click', () => {
                        const from = document.getElementById('monitoringDateFrom').value;
                        const to = document.getElementById('monitoringDateTo').value;
                        if (!from || !to) {
                            alert('Tanggal custom range wajib diisi.');
                            return;
                        }

                        monitoringMode = 'custom';
                        document.querySelectorAll('.monitoring-preset-btn').forEach(button => {
                            button.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                        });
                        loadMonitoringChart();
                    });

                    document.getElementById('monitoringZoomInBtn')?.addEventListener('click', () => monitoringChart?.zoom(1.15));
                    document.getElementById('monitoringZoomOutBtn')?.addEventListener('click', () => monitoringChart?.zoom(0.9));
                    document.getElementById('monitoringResetZoomBtn')?.addEventListener('click', () => monitoringChart?.resetZoom());
                    document.getElementById('monitoringBtnDragZoom')?.addEventListener('click', () => configureMonitoringZoom('drag'));
                    document.getElementById('monitoringBtnScrollZoom')?.addEventListener('click', () => configureMonitoringZoom('scroll'));
                    document.getElementById('monitoringBtnPanMode')?.addEventListener('click', () => configureMonitoringZoom('pan'));

                    loadMonitoringChart();
                }
            });
        @endif

        // Client Modal Functions
        function openClientModal() {
            const modal = document.getElementById('clientModal');
            const backdrop = document.getElementById('clientModalBackdrop');
            const panel = document.getElementById('clientModalPanel');

            modal.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('scale-95', 'opacity-0');
            }, 10);
            lucide.createIcons();
        }

        function closeClientModal() {
            const modal = document.getElementById('clientModal');
            const backdrop = document.getElementById('clientModalBackdrop');
            const panel = document.getElementById('clientModalPanel');

            backdrop.classList.add('opacity-0');
            panel.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function switchClientTab(tab) {
            const tabInfo = document.getElementById('clientTabInfo');
            const tabContacts = document.getElementById('clientTabContacts');
            const panelInfo = document.getElementById('clientPanelInfo');
            const panelContacts = document.getElementById('clientPanelContacts');

            const activeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
            const inactiveClass = 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700';

            if (tab === 'info') {
                tabInfo.className = `px-4 py-2 rounded-t-xl text-sm font-bold transition-colors ${activeClass}`;
                tabContacts.className = `px-4 py-2 rounded-t-xl text-sm font-bold transition-colors ${inactiveClass}`;
                panelInfo.classList.remove('hidden');
                panelContacts.classList.add('hidden');
            } else {
                tabContacts.className = `px-4 py-2 rounded-t-xl text-sm font-bold transition-colors ${activeClass}`;
                tabInfo.className = `px-4 py-2 rounded-t-xl text-sm font-bold transition-colors ${inactiveClass}`;
                panelContacts.classList.remove('hidden');
                panelInfo.classList.add('hidden');
            }

            lucide.createIcons();
        }

        // Close on backdrop click
        document.getElementById('clientModalBackdrop')?.addEventListener('click', closeClientModal);

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeClientModal();
        });
    </script>

    @if($subscription->connectivity)
        @push('vite')
            @vite(['resources/js/topology/index.jsx'])
        @endpush
    @endif
<!-- Form Modal (Copied from index.blade.php) -->
    <div id="formModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="formModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]"
                id="formModalPanel">

                <!-- Modal Header -->
                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Edit Layanan
                    </h3>
                    <button onclick="window.closeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div id="modalScrollableBody" class="flex-1 overflow-y-auto custom-scrollbar p-6">
                    <form id="dataForm" class="space-y-6">
                        @csrf
                        <input type="hidden" id="dataId" name="id">

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 p-2">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <button type="button" id="subscription-tab-general" onclick="switchSubscriptionFormTab('general')"
                                    class="subscription-form-tab flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition-all">
                                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                    <span>Umum</span>
                                </button>
                                <button type="button" id="subscription-tab-billing" onclick="switchSubscriptionFormTab('billing')"
                                    class="subscription-form-tab flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition-all">
                                    <i data-lucide="receipt-text" class="w-4 h-4"></i>
                                    <span>Billing</span>
                                </button>
                                <button type="button" id="subscription-tab-technical" onclick="switchSubscriptionFormTab('technical')"
                                    class="subscription-form-tab flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition-all">
                                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                                    <span>Teknis</span>
                                </button>
                            </div>
                        </div>

                        <div id="subscription-panel-general" class="subscription-form-panel space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih
                                        Pelanggan <span class="text-red-500">*</span></label>
                                    <select id="client_id" name="client_id" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        <option value="">-- Pilih Pelanggan --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih
                                        Paket Layanan <span class="text-red-500">*</span></label>
                                    <select id="package_id" name="package_id" required onchange="handlePackageChange()"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        <option value="">-- Pilih Paket --</option>
                                        @foreach($packages as $pkg)
                                            <option value="{{ $pkg->id }}" data-type="{{ $pkg->service->type }}"
                                                data-price="{{ $pkg->price }}">
                                                {{ $pkg->name }} (Rp {{ number_format($pkg->price, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal
                                        Pemasangan <span class="text-red-500">*</span></label>
                                    <input type="date" id="installed_at" name="installed_at" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status
                                        Layanan <span class="text-red-500">*</span></label>
                                    <select id="status" name="status" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        <option value="pending">Pending (Menunggu Aktivasi)</option>
                                        <option value="active">Active (Aktif)</option>
                                        <option value="suspended">Suspended (Isolir)</option>
                                        <option value="terminated">Terminated (Berhenti)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan
                                    Tambahan</label>
                                <input type="text" id="notes" name="notes"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>
                        </div>

                        <div id="subscription-panel-billing" class="subscription-form-panel hidden space-y-6">
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-5 space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Harga
                                            Paket</label>
                                        <div id="package_price_display"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-100 dark:bg-slate-600 text-slate-600 dark:text-slate-300 font-mono">
                                            Rp 0
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Harga
                                            Khusus (Deal)
                                            <span class="text-xs text-slate-400 font-normal">(opsional)</span></label>
                                        <input type="number" id="custom_price" name="custom_price" step="0.01"
                                            placeholder="Kosongkan jika pakai harga paket"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Siklus
                                            Tagihan</label>
                                        <select id="billing_period_months" name="billing_period_months"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="1">Bulanan (1 Bulan)</option>
                                            <option value="3">Triwulan (3 Bulan)</option>
                                            <option value="6">Semester (6 Bulan)</option>
                                            <option value="12">Tahunan (12 Bulan)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-[1.1fr_0.9fr] gap-6">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 space-y-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">PPN 11%</h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jika aktif, PPN dihitung 11% dari harga jual.</p>
                                            </div>
                                            <label class="tax-toggle inline-flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" id="uses_ppn" name="uses_ppn" value="1" class="sr-only">
                                                <span class="tax-toggle-track tax-toggle-track--emerald">
                                                    <span class="tax-toggle-thumb"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nominal PPN 11%</label>
                                            <input type="text" id="ppn_amount_display" value="Rp 0" disabled
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono disabled:opacity-100">
                                        </div>
                                        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 flex items-start justify-between gap-4">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">PPh23</h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jika aktif, PPh23 dihitung 2% dari harga jual dan menjadi potongan tagihan.</p>
                                            </div>
                                            <label class="tax-toggle inline-flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" id="uses_pph23" name="uses_pph23" value="1" class="sr-only">
                                                <span class="tax-toggle-track tax-toggle-track--amber">
                                                    <span class="tax-toggle-thumb"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nominal PPh23</label>
                                            <input type="text" id="pph23_amount_display" value="Rp 0" disabled
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono disabled:opacity-100">
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-blue-200 dark:border-blue-900/40 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-slate-800 dark:to-slate-900 p-4 space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">Harga jual</span>
                                            <span id="billing_base_price" class="font-mono font-semibold text-slate-800 dark:text-slate-100">Rp 0</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">PPN 11%</span>
                                            <span id="billing_ppn_price" class="font-mono font-semibold text-slate-800 dark:text-slate-100">Rp 0</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">PPh23</span>
                                            <span id="billing_pph23_price" class="font-mono font-semibold text-amber-700 dark:text-amber-300">Rp 0</span>
                                        </div>
                                        <div class="border-t border-blue-200/70 dark:border-slate-700 pt-3 flex items-center justify-between">
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Total tagihan</span>
                                            <span id="billing_total_price" class="font-mono text-lg font-bold text-blue-700 dark:text-blue-300">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="subscription-panel-technical" class="subscription-form-panel hidden space-y-6">
                            <div id="technical-details"
                                class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 p-5 hidden">
                                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Detail Teknis
                                </h4>

                                <div id="fields-connectivity" class="hidden space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Router/Gateway</label>
                                        <select id="router_id" name="router_id"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="">-- Pilih Router Mikrotik --</option>
                                            @foreach($routers as $router)
                                                <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->host }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">IP
                                            Address</label>
                                        <input type="text" name="ip_address" id="ip_address" placeholder="192.168.x.x"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">PPPoE
                                            Username</label>
                                        <input type="text" name="pppoe_user" id="pppoe_user"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">PPPoE
                                            Password / Secret</label>
                                        <input type="password" name="pppoe_secret" id="pppoe_secret"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Modem
                                            SN (ONT)</label>
                                        <input type="text" name="ont_sn" id="ont_sn"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">VLAN
                                            ID</label>
                                        <input type="number" name="vlan_id" id="vlan_id"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-5 space-y-4">
                                    <div>
                                        <h5 class="text-sm font-bold text-slate-700 dark:text-slate-200">Monitoring Zabbix</h5>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih group, host, dan satu atau lebih network interface untuk layanan ini.</p>
                                    </div>

                                    <input type="hidden" id="zabbix_group_name" name="zabbix_group_name">
                                    <input type="hidden" id="zabbix_host_name" name="zabbix_host_name">
                                    <div id="zabbixInterfacesHiddenInputs"></div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Group Zabbix</label>
                                            <select id="zabbix_group_id" name="zabbix_group_id"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                <option value="">Pilih group</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Host Zabbix</label>
                                            <select id="zabbix_host_id" name="zabbix_host_id"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                                                disabled>
                                                <option value="">Pilih group terlebih dahulu</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Network Interface Zabbix</label>
                                        <button type="button" id="zabbixInterfaceToggle"
                                            class="w-full flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <span id="zabbixInterfaceSummary" class="text-left text-sm text-slate-500 dark:text-slate-400">Pilih host terlebih dahulu</span>
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </button>
                                        <div id="zabbixInterfaceDropdown"
                                            class="hidden absolute left-0 right-0 mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl z-[80] overflow-hidden">
                                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 text-xs font-bold uppercase tracking-widest text-slate-400">
                                                Pilih Interface
                                            </div>
                                            <div id="zabbixInterfaceOptions" class="max-h-72 overflow-y-auto p-2 space-y-1">
                                                <p class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">Belum ada interface.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="zabbixSelectedInterfaces" class="flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                                <!-- Metro Ethernet Details -->
                                <div class="relative py-4">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                                    </div>
                                    <div class="relative flex justify-start">
                                        <span class="pr-3 bg-white dark:bg-slate-800 text-sm font-medium text-slate-500">Metro
                                            Ethernet Details</span>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Metro Ethernet</label>
                                    <select id="metro_option" name="metro_option" onchange="toggleMetroForm()"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        <option value="">-- Tidak Ada / Pilih Nanti --</option>
                                        <option value="new" class="font-bold text-blue-600">+ Buat Baru</option>
                                        <optgroup label="Tersedia">
                                            @foreach($metroEthernets as $metro)
                                                <option value="existing" data-id="{{ $metro->id }}">
                                                    {{ $metro->selection_label }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <input type="hidden" name="metro_ethernet_id" id="metro_ethernet_id_input">
                                </div>

                                <div id="metro-new-form" class="hidden space-y-6 border-l-4 border-blue-500 pl-4">
                                    <h5 class="text-sm font-bold text-blue-600">Buat Metro Ethernet Baru</h5>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Metro Ethernet</label>
                                        <input type="text" id="metro_name" name="metro_name"
                                            placeholder="Contoh: Link Metro Salatiga POP A"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label
                                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Vendor
                                                Backbone</label>
                                            <select id="metro_vendor_id" name="metro_vendor_id"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                <option value="">-- Pilih Vendor --</option>
                                                <!-- Assuming $vendors is available or fallback -->
                                                @php $uniqueVendors = $metroEthernets->pluck('vendor')->unique('id')->filter(); @endphp
                                                @foreach($uniqueVendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">CID
                                                (Circuit ID)</label>
                                            <input type="text" id="metro_cid" name="metro_cid"
                                                placeholder="Contoh: CID-12345"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">IP
                                                Address (Metro)</label>
                                            <input type="text" id="metro_ip_address" name="metro_ip_address"
                                                placeholder="192.168.x.x"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Bandwidth
                                                (Mbps)</label>
                                            <input type="number" id="metro_bandwidth" name="metro_bandwidth" min="0"
                                                placeholder="Contoh: 100"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="fields-hosting" class="hidden space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div id="div-hosting-server">
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Hosting
                                            Server</label>
                                        <select id="hosting_server_id" name="hosting_server_id"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="">-- Pilih Server Hosting --</option>
                                            @foreach($servers as $server)
                                                <option value="{{ $server->id }}">{{ $server->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama
                                            Domain</label>
                                        <input type="text" name="domain" id="domain" placeholder="example.com"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username
                                            (Panel)</label>
                                        <input type="text" name="username" id="username"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password</label>
                                        <input type="password" name="password" id="password"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                            </div>

                            <div id="technical-empty-state" class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-8 text-center">
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Pilih paket layanan terlebih dahulu.</p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Form teknis akan menyesuaikan otomatis untuk internet atau hosting.</p>
                            </div>
                        </div>
                    </form>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0">
                <button type="button" onclick="window.closeModal()"
                    class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                <button type="button" onclick="submitForm()" id="submitBtn"
                    class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="submitSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span id="submitText">Simpan Layanan</span>
                </button>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        @if($subscription->connectivity && !empty($subscription->connectivity->zabbix_interfaces))
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
        @endif
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

        <style>
            /* Choices.js Custom Styling */
            .choices { margin-bottom: 0 !important; }
            .choices__inner {
                min-height: 46px !important;
                border-radius: 0.75rem !important;
                border: 1px solid #e2e8f0 !important;
                background-color: #f8fafc !important;
                padding: 0.625rem 0.75rem !important;
                font-size: 0.875rem !important;
            }
            .dark .choices__inner {
                background-color: rgba(51, 65, 85, 0.5) !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }
            .dark .choices__list--dropdown {
                background-color: #1e293b !important;
                border-color: #475569 !important;
                color: #e2e8f0 !important;
            }

            .zabbix-interface-option {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 0.75rem;
                border-radius: 0.875rem;
                cursor: pointer;
                transition: background-color 0.15s ease-in-out;
            }

            .zabbix-interface-option:hover {
                background-color: #f8fafc;
            }

            .dark .zabbix-interface-option:hover {
                background-color: rgba(51, 65, 85, 0.45);
            }

            .subscription-form-tab.is-active {
                background: linear-gradient(135deg, #dbeafe 0%, #ecfeff 100%);
                color: #1d4ed8;
                box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.15);
            }

            .dark .subscription-form-tab.is-active {
                background: rgba(30, 64, 175, 0.22);
                color: #93c5fd;
                box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.16);
            }

            .tax-toggle {
                user-select: none;
            }

            .tax-toggle-track {
                position: relative;
                display: inline-flex;
                align-items: center;
                width: 3.25rem;
                height: 1.875rem;
                padding: 0.25rem;
                border-radius: 9999px;
                background: #cbd5e1;
                transition: background-color 0.2s ease;
                flex-shrink: 0;
            }

            .tax-toggle-thumb {
                width: 1.375rem;
                height: 1.375rem;
                border-radius: 9999px;
                background: #ffffff;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
                transition: transform 0.2s ease;
            }

            .tax-toggle input:focus-visible + .tax-toggle-track {
                outline: 2px solid rgba(59, 130, 246, 0.35);
                outline-offset: 2px;
            }

            .tax-toggle input:checked + .tax-toggle-track--emerald {
                background: #10b981;
            }

            .tax-toggle input:checked + .tax-toggle-track--amber {
                background: #f59e0b;
            }

            .tax-toggle input:checked + .tax-toggle-track .tax-toggle-thumb {
                transform: translateX(1.375rem);
            }

            .dark .tax-toggle-track {
                background: #475569;
            }
        </style>

        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                let clientChoice;
                let zabbixGraphOptions = [];
                let selectedZabbixInterfaces = [];
                let activeSubscriptionFormTab = 'general';
                const zabbixRoutes = {
                    groups: '{{ route('zabbix-monitors.groups') }}',
                    hosts: '{{ route('zabbix-monitors.hosts') }}',
                    graphs: '{{ route('zabbix-monitors.graphs') }}'
                };

                $(document).ready(function () {
                    clientChoice = new Choices('#client_id', {
                        searchEnabled: true,
                        searchPlaceholderValue: 'Cari pelanggan...',
                        placeholder: true,
                        placeholderValue: '-- Pilih Pelanggan --',
                        removeItemButton: true,
                        shouldSort: false
                    });

                    initializeZabbixSelectors();
                    initializeSubscriptionBillingListeners();
                    resetSubscriptionFormTabs();
                });

                function initializeZabbixSelectors() {
                    const groupSelect = document.getElementById('zabbix_group_id');
                    const hostSelect = document.getElementById('zabbix_host_id');
                    const toggle = document.getElementById('zabbixInterfaceToggle');
                    const dropdown = document.getElementById('zabbixInterfaceDropdown');

                    loadZabbixGroups();

                    groupSelect.addEventListener('change', async function() {
                        updateZabbixNameField('zabbix_group_name', groupSelect);
                        clearZabbixInterfaces();
                        await loadZabbixHosts(this.value);
                    });

                    hostSelect.addEventListener('change', async function() {
                        updateZabbixNameField('zabbix_host_name', hostSelect);
                        clearZabbixInterfaces();
                        await loadZabbixGraphs(this.value);
                    });

                    toggle.addEventListener('click', function() {
                        if (hostSelect.disabled) return;
                        dropdown.classList.toggle('hidden');
                    });

                    document.addEventListener('click', function(event) {
                        if (!event.target.closest('#zabbixInterfaceToggle') && !event.target.closest('#zabbixInterfaceDropdown')) {
                            dropdown.classList.add('hidden');
                        }
                    });
                }

                async function loadZabbixGroups(selectedValue = '') {
                    const groupSelect = document.getElementById('zabbix_group_id');
                    try {
                        const response = await fetch(zabbixRoutes.groups, { headers: { 'Accept': 'application/json' } });
                        const groups = await response.json();
                        groupSelect.innerHTML = '<option value="">Pilih group</option>';
                        groups.forEach(group => {
                            groupSelect.insertAdjacentHTML('beforeend', `<option value="${group.groupid}">${group.name}</option>`);
                        });
                        groupSelect.value = selectedValue;
                    } catch (error) {
                        alert('Gagal memuat group Zabbix');
                    }
                }

                async function loadZabbixHosts(groupId, selectedValue = '') {
                    const hostSelect = document.getElementById('zabbix_host_id');
                    hostSelect.disabled = true;
                    hostSelect.innerHTML = `<option value="">${groupId ? 'Memuat host...' : 'Pilih group terlebih dahulu'}</option>`;

                    if (!groupId) {
                        updateZabbixInterfaceSummary();
                        return;
                    }

                    try {
                        const url = new URL(zabbixRoutes.hosts);
                        url.searchParams.set('groupid', groupId);
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const hosts = await response.json();
                        hostSelect.innerHTML = '<option value="">Pilih host</option>';
                        hosts.forEach(host => {
                            hostSelect.insertAdjacentHTML('beforeend', `<option value="${host.hostid}">${host.name}</option>`);
                        });
                        hostSelect.disabled = false;
                        hostSelect.value = selectedValue;
                    } catch (error) {
                        hostSelect.innerHTML = '<option value="">Gagal memuat host</option>';
                    }
                }

                async function loadZabbixGraphs(hostId, selectedValues = []) {
                    const optionsContainer = document.getElementById('zabbixInterfaceOptions');
                    zabbixGraphOptions = [];
                    optionsContainer.innerHTML = `<p class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">${hostId ? 'Memuat interface...' : 'Pilih host terlebih dahulu'}</p>`;

                    if (!hostId) {
                        updateZabbixInterfaceSummary();
                        return;
                    }

                    try {
                        const url = new URL(zabbixRoutes.graphs);
                        url.searchParams.set('hostid', hostId);
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        zabbixGraphOptions = await response.json();
                        selectedZabbixInterfaces = zabbixGraphOptions.filter(option => selectedValues.includes(String(option.graphid)));
                        renderZabbixInterfaceOptions();
                        syncZabbixHiddenInputs();
                    } catch (error) {
                        optionsContainer.innerHTML = '<p class="px-3 py-2 text-sm text-red-500">Gagal memuat interface.</p>';
                    }
                }

                function renderZabbixInterfaceOptions() {
                    const optionsContainer = document.getElementById('zabbixInterfaceOptions');
                    if (!zabbixGraphOptions.length) {
                        optionsContainer.innerHTML = '<p class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">Tidak ada network interface yang cocok.</p>';
                        updateZabbixInterfaceSummary();
                        renderSelectedZabbixInterfaces();
                        return;
                    }

                    optionsContainer.innerHTML = zabbixGraphOptions.map(option => {
                        const checked = selectedZabbixInterfaces.some(item => String(item.graphid) === String(option.graphid));
                        return `
                            <label class="zabbix-interface-option">
                                <input type="checkbox" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    value="${option.graphid}" ${checked ? 'checked' : ''} onchange="window.toggleZabbixInterface('${option.graphid}')">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-200">${option.name}</div>
                                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-1">IN ${option.itemIn} / OUT ${option.itemOut}</div>
                                </div>
                            </label>
                        `;
                    }).join('');

                    updateZabbixInterfaceSummary();
                    renderSelectedZabbixInterfaces();
                }

                window.toggleZabbixInterface = function(graphId) {
                    const selected = zabbixGraphOptions.find(option => String(option.graphid) === String(graphId));
                    if (!selected) return;

                    const exists = selectedZabbixInterfaces.some(item => String(item.graphid) === String(graphId));
                    if (exists) {
                        selectedZabbixInterfaces = selectedZabbixInterfaces.filter(item => String(item.graphid) !== String(graphId));
                    } else {
                        selectedZabbixInterfaces.push(selected);
                    }

                    syncZabbixHiddenInputs();
                    updateZabbixInterfaceSummary();
                    renderSelectedZabbixInterfaces();
                };

                function renderSelectedZabbixInterfaces() {
                    const container = document.getElementById('zabbixSelectedInterfaces');
                    if (!selectedZabbixInterfaces.length) {
                        container.innerHTML = '<span class="text-xs text-slate-400">Belum ada interface dipilih.</span>';
                        return;
                    }

                    container.innerHTML = selectedZabbixInterfaces.map(item => `
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-semibold">
                            ${item.name}
                        </span>
                    `).join('');
                }

                function updateZabbixInterfaceSummary() {
                    const summary = document.getElementById('zabbixInterfaceSummary');
                    const hostSelect = document.getElementById('zabbix_host_id');
                    if (hostSelect.disabled || !hostSelect.value) {
                        summary.textContent = 'Pilih host terlebih dahulu';
                        summary.className = 'text-left text-sm text-slate-500 dark:text-slate-400';
                        return;
                    }

                    if (!selectedZabbixInterfaces.length) {
                        summary.textContent = 'Pilih satu atau lebih network interface';
                        summary.className = 'text-left text-sm text-slate-500 dark:text-slate-400';
                        return;
                    }

                    summary.textContent = `${selectedZabbixInterfaces.length} interface dipilih`;
                    summary.className = 'text-left text-sm text-slate-700 dark:text-slate-200 font-medium';
                }

                function syncZabbixHiddenInputs() {
                    const container = document.getElementById('zabbixInterfacesHiddenInputs');
                    container.innerHTML = selectedZabbixInterfaces.map((item, index) => `
                        <input type="hidden" name="zabbix_interfaces[${index}][graphid]" value="${item.graphid}">
                        <input type="hidden" name="zabbix_interfaces[${index}][name]" value="${escapeHtml(item.name)}">
                        <input type="hidden" name="zabbix_interfaces[${index}][itemIn]" value="${item.itemIn}">
                        <input type="hidden" name="zabbix_interfaces[${index}][itemOut]" value="${item.itemOut}">
                    `).join('');
                }

                function clearZabbixInterfaces() {
                    selectedZabbixInterfaces = [];
                    zabbixGraphOptions = [];
                    document.getElementById('zabbixInterfaceDropdown').classList.add('hidden');
                    syncZabbixHiddenInputs();
                    renderSelectedZabbixInterfaces();
                    updateZabbixInterfaceSummary();
                    document.getElementById('zabbixInterfaceOptions').innerHTML = '<p class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">Pilih host terlebih dahulu.</p>';
                }

                function resetZabbixFields() {
                    document.getElementById('zabbix_group_id').value = '';
                    document.getElementById('zabbix_group_name').value = '';
                    document.getElementById('zabbix_host_id').innerHTML = '<option value="">Pilih group terlebih dahulu</option>';
                    document.getElementById('zabbix_host_id').disabled = true;
                    document.getElementById('zabbix_host_name').value = '';
                    clearZabbixInterfaces();
                }

                function updateZabbixNameField(fieldId, selectElement) {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    document.getElementById(fieldId).value = selectedOption && selectedOption.value ? selectedOption.textContent.trim() : '';
                }

                function escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }

                window.handlePackageChange = function () {
                    const packageSelect = document.getElementById('package_id');
                    const selectedOption = packageSelect.options[packageSelect.selectedIndex];
                    const serviceType = selectedOption ? selectedOption.getAttribute('data-type') : null;

                    const price = selectedOption ? (selectedOption.getAttribute('data-price') || 0) : 0;
                    const priceDisplay = document.getElementById('package_price_display');
                    if (priceDisplay) {
                        priceDisplay.textContent = 'Rp ' + Number(price).toLocaleString('id-ID');
                    }

                    const detailsSection = document.getElementById('technical-details');
                    const fieldsConn = document.getElementById('fields-connectivity');
                    const fieldsHost = document.getElementById('fields-hosting');
                    const technicalEmptyState = document.getElementById('technical-empty-state');

                    if (fieldsConn) fieldsConn.classList.add('hidden');
                    if (fieldsHost) fieldsHost.classList.add('hidden');
                    if (detailsSection) detailsSection.classList.add('hidden');
                    if (technicalEmptyState) technicalEmptyState.classList.remove('hidden');

                    if (serviceType) {
                        if (detailsSection) detailsSection.classList.remove('hidden');
                        if (technicalEmptyState) technicalEmptyState.classList.add('hidden');
                        if (serviceType === 'connectivity') {
                            if (fieldsConn) fieldsConn.classList.remove('hidden');
                            toggleMetroForm();
                        } else if (serviceType === 'hosting' || serviceType === 'domain') {
                            if (fieldsHost) fieldsHost.classList.remove('hidden');
                            const divServer = document.getElementById('div-hosting-server');
                            if (divServer) {
                                if (serviceType === 'domain') {
                                    divServer.classList.add('hidden');
                                } else {
                                    divServer.classList.remove('hidden');
                                }
                            }
                        }
                    }

                    updateBillingPreview();
                };

                window.toggleMetroForm = function() {
                    const metroOption = document.getElementById('metro_option');
                    const newForm = document.getElementById('metro-new-form');
                    const hiddenIdInput = document.getElementById('metro_ethernet_id_input');
                    const selectedOption = metroOption.options[metroOption.selectedIndex];

                    if (metroOption.value === 'new') {
                        newForm.classList.remove('hidden');
                        hiddenIdInput.value = '';
                    } else if (metroOption.value === 'existing') {
                        newForm.classList.add('hidden');
                        hiddenIdInput.value = selectedOption.getAttribute('data-id');
                    } else {
                        newForm.classList.add('hidden');
                        hiddenIdInput.value = '';
                    }
                };

                function initializeSubscriptionBillingListeners() {
                    ['custom_price', 'billing_period_months', 'package_id'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) {
                            el.addEventListener('input', updateBillingPreview);
                            el.addEventListener('change', updateBillingPreview);
                        }
                    });

                    ['uses_ppn', 'uses_pph23'].forEach(id => {
                        const toggle = document.getElementById(id);
                        if (toggle) {
                            toggle.addEventListener('change', updateBillingPreview);
                        }
                    });
                }

                function calculateBillingNumbers() {
                    const packageSelect = document.getElementById('package_id');
                    const selectedOption = packageSelect.options[packageSelect.selectedIndex];
                    const packagePrice = Number(selectedOption ? (selectedOption.getAttribute('data-price') || 0) : 0);
                    const billingPeriodMonths = Number(document.getElementById('billing_period_months').value || 1);
                    const customPrice = Number(document.getElementById('custom_price').value || 0);
                    const basePrice = customPrice > 0 ? customPrice : (packagePrice * billingPeriodMonths);
                    const usesPpn = document.getElementById('uses_ppn').checked;
                    const usesPph23 = document.getElementById('uses_pph23').checked;
                    const ppnAmount = usesPpn ? (basePrice * 0.11) : 0;
                    const pph23Amount = usesPph23 ? (basePrice * 0.02) : 0;
                    const totalAmount = basePrice + ppnAmount - pph23Amount;

                    return { basePrice, ppnAmount, pph23Amount, totalAmount };
                }

                function formatCurrency(value) {
                    return 'Rp ' + Number(value || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }

                function updateBillingPreview() {
                    const { basePrice, ppnAmount, pph23Amount, totalAmount } = calculateBillingNumbers();

                    document.getElementById('billing_base_price').textContent = formatCurrency(basePrice);
                    document.getElementById('billing_ppn_price').textContent = formatCurrency(ppnAmount);
                    document.getElementById('billing_pph23_price').textContent = formatCurrency(pph23Amount);
                    document.getElementById('billing_total_price').textContent = formatCurrency(totalAmount);
                    document.getElementById('ppn_amount_display').value = formatCurrency(ppnAmount);
                    document.getElementById('pph23_amount_display').value = formatCurrency(pph23Amount);
                }

                window.switchSubscriptionFormTab = function (tabName) {
                    activeSubscriptionFormTab = tabName;
                    ['general', 'billing', 'technical'].forEach(name => {
                        const panel = document.getElementById(`subscription-panel-${name}`);
                        const tab = document.getElementById(`subscription-tab-${name}`);
                        if (panel) panel.classList.toggle('hidden', name !== tabName);
                        if (tab) {
                            tab.classList.toggle('is-active', name === tabName);
                            tab.classList.toggle('text-slate-500', name !== tabName);
                            tab.classList.toggle('dark:text-slate-400', name !== tabName);
                        }
                    });
                    lucide.createIcons();
                };

                function resetSubscriptionFormTabs() {
                    window.switchSubscriptionFormTab('general');
                }

                window.openModal = function (isEdit = false) {
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');
                        setTimeout(() => {
                            panel.classList.remove('scale-100');
                        }, 350);
                    }, 10);

                    updateBillingPreview();
                    resetSubscriptionFormTabs();
                    lucide.createIcons();
                };

                window.closeModal = function () {
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');

                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                window.submitForm = function () {
                    document.getElementById('dataForm').dispatchEvent(new Event('submit'));
                }

                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('dataId').value;
                    const url = `${baseUrl}/subscriptions/${id}`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = 'Update Layanan';

                    // Simple loading state
                    btn.disabled = true;
                    spinner.classList.remove('hidden');
                    text.innerText = 'Menyimpan...';

                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');

                    fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(res => {
                            // Reset loading
                            btn.disabled = false;
                            spinner.classList.add('hidden');
                            text.innerText = originalText;

                            if (res.success) {
                                // Reload page to show updates
                                window.location.reload();
                            } else {
                                alert(res.message || 'Gagal menyimpan data');
                            }
                        })
                        .catch(error => {
                            btn.disabled = false;
                            spinner.classList.add('hidden');
                            text.innerText = originalText;
                            console.error(error);
                            alert('Terjadi kesalahan!');
                        });
                });

                window.editData = function (id) {
                    openModal(true);

                    fetch(`${baseUrl}/subscriptions/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(r => r.json())
                        .then(async data => {
                            document.getElementById('dataId').value = data.id;

                            // Fill Form Values
                            if(clientChoice) clientChoice.setChoiceByValue(data.client_id.toString());
                            document.getElementById('package_id').value = data.package_id;
                            document.getElementById('installed_at').value = data.installed_at ? data.installed_at.split('T')[0] : '';
                            document.getElementById('status').value = data.status;
                            document.getElementById('notes').value = data.notes || '';

                            // Fill Pricing Fields
                            document.getElementById('custom_price').value = data.custom_price || '';
                            document.getElementById('billing_period_months').value = data.billing_period_months || 1;
                            document.getElementById('uses_ppn').checked = Boolean(data.uses_ppn);
                            document.getElementById('uses_pph23').checked = Boolean(data.uses_pph23);

                            // Trigger Change to show fields
                            handlePackageChange();
                            updateBillingPreview();

                            // Fill Details based on type
                            if (data.connectivity) {
                                document.getElementById('router_id').value = data.connectivity.router_id || '';
                                document.getElementById('ip_address').value = data.connectivity.ip_address || '';
                                document.getElementById('pppoe_user').value = data.connectivity.pppoe_user || '';
                                document.getElementById('pppoe_secret').value = ''; // Don't show password
                                document.getElementById('ont_sn').value = data.connectivity.ont_sn || '';
                                document.getElementById('vlan_id').value = data.connectivity.vlan_id || '';

                                const selectedInterfaces = (data.connectivity.zabbix_interfaces || []).map(item => String(item.graphid));
                                await loadZabbixGroups(data.connectivity.zabbix_group_id || '');
                                await loadZabbixHosts(data.connectivity.zabbix_group_id || '', data.connectivity.zabbix_host_id || '');
                                updateZabbixNameField('zabbix_group_name', document.getElementById('zabbix_group_id'));
                                updateZabbixNameField('zabbix_host_name', document.getElementById('zabbix_host_id'));
                                await loadZabbixGraphs(data.connectivity.zabbix_host_id || '', selectedInterfaces);

                                // Metro Ethernet Refactor
                                if (data.connectivity.metro_ethernet_id) {
                                     const metroSelect = document.getElementById('metro_option');
                                     let found = false;
                                     for (let i = 0; i < metroSelect.options.length; i++) {
                                         if (metroSelect.options[i].getAttribute('data-id') == data.connectivity.metro_ethernet_id) {
                                             metroSelect.selectedIndex = i;
                                             document.getElementById('metro_ethernet_id_input').value = data.connectivity.metro_ethernet_id;
                                             found = true;
                                             break;
                                         }
                                     }
                                     if (!found) {
                                         metroSelect.value = '';
                                     }
                                     toggleMetroForm();
                                } else {
                                    document.getElementById('metro_option').value = '';
                                    toggleMetroForm();
                                }
                            } else {
                                resetZabbixFields();
                            }
                            // Hosting
                            if (data.hosting) {
                                document.getElementById('hosting_server_id').value = data.hosting.hosting_server_id || '';
                                document.getElementById('domain').value = data.hosting.domain || '';
                                document.getElementById('username').value = data.hosting.username || '';
                                document.getElementById('password').value = '';
                            }
                            if (data.domain) {
                                document.getElementById('domain').value = data.domain.domain_name || '';
                            }

                            resetSubscriptionFormTabs();
                        });
                };

            })();
        </script>
    @endpush
</x-app-layout>
