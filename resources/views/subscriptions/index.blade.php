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

                        <!-- Main Subscription Info -->
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

                        <!-- Pricing & Billing Section -->
                        <div class="border-t border-slate-200 dark:border-slate-700 pt-6 mt-2">
                            <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Harga & Billing
                            </h4>
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Diskon
                                        (%)
                                        <span class="text-xs text-slate-400 font-normal">(opsional)</span></label>
                                    <input type="number" id="discount_percent" name="discount_percent" step="0.01"
                                        min="0" max="100" placeholder="0"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alasan
                                        Diskon
                                        <span class="text-xs text-slate-400 font-normal">(opsional)</span></label>
                                    <input type="text" id="discount_notes" name="discount_notes"
                                        placeholder="Misal: Kontrak 1 tahun, diskon 10%"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Technical Details Section -->
                        <div id="technical-details"
                            class="border-t border-slate-200 dark:border-slate-700 pt-6 mt-2 hidden">
                            <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Detail Teknis
                            </h4>

                            <!-- Connectivity Fields -->
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
                                                    {{ $metro->vendor->name ?? 'Unknown' }} | CID: {{ $metro->cid }} | {{ $metro->bandwidth }} Mbps
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <input type="hidden" name="metro_ethernet_id" id="metro_ethernet_id_input">
                                </div>

                                <div id="metro-new-form" class="hidden space-y-6 border-l-4 border-blue-500 pl-4">
                                    <h5 class="text-sm font-bold text-blue-600">Buat Metro Ethernet Baru</h5>
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

                        <!-- Hosting Fields -->
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
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan
                        Tambahan</label>
                    <input type="text" id="notes" name="notes"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
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
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
                margin-top: 4px !important;
                z-index: 9999 !important;
            }

            .choices__list--dropdown .choices__item {
                padding: 0.625rem 0.875rem !important;
                font-size: 0.875rem !important;
                color: #334155 !important;
            }

            .choices__list--dropdown .choices__item--selectable.is-highlighted {
                background-color: #3b82f6 !important;
                color: white !important;
            }

            /* Search Input */
            .choices__input {
                background-color: transparent !important;
                font-size: 0.875rem !important;
                margin-bottom: 0 !important;
                padding: 0 !important;
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

            .dark .choices__list--dropdown .choices__item {
                color: #e2e8f0 !important;
            }

            .dark .choices__list--dropdown .choices__item--selectable.is-highlighted {
                background-color: #3b82f6 !important;
            }
        </style>

        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                let tableData = @json($subscriptions);
                let table;
                let clientChoice; // Declare in outer scope

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

                    if (fieldsConn) fieldsConn.classList.add('hidden');
                    if (fieldsHost) fieldsHost.classList.add('hidden');
                    if (detailsSection) detailsSection.classList.add('hidden');

                    if (serviceType) {
                        if (detailsSection) detailsSection.classList.remove('hidden');
                        if (serviceType === 'connectivity') {
                            if (fieldsConn) fieldsConn.classList.remove('hidden');
                            toggleMetroForm();
                        } else if (serviceType === 'hosting' || serviceType === 'domain') {
                            if (fieldsHost) fieldsHost.classList.remove('hidden');
                            // Hide 'Hosting Server' for domain registration
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
                };

                window.toggleMetroForm = function() {
                    const metroOption = document.getElementById('metro_option');
                    const newForm = document.getElementById('metro-new-form');
                    const hiddenIdInput = document.getElementById('metro_ethernet_id_input');
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
                        document.getElementById('installed_at').valueAsDate = new Date();

                        // Reset Choices.js
                        if (clientChoice) {
                            clientChoice.setChoiceByValue('');
                        }

                        // Reset fields view
                        handlePackageChange();
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
                    document.getElementById('dataForm').dispatchEvent(new Event('submit'));
                }

                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('dataId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/subscriptions/${id}` : `${baseUrl}/subscriptions`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Layanan' : 'Simpan Layanan';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
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
                        .then(data => {
                            document.getElementById('modalTitle').innerText = 'Edit Layanan';
                            document.getElementById('submitText').innerText = 'Update Layanan';
                            document.getElementById('dataId').value = data.id;

                            // Fill Form Values
                            clientChoice.setChoiceByValue(data.client_id.toString());
                            document.getElementById('package_id').value = data.package_id;
                            document.getElementById('installed_at').value = data.installed_at ? data.installed_at.split('T')[0] : '';
                            document.getElementById('status').value = data.status;
                            document.getElementById('notes').value = data.notes || '';

                            // Fill Pricing Fields
                            document.getElementById('custom_price').value = data.custom_price || '';
                            document.getElementById('billing_period_months').value = data.billing_period_months || 1;
                            document.getElementById('discount_percent').value = data.discount_percent || '';
                            document.getElementById('discount_notes').value = data.discount_notes || '';

                            // Trigger Change to show fields
                            handlePackageChange();

                            // Fill Details based on type
                            if (data.connectivity) {
                                document.getElementById('router_id').value = data.connectivity.router_id || '';
                                document.getElementById('ip_address').value = data.connectivity.ip_address || '';
                                document.getElementById('pppoe_user').value = data.connectivity.pppoe_user || '';
                                document.getElementById('ont_sn').value = data.connectivity.ont_sn || '';
                                document.getElementById('ont_sn').value = data.connectivity.ont_sn || '';
                                document.getElementById('vlan_id').value = data.connectivity.vlan_id || '';

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
                            }
                            if (data.hosting) {
                                document.getElementById('hosting_server_id').value = data.hosting.hosting_server_id || '';
                                document.getElementById('domain').value = data.hosting.domain || '';
                                document.getElementById('username').value = data.hosting.username || '';
                            }

                            window.openModal(true);
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