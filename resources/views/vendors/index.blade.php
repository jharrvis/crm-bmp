<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Vendor Backbone</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola data vendor internet backbone dan
                        kontak.</p>
                </div>
                <button onclick="window.openModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Vendor</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4">Nama Vendor</th>
                            <th class="p-4">Alamat</th>
                            <th class="p-4">Kontak</th>
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
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Vendor Baru
                    </h3>
                    <button onclick="window.closeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                    <form id="dataForm" class="space-y-6">
                        @csrf
                        <input type="hidden" id="dataId" name="id">

                        <!-- Tabs -->
                        <div class="flex border-b border-slate-200 dark:border-slate-700 mb-6">
                            <button type="button" onclick="switchTab('main')" id="tab-main"
                                class="px-6 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 transition-colors">
                                Data Utama
                            </button>
                            <button type="button" onclick="switchTab('contacts')" id="tab-contacts"
                                class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                                Kontak
                            </button>
                        </div>

                        <!-- Tab Content: Main -->
                        <div id="content-main" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama
                                        Vendor <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" name="name" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                        placeholder="Nama Vendor / ISP">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat
                                    Lengkap</label>
                                <textarea id="address" name="address" rows="3"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"></textarea>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                                <input type="text" id="notes" name="notes"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                            </div>
                        </div>

                        <!-- Tab Content: Contacts -->
                        <div id="content-contacts" class="hidden space-y-4">
                            <div id="contacts-container" class="space-y-4">
                                <!-- Contact items will be added here via JS -->
                            </div>
                            <button type="button" onclick="addContactRow()"
                                class="w-full py-3 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl text-slate-500 hover:text-blue-600 hover:border-blue-500 dark:hover:text-blue-400 text-sm font-bold transition-all flex items-center justify-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kontak Lain
                            </button>
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
                        <span id="submitText">Simpan Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Template for Contact Row -->
    <template id="contact-row-template">
        <div
            class="contact-row bg-slate-50 dark:bg-slate-700/30 p-4 rounded-xl border border-slate-100 dark:border-slate-700 relative group">
            <button type="button" onclick="removeContactRow(this)"
                class="absolute top-2 right-2 p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pr-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Kontak <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="contacts[INDEX][name]" required
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-2 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none"
                        placeholder="Nama Lengkap">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nomor HP/WA</label>
                    <input type="text" name="contacts[INDEX][phone]"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-2 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none"
                        placeholder="Contoh: 08123456789">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                    <input type="email" name="contacts[INDEX][email]"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-2 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none"
                        placeholder="email@example.com">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Jabatan / Relasi</label>
                    <input type="text" name="contacts[INDEX][position]"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-2 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none"
                        placeholder="Misal: Account Manager">
                </div>
            </div>
        </div>
    </template>

    <x-confirm-modal />

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                let tableData = @json($vendors); // Passed from controller
                let table;
                let contactIndex = 0;

                $(document).ready(function () {
                    // Initialize DataTable
                    table = $('#dataTable').DataTable({
                        data: tableData,
                        columns: [
                            {
                                data: 'name',
                                className: 'p-4',
                                render: (data) => `<div class="font-bold text-slate-800 dark:text-slate-300">${data}</div>`
                            },
                            {
                                data: 'address',
                                className: 'p-4',
                                render: (data) => data ? `<span class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-xs block">${data}</span>` : '-'
                            },
                            {
                                data: 'contacts',
                                className: 'p-4',
                                render: function (data) {
                                    if (data && data.length > 0) {
                                        const contact = data[0];
                                        let phoneDisplay = '-';

                                        if (contact.phone) {
                                            // Format Phone: Remove non-digits, replace 0 with 62
                                            let rawPhone = contact.phone.replace(/\D/g, '');
                                            if (rawPhone.startsWith('0')) {
                                                rawPhone = '62' + rawPhone.substring(1);
                                            }

                                            phoneDisplay = `
                                                        <div class="flex items-center gap-1">
                                                            <span class="text-xs text-slate-500">${contact.phone}</span>
                                                            <a href="https://wa.me/${rawPhone}" target="_blank" class="text-green-500 hover:text-green-600" title="Chat WhatsApp">
                                                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                            </a>
                                                        </div>
                                                    `;
                                        }

                                        return `<div class="flex flex-col gap-1">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">${contact.name}</span>
                                                        ${phoneDisplay}
                                                    </div>`
                                            + (data.length > 1 ? `<span class="text-xs text-blue-500">+${data.length - 1} lainnya</span>` : '');
                                    }
                                    return '-';
                                }
                            },
                            {
                                data: null,
                                className: "p-4 pr-6 text-center",
                                orderable: false,
                                render: function (data, type, row) {
                                    return `
                                                                <div class="flex items-center justify-center gap-2">
                                                                    <button onclick="window.viewData(${row.id})" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat Detail">
                                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                                    </button>
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
                            searchPlaceholder: "Cari vendor...",
                            lengthMenu: "Tampilkan _MENU_",
                            info: "_START_ - _END_ dari _TOTAL_",
                            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                        },
                        drawCallback: function () { lucide.createIcons(); },
                        createdRow: function (row) { $(row).addClass('hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors'); }
                    });
                });


                // Tab Switcher
                window.switchTab = function (tabName) {
                    // Update buttons
                    document.getElementById('tab-main').classList.toggle('text-blue-600', tabName === 'main');
                    document.getElementById('tab-main').classList.toggle('border-blue-600', tabName === 'main');
                    document.getElementById('tab-main').classList.toggle('text-slate-500', tabName !== 'main');

                    document.getElementById('tab-contacts').classList.toggle('text-blue-600', tabName === 'contacts');
                    document.getElementById('tab-contacts').classList.toggle('border-blue-600', tabName === 'contacts');
                    document.getElementById('tab-contacts').classList.toggle('text-slate-500', tabName !== 'contacts');

                    // Update Content
                    document.getElementById('content-main').classList.toggle('hidden', tabName !== 'main');
                    document.getElementById('content-contacts').classList.toggle('hidden', tabName !== 'contacts');
                }

                // Contact Row Management
                window.addContactRow = function (data = null) {
                    const template = document.getElementById('contact-row-template');
                    const clone = template.content.cloneNode(true);
                    const container = document.getElementById('contacts-container');
                    const rowId = contactIndex++;
                    const isFirst = container.children.length === 0;

                    // Update Index in Name Attributes
                    clone.querySelectorAll('[name]').forEach(el => {
                        el.name = el.name.replace('INDEX', rowId);
                    });

                    // Hide trash button for first item if needed, but for vendors maybe allow deleting all if optional
                    const trashBtn = clone.querySelector('button');

                    if (isFirst) {
                        // Keep trash button visible, maybe vendor has 0 contacts initially
                        // trashBtn.classList.add('hidden');
                    } else {
                        // trashBtn.classList.remove('hidden');
                    }

                    // Fill data if editing
                    if (data) {
                        clone.querySelector(`[name="contacts[${rowId}][name]"]`).value = data.name;
                        clone.querySelector(`[name="contacts[${rowId}][phone]"]`).value = data.phone || '';
                        clone.querySelector(`[name="contacts[${rowId}][email]"]`).value = data.email || '';
                        clone.querySelector(`[name="contacts[${rowId}][position]"]`).value = data.position || '';
                    }

                    container.appendChild(clone);
                    lucide.createIcons();
                }

                window.removeContactRow = function (btn) {
                    btn.closest('.contact-row').remove();
                }

                // Modal Functions
                window.openModal = function (mode = 'add') { // mode: 'add', 'edit', 'view'
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');
                    const form = document.getElementById('dataForm');
                    const submitBtn = document.getElementById('submitBtn');

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');
                    }, 10);

                    // Enable/Disable form based on mode
                    const inputs = form.querySelectorAll('input, select, textarea, button[type="button"]');
                    if (mode === 'view') {
                        inputs.forEach(el => {
                            // Don't disable modal close button and tab buttons
                            if (!el.closest('.shrink-0') && !el.id.startsWith('tab-')) {
                                el.disabled = true;
                            }
                        });
                        submitBtn.classList.add('hidden');
                        document.querySelector('button[onclick="addContactRow()"]').classList.add('hidden');
                        document.querySelectorAll('.contact-row button').forEach(b => b.classList.add('hidden'));
                    } else {
                        inputs.forEach(el => el.disabled = false);
                        submitBtn.classList.remove('hidden');
                        document.querySelector('button[onclick="addContactRow()"]').classList.remove('hidden');
                    }

                    if (mode === 'add') {
                        document.getElementById('modalTitle').innerText = 'Tambah Vendor Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        form.reset();
                        document.getElementById('dataId').value = '';

                        // Reset Contacts: Add one empty row
                        document.getElementById('contacts-container').innerHTML = '';
                        contactIndex = 0;
                        addContactRow();

                        switchTab('main');
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
                    const url = isUpdate ? `${baseUrl}/vendors/${id}` : `${baseUrl}/vendors`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
                    if (isUpdate) formData.append('_method', 'PUT');

                    // Parse FormData to JSON object for `contacts` array
                    const object = {};
                    formData.forEach((value, key) => {
                        if (key.includes('contacts[')) {
                            const match = key.match(/contacts\[(\d+)\]\[(\w+)\]/);
                            if (match) {
                                if (!object._contacts_temp) object._contacts_temp = {};
                                if (!object._contacts_temp[match[1]]) object._contacts_temp[match[1]] = {};
                                object._contacts_temp[match[1]][match[2]] = value;
                            }
                        } else {
                            object[key] = value;
                        }
                    });

                    // Format contacts array from temp object
                    if (object._contacts_temp) {
                        object.contacts = Object.values(object._contacts_temp).filter(c => {
                            return c.name && c.name.trim() !== '';
                        });
                        delete object._contacts_temp;
                    }

                    // Add _token if missing from loop (sometimes happens with manual JSON construction)
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
                                if (isUpdate) {
                                    const index = tableData.findIndex(d => d.id === parseInt(id));
                                    if (index >= 0) tableData[index] = res.data; // Controller returns 'data' key
                                    showToast('Data vendor berhasil diperbarui!');
                                } else {
                                    tableData.push(res.data);
                                    showToast('Vendor berhasil ditambahkan!');
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

                // View Data
                window.viewData = function (id) {
                    const item = tableData.find(d => d.id === id);
                    if (item) {
                        fillFormWithData(item);
                        document.getElementById('modalTitle').innerText = 'Detail Vendor';
                        window.openModal('view');
                    }
                };

                // Helper to fill form
                function fillFormWithData(item) {
                    document.getElementById('dataId').value = item.id;
                    document.getElementById('name').value = item.name;
                    document.getElementById('address').value = item.address || '';
                    document.getElementById('notes').value = item.notes || '';

                    // Fill Contacts
                    document.getElementById('contacts-container').innerHTML = '';
                    contactIndex = 0;
                    if (item.contacts && item.contacts.length > 0) {
                        item.contacts.forEach(contact => {
                            addContactRow(contact);
                        });
                    } else {
                        addContactRow();
                    }
                    switchTab('main');
                }

                // Edit Data
                window.editData = function (id) {
                    const item = tableData.find(d => d.id === id);
                    if (item) {
                        fillFormWithData(item);
                        document.getElementById('modalTitle').innerText = 'Edit Vendor';
                        document.getElementById('submitText').innerText = 'Update Data';
                        window.openModal('edit');
                    }
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Vendor?', 'Data kontak terkait juga akan terhapus.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/vendors/${deleteId}`, {
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
                                    showToast('Vendor berhasil dihapus!');
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
                document.getElementById('dataForm').addEventListener('input', function (e) {
                    if (e.target.name && e.target.name.includes('[phone]')) {
                        let input = e.target.value;
                        // Replace 08... with 628...
                        if (input.startsWith('08')) {
                            input = '628' + input.substring(2);
                        }
                        // Remove non-numeric characters (optional, based on preference, but good for standardization)
                        input = input.replace(/[^0-9+]/g, '');

                        if (input !== e.target.value) {
                            e.target.value = input;
                        }
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>