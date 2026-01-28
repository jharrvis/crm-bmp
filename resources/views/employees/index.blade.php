<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Karyawan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Daftar pengguna staff dan teknisi.</p>
                </div>
                <button onclick="window.openEmployeeModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    <span>Tambah Karyawan</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="employeeTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Cabang</th>
                            <th class="p-4">Divisi</th>
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

    <!-- Employee Form Modal -->
    <div id="employeeModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="employeeModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[90vh] overflow-y-auto no-scrollbar"
                id="employeeModalPanel">
                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center sticky top-0 bg-white dark:bg-slate-800 z-10">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="employeeModalTitle">Tambah Karyawan
                        Baru</h3>
                    <button onclick="window.closeEmployeeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="employeeForm">
                    @csrf
                    <input type="hidden" id="employeeId" name="id">
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama
                                    Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" id="employeeName" name="name" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="Nama karyawan">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email
                                    <span class="text-red-500">*</span></label>
                                <input type="email" id="employeeEmail" name="email" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="email@example.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password
                                    <span id="passwordRequired" class="text-red-500">*</span></label>
                                <input type="password" id="employeePassword" name="password"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="••••••••">
                                <p class="text-xs text-slate-500 mt-1 hidden" id="passwordHint">Kosongkan jika tidak
                                    ingin mengubah password.</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi
                                    Password <span id="passwordConfirmRequired" class="text-red-500">*</span></label>
                                <input type="password" id="employeePasswordConfirmation" name="password_confirmation"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    placeholder="••••••••">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Role <span
                                    class="text-red-500">*</span></label>
                            <select id="employeeRole" name="role" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cabang</label>
                                <select id="employeeBranch" name="branch_id"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    <option value="">Tidak ada</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Divisi</label>
                                <select id="employeeDivision" name="division_id"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    <option value="">Tidak ada</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div
                        class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 sticky bottom-0 bg-white dark:bg-slate-800 z-10">
                        <button type="button" onclick="window.closeEmployeeModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" id="employeeSubmitBtn"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="employeeSubmitSpinner" class="animate-spin h-5 w-5 hidden"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="employeeSubmitText">Simpan Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div id="viewEmployeeModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="viewEmployeeBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300"
                id="viewEmployeePanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Detail Karyawan</h3>
                    <button onclick="window.closeViewEmployeeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</p>
                        <p class="text-lg font-semibold text-slate-800 dark:text-white" id="viewEmployeeName">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                        <p class="text-base text-slate-600 dark:text-slate-300" id="viewEmployeeEmail">-</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Role</p>
                            <span id="viewEmployeeRole"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">-</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Divisi</p>
                            <p class="text-base text-slate-600 dark:text-slate-300" id="viewEmployeeDivision">-</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Cabang</p>
                        <p class="text-base text-slate-600 dark:text-slate-300" id="viewEmployeeBranch">-</p>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                    <button onclick="window.closeViewEmployeeModal()"
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
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2" id="confirmTitle">Hapus Karyawan?
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
                let employeeData = @json($employees);
                let table;
                let confirmCallback = null;
                let currentViewEmployeeId = null;

                $(document).ready(function () {
                    table = $('#employeeTable').DataTable({
                        data: employeeData,
                        columns: [
                            {
                                data: 'name',
                                className: 'p-4 pl-6',
                                render: (data) => `<span class="font-bold text-slate-800 dark:text-white">${data}</span>`
                            },
                            {
                                data: 'email',
                                className: 'p-4',
                                render: (data) => `<span class="text-slate-500 dark:text-slate-400">${data}</span>`
                            },
                            {
                                data: 'roles',
                                className: 'p-4',
                                render: (data) => {
                                    return data.map(role =>
                                        `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mr-1">${role.name}</span>`
                                    ).join('');
                                }
                            },
                            {
                                data: 'branch',
                                className: 'p-4',
                                render: (data) => `<span class="text-slate-500 dark:text-slate-400">${data ? data.name : '-'}</span>`
                            },
                            {
                                data: 'division',
                                className: 'p-4',
                                render: (data) => `<span class="text-slate-500 dark:text-slate-400">${data ? data.name : '-'}</span>`
                            },
                            {
                                data: null,
                                className: "p-4 pr-6 text-center",
                                orderable: false,
                                render: function (data, type, row) {
                                    let buttons = `
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="window.viewEmployee(${row.id})" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <button onclick="window.editEmployee(${row.id})" class="p-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 text-yellow-600 rounded-lg transition-colors" title="Edit">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                    `;
                                    if (row.id !== {{ auth()->id() }}) {
                                        buttons += `
                                            <button onclick="window.deleteEmployee(${row.id})" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        `;
                                    }
                                    buttons += `</div>`;
                                    return buttons;
                                }
                            }
                        ],
                        dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-4"ip>',
                        language: {
                            search: "",
                            searchPlaceholder: "Cari karyawan...",
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

                // Confirm Modal & Common Modals
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

                // Employee Form Modal
                window.openEmployeeModal = function (isEdit = false) {
                    const modal = document.getElementById('employeeModal');
                    const backdrop = document.getElementById('employeeModalBackdrop');
                    const panel = document.getElementById('employeeModalPanel');
                    modal.classList.remove('hidden');
                    setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);

                    if (!isEdit) {
                        document.getElementById('employeeModalTitle').innerText = 'Tambah Karyawan Baru';
                        document.getElementById('employeeSubmitText').innerText = 'Simpan Data';
                        document.getElementById('employeeForm').reset();
                        document.getElementById('employeeId').value = '';

                        document.getElementById('passwordRequired').classList.remove('hidden');
                        document.getElementById('passwordConfirmRequired').classList.remove('hidden');
                        document.getElementById('passwordHint').classList.add('hidden');
                        document.getElementById('employeePassword').required = true;
                        document.getElementById('employeePasswordConfirmation').required = true;
                    } else {
                        document.getElementById('passwordRequired').classList.add('hidden');
                        document.getElementById('passwordConfirmRequired').classList.add('hidden');
                        document.getElementById('passwordHint').classList.remove('hidden');
                        document.getElementById('employeePassword').required = false;
                        document.getElementById('employeePasswordConfirmation').required = false;
                    }
                    lucide.createIcons();
                };

                window.closeEmployeeModal = function () {
                    const modal = document.getElementById('employeeModal');
                    const backdrop = document.getElementById('employeeModalBackdrop');
                    const panel = document.getElementById('employeeModalPanel');
                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                // View Employee Modal
                window.viewEmployee = function (id) {
                    currentViewEmployeeId = id;
                    const employee = employeeData.find(e => e.id === id);
                    if (employee) {
                        document.getElementById('viewEmployeeName').innerText = employee.name;
                        document.getElementById('viewEmployeeEmail').innerText = employee.email;
                        document.getElementById('viewEmployeeRole').innerText = employee.roles.length ? employee.roles[0].name : '-';
                        document.getElementById('viewEmployeeBranch').innerText = employee.branch ? employee.branch.name : '-';
                        document.getElementById('viewEmployeeDivision').innerText = employee.division ? employee.division.name : '-';

                        const modal = document.getElementById('viewEmployeeModal');
                        const backdrop = document.getElementById('viewEmployeeBackdrop');
                        const panel = document.getElementById('viewEmployeePanel');
                        modal.classList.remove('hidden');
                        setTimeout(() => { backdrop.classList.remove('opacity-0'); panel.classList.remove('scale-95', 'opacity-0'); panel.classList.add('scale-100', 'opacity-100'); }, 10);
                        lucide.createIcons();
                    }
                };

                window.closeViewEmployeeModal = function () {
                    const modal = document.getElementById('viewEmployeeModal');
                    const backdrop = document.getElementById('viewEmployeeBackdrop');
                    const panel = document.getElementById('viewEmployeePanel');
                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                window.editFromView = function () {
                    window.closeViewEmployeeModal();
                    setTimeout(() => window.editEmployee(currentViewEmployeeId), 350);
                };

                // Edit Employee
                window.editEmployee = function (id) {
                    const employee = employeeData.find(e => e.id === id);
                    if (employee) {
                        document.getElementById('employeeModalTitle').innerText = 'Edit Data Karyawan';
                        document.getElementById('employeeSubmitText').innerText = 'Update Data';
                        document.getElementById('employeeId').value = employee.id;
                        document.getElementById('employeeName').value = employee.name;
                        document.getElementById('employeeEmail').value = employee.email;
                        document.getElementById('employeeRole').value = employee.roles.length ? employee.roles[0].name : '';
                        document.getElementById('employeeBranch').value = employee.branch_id || '';
                        document.getElementById('employeeDivision').value = employee.division_id || '';

                        document.getElementById('employeePassword').value = '';
                        document.getElementById('employeePasswordConfirmation').value = '';

                        window.openEmployeeModal(true);
                    }
                };

                // Delete Employee
                let deleteId = null;
                window.deleteEmployee = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Karyawan?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/employees/${deleteId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ _method: 'DELETE' })
                        })
                            .then(r => r.json())
                            .then(data => {
                                setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                                if (data.success) {
                                    employeeData = employeeData.filter(e => e.id !== deleteId);
                                    table.clear().rows.add(employeeData).draw();
                                    showToast('Karyawan berhasil dihapus!');
                                    closeConfirmModal();
                                } else {
                                    showToast(data.message || 'Gagal menghapus data', 'error');
                                }
                            })
                            .catch(() => { setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!'); showToast('Terjadi kesalahan!', 'error'); });
                    });
                };

                // Form Submit
                document.getElementById('employeeForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('employeeId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/employees/${id}` : `${baseUrl}/employees`;
                    const btn = document.getElementById('employeeSubmitBtn');
                    const spinner = document.getElementById('employeeSubmitSpinner');
                    const text = document.getElementById('employeeSubmitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = {
                        name: document.getElementById('employeeName').value,
                        email: document.getElementById('employeeEmail').value,
                        role: document.getElementById('employeeRole').value,
                        branch_id: document.getElementById('employeeBranch').value,
                        division_id: document.getElementById('employeeDivision').value,
                    };

                    const password = document.getElementById('employeePassword').value;
                    const passwordConfirm = document.getElementById('employeePasswordConfirmation').value;

                    if (password) {
                        formData.password = password;
                        formData.password_confirmation = passwordConfirm;
                    }

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
                                    const index = employeeData.findIndex(e => e.id === parseInt(id));
                                    if (index >= 0) employeeData[index] = data.employee;
                                    showToast('Data karyawan berhasil diperbarui!');
                                } else {
                                    employeeData.push(data.employee);
                                    showToast('Karyawan berhasil ditambahkan!');
                                }
                                table.clear().rows.add(employeeData).draw();
                                window.closeEmployeeModal();
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