<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Internet Backup</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola koneksi internet backup dari provider.</p>
                </div>
                @can('internet_backups.create')
                <button onclick="window.openModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Data</span>
                </button>
                @endcan
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Filter Vendor -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Vendor</label>
                    <div class="relative">
                        <select id="filter_vendor"
                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none text-slate-700 dark:text-slate-200">
                            <option value="">Semua Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="building-2" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 absolute right-3 top-2.5 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Status</label>
                    <div class="relative">
                        <select id="filter_status"
                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none text-slate-700 dark:text-slate-200">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="filter" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 absolute right-3 top-2.5 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Nama Koneksi</th>
                            <th class="p-4">Client</th>
                            <th class="p-4">Subscription</th>
                            <th class="p-4">Vendor</th>
                            <th class="p-4">Bandwidth</th>
                            <th class="p-4">Biaya/Bulan</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 pr-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <!-- Data populated by DataTables -->
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 dark:bg-slate-700/50 font-bold text-slate-800 dark:text-slate-200">
                            <td colspan="4" class="p-4 text-right">Total Bandwidth / Total Biaya:</td>
                            <td class="p-4" id="total-bandwidth-display">0 Mbps</td>
                            <td class="p-4" id="total-cost-display">Rp 0</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div id="formModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="formModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[calc(100vh-2rem)] transform scale-95 opacity-0 transition-all duration-300 flex flex-col"
                id="formModalPanel">

                <!-- Modal Header -->
                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Data Baru</h3>
                    <button onclick="window.closeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto">
                    <form id="dataForm" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf
                        <input type="hidden" id="dataId" name="id">

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Koneksi <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="Contoh: Backup Indihome Karanganyar">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Vendor <span class="text-red-500">*</span></label>
                            <select id="vendor_id" name="vendor_id" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subscription</label>
                            <select id="subscription_id" name="subscription_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Subscription</option>
                                @foreach($subscriptions as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->subscription_code }} — {{ $sub->client?->name ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status <span class="text-red-500">*</span></label>
                            <select id="status" name="status" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Circuit ID</label>
                            <input type="text" id="circuit_id" name="circuit_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="Contoh: CID-12345">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Bandwidth (Mbps) <span class="text-red-500">*</span></label>
                            <input type="number" id="bandwidth_mbps" name="bandwidth_mbps" required min="1"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="0">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">IP Address</label>
                            <input type="text" id="ip_address" name="ip_address"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="Contoh: 192.168.1.1/24">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gateway</label>
                            <input type="text" id="gateway" name="gateway"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="Contoh: 192.168.1.1">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Biaya Bulanan (Rp)</label>
                            <input type="number" id="monthly_cost" name="monthly_cost" min="0" step="0.01"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="0">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal Aktif</label>
                            <input type="date" id="active_date" name="active_date"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat / Lokasi</label>
                            <input type="text" id="address" name="address"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="Contoh: Jl. Raya Karanganyar No. 1">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="window.closeModal()"
                        class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                    <button type="button" onclick="submitForm()" id="submitBtn"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                        <svg id="submitSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span id="submitText">Simpan Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-confirm-modal />

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                const canUpdate = @json(auth()->user()->can('internet_backups.update'));
                const canDelete = @json(auth()->user()->can('internet_backups.delete'));
                let table;

                $(document).ready(function () {
                    table = $('#dataTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ route('internet-backups.index') }}',
                            data: function (d) {
                                d.vendor_id = $('#filter_vendor').val();
                                d.status = $('#filter_status').val();
                            }
                        },
                        columns: [
                            {
                                data: 'name',
                                name: 'name',
                                className: 'p-4 pl-6',
                                render: (data) => `<div class="font-semibold text-slate-800 dark:text-slate-200">${escapeHtml(data || '-')}</div>`
                            },
                            {
                                data: 'client_name',
                                name: 'subscription.client.name',
                                className: 'p-4',
                                render: (data) => `<div class="text-slate-700 dark:text-slate-300">${escapeHtml(data || '-')}</div>`
                            },
                            {
                                data: 'subscription_code',
                                name: 'subscription.subscription_code',
                                className: 'p-4',
                                render: (data) => data ? `<span class="font-mono text-sm text-slate-600 dark:text-slate-400">${escapeHtml(data)}</span>` : '-'
                            },
                            {
                                data: 'vendor_name',
                                name: 'vendor.name',
                                className: 'p-4',
                                render: (data) => `<div class="font-bold text-slate-800 dark:text-slate-300">${escapeHtml(data || '-')}</div>`
                            },
                            {
                                data: 'bandwidth_formatted',
                                name: 'bandwidth_mbps',
                                className: 'p-4',
                                render: (data) => `<span class="px-2 py-1 rounded bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-bold text-xs">${escapeHtml(data)}</span>`
                            },
                            {
                                data: 'cost_formatted',
                                name: 'monthly_cost',
                                className: 'p-4',
                                render: (data) => `<span class="text-sm text-slate-600 dark:text-slate-400">${escapeHtml(data)}</span>`
                            },
                            {
                                data: 'status_label',
                                name: 'status',
                                className: 'p-4',
                                render: function (data, type, row) {
                                    const colors = {
                                        'Planned': 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'Active': 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                        'Suspended': 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                        'Terminated': 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                    };
                                    const cls = colors[data] || 'bg-slate-50 text-slate-700';
                                    return `<span class="px-2 py-1 rounded font-bold text-xs ${cls}">${escapeHtml(data)}</span>`;
                                }
                            },
                            {
                                data: null,
                                className: "p-4 pr-6 text-center",
                                orderable: false,
                                searchable: false,
                                render: function (data, type, row) {
                                    const actions = [];

                                    actions.push(`<a href="${baseUrl}/internet-backups/${row.id}" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat Detail"><i data-lucide="eye" class="w-4 h-4"></i></a>`);

                                    if (canUpdate) {
                                        actions.push(`<button onclick="window.editData(${row.id})" class="p-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 text-yellow-600 rounded-lg transition-colors" title="Edit"><i data-lucide="pencil" class="w-4 h-4"></i></button>`);
                                    }

                                    if (canDelete) {
                                        actions.push(`<button onclick="window.deleteData(${row.id})" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>`);
                                    }

                                    return actions.length
                                        ? `<div class="flex items-center justify-center gap-2">${actions.join('')}</div>`
                                        : '-';
                                }
                            }
                        ],
                        dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-4"ip>',
                        language: {
                            search: "",
                            searchPlaceholder: "Cari...",
                            lengthMenu: "Tampilkan _MENU_",
                            info: "_START_ - _END_ dari _TOTAL_",
                            paginate: { first: "\u00ab", last: "\u00bb", next: "\u203a", previous: "\u2039" },
                            processing: "Memuat data..."
                        },
                        drawCallback: function (settings) {
                            lucide.createIcons();
                            const json = settings.json;
                            if (json) {
                                if (json.total_bandwidth !== undefined) {
                                    $('#total-bandwidth-display').text(json.total_bandwidth + ' Mbps');
                                }
                                if (json.total_cost !== undefined) {
                                    $('#total-cost-display').text('Rp ' + Number(json.total_cost).toLocaleString('id-ID'));
                                }
                            }
                        },
                        createdRow: function (row) { $(row).addClass('hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors'); }
                    });

                    $('#filter_vendor').on('change', function () { table.draw(); });
                    $('#filter_status').on('change', function () { table.draw(); });
                });

                window.openModal = function (mode = 'add') {
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');
                    const form = document.getElementById('dataForm');

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');
                    }, 10);

                    if (mode === 'add') {
                        document.getElementById('modalTitle').innerText = 'Tambah Data Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        form.reset();
                        document.getElementById('dataId').value = '';
                        document.getElementById('status').value = 'planned';
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

                window.submitForm = function () {
                    document.getElementById('dataForm').dispatchEvent(new Event('submit'));
                }

                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('dataId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/internet-backups/${id}` : `${baseUrl}/internet-backups`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
                    if (isUpdate) formData.append('_method', 'PUT');

                    const object = {};
                    formData.forEach((value, key) => object[key] = value);
                    object._token = document.querySelector('input[name="_token"]').value;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': object._token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(object)
                    })
                        .then(r => r.json())
                        .then(res => {
                            setButtonLoading(btn, spinner, text, false, originalText);
                            if (res.success) {
                                showToast(res.message);
                                table.draw();
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

                window.editData = function (id) {
                    fetch(`${baseUrl}/internet-backups/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(r => r.json())
                        .then(item => {
                            document.getElementById('dataId').value = item.id;
                            document.getElementById('vendor_id').value = item.vendor_id || '';
                            document.getElementById('subscription_id').value = item.subscription_id || '';
                            document.getElementById('name').value = item.name || '';
                            document.getElementById('circuit_id').value = item.circuit_id || '';
                            document.getElementById('ip_address').value = item.ip_address || '';
                            document.getElementById('gateway').value = item.gateway || '';
                            document.getElementById('bandwidth_mbps').value = item.bandwidth_mbps || '';
                            document.getElementById('monthly_cost').value = item.monthly_cost || '';
                            document.getElementById('active_date').value = item.active_date ? item.active_date.split('T')[0] : '';
                            document.getElementById('address').value = item.address || '';
                            document.getElementById('status').value = item.status || 'planned';
                            document.getElementById('notes').value = item.notes || '';

                            document.getElementById('modalTitle').innerText = 'Edit Data';
                            document.getElementById('submitText').innerText = 'Update Data';
                            window.openModal('edit');
                        })
                        .catch(e => {
                            console.error(e);
                            showToast('Gagal memuat data', 'error');
                        });
                };

                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Data?', 'Data tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/internet-backups/${deleteId}`, {
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
                                    table.draw();
                                    showToast(data.message);
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
