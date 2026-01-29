<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Paket</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Daftar harga dan spesifikasi paket
                        layanan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="syncBtn" onclick="window.syncData()"
                        class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-300 px-5 py-2.5 rounded-xl font-bold transition-all">
                        <svg id="syncSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        <span id="syncText">Sync HestiaCP</span>
                    </button>
                    <button onclick="window.openModal()"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        <span>Tambah Paket</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Layanan</th>
                            <th class="p-4">Nama Paket</th>
                            <th class="p-4">Harga</th>
                            <th class="p-4">Bandwidth / Spesifikasi</th>
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
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Paket Baru</h3>
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
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kategori
                                Layanan <span class="text-red-500">*</span></label>
                            <select id="service_id" name="service_id" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Layanan</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}
                                        ({{ strtoupper($service->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Paket
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: Paket 20 Mbps Home">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Harga Bulanan
                                (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" id="price" name="price" required min="0"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: 150000">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Download
                                    (Max)</label>
                                <input type="text" id="bandwidth_down" name="bandwidth_down"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="20M">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Upload
                                    (Max)</label>
                                <input type="text" id="bandwidth_up" name="bandwidth_up"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="5M">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Quota / FUP /
                                Storage</label>
                            <input type="text" id="quota" name="quota"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Unlimited">
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
                                class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan Paket
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
                let tableData = @json($packages);
                let table;

                // Currency formatter
                const formatRupiah = (n) => {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(n);
                };

                $(document).ready(function () {
                    table = $('#dataTable').DataTable({
                        data: tableData,
                        columns: [
                            {
                                data: 'service',
                                className: 'p-4 pl-6',
                                render: (data) => data ? `<span class="uppercase font-bold text-xs text-slate-500 bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded">${data.code}</span>` : '-'
                            },
                            {
                                data: 'name',
                                className: 'p-4',
                                render: (data) => `<span class="font-bold text-slate-700 dark:text-slate-200">${data}</span>`
                            },
                            {
                                data: 'price',
                                className: 'p-4',
                                render: (data) => `<span class="font-bold text-green-600 dark:text-green-400">${formatRupiah(data)}</span>`
                            },
                            {
                                data: null,
                                className: 'p-4',
                                render: (data, type, row) => {
                                    let spec = [];
                                    if (row.bandwidth_down) spec.push('↓ ' + row.bandwidth_down);
                                    if (row.bandwidth_up) spec.push('↑ ' + row.bandwidth_up);
                                    if (row.quota) spec.push('Qt: ' + row.quota);
                                    return `<span class="text-sm text-slate-600 dark:text-slate-400">${spec.join(' | ')}</span>`;
                                }
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
                            searchPlaceholder: "Cari paket...",
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
                        document.getElementById('modalTitle').innerText = 'Tambah Paket Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        document.getElementById('dataForm').reset();
                        document.getElementById('dataId').value = '';
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

                // Edit Data
                window.editData = function (id) {
                    const item = tableData.find(d => d.id === id);
                    if (item) {
                        document.getElementById('modalTitle').innerText = 'Edit Paket';
                        document.getElementById('submitText').innerText = 'Update Data';
                        document.getElementById('dataId').value = item.id;

                        document.getElementById('service_id').value = item.service_id;
                        document.getElementById('name').value = item.name;
                        document.getElementById('price').value = parseInt(item.price);
                        document.getElementById('bandwidth_down').value = item.bandwidth_down || '';
                        document.getElementById('bandwidth_up').value = item.bandwidth_up || '';
                        document.getElementById('quota').value = item.quota || '';
                        document.getElementById('description').value = item.description || '';
                        document.getElementById('is_active').checked = item.is_active;

                        window.openModal(true);
                    }
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Paket?', 'Data paket yang dihapus tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/packages/${deleteId}`, {
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
                                    showToast('Paket berhasil dihapus!');
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
                    // ... (existing submit logic remains the same, assuming it's correctly closed in original) ...
                    const id = document.getElementById('dataId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/packages/${id}` : `${baseUrl}/packages`;
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
                                    if (index >= 0) tableData[index] = res.package;
                                    showToast('Paket berhasil diperbarui!');
                                } else {
                                    tableData.push(res.package);
                                    showToast('Paket berhasil ditambahkan!');
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

                // Sync Function
                window.syncData = function () {
                    const btn = document.getElementById('syncBtn');
                    const spinner = document.getElementById('syncSpinner');
                    const text = document.getElementById('syncText');

                    setButtonLoading(btn, spinner, text, true, 'Sync HestiaCP');

                    fetch(`${baseUrl}/packages/sync`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(res => {
                            setButtonLoading(btn, spinner, text, false, 'Sync HestiaCP');
                            if (res.success) {
                                showToast(res.message);
                                setTimeout(() => location.reload(), 1500); // Reload to get fresh data including relations
                            } else {
                                showToast(res.message || 'Gagal melakukan sinkronisasi', 'error');
                                if (res.errors && res.errors.length > 0) {
                                    console.error(res.errors);
                                    showToast('Cek console untuk detail error', 'error');
                                }
                            }
                        })
                        .catch(err => {
                            setButtonLoading(btn, spinner, text, false, 'Sync HestiaCP');
                            console.error(err);
                            showToast('Terjadi kesalahan koneksi', 'error');
                        });
                };
            })();
        </script>
    @endpush
</x-app-layout>