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
                        <button type="button" onclick="openClientModal()"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                            Lihat Profil <i data-lucide="user-circle" class="w-3 h-3"></i>
                        </button>
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

                {{-- Network Topology Editor (only for connectivity services) --}}
                @if($subscription->connectivity)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="git-branch" class="w-4 h-4"></i>
                            Topologi Jaringan
                        </h4>
                        
                        {{-- React Flow will mount here --}}
                        <div 
                            id="topology-editor-root"
                            data-subscription-id="{{ $subscription->id }}"
                            data-api-base-url="{{ url('/') }}"
                            data-can-edit="{{ auth()->user()->division && strtoupper(auth()->user()->division->name) === 'NOC' || auth()->user()->hasRole('admin') || auth()->user()->hasRole('Owner') ? 'true' : 'false' }}"
                        ></div>
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

    <!-- Client Profile Modal -->
    <div id="clientModal" class="fixed inset-0 z-50 hidden">
        <div id="clientModalBackdrop" class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity opacity-0"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div id="clientModalPanel"
                class="bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all scale-95 opacity-0">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($subscription->client->name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <span>{{ $subscription->client->name ?? '-' }}</span>
                            <p class="text-sm font-normal text-slate-500 font-mono">{{ $subscription->client->client_code ?? '-' }}</p>
                        </div>
                    </h3>
                    <button type="button" onclick="closeClientModal()"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
                    </button>
                </div>

                <!-- Modal Body with Tabs -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <!-- Tabs -->
                    <div class="flex gap-2 mb-6 border-b border-slate-200 dark:border-slate-700 pb-3">
                        <button type="button" id="tabInfo" onclick="switchTab('info')"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-colors bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>Informasi
                        </button>
                        <button type="button" id="tabContacts" onclick="switchTab('contacts')"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-colors text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <i data-lucide="users" class="w-4 h-4 inline mr-1"></i>Kontak Person
                        </button>
                    </div>

                    <!-- Tab: Info -->
                    <div id="panelInfo" class="space-y-4">
                        @php $client = $subscription->client; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tipe</p>
                                <p class="text-slate-800 dark:text-white font-medium">
                                    @if($client->type === 'personal')
                                        <span class="inline-flex items-center gap-1"><i data-lucide="user" class="w-4 h-4"></i> Personal</span>
                                    @else
                                        <span class="inline-flex items-center gap-1"><i data-lucide="building" class="w-4 h-4"></i> Perusahaan</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">NIK / NPWP</p>
                                <p class="text-slate-800 dark:text-white font-mono">{{ $client->identity_number ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Alamat</p>
                                <p class="text-slate-800 dark:text-white">{{ $client->address ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Kota</p>
                                <p class="text-slate-800 dark:text-white">{{ $client->city ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Kode Pos</p>
                                <p class="text-slate-800 dark:text-white font-mono">{{ $client->postal_code ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Status</p>
                                @php
                                    $clientStatusStyles = [
                                        'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'inactive' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $clientStatusStyles[$client->status ?? 'active'] ?? $clientStatusStyles['active'] }}">
                                    {{ ucfirst($client->status ?? 'active') }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Cabang</p>
                                <p class="text-slate-800 dark:text-white">{{ $client->branch->name ?? '-' }}</p>
                            </div>
                        </div>
                        @if($client->notes)
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Catatan</p>
                            <p class="text-slate-700 dark:text-slate-300 text-sm">{{ $client->notes }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Tab: Contacts -->
                    <div id="panelContacts" class="hidden space-y-3">
                        @php $contacts = $subscription->client->contacts ?? collect(); @endphp
                        @forelse($contacts as $contact)
                            <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-teal-500 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h5 class="font-bold text-slate-800 dark:text-white">{{ $contact->name }}</h5>
                                        @if($contact->is_primary)
                                            <span class="text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 px-2 py-0.5 rounded-full font-medium">Utama</span>
                                        @endif
                                    </div>
                                    @if($contact->position)
                                        <p class="text-sm text-slate-500">{{ $contact->position }}</p>
                                    @endif
                                    <div class="mt-2 flex flex-wrap gap-3 text-sm">
                                        @if($contact->phone)
                                            <a href="tel:{{ $contact->phone }}" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700">
                                                <i data-lucide="phone" class="w-3 h-3"></i>{{ $contact->phone }}
                                            </a>
                                        @endif
                                        @if($contact->email)
                                            <a href="mailto:{{ $contact->email }}" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700">
                                                <i data-lucide="mail" class="w-3 h-3"></i>{{ $contact->email }}
                                            </a>
                                        @endif
                                        @if($contact->whatsapp)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="inline-flex items-center gap-1 text-green-600 hover:text-green-700">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i>WhatsApp
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400">
                                <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p>Belum ada kontak person</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeClientModal()"
                            class="px-4 py-2 rounded-xl font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            Tutup
                        </button>
                        <a href="{{ route('clients.index') }}?client_id={{ $subscription->client_id }}"
                            class="px-4 py-2 rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white transition-colors flex items-center gap-2">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            Buka Halaman Pelanggan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openClientModal() {
            const modal = document.getElementById('clientModal');
            const backdrop = document.getElementById('clientModalBackdrop');
            const panel = document.getElementById('clientModalPanel');

            modal.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('scale-95', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
            }, 10);

            lucide.createIcons();
        }

        function closeClientModal() {
            const modal = document.getElementById('clientModal');
            const backdrop = document.getElementById('clientModalBackdrop');
            const panel = document.getElementById('clientModalPanel');

            backdrop.classList.add('opacity-0');
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        function switchTab(tab) {
            const tabInfo = document.getElementById('tabInfo');
            const tabContacts = document.getElementById('tabContacts');
            const panelInfo = document.getElementById('panelInfo');
            const panelContacts = document.getElementById('panelContacts');

            const activeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
            const inactiveClass = 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700';

            if (tab === 'info') {
                tabInfo.className = `px-4 py-2 rounded-xl text-sm font-bold transition-colors ${activeClass}`;
                tabContacts.className = `px-4 py-2 rounded-xl text-sm font-bold transition-colors ${inactiveClass}`;
                panelInfo.classList.remove('hidden');
                panelContacts.classList.add('hidden');
            } else {
                tabContacts.className = `px-4 py-2 rounded-xl text-sm font-bold transition-colors ${activeClass}`;
                tabInfo.className = `px-4 py-2 rounded-xl text-sm font-bold transition-colors ${inactiveClass}`;
                panelContacts.classList.remove('hidden');
                panelInfo.classList.add('hidden');
            }

            lucide.createIcons();
        }

        // Close on backdrop click
        document.getElementById('clientModalBackdrop')?.addEventListener('click', closeClientModal);

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeClientModal();
        });
    </script>

    @if($subscription->connectivity)
        @push('vite')
            @vite(['resources/js/topology/index.jsx'])
        @endpush
    @endif
</x-app-layout>