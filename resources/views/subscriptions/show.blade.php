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
                            {{ $subscription->subscription_code }}</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Info Card -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
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
                                {{ $subscription->client->name ?? '-' }}</h5>
                            <p class="text-sm text-slate-500 font-mono">{{ $subscription->client->client_code ?? '-' }}
                            </p>
                            @if($subscription->client->address)
                                <p class="text-sm text-slate-500 mt-2">{{ $subscription->client->address }}</p>
                            @endif
                        </div>
                        <a href="{{ route('clients.index') }}?client_id={{ $subscription->client_id }}"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                            Lihat Profil <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>

                <!-- Package & Service Info -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        Paket Layanan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Layanan</p>
                            <p class="text-slate-800 dark:text-white font-medium">
                                {{ $subscription->package->service->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Paket</p>
                            <p class="text-slate-800 dark:text-white font-medium">
                                {{ $subscription->package->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tanggal Pasang</p>
                            <p class="text-slate-800 dark:text-white font-medium">
                                {{ $subscription->installed_at ? $subscription->installed_at->format('d M Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tanggal Billing Berikutnya
                            </p>
                            <p class="text-slate-800 dark:text-white font-medium">
                                {{ $subscription->next_billing_date ? $subscription->next_billing_date->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Technical Details -->
                @if($subscription->connectivity)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="wifi" class="w-4 h-4"></i>
                            Detail Koneksi Internet
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">IP Address</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->connectivity->ip_address ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">PPPoE Username</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->connectivity->pppoe_user ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Router</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->connectivity->router->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Modem SN (ONT)</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->connectivity->ont_sn ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">VLAN ID</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->connectivity->vlan_id ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Signal RX</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->connectivity->signal_rx ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($subscription->hosting)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="server" class="w-4 h-4"></i>
                            Detail Hosting
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Domain</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->hosting->domain ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Username</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->hosting->username ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Server</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->hosting->hostingServer->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Disk Quota</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->hosting->disk_quota_gb ?? 0 }} GB</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Email Accounts</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->hosting->email_accounts ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Databases</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->hosting->databases ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($subscription->domain)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                            Detail Domain
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Nama Domain</p>
                                <p class="text-slate-800 dark:text-white font-mono">
                                    {{ $subscription->domain->domain_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Registrar</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->domain->registrar ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tanggal Expired</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    {{ $subscription->domain->expires_at ? $subscription->domain->expires_at->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if($subscription->notes)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
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
                                {{ number_format($subscription->package->price ?? 0, 0, ',', '.') }}</p>
                        </div>

                        @if($subscription->custom_price)
                            <div class="pt-3 border-t border-white/20">
                                <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Harga Khusus (Deal)</p>
                                <p class="text-2xl font-bold font-mono">Rp
                                    {{ number_format($subscription->custom_price, 0, ',', '.') }}</p>
                            </div>
                        @endif

                        @if($subscription->discount_percent)
                            <div>
                                <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Diskon</p>
                                <p class="text-lg font-bold">{{ $subscription->discount_percent }}%</p>
                                @if($subscription->discount_notes)
                                    <p class="text-xs opacity-70 mt-1">{{ $subscription->discount_notes }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="pt-3 border-t border-white/20">
                            <p class="text-xs opacity-70 uppercase tracking-wider mb-1">Harga Efektif</p>
                            <p class="text-3xl font-bold font-mono">Rp
                                {{ number_format($subscription->effective_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Billing Cycle Card -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
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
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
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
</x-app-layout>