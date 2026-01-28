<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Divisi</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola data divisi dan unit kerja
                        perusahaan.</p>
                </div>
                <button onclick="window.openDivisionModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Divisi</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="divisionTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Nama Divisi</th>
                            <th class="p-4">Deskripsi</th>
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

    <!-- Division Form Modal -->
    <div id="divisionModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="divisionModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300"
                id="divisionModalPanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="divisionModalTitle">Tambah Divisi
                        Baru</h3>
                    <button onclick="window.closeDivisionModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="divisionForm">
                    @csrf
                    <input type="hidden" id="divisionId" name="id">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Divisi
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="divisionName" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: Operasional">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Deskripsi</label>
                            <textarea id="divisionDescription" name="description" rows="3"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Deskripsi tugas dan tanggung jawab divisi..."></textarea>
                        </div>
                    </div>
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" onclick="window.closeDivisionModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" id="divisionSubmitBtn"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="divisionSubmitSpinner" class="animate-spin h-5 w-5 hidden"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="divisionSubmitText">Simpan Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Division Modal -->
    <div id="viewDivisionModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="viewDivisionBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300"
                id="viewDivisionPanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Detail Divisi</h3>
                    <button onclick="window.closeViewDivisionModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Divisi</p>
                        <p class="text-lg font-semibold text-slate-800 dark:text-white" id="viewDivisionName">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                        <p class="text-base text-slate-600 dark:text-slate-300" id="viewDivisionDescription">-</p>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                    <button onclick="window.closeViewDivisionModal()"
                        class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Tutup</button>
                    <button onclick="window.editFromView()" id="editFromViewBtn"
                        class="px-5 py-2.5 rounded-xl font-bold bg-yellow-500 text-white hover:bg-yellow-600 shadow-lg shadow-yellow-200 dark:shadow-none transition-all flex items-center gap-2">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        <span>Edit Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div id="confirmModal" class="fixed inset-0 z-[70] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="confirmBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300"
                id="confirmPanel">
                <div class="p-6 text-center">
                    <div
                        class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2" id="confirmTitle">Hapus Divisi?
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-6" id="confirmText">Data yang dihapus tidak
                        dapat dikembalikan lagi.</p>
                    <div class="flex gap-3 justify-center">
                        <button onclick="closeConfirmModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button id="confirmYesBtn"
                            class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50">
                            <svg id="confirmSpinner" class="animate-spin h-5 w-5 hidden"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="confirmBtnText">Ya, Hapus!</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-[80] flex flex-col gap-2 pointer-events-none"></div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                let divisionData = @json($divisions);
                let table;
                let confirmCallback = null;
                let currentViewDivisionId = null;

                $(document).ready(function () {
                    table = $('#divisionTable').DataTable({
                        data: divisionData,
                        columns: [
                            {
                                data: 'name',
                                className: 'p-4 pl-6',
                                render: (data) => `<span class="font-bold text-slate-800 dark:text-white">${data}</span>`
                            },
                            {
                                data: 'description',
                                className: 'p-4',
                                render: (data) => `<span class="text-slate-500 dark:text-slate-400 line-clamp-1" title="${data || ''}">${data || '-'}</span>`
                            },
                            {
                                data: null,
                                className: "p-4 pr-6 text-center",
                                orderable: false,
                                render: function (data, type, row) {
                                    return `
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="window.viewDivision(${row.id})" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <button onclick="window.editDivision(${row.id})" class="p-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 text-yellow-600 rounded-lg transition-colors" title="Edit">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                            <button onclick="window.deleteDivision(${row.id})" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors" title="Hapus">
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
                            searchPlaceholder: "Cari divisi...",
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

                // Toast
                function showToast(message, type = 'success') {
                    const container = document.getElementById('toast-container');
                    const toast = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-white dark:bg-slate-800' : 'bg-red-50 dark:bg-red-900/50';
                    const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
                    const icon = type === 'success' ? 'check-circle' : 'alert-circle';

                    toast.className = `${bgColor} border border-slate-200 dark:border-slate-700 shadow-xl rounded-2xl p-4 flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0 pointer-events-auto min-w-[300px]`;
                    toast.innerHTML = `<i data-lucide="${icon}" class="${iconColor} w-6 h-6 shrink-0"></i><p class="font-bold text-sm text-slate-800 dark:text-white">${message}</p>`;
                    container.appendChild(toast);
                    lucide.createIcons();
                    requestAnimationFrame(() => toast.classList.remove('translate-x-full', 'opacity-0'));
                    setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 3000);
                }

                // Loading State Helpers
                function setButtonLoading(btn, spinner, textEl, loading, originalText = null) {
                    if (loading) {
                        btn.disabled = true;
                        spinner.classList.remove('hidden');
                        if (originalText) textEl.innerText = 'Memproses...';
                    } else {
                        btn.disabled = false;
                        spinner.classList.add('hidden');
                        if (originalText) textEl.innerText = originalText;
                    }
                }

                // Confirm Modal
                function showConfirmModal(title, text, callback) {
                    confirmCallback = callback;
                    const modal = document.getElementById('confirmModal');
                    const backdrop = document.getElementById('confirmBackdrop');
                    const panel = document.getElementById('confirmPanel');
                    document.getElementById('confirmTitle').innerText = title;
                    document.getElementById('confirmText').innerText = text;
                    modal.classList.remove('hidden');
                    setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);
                }

                function closeConfirmModal() {
                    const modal = document.getElementById('confirmModal');
                    const backdrop = document.getElementById('confirmBackdrop');
                    const panel = document.getElementById('confirmPanel');
                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => { modal.classList.add('hidden'); confirmCallback = null; }, 300);
                }

                document.getElementById('confirmYesBtn').addEventListener('click', () => {
                    if (confirmCallback) confirmCallback();
                });

                // Division Form Modal
                window.openDivisionModal = function (isEdit = false) {
                    const modal = document.getElementById('divisionModal');
                    const backdrop = document.getElementById('divisionModalBackdrop');
                    const panel = document.getElementById('divisionModalPanel');
                    modal.classList.remove('hidden');
                    setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);
                    if (!isEdit) {
                        document.getElementById('divisionModalTitle').innerText = 'Tambah Divisi Baru';
                        document.getElementById('divisionSubmitText').innerText = 'Simpan Data';
                        document.getElementById('divisionForm').reset();
                        document.getElementById('divisionId').value = '';
                    }
                    lucide.createIcons();
                };

                window.closeDivisionModal = function () {
                    const modal = document.getElementById('divisionModal');
                    const backdrop = document.getElementById('divisionModalBackdrop');
                    const panel = document.getElementById('divisionModalPanel');
                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                // View Division Modal
                window.viewDivision = function (id) {
                    currentViewDivisionId = id;
                    const division = divisionData.find(d => d.id === id);
                    if (division) {
                        document.getElementById('viewDivisionName').innerText = division.name;
                        document.getElementById('viewDivisionDescription').innerText = division.description || '-';

                        const modal = document.getElementById('viewDivisionModal');
                        const backdrop = document.getElementById('viewDivisionBackdrop');
                        const panel = document.getElementById('viewDivisionPanel');
                        modal.classList.remove('hidden');
                        setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);
                        lucide.createIcons();
                    }
                };

                window.closeViewDivisionModal = function () {
                    const modal = document.getElementById('viewDivisionModal');
                    const backdrop = document.getElementById('viewDivisionBackdrop');
                    const panel = document.getElementById('viewDivisionPanel');
                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                window.editFromView = function () {
                    window.closeViewDivisionModal();
                    setTimeout(() => window.editDivision(currentViewDivisionId), 350);
                };

                // Edit Division
                window.editDivision = function (id) {
                    const division = divisionData.find(d => d.id === id);
                    if (division) {
                        document.getElementById('divisionModalTitle').innerText = 'Edit Data Divisi';
                        document.getElementById('divisionSubmitText').innerText = 'Update Data';
                        document.getElementById('divisionId').value = division.id;
                        document.getElementById('divisionName').value = division.name;
                        document.getElementById('divisionDescription').value = division.description || '';
                        window.openDivisionModal(true);
                    }
                };

                // Delete Division
                let deleteId = null;
                window.deleteDivision = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Divisi?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/divisions/${deleteId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ _method: 'DELETE' })
                        })
                            .then(r => r.json())
                            .then(data => {
                                setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                                if (data.success) {
                                    divisionData = divisionData.filter(d => d.id !== deleteId);
                                    table.clear().rows.add(divisionData).draw();
                                    showToast('Divisi berhasil dihapus!');
                                    closeConfirmModal();
                                } else {
                                    showToast(data.message || 'Gagal menghapus data', 'error');
                                }
                            })
                            .catch(() => { setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!'); showToast('Terjadi kesalahan!', 'error'); });
                    });
                };

                // Form Submit
                document.getElementById('divisionForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('divisionId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/divisions/${id}` : `${baseUrl}/divisions`;
                    const btn = document.getElementById('divisionSubmitBtn');
                    const spinner = document.getElementById('divisionSubmitSpinner');
                    const text = document.getElementById('divisionSubmitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = {
                        name: document.getElementById('divisionName').value,
                        description: document.getElementById('divisionDescription').value,
                    };
                    if (isUpdate) formData._method = 'PUT';

                    fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    })
                        .then(r => r.json())
                        .then(data => {
                            setButtonLoading(btn, spinner, text, false, originalText);
                            if (data.success) {
                                if (isUpdate) {
                                    const index = divisionData.findIndex(d => d.id === parseInt(id));
                                    if (index >= 0) divisionData[index] = data.division;
                                    showToast('Divisi berhasil diperbarui!');
                                } else {
                                    divisionData.push(data.division);
                                    showToast('Divisi berhasil ditambahkan!');
                                }
                                table.clear().rows.add(divisionData).draw();
                                window.closeDivisionModal();
                            } else {
                                let errorMsg = data.message || 'Gagal menyimpan data';
                                if (data.errors) errorMsg = Object.values(data.errors).flat().join(', ');
                                showToast(errorMsg, 'error');
                            }
                        })
                        .catch(error => { setButtonLoading(btn, spinner, text, false, originalText); console.error(error); showToast('Terjadi kesalahan!', 'error'); });
                });
            })();
        </script>
    @endpush
</x-app-layout>