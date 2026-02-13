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
                    <a href="{{ route('subscriptions.index') }}?edit={{ $subscription->id }}"
                        class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-yellow-200 dark:shadow-none transition-all">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        <span>Edit</span>
                    </a>
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

                            @if($subscription->discount_percent)
                                <div>
                                    <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Diskon</p>
                                    <p class="text-lg font-bold">{{ $subscription->discount_percent }}%</p>
                                </div>
                            @endif
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
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Signal RX</p>
                            <p class="text-slate-800 dark:text-white font-mono text-lg">
                                {{ $subscription->connectivity->signal_rx ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Topologi Tab (Full Width Editor) -->
        @if($subscription->connectivity)
            <div id="panelTopologi" class="tab-panel hidden">
                <div
                    class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    {{-- React Flow will mount here - FULL WIDTH --}}
                    <div id="topology-editor-root" data-subscription-id="{{ $subscription->id }}"
                        data-api-base-url="{{ url('/') }}" data-can-edit="true" style="height: 75vh; min-height: 600px;">
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
                                {{ $subscription->client->client_code ?? '-' }}</p>
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
            const tabs = ['Info', 'Teknis', 'Topologi', 'Hosting', 'Domain'];
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
</x-app-layout>