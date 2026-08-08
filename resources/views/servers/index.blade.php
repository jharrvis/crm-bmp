<x-app-layout>
    @php
        $pageTitle = $category === 'mail' ? 'Manajemen Server Mail Hosting' : ($category === 'web' ? 'Manajemen Server Web Hosting' : 'Manajemen Server');
        $pageDescription = $category === 'mail'
            ? 'Kelola server Zimbra dan kredensial SOAP Admin API.'
            : ($category === 'web' ? 'Kelola server HestiaCP, cPanel, dan CyberPanel.' : 'Kelola data server web dan mail hosting.');
    @endphp
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ $pageTitle }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ $pageDescription }}</p>
                </div>
                <button onclick="window.openModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Server</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Nama Server</th>
                            <th class="p-4">Host / IP</th>
                            <th class="p-4">Tipe</th>
                            <th class="p-4">Lokasi</th>
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
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300"
                id="formModalPanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Server Baru</h3>
                    <button onclick="window.closeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="dataForm">
                    @csrf
                    <input type="hidden" id="dataId" name="id">
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Server
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: Server Hosting Alpha">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Host / IP
                                    <span class="text-red-500">*</span></label>
                                <input type="text" id="host" name="host" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="cp.domain.com">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Port API
                                    <span class="text-red-500">*</span></label>
                                <input type="number" id="port" name="port" required value="8083"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">API Endpoint
                                (opsional)</label>
                            <input type="text" id="api_endpoint" name="api_endpoint"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: /service/admin/soap (khusus Zimbra)">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipe
                                    Panel</label>
                                <select id="type" name="type"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    <option value="hestiacp">HestiaCP</option>
                                    <option value="cpanel">cPanel</option>
                                    <option value="cyberpanel">CyberPanel</option>
                                    <option value="zimbra">Zimbra Mail Server</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Lokasi
                                    Data Center</label>
                                <input type="text" id="location" name="location"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="Contoh: Jakarta">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Maksimal Akun
                                (0 = Unlimited)</label>
                            <input type="number" id="max_accounts" name="max_accounts" required value="0" min="0"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Admin
                                Username</label>
                            <input type="text" id="username" name="username"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="admin">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">API Key /
                                Access Key</label>
                            <input type="password" id="api_key" name="api_key"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Secret
                                Key</label>
                            <input type="password" id="secret_key" name="secret_key"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Deskripsi</label>
                            <textarea id="description" name="description" rows="2"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                class="w-5 h-5 rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="is_active"
                                class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan Server
                                ini</label>
                        </div>
                    </div>
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" onclick="window.closeModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" id="submitBtn"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="submitSpinner" class="animate-spin h-5 w-5 hidden"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="submitText">Simpan Data</span>
                        </button>
                    </div>
                </form>
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
                let tableData = @json($servers);
                let table;

                $(document).ready(function () {
                    table = $('#dataTable').DataTable({
                        data: tableData,
                        columns: [
                            {
                                data: 'name',
                                className: 'p-4 pl-6',
                                render: (data) => `<span class="font-bold text-slate-700 dark:text-slate-200">${data}</span>`
                            },
                            {
                                data: 'host',
                                className: 'p-4',
                                render: (data, type, row) => `
                                    <div>
                                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300">${data}</div>
                                        <div class="text-xs text-slate-500">Port: ${row.port}</div>
                                    </div>
                                `
                            },
                            {
                                data: 'type',
                                className: 'p-4',
                                render: (data) => `<span class="uppercase text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded">${data}</span>`
                            },
                            {
                                data: 'location',
                                className: 'p-4',
                                render: (data) => `<span class="text-slate-600 dark:text-slate-400 text-sm">${data || '-'}</span>`
                            },
                            {
                                data: 'is_active',
                                className: 'p-4',
                                render: (data) => data
                                    ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Aktif</span>`
                                    : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400">Non-Aktif</span>`
                            },
                            {
                                data: null,
                                className: "p-4 pr-6 text-center",
                                orderable: false,
                                render: function (data, type, row) {
                                    return `
                                        <div class="flex items-center justify-center gap-2">
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
                            searchPlaceholder: "Cari server...",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                            infoEmpty: "Tidak ada data",
                            zeroRecords: "Data tidak ditemukan",
                            paginate: { first: "Awal", last: "Akhir", next: "»", previous: "«" }
                        },
                        drawCallback: function () { lucide.createIcons(); },
                        createdRow: function (row) { $(row).addClass('hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors'); }
                    });
                });

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
                    }, 10);

                    if (!isEdit) {
                        document.getElementById('modalTitle').innerText = 'Tambah Server Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        document.getElementById('dataForm').reset();
                        document.getElementById('dataId').value = '';
                        applyCategoryDefaults();
                    }
                    lucide.createIcons();
                };

                function applyCategoryDefaults() {
                    const category = @json($category);

                    if (category === 'mail') {
                        document.getElementById('type').value = 'zimbra';
                        document.getElementById('port').value = 7071;
                        document.getElementById('api_endpoint').value = '/service/admin/soap';
                    } else if (category === 'web') {
                        document.getElementById('type').value = 'hestiacp';
                        document.getElementById('port').value = 8083;
                    }
                }

                window.closeModal = function () {
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');

                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                // Edit Data
                window.editData = function (id) {
                    const item = tableData.find(d => d.id === id);
                    if (item) {
                        document.getElementById('modalTitle').innerText = 'Edit Server';
                        document.getElementById('submitText').innerText = 'Update Data';
                        document.getElementById('dataId').value = item.id;

                        document.getElementById('name').value = item.name;
                        document.getElementById('host').value = item.host;
                        document.getElementById('port').value = item.port;
                        document.getElementById('api_endpoint').value = item.api_endpoint || '';
                        document.getElementById('type').value = item.type;
                        document.getElementById('location').value = item.location || '';
                        document.getElementById('max_accounts').value = item.max_accounts;
                        document.getElementById('username').value = item.username || '';
                        document.getElementById('description').value = item.description || '';
                        document.getElementById('is_active').checked = item.is_active;

                        // Keys are not sent back for security (usually), so they are blank.
                        document.getElementById('api_key').value = '';
                        document.getElementById('secret_key').value = '';

                        window.openModal(true);
                    }
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Server?', 'Data server yang dihapus tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/servers/${deleteId}`, {
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
                                if (data.success) {
                                    tableData = tableData.filter(d => d.id !== deleteId);
                                    table.clear().rows.add(tableData).draw();
                                    showToast('Server berhasil dihapus!');
                                    hideConfirmModal();
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

                // Form Submit
                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('dataId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/servers/${id}` : `${baseUrl}/servers`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
                    if (isUpdate) formData.append('_method', 'PUT');

                    const data = {};
                    formData.forEach((value, key) => data[key] = value);
                    data.is_active = document.getElementById('is_active').checked ? 1 : 0;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                        .then(r => r.json())
                        .then(res => {
                            setButtonLoading(btn, spinner, text, false, originalText);
                            if (res.success) {
                                if (isUpdate) {
                                    const index = tableData.findIndex(d => d.id === parseInt(id));
                                    if (index >= 0) tableData[index] = res.server;
                                    showToast('Server berhasil diperbarui!');
                                } else {
                                    tableData.push(res.server);
                                    showToast('Server berhasil ditambahkan!');
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
            })();
        </script>
    @endpush
</x-app-layout>
