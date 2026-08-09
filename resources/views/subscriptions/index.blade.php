<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Layanan Pelanggan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola langganan Internet, Hosting, dan
                        layanan lainnya.</p>
                </div>
                <button onclick="window.openModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Layanan</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">ID Layanan</th>
                            <th class="p-4">Pelanggan</th>
                            <th class="p-4">Paket & Layanan</th>
                            <th class="p-4">Tgl Pasang</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 pr-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <!-- Data populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div id="formModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="formModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]"
                id="formModalPanel">

                <!-- Modal Header -->
                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Layanan Baru
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
                                    <p id="package-edit-hint" class="hidden mt-2 text-xs text-slate-500 dark:text-slate-400">Saat edit, paket hanya dapat diganti ke jenis layanan yang sama agar data teknis tetap konsisten.</p>
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

                                <div id="metro-ethernet-section" class="hidden">
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
                                                <!-- Assuming $vendors is available, if not passed explicitly, maybe fetch via $metroEthernets relationship or pass it from controller -->
                                                @if(isset($vendors))
                                                    @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                    @endforeach
                                                @else
                                                     <!-- Fallback if vendors not passed directly, though better to pass it -->
                                                     @php $uniqueVendors = $metroEthernets->pluck('vendor')->unique('id')->filter(); @endphp
                                                     @foreach($uniqueVendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                     @endforeach
                                                @endif
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
                            </div>

                            <div id="fields-hosting" class="hidden space-y-4">
                                <div class="rounded-lg border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900/50 dark:bg-blue-950/20">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Metode akun HestiaCP</p>
                                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-800">
                                            <input type="radio" name="hosting_account_mode" value="new" checked onchange="toggleHostingAccountMode()" class="mt-1 text-blue-600 focus:ring-blue-500">
                                            <span><span class="block text-sm font-semibold text-slate-800 dark:text-white">Buat akun baru</span><span class="block text-xs text-slate-500 dark:text-slate-400">CRM membuat user dan domain melalui antrean provisioning.</span></span>
                                        </label>
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-800">
                                            <input type="radio" name="hosting_account_mode" value="existing" onchange="toggleHostingAccountMode()" class="mt-1 text-blue-600 focus:ring-blue-500">
                                            <span><span class="block text-sm font-semibold text-slate-800 dark:text-white">Tautkan user existing</span><span class="block text-xs text-slate-500 dark:text-slate-400">CRM hanya menautkan akun yang sudah ada, tanpa mengubah server.</span></span>
                                        </label>
                                    </div>
                                    <p id="hosting-account-mode-help" class="mt-3 text-xs text-slate-500 dark:text-slate-400"></p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div id="div-hosting-server">
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Hosting
                                            Server</label>
                                        <select id="hosting_server_id" name="hosting_server_id" onchange="handleHostingServerChange()"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="">-- Pilih Server Hosting --</option>
                                            @foreach($servers as $server)
                                                <option value="{{ $server->id }}">{{ $server->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="hosting-new-domain-field">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama
                                            Domain</label>
                                        <input type="text" name="domain" id="domain" placeholder="example.com"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div id="hosting-existing-domain-field" class="hidden">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Domain akun</label>
                                        <select name="domain" id="hosting_existing_domain" disabled
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="">-- Pilih user terlebih dahulu --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div id="hosting-new-username-field">
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username
                                            (Panel)</label>
                                        <input type="text" name="username" id="username"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div id="hosting-existing-username-field" class="hidden">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">User HestiaCP</label>
                                        <select name="username" id="hosting_existing_username" disabled onchange="handleExistingHostingUserChange()"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="">-- Pilih server terlebih dahulu --</option>
                                        </select>
                                    </div>
                                    <div id="hosting-password-field">
                                        <label
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password</label>
                                        <input type="password" name="password" id="password"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                            </div>

                            <div id="fields-domain" class="hidden space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Domain <span class="text-red-500">*</span></label>
                                        <input type="text" name="domain_name" id="domain_name" placeholder="example.com"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Registrar <span class="text-xs text-slate-400 font-normal">(opsional)</span></label>
                                        <input type="text" name="registrar" id="registrar" placeholder="Contoh: Rumahweb"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal Registrasi</label>
                                        <input type="date" name="registered_at" id="registered_at"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal Berakhir</label>
                                        <input type="date" name="expires_at" id="expires_at"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Auth Code <span class="text-xs text-slate-400 font-normal">(opsional)</span></label>
                                    <input type="password" name="auth_code" id="auth_code" autocomplete="new-password"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan Domain</label>
                                    <textarea name="domain_notes" id="domain_notes" rows="3"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"></textarea>
                                </div>
                            </div>

                            <div id="fields-mail" class="hidden space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Mail Server <span class="text-red-500">*</span></label>
                                        <select id="mail_server_id" name="mail_server_id"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            <option value="">-- Pilih Mail Server --</option>
                                            @foreach($mailServers as $server)
                                                <option value="{{ $server->id }}">{{ $server->name }} ({{ $server->host }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Domain <span class="text-red-500">*</span></label>
                                        <input type="text" name="mail_domain" id="mail_domain" placeholder="example.com"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kontak Admin <span class="text-xs text-slate-400 font-normal">(opsional, tidak membuat mailbox)</span></label>
                                        <input type="email" name="admin_email" id="admin_email" placeholder="admin@example.com"
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

    <x-confirm-modal />

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

        <style>
            /* Choices.js Custom Styling - Matches Tailwind Design */
            .choices {
                margin-bottom: 0 !important;
                width: 100% !important;
            }

            .choices__inner {
                min-height: 46px !important;
                border-radius: 0.75rem !important;
                border: 1px solid #e2e8f0 !important;
                background-color: #f8fafc !important;
                padding: 0.625rem 0.75rem !important;
                font-size: 0.875rem !important;
                transition: all 0.15s ease-in-out !important;
            }

            .choices__inner:focus,
            .choices.is-focused .choices__inner {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3) !important;
                outline: none !important;
            }

            .choices__list--single {
                padding: 0 !important;
            }

            .choices__placeholder {
                color: #94a3b8 !important;
                opacity: 1 !important;
            }

            /* Dropdown */
            .choices__list--dropdown {
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.75rem !important;
                overflow: hidden !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
                margin-top: 0.5rem !important;
                z-index: 9999 !important;
            }

            .choices__list--dropdown .choices__list {
                max-height: 260px !important;
                overflow-y: auto !important;
            }

            .choices__list--dropdown .choices__item {
                padding: 0.625rem 0.875rem !important;
                font-size: 0.875rem !important;
                color: #334155 !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            .choices__list--dropdown .choices__item--selectable.is-highlighted {
                background-color: #3b82f6 !important;
                color: white !important;
            }

            .choices__list--dropdown .choices__item:last-child {
                border-bottom: 0 !important;
            }

            /* Search Input */
            .choices__input {
                background-color: transparent !important;
                font-size: 0.875rem !important;
                margin-bottom: 0 !important;
                padding: 0 !important;
            }

            .choices[data-type*="select-one"] .choices__input,
            .choices__list--dropdown .choices__input--cloned {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0.875rem 1rem !important;
                background-color: #ffffff !important;
                border: 0 !important;
                border-bottom: 1px solid #e2e8f0 !important;
                border-radius: 0 !important;
                color: #334155 !important;
                box-shadow: none !important;
            }

            .choices[data-type*="select-one"] .choices__input::placeholder,
            .choices__list--dropdown .choices__input--cloned::placeholder {
                color: #94a3b8 !important;
                opacity: 1 !important;
            }

            /* Dark Mode */
            .dark .choices__inner {
                background-color: rgba(51, 65, 85, 0.5) !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }

            .dark .choices__list--dropdown {
                background-color: #1e293b !important;
                border-color: #475569 !important;
            }

            .dark .choices[data-type*="select-one"] .choices__input,
            .dark .choices__list--dropdown .choices__input--cloned {
                background-color: #1e293b !important;
                border-bottom-color: #334155 !important;
                color: #e2e8f0 !important;
            }

            .dark .choices__list--dropdown .choices__item {
                color: #e2e8f0 !important;
                border-bottom-color: #334155 !important;
            }

            .dark .choices__list--dropdown .choices__item--selectable.is-highlighted {
                background-color: #3b82f6 !important;
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
                let tableData = @json($subscriptions);
                let table;
                let clientChoice; // Declare in outer scope
                let zabbixGraphOptions = [];
                let selectedZabbixInterfaces = [];
                let activeSubscriptionFormTab = 'general';

                const zabbixRoutes = {
                    groups: '{{ route('zabbix-monitors.groups') }}',
                    hosts: '{{ route('zabbix-monitors.hosts') }}',
                    graphs: '{{ route('zabbix-monitors.graphs') }}'
                };

                $(document).ready(function () {
                    // Initialize Choices.js (works perfectly in modals)
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

                    // Initialize DataTable
                    table = $('#dataTable').DataTable({
                        data: tableData,
                        columns: [
                            {
                                data: 'subscription_code',
                                className: 'p-4 pl-6',
                                render: (data) => `<span class="font-mono text-sm font-bold text-slate-600 dark:text-slate-300">${data}</span>`
                            },
                            {
                                data: 'client',
                                className: 'p-4',
                                render: (data) => data ? `
                                                                                                    <div class="font-bold text-slate-800 dark:text-white">${data.name}</div>
                                                                                                    <div class="text-xs text-slate-500 font-mono">${data.client_code}</div>
                                                                                                ` : '-'
                            },
                            {
                                data: 'package',
                                className: 'p-4',
                                render: (data) => data ? `
                                                                                                    <div class="flex items-center gap-2">
                                                                                                        <div class="font-medium text-slate-700 dark:text-slate-300">${data.name}</div>
                                                                                                    </div>
                                                                                                    <div class="text-xs text-slate-500">${data.service ? data.service.name : '-'}</div>
                                                                                                ` : '-'
                            },
                            {
                                data: 'installed_at',
                                className: 'p-4',
                                render: (data) => data ? new Date(data).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
                            },
                            {
                                data: 'status',
                                className: 'p-4',
                                render: function (data) {
                                    const styles = {
                                        'active': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'suspended': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'terminated': 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400'
                                    };
                                    const labels = {
                                        'active': 'Aktif',
                                        'pending': 'Pending',
                                        'suspended': 'Suspend',
                                        'terminated': 'Berhenti'
                                    };
                                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${styles[data] || styles.pending}">${labels[data] || data}</span>`;
                                }
                            },
                            {
                                data: null,
                                className: "p-4 pr-6 text-center",
                                orderable: false,
                                render: function (data, type, row) {
                                    return `
                                                                                                        <div class="flex items-center justify-center gap-2">
                                                                                                            <a href="${baseUrl}/subscriptions/${row.id}" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat Detail">
                                                                                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                                                                                            </a>
                                                                                                            <button onclick="window.editData(${row.id})" class="p-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 text-yellow-600 rounded-lg transition-colors" title="Edit">
                                                                                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                                                                                            </button>
                                                                                                            <button onclick="window.deleteData(${row.id})" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors" title="Hapus">
                                                                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    `;
                                }
                            }
                        ],
                        dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-4"ip>',
                        language: {
                            search: "",
                            searchPlaceholder: "Cari layanan...",
                            lengthMenu: "Tampilkan _MENU_",
                            info: "_START_ - _END_ dari _TOTAL_",
                            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                        },
                        drawCallback: function () { lucide.createIcons(); },
                        createdRow: function (row) { $(row).addClass('hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors'); }
                    });
                });

                window.handlePackageChange = function () {
                    const packageSelect = document.getElementById('package_id');
                    const selectedOption = packageSelect.options[packageSelect.selectedIndex];
                    const serviceType = selectedOption ? selectedOption.getAttribute('data-type') : null;

                    // Update package price display
                    const price = selectedOption ? (selectedOption.getAttribute('data-price') || 0) : 0;
                    const priceDisplay = document.getElementById('package_price_display');
                    if (priceDisplay) {
                        priceDisplay.textContent = 'Rp ' + Number(price).toLocaleString('id-ID');
                    }

const detailsSection = document.getElementById('technical-details');
                    const fieldsConn = document.getElementById('fields-connectivity');
                    const fieldsHost = document.getElementById('fields-hosting');
                    const fieldsDomain = document.getElementById('fields-domain');
                    const fieldsMail = document.getElementById('fields-mail');
                    const metroSection = document.getElementById('metro-ethernet-section');
                    const technicalEmptyState = document.getElementById('technical-empty-state');
                    const isConnectivity = serviceType === 'connectivity';

                    [fieldsConn, fieldsHost, fieldsDomain, fieldsMail].forEach((section) => {
                        if (!section) return;
                        section.classList.add('hidden');
                        section.querySelectorAll('input, select, textarea').forEach((field) => {
                            field.disabled = true;
                        });
                    });
                    if (metroSection) {
                        metroSection.classList.toggle('hidden', !isConnectivity);
                        metroSection.querySelectorAll('input, select, textarea').forEach((field) => {
                            field.disabled = !isConnectivity;
                        });
                    }
                    if (detailsSection) detailsSection.classList.add('hidden');
                    if (technicalEmptyState) technicalEmptyState.classList.remove('hidden');

                    if (serviceType) {
                        if (detailsSection) detailsSection.classList.remove('hidden');
                        if (technicalEmptyState) technicalEmptyState.classList.add('hidden');
                        if (isConnectivity) {
                            if (fieldsConn) {
                                fieldsConn.classList.remove('hidden');
                                fieldsConn.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = false);
                            }
                            loadZabbixGroups();
                            toggleMetroForm();
                        } else if (serviceType === 'hosting') {
                            if (fieldsHost) {
                                fieldsHost.classList.remove('hidden');
                                fieldsHost.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = false);
                            }
                            toggleHostingAccountMode();
} else if (serviceType === 'domain' && fieldsDomain) {
                            fieldsDomain.classList.remove('hidden');
                            fieldsDomain.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = false);
                        } else if (serviceType === 'mail') {
                            if (fieldsMail) {
                                fieldsMail.classList.remove('hidden');
                                fieldsMail.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = false);
                            }
                        }
                    }

                    updateBillingPreview();
                };

                window.toggleMetroForm = function() {
                    const metroOption = document.getElementById('metro_option');
                    const newForm = document.getElementById('metro-new-form');
                    const hiddenIdInput = document.getElementById('metro_ethernet_id_input');
                    if (!metroOption || metroOption.disabled || !newForm || !hiddenIdInput) {
                        return;
                    }
                    const selectedOption = metroOption.options[metroOption.selectedIndex];

                    if (metroOption.value === 'new') {
                        newForm.classList.remove('hidden');
                        hiddenIdInput.value = ''; // Clear ID if creating new
                    } else if (metroOption.value === 'existing') {
                        newForm.classList.add('hidden');
                        hiddenIdInput.value = selectedOption.getAttribute('data-id');
                    } else {
                        newForm.classList.add('hidden');
                        hiddenIdInput.value = '';
                    }
                };

                window.toggleHostingAccountMode = function(loadUsers = true) {
                    const mode = document.querySelector('input[name="hosting_account_mode"]:checked')?.value || 'new';
                    const passwordField = document.getElementById('hosting-password-field');
                    const password = document.getElementById('password');
                    const help = document.getElementById('hosting-account-mode-help');
                    const newUsernameField = document.getElementById('hosting-new-username-field');
                    const newDomainField = document.getElementById('hosting-new-domain-field');
                    const existingUsernameField = document.getElementById('hosting-existing-username-field');
                    const existingDomainField = document.getElementById('hosting-existing-domain-field');
                    const newUsername = document.getElementById('username');
                    const newDomain = document.getElementById('domain');
                    const existingUsername = document.getElementById('hosting_existing_username');
                    const existingDomain = document.getElementById('hosting_existing_domain');

                    if (!passwordField || !password) return;

                    const isExisting = mode === 'existing';
                    passwordField.classList.toggle('hidden', isExisting);
                    password.disabled = isExisting;
                    password.required = !isExisting && !document.getElementById('dataId')?.value;
                    newUsernameField?.classList.toggle('hidden', isExisting);
                    newDomainField?.classList.toggle('hidden', isExisting);
                    existingUsernameField?.classList.toggle('hidden', !isExisting);
                    existingDomainField?.classList.toggle('hidden', !isExisting);
                    if (newUsername) newUsername.disabled = isExisting;
                    if (newDomain) newDomain.disabled = isExisting;
                    if (existingUsername) existingUsername.disabled = !isExisting;
                    if (existingDomain) existingDomain.disabled = !isExisting;

                    if (isExisting) password.value = '';
                    if (help) {
                        help.textContent = isExisting
                            ? 'Username dan domain akan diverifikasi pada HestiaCP. Akun tertaut bersifat read-only dan tidak akan diprovisikan atau diubah oleh CRM.'
                            : 'Password wajib untuk akun baru. CRM akan membuat user serta domain setelah layanan disimpan.';
                    }

                    if (loadUsers && isExisting && document.getElementById('hosting_server_id')?.value) {
                        window.loadExistingHostingUsers(existingUsername?.value || '', existingDomain?.value || '');
                    }
                };

                window.loadExistingHostingUsers = async function(selectedUsername = '', selectedDomain = '') {
                    const serverId = document.getElementById('hosting_server_id')?.value;
                    const userSelect = document.getElementById('hosting_existing_username');

                    if (!serverId || !userSelect) return;

                    resetExistingHostingDomains();
                    setExistingHostingOptions(userSelect, [], '-- Memuat user HestiaCP... --');

                    try {
                        const response = await fetch(`${baseUrl}/subscriptions/hosting-servers/${serverId}/users`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Daftar user tidak dapat dimuat.');

                        setExistingHostingOptions(userSelect, payload.users || [], '-- Pilih user HestiaCP --', selectedUsername);
                        if (selectedUsername) await loadExistingHostingDomains(selectedUsername, selectedDomain);
                    } catch (error) {
                        setExistingHostingOptions(userSelect, [], '-- Gagal memuat user HestiaCP --');
                        window.showToast?.(error.message || 'Daftar user HestiaCP tidak dapat dimuat.', 'error');
                    }
                };

                window.handleHostingServerChange = function() {
                    if (document.querySelector('input[name="hosting_account_mode"]:checked')?.value === 'existing') {
                        window.loadExistingHostingUsers();
                    }
                };

                window.handleExistingHostingUserChange = function() {
                    const username = document.getElementById('hosting_existing_username')?.value;
                    if (username) loadExistingHostingDomains(username);
                    else resetExistingHostingDomains();
                };

                function resetExistingHostingDomains() {
                    setExistingHostingOptions(document.getElementById('hosting_existing_domain'), [], '-- Pilih user terlebih dahulu --');
                }

                async function loadExistingHostingDomains(username, selectedDomain = '') {
                    const serverId = document.getElementById('hosting_server_id')?.value;
                    const domainSelect = document.getElementById('hosting_existing_domain');
                    if (!serverId || !domainSelect) return;

                    setExistingHostingOptions(domainSelect, [], '-- Memuat domain... --');
                    try {
                        const response = await fetch(`${baseUrl}/subscriptions/hosting-servers/${serverId}/domains?username=${encodeURIComponent(username)}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Domain tidak dapat dimuat.');

                        setExistingHostingOptions(domainSelect, payload.domains || [], '-- Pilih domain akun --', selectedDomain);
                    } catch (error) {
                        setExistingHostingOptions(domainSelect, [], '-- Gagal memuat domain --');
                        window.showToast?.(error.message || 'Domain HestiaCP tidak dapat dimuat.', 'error');
                    }
                }

                function setExistingHostingOptions(select, values, placeholder, selectedValue = '') {
                    if (!select) return;
                    select.innerHTML = `<option value="">${placeholder}</option>` + values
                        .map(value => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`)
                        .join('');
                    select.disabled = values.length === 0;
                    if (selectedValue && values.includes(selectedValue)) select.value = selectedValue;

                    if (select.id === 'hosting_existing_username' && window.hostingUserChoice) {
                        window.hostingUserChoice.destroy();
                        window.hostingUserChoice = null;
                    }
                    if (select.id === 'hosting_existing_username' && values.length > 0 && window.Choices) {
                        window.hostingUserChoice = new Choices(select, {
                            searchEnabled: true,
                            searchPlaceholderValue: 'Cari user HestiaCP...',
                            shouldSort: false,
                            itemSelectText: ''
                        });
                        if (selectedValue && values.includes(selectedValue)) {
                            window.hostingUserChoice.setChoiceByValue(selectedValue);
                        }
                    }
                }

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
                    const ppnAmount = usesPpn ? (basePrice * @json(\App\Models\SystemSetting::get('billing.ppn_rate', 11) / 100)) : 0;
                    const pph23Amount = usesPph23 ? (basePrice * @json(\App\Models\SystemSetting::get('billing.pph23_rate', 2) / 100)) : 0;
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

                function setPackageEditScope(serviceType = null) {
                    const packageSelect = document.getElementById('package_id');
                    const hint = document.getElementById('package-edit-hint');

                    Array.from(packageSelect.options).forEach((option) => {
                        option.disabled = Boolean(serviceType && option.value && option.getAttribute('data-type') !== serviceType);
                    });

                    hint?.classList.toggle('hidden', !serviceType);
                }

                function setClientEditable(isEditable) {
                    if (!clientChoice) return;

                    if (isEditable) {
                        clientChoice.enable();
                    } else {
                        clientChoice.disable();
                    }
                }

                function initializeZabbixSelectors() {
                    const groupSelect = document.getElementById('zabbix_group_id');
                    const hostSelect = document.getElementById('zabbix_host_id');
                    const toggle = document.getElementById('zabbixInterfaceToggle');
                    const dropdown = document.getElementById('zabbixInterfaceDropdown');

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
                        if (hostSelect.disabled) {
                            return;
                        }
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
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Group Zabbix tidak dapat dimuat.');
                        const groups = payload;
                        groupSelect.innerHTML = '<option value="">Pilih group</option>';
                        groups.forEach(group => {
                            groupSelect.insertAdjacentHTML('beforeend', `<option value="${group.groupid}">${group.name}</option>`);
                        });
                        groupSelect.value = selectedValue;
                    } catch (error) {
                        groupSelect.innerHTML = '<option value="">Group Zabbix tidak tersedia</option>';
                        showToast(error.message || 'Gagal memuat group Zabbix', 'error');
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
                        showToast('Gagal memuat host Zabbix', 'error');
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
                        showToast('Gagal memuat interface Zabbix', 'error');
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

                // Modal Functions
                window.openModal = function (isEdit = false) {
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');

                        // Remove transform after animation to fix absolute positioning of Select2
                        setTimeout(() => {
                            panel.classList.remove('scale-100');
                        }, 350);
                    }, 10);

                    if (!isEdit) {
                        document.getElementById('modalTitle').innerText = 'Tambah Layanan Baru';
                        document.getElementById('submitText').innerText = 'Simpan Layanan';
                        document.getElementById('dataForm').reset();
                        document.getElementById('dataId').value = '';
                        const today = new Date();
                        today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
                        document.getElementById('installed_at').value = today.toISOString().slice(0, 10);

                        // Reset Choices.js
                        if (clientChoice) {
                            setClientEditable(true);
                            clientChoice.setChoiceByValue('');
                        }
                        setPackageEditScope();

                        resetZabbixFields();

                        // Reset fields view
                        handlePackageChange();
                        document.getElementById('uses_ppn').checked = false;
                        document.getElementById('uses_pph23').checked = false;
                        updateBillingPreview();
                        resetSubscriptionFormTabs();
                    }
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

                // Form Submit
                window.submitForm = function () {
                    document.getElementById('dataForm').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                }

                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const installedAt = document.getElementById('installed_at');
                    if (!installedAt || !installedAt.value) {
                        window.switchSubscriptionFormTab('general');
                        showToast('Tanggal pemasangan wajib diisi.', 'error');
                        installedAt?.focus();
                        return;
                    }

                    const id = document.getElementById('dataId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/subscriptions/${id}` : `${baseUrl}/subscriptions`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Layanan' : 'Simpan Layanan';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
                    formData.set('installed_at', installedAt.value);
                    if (isUpdate) formData.append('_method', 'PUT');

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
                            setButtonLoading(btn, spinner, text, false, originalText);
                            if (res.success) {
                                if (isUpdate) {
                                    const index = tableData.findIndex(d => d.id === parseInt(id));
                                    if (index >= 0) tableData[index] = res.subscription;
                                    showToast('Layanan berhasil diperbarui!');
                                } else {
                                    tableData.push(res.subscription);
                                    showToast('Layanan berhasil ditambahkan!');
                                }
                                table.clear().rows.add(tableData).draw();
                                window.closeModal();
                            } else {
                                let errorMsg = res.message || 'Gagal menyimpan data';
                                if (res.errors) errorMsg = Object.values(res.errors).flat().join(', ');
                                showToast(errorMsg, 'error');
                            }
                        })
                        .catch(error => {
                            setButtonLoading(btn, spinner, text, false, originalText);
                            console.error(error);
                            showToast('Terjadi kesalahan!', 'error');
                        });
                });

                // Edit Data (Fetch full details first)
                window.editData = function (id) {
                    const btn = document.querySelector(`button[onclick="window.editData(${id})"]`);
                    // Optional: spinner on button?

                    fetch(`${baseUrl}/subscriptions/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(r => r.json())
                        .then(async data => {
                            document.getElementById('modalTitle').innerText = 'Edit Layanan';
                            document.getElementById('submitText').innerText = 'Update Layanan';
                            document.getElementById('dataId').value = data.id;

                            // Fill Form Values
                            clientChoice.setChoiceByValue(data.client_id.toString());
                            document.getElementById('package_id').value = data.package_id;
                            const currentServiceType = document.getElementById('package_id').selectedOptions[0]?.getAttribute('data-type');
                            setClientEditable(false);
                            setPackageEditScope(currentServiceType);
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
                                     // Find option with data-id matching the ID
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
                                         // Handle case if referenced metro is not in list (deleted/inactive?)
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
                            if (data.hosting) {
                                document.getElementById('hosting_server_id').value = data.hosting.hosting_server_id || '';
                                const hostingMode = data.hosting.managed_by_crm ? 'new' : 'existing';
                                document.querySelector(`input[name="hosting_account_mode"][value="${hostingMode}"]`).checked = true;
                                toggleHostingAccountMode(false);
                                if (hostingMode === 'existing') {
                                    await loadExistingHostingUsers(data.hosting.username || '', data.hosting.domain || '');
                                } else {
                                    document.getElementById('domain').value = data.hosting.domain || '';
                                    document.getElementById('username').value = data.hosting.username || '';
                                }
                            }
                            if (data.domain) {
                                document.getElementById('domain_name').value = data.domain.domain_name || '';
                                document.getElementById('registrar').value = data.domain.registrar || '';
                                document.getElementById('registered_at').value = data.domain.registered_at ? data.domain.registered_at.split('T')[0] : '';
                                document.getElementById('expires_at').value = data.domain.expires_at ? data.domain.expires_at.split('T')[0] : '';
document.getElementById('auth_code').value = '';
                                document.getElementById('domain_notes').value = data.domain.notes || '';
                            }
                            if (data.mailHosting) {
                                document.getElementById('mail_server_id').value = data.mailHosting.mail_server_id || '';
                                document.getElementById('mail_domain').value = data.mailHosting.domain || '';
                                document.getElementById('admin_email').value = data.mailHosting.admin_email || '';
                            }

                            window.openModal(true);
                            resetSubscriptionFormTabs();
                        })
                        .catch(e => {
                            console.error(e);
                            showToast('Gagal memuat data detail', 'error');
                        });
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Layanan?', 'Data langganan akan dihapus permanen. Tagihan terkait mungkin akan terpengaruh.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/subscriptions/${deleteId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ _method: 'DELETE' })
                        })
                            .then(r => r.json())
                            .then(data => {
                                setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                                hideConfirmModal();
                                if (data.success) {
                                    tableData = tableData.filter(d => d.id !== deleteId);
                                    table.clear().rows.add(tableData).draw();
                                    showToast('Layanan berhasil dihapus!');
                                } else {
                                    showToast(data.message || 'Gagal menghapus data', 'error');
                                }
                            })
                            .catch(() => {
                                setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                                showToast('Terjadi kesalahan!', 'error');
                            });
                    });
                };
            })();
        </script>
    @endpush
</x-app-layout>
