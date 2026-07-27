<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Router</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola data router MikroTik di setiap
                        cabang.</p>
                </div>
                <button onclick="window.openModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Router</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Nama</th>
                            <th class="p-4">Host</th>
                            <th class="p-4">Cabang</th>
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
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Router Baru</h3>
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
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Router
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: Router Utama">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cabang</label>
                            <select id="branch_id" name="branch_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Host / IP
                                    <span class="text-red-500">*</span></label>
                                <input type="text" id="host" name="host" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="192.168.88.1">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Port API
                                    <span class="text-red-500">*</span></label>
                                <input type="number" id="port" name="port" required value="8728"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username
                                    <span class="text-red-500">*</span></label>
                                <input type="text" id="user" name="user" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="admin">
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <label for="password" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Password
                                        <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" id="toggleRouterPasswordButton" onclick="window.toggleRouterPassword()"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                                            title="Tampilkan password" aria-label="Tampilkan password">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </button>
                                        <button type="button" onclick="window.copyRouterPassword()"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                                            title="Salin password" aria-label="Salin password">
                                            <i data-lucide="copy" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="password" id="password" name="password" required autocomplete="off"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="Password Router">
                                <p class="text-xs text-slate-400 mt-1 hidden" id="passwordHint">Kosongkan jika tidak
                                    ingin mengubah password.</p>
                            </div>
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
                                class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan Router
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
                let tableData = @json($routers);
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
                                data: 'branch',
                                className: 'p-4',
                                render: (data) => data ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">${data.name}</span>` : '-'
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
                            searchPlaceholder: "Cari router...",
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
                        document.getElementById('modalTitle').innerText = 'Tambah Router Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        document.getElementById('dataForm').reset();
                        document.getElementById('dataId').value = '';
                        document.getElementById('password').required = true;
                        document.getElementById('password').type = 'password';
                        window.setRouterPasswordVisibility(false);
                        document.getElementById('passwordHint').classList.add('hidden');
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

                window.setRouterPasswordVisibility = function (isVisible) {
                    const button = document.getElementById('toggleRouterPasswordButton');
                    const action = isVisible ? 'Sembunyikan' : 'Tampilkan';

                    button.title = `${action} password`;
                    button.setAttribute('aria-label', `${action} password`);
                    button.innerHTML = `<i data-lucide="${isVisible ? 'eye-off' : 'eye'}" class="h-4 w-4"></i>`;
                    lucide.createIcons();
                };

                window.toggleRouterPassword = function () {
                    const passwordInput = document.getElementById('password');
                    const isVisible = passwordInput.type === 'password';

                    passwordInput.type = isVisible ? 'text' : 'password';
                    window.setRouterPasswordVisibility(isVisible);
                };

                window.copyRouterPassword = async function () {
                    const password = document.getElementById('password').value;

                    if (!password) {
                        showToast('Password router belum tersedia untuk disalin.', 'error');
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(password);
                        showToast('Password router berhasil disalin.');
                    } catch (error) {
                        showToast('Gagal menyalin password router.', 'error');
                    }
                };

                // Edit Data
                window.editData = function (id) {
                    const item = tableData.find(d => d.id === id);
                    if (item) {
                        document.getElementById('modalTitle').innerText = 'Edit Router';
                        document.getElementById('submitText').innerText = 'Update Data';
                        document.getElementById('dataId').value = item.id;

                        document.getElementById('name').value = item.name;
                        document.getElementById('host').value = item.host;
                        document.getElementById('port').value = item.port;
                        document.getElementById('user').value = item.user;
                        document.getElementById('description').value = item.description || '';
                        document.getElementById('branch_id').value = item.branch_id || '';

                        document.getElementById('is_active').checked = item.is_active;

                        // Password Handling
                        const passInput = document.getElementById('password');
                        passInput.value = item.password || '';
                        passInput.type = 'password';
                        window.setRouterPasswordVisibility(false);
                        passInput.required = false;
                        document.getElementById('passwordHint').classList.remove('hidden');

                        window.openModal(true);
                    }
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Router?', 'Data router yang dihapus tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/routers/${deleteId}`, {
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
                                    showToast('Router berhasil dihapus!');
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
                    const url = isUpdate ? `${baseUrl}/routers/${id}` : `${baseUrl}/routers`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
                    if (isUpdate) formData.append('_method', 'PUT');
                    if (!document.getElementById('is_active').checked) {
                        // Checkbox Unchecked sends nothing, but we need to explicitly send 0 if we rely on has('is_active') or similar
                        // Actually FormData should define it? No, unchecked checkboxes are not included in FormData.
                        // But Laravel `boolean('is_active')` returns false if missing? No, it looks for key presence usually or values like '0', 'false', etc.
                        // Let's manually ensure it.
                    }

                    // Since we use FormData, we can convert to JSON to consistent with other parts, or just send FormData.
                    // Fetch body accepts FormData. But controller expects JSON? 
                    // BranchController used JSON.stringify(object). Let's stick to JSON for consistency and to control types like boolean.

                    const data = {};
                    formData.forEach((value, key) => data[key] = value);

                    // Handle checkbox explicit boolean
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
                                    if (index >= 0) tableData[index] = res.router;
                                    showToast('Router berhasil diperbarui!');
                                } else {
                                    tableData.push(res.router);
                                    showToast('Router berhasil ditambahkan!');
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
