<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Cabang</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola data kantor cabang operasional.</p>
                </div>
                <button onclick="window.openBranchModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Cabang</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="branchTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Kode</th>
                            <th class="p-4">Nama Cabang</th>
                            <th class="p-4">Alamat</th>
                            <th class="p-4">Telepon</th>
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

    <!-- Branch Form Modal -->
    <div id="branchModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="branchModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300" id="branchModalPanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="branchModalTitle">Tambah Cabang Baru</h3>
                    <button onclick="window.closeBranchModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="branchForm">
                    @csrf
                    <input type="hidden" id="branchId" name="id">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Cabang <span class="text-red-500">*</span></label>
                            <input type="text" id="branchName" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: Cabang Salatiga">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kode Cabang <span class="text-red-500">*</span></label>
                            <input type="text" id="branchCode" name="code" required maxlength="10"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all uppercase"
                                placeholder="Contoh: SLT">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nomor Telepon</label>
                            <input type="text" id="branchPhone" name="phone"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Contoh: 0298-123456">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat Lengkap</label>
                            <textarea id="branchAddress" name="address" rows="3"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                placeholder="Alamat lengkap kantor cabang..."></textarea>
                        </div>
                    </div>
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" onclick="window.closeBranchModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" id="branchSubmitBtn"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="branchSubmitSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="branchSubmitText">Simpan Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Branch Modal -->
    <div id="viewBranchModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="viewBranchBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300" id="viewBranchPanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Detail Cabang</h3>
                    <button onclick="window.closeViewBranchModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Cabang</p>
                            <p class="text-base font-semibold text-slate-800 dark:text-white" id="viewBranchName">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Cabang</p>
                            <p class="text-base font-bold text-blue-600 dark:text-blue-400" id="viewBranchCode">-</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Telepon</p>
                        <p class="text-base text-slate-600 dark:text-slate-300" id="viewBranchPhone">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat Lengkap</p>
                        <p class="text-base text-slate-600 dark:text-slate-300" id="viewBranchAddress">-</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Karyawan di Cabang Ini</p>
                        <div id="viewBranchEmployees" class="flex flex-wrap gap-2">
                            <span class="text-sm text-slate-400">Memuat...</span>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                    <button onclick="window.closeViewBranchModal()"
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
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="confirmBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300" id="confirmPanel">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2" id="confirmTitle">Hapus Cabang?</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-6" id="confirmText">Data yang dihapus tidak dapat dikembalikan lagi.</p>
                    <div class="flex gap-3 justify-center">
                        <button onclick="closeConfirmModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button id="confirmYesBtn"
                            class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50">
                            <svg id="confirmSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
        (function() {
            const baseUrl = '{{ url('/') }}';
            let branchData = @json($branches);
            let table;
            let confirmCallback = null;
            let currentViewBranchId = null;

            $(document).ready(function () {
                table = $('#branchTable').DataTable({
                    data: branchData,
                    columns: [
                        {
                            data: 'code',
                            className: 'p-4 pl-6',
                            render: (data) => `<span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded">${data}</span>`
                        },
                        {
                            data: 'name',
                            className: 'p-4',
                            render: (data) => `<span class="font-bold text-slate-700 dark:text-slate-200">${data}</span>`
                        },
                        {
                            data: 'address',
                            className: 'p-4',
                            render: (data) => `<div class="truncate max-w-xs text-slate-500 dark:text-slate-400" title="${data || ''}">${data || '-'}</div>`
                        },
                        {
                            data: 'phone',
                            className: 'p-4',
                            render: (data) => `<span class="text-slate-500 dark:text-slate-400">${data || '-'}</span>`
                        },
                        {
                            data: null,
                            className: "p-4 pr-6 text-center",
                            orderable: false,
                            render: function (data, type, row) {
                                return `
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="window.viewBranch(${row.id})" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="window.editBranch(${row.id})" class="p-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 text-yellow-600 rounded-lg transition-colors" title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="window.deleteBranch(${row.id})" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors" title="Hapus">
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
                        searchPlaceholder: "Cari cabang...",
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

            // Branch Form Modal
            window.openBranchModal = function(isEdit = false) {
                const modal = document.getElementById('branchModal');
                const backdrop = document.getElementById('branchModalBackdrop');
                const panel = document.getElementById('branchModalPanel');
                modal.classList.remove('hidden');
                setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);
                if (!isEdit) {
                    document.getElementById('branchModalTitle').innerText = 'Tambah Cabang Baru';
                    document.getElementById('branchSubmitText').innerText = 'Simpan Data';
                    document.getElementById('branchForm').reset();
                    document.getElementById('branchId').value = '';
                }
                lucide.createIcons();
            };

            window.closeBranchModal = function() {
                const modal = document.getElementById('branchModal');
                const backdrop = document.getElementById('branchModalBackdrop');
                const panel = document.getElementById('branchModalPanel');
                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            // View Branch Modal
            window.viewBranch = function(id) {
                currentViewBranchId = id;
                const branch = branchData.find(b => b.id === id);
                if (branch) {
                    document.getElementById('viewBranchName').innerText = branch.name;
                    document.getElementById('viewBranchCode').innerText = branch.code;
                    document.getElementById('viewBranchPhone').innerText = branch.phone || '-';
                    document.getElementById('viewBranchAddress').innerText = branch.address || '-';
                    
                    // Load employees via AJAX
                    const empContainer = document.getElementById('viewBranchEmployees');
                    empContainer.innerHTML = '<span class="text-sm text-slate-400">Memuat...</span>';
                    
                    fetch(`${baseUrl}/branches/${id}`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            if (data.users && data.users.length > 0) {
                                empContainer.innerHTML = data.users.map(u => 
                                    `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">${u.name}</span>`
                                ).join('');
                            } else {
                                empContainer.innerHTML = '<span class="text-sm text-slate-400">Belum ada karyawan di cabang ini.</span>';
                            }
                        })
                        .catch(() => { empContainer.innerHTML = '<span class="text-sm text-red-400">Gagal memuat data.</span>'; });
                    
                    const modal = document.getElementById('viewBranchModal');
                    const backdrop = document.getElementById('viewBranchBackdrop');
                    const panel = document.getElementById('viewBranchPanel');
                    modal.classList.remove('hidden');
                    setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);
                    lucide.createIcons();
                }
            };

            window.closeViewBranchModal = function() {
                const modal = document.getElementById('viewBranchModal');
                const backdrop = document.getElementById('viewBranchBackdrop');
                const panel = document.getElementById('viewBranchPanel');
                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            window.editFromView = function() {
                window.closeViewBranchModal();
                setTimeout(() => window.editBranch(currentViewBranchId), 350);
            };

            // Edit Branch
            window.editBranch = function(id) {
                const branch = branchData.find(b => b.id === id);
                if (branch) {
                    document.getElementById('branchModalTitle').innerText = 'Edit Data Cabang';
                    document.getElementById('branchSubmitText').innerText = 'Update Data';
                    document.getElementById('branchId').value = branch.id;
                    document.getElementById('branchName').value = branch.name;
                    document.getElementById('branchCode').value = branch.code;
                    document.getElementById('branchPhone').value = branch.phone || '';
                    document.getElementById('branchAddress').value = branch.address || '';
                    window.openBranchModal(true);
                }
            };

            // Delete Branch
            let deleteId = null;
            window.deleteBranch = function(id) {
                deleteId = id;
                showConfirmModal('Hapus Cabang?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
                    const btn = document.getElementById('confirmYesBtn');
                    const spinner = document.getElementById('confirmSpinner');
                    const text = document.getElementById('confirmBtnText');
                    setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                    fetch(`${baseUrl}/branches/${deleteId}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(r => r.json())
                    .then(data => {
                        setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                        if (data.success) {
                            branchData = branchData.filter(b => b.id !== deleteId);
                            table.clear().rows.add(branchData).draw();
                            showToast('Cabang berhasil dihapus!');
                            closeConfirmModal();
                        } else {
                            showToast(data.message || 'Gagal menghapus data', 'error');
                        }
                    })
                    .catch(() => { setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!'); showToast('Terjadi kesalahan!', 'error'); });
                });
            };

            // Form Submit
            document.getElementById('branchForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('branchId').value;
                const isUpdate = !!id;
                const url = isUpdate ? `${baseUrl}/branches/${id}` : `${baseUrl}/branches`;
                const btn = document.getElementById('branchSubmitBtn');
                const spinner = document.getElementById('branchSubmitSpinner');
                const text = document.getElementById('branchSubmitText');
                const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                setButtonLoading(btn, spinner, text, true, originalText);

                const formData = {
                    name: document.getElementById('branchName').value,
                    code: document.getElementById('branchCode').value.toUpperCase(),
                    phone: document.getElementById('branchPhone').value,
                    address: document.getElementById('branchAddress').value,
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
                            const index = branchData.findIndex(b => b.id === parseInt(id));
                            if (index >= 0) branchData[index] = data.branch;
                            showToast('Cabang berhasil diperbarui!');
                        } else {
                            branchData.push(data.branch);
                            showToast('Cabang berhasil ditambahkan!');
                        }
                        table.clear().rows.add(branchData).draw();
                        window.closeBranchModal();
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