<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Layanan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kategori layanan utama (Internet,
                        Hosting, VPS).</p>
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
                            <th class="p-4 pl-6">Kode</th>
                            <th class="p-4">Nama Layanan</th>
                            <th class="p-4">Tipe</th>
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
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Layanan Baru
                    </h3>
                    <button onclick="window.closeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="dataForm">
                    @csrf
                    <input type="hidden" id="dataId" name="id">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Layanan
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: Internet Dedicated">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kode
                                    (Unik) <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all uppercase"
                                    placeholder="INT-DED">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipe
                                    Sistem <span class="text-red-500">*</span></label>
                                <select id="type" name="type"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    <option value="connectivity">Connectivity (Internet)</option>
                                    <option value="hosting">Web Hosting</option>
                                    <option value="vps">VPS / Cloud</option>
                                    <option value="colocation">Colocation</option>
                                    <option value="domain">Domain Registration</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Metro Ethernet Section -->
                    <div id="metro-section"
                        class="hidden space-y-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="network" class="w-4 h-4 text-blue-500"></i>
                            Detail Metro Ethernet
                        </h4>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Vendor
                                Backbone <span class="text-red-500">*</span></label>
                            <select id="metro_vendor_id" name="metro_vendor_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">CID
                                    (Circuit ID)</label>
                                <input type="text" id="metro_cid" name="metro_cid"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="Contoh: CID-12345">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">IP
                                    Address</label>
                                <input type="text" id="metro_ip_address" name="metro_ip_address"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="192.168.x.x">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kapasitas
                                Bandwidth
                                (Mbps) <span class="text-red-500">*</span></label>
                            <input type="number" id="metro_bandwidth" name="metro_bandwidth" min="0"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: 100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                            class="w-5 h-5 rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan
                            Layanan
                            ini</label>
                    </div>
            </div>
            <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" onclick="window.closeModal()"
                    class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                <button type="submit" id="submitBtn"
                    class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
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
                let tableData = @json($services);
                let table;

                $(document).ready(function () {
                    // Type Change Event
                    $('#type').on('change', function () {
                        const val = $(this).val();
                        if (val === 'connectivity') {
                            $('#metro-section').removeClass('hidden');
                            $('#metro_vendor_id').attr('required', true);
                            $('#metro_bandwidth').attr('required', true);
                        } else {
                            $('#metro-section').addClass('hidden');
                            $('#metro_vendor_id').removeAttr('required');
                            $('#metro_bandwidth').removeAttr('required');
                        }
                    });

                    // Existing DataTable init code...
                    table = $('#dataTable').DataTable({
                        data: tableData,
                        columns: [
                            {
                                data: 'code',
                                className: 'p-4 pl-6',
                                render: (data) => `<span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded">${data}</span>`
                            },
                            {
                                data: 'name',
                                className: 'p-4',
                                render: (data, type, row) => {
                                    let html = `<div class="font-bold text-slate-700 dark:text-slate-200">${data}</div>`;
                                    if (row.metro_ethernet) {
                                        html += `<div class="text-xs text-slate-500 mt-1 flex items-center gap-1"><i data-lucide="network" class="w-3 h-3"></i> ${row.metro_ethernet.vendor.name} (${row.metro_ethernet.bandwidth} Mbps)</div>`;
                                    }
                                    return html;
                                }
                            },
                            {
                                data: 'type',
                                className: 'p-4',
                                render: (data) => {
                                    let label = data;
                                    if (data === 'connectivity') label = 'Connectivity';
                                    else if (data === 'hosting') label = 'Web Hosting';
                                    else if (data === 'vps') label = 'VPS / Cloud';
                                    else if (data === 'domain') label = 'Domain Registration';
                                    return `<span class="text-slate-600 dark:text-slate-400 capitalize">${label}</span>`;
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
                                searchable: false,
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
                            searchPlaceholder: "Cari layanan...",
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
                        document.getElementById('modalTitle').innerText = 'Tambah Layanan Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        document.getElementById('dataForm').reset();
                        document.getElementById('dataId').value = '';
                        // Trigger Event to reset validation and hide metro section
                        $('#type').trigger('change');
                        $('#metro-section').addClass('hidden'); // Ensure hidden by default for new
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
                        document.getElementById('modalTitle').innerText = 'Edit Layanan';
                        document.getElementById('submitText').innerText = 'Update Data';
                        document.getElementById('dataId').value = item.id;

                        document.getElementById('name').value = item.name;
                        document.getElementById('code').value = item.code;
                        document.getElementById('type').value = item.type;
                        document.getElementById('description').value = item.description || '';
                        document.getElementById('is_active').checked = item.is_active;

                        // Metro Data Pre-fill
                        if (item.type === 'connectivity') {
                            $('#metro-section').removeClass('hidden');
                            $('#metro_vendor_id').attr('required', true);
                            $('#metro_bandwidth').attr('required', true);

                            if (item.metro_ethernet) {
                                document.getElementById('metro_vendor_id').value = item.metro_ethernet.vendor_id;
                                document.getElementById('metro_cid').value = item.metro_ethernet.cid || '';
                                document.getElementById('metro_ip_address').value = item.metro_ethernet.ip_address || '';
                                document.getElementById('metro_bandwidth').value = item.metro_ethernet.bandwidth || '';
                            } else {
                                // Clear if no details but type is connectivity
                                document.getElementById('metro_vendor_id').value = '';
                                document.getElementById('metro_cid').value = '';
                                document.getElementById('metro_ip_address').value = '';
                                document.getElementById('metro_bandwidth').value = '';
                            }
                        } else {
                            $('#metro-section').addClass('hidden');
                            $('#metro_vendor_id').removeAttr('required');
                            $('#metro_bandwidth').removeAttr('required');
                            // Clear data
                            document.getElementById('metro_vendor_id').value = '';
                            document.getElementById('metro_cid').value = '';
                            document.getElementById('metro_ip_address').value = '';
                            document.getElementById('metro_bandwidth').value = '';
                        }

                        window.openModal(true);
                    }
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Layanan?', 'Data layanan yang dihapus tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/services/${deleteId}`, {
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
                                    showToast('Layanan berhasil dihapus!');
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
                    const url = isUpdate ? `${baseUrl}/services/${id}` : `${baseUrl}/services`;
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
                    // Log data for debugging
                    console.log('Form Data:', data);

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
                                    if (index >= 0) tableData[index] = res.service;
                                    showToast('Layanan berhasil diperbarui!');
                                } else {
                                    tableData.push(res.service);
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
            })();
        </script>
    @endpush
</x-app-layout>