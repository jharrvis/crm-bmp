<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Pelanggan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola data pelanggan dan kontak.</p>
                </div>
                <button onclick="window.openModal()"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Tambah Pelanggan</span>
                </button>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">


                <!-- Filter Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Status</label>
                    <div class="relative">
                        <select id="filter_status"
                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none text-slate-700 dark:text-slate-200">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Non-Aktif</option>
                            <option value="prospect">Leads</option>
                            <option value="suspended">Suspend</option>
                        </select>
                        <i data-lucide="activity" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
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
                            <th class="p-4 pl-6">ID Pelanggan</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Cabang</th>
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
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]"
                id="formModalPanel">

                <!-- Modal Header -->
                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Pelanggan Baru
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
                            <button type="button" onclick="switchTab('address')" id="tab-address"
                                class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                                Alamat
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
                                        Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" name="name" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                        placeholder="Nama Pelanggan / Perusahaan">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cabang
                                        <span class="text-red-500">*</span></label>
                                    <select id="branch_id" name="branch_id" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                        <option value="">Pilih Cabang</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipe
                                        Pelanggan</label>
                                    <select id="type" name="type" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                        @foreach(\App\Models\Client::TYPE_OPTIONS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="customTypeField" class="hidden">
                                    <label for="custom_type" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kategori Custom <span class="text-red-500">*</span></label>
                                    <input type="text" id="custom_type" name="custom_type" maxlength="100"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                        placeholder="Contoh: Koperasi Desa">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">No.
                                        Identitas (KTP/NPWP)</label>
                                    <input type="text" id="identity_number" name="identity_number"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                        placeholder="3374XXXXXXXXXXXX">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal
                                        Registrasi</label>
                                    <input type="date" id="registered_at" name="registered_at"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                                    <select id="status" name="status" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Non-Aktif</option>
                                        <option value="prospect">Calon Pelanggan (Prospect)</option>
                                        <option value="suspended">Ditangguhkan (Suspend)</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                                    <textarea id="notes" name="notes" rows="3"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                        placeholder="Catatan internal pelanggan (opsional)"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Address -->
                        <div id="content-address" class="hidden space-y-5">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Alamat lama tetap dipertahankan. Lengkapi wilayah administratif bila tersedia.</p>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat Lengkap</label>
                                <textarea id="address" name="address" rows="3"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"
                                    placeholder="Nama jalan, nomor bangunan, blok, atau patokan lokasi"></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Provinsi</label>
                                    <select id="province_code" name="province_code" class="area-select w-full"><option value="">Pilih provinsi</option></select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kabupaten/Kota</label>
                                    <select id="regency_code" name="regency_code" class="area-select w-full" disabled><option value="">Pilih kabupaten/kota</option></select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kecamatan</label>
                                    <select id="district_code" name="district_code" class="area-select w-full" disabled><option value="">Pilih kecamatan</option></select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kelurahan/Desa</label>
                                    <select id="village_code" name="village_code" class="area-select w-full" disabled><option value="">Pilih kelurahan/desa</option></select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">RT</label>
                                    <input type="text" id="rt" name="rt" maxlength="5" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="001">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">RW</label>
                                    <input type="text" id="rw" name="rw" maxlength="5" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="002">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kota/Kabupaten Lama</label>
                                    <input type="text" id="city" name="city" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kode Pos</label>
                                    <input type="text" id="postal_code" name="postal_code" maxlength="20" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Latitude</label>
                                        <button type="button" onclick="openLocationMap()" class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 inline-flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Pilih di Peta
                                        </button>
                                    </div>
                                    <input type="text" id="latitude" name="latitude" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="-7.12345678">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="110.12345678">
                                </div>
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

    <div id="locationMapModal" class="fixed inset-0 z-[70] hidden">
        <div id="locationMapBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm opacity-0 transition-opacity"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div id="locationMapPanel" class="w-full max-w-3xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl scale-95 opacity-0 transition-all duration-300 overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pilih Lokasi Pelanggan</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Klik peta atau geser marker untuk mengisi latitude dan longitude.</p>
                    </div>
                    <button type="button" onclick="closeLocationMap()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 space-y-3">
                    <form id="locationSearchForm" class="flex gap-2">
                        <label for="locationSearchQuery" class="sr-only">Cari lokasi</label>
                        <input id="locationSearchQuery" type="search" minlength="3" maxlength="120"
                            class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Cari jalan, gedung, kelurahan, atau kecamatan">
                        <button id="locationSearchButton" type="submit" class="px-4 py-2.5 rounded-xl text-sm font-bold bg-slate-800 hover:bg-slate-700 dark:bg-slate-600 dark:hover:bg-slate-500 text-white inline-flex items-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i> Cari
                        </button>
                    </form>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pencarian dikirim saat tombol Cari ditekan dan dibatasi untuk Indonesia. Jangan gunakan untuk data alamat rahasia.</p>
                    <div id="locationSearchMessage" class="hidden text-sm"></div>
                    <div id="locationSearchResults" class="hidden max-h-36 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-600 divide-y divide-slate-100 dark:divide-slate-700"></div>
                </div>
                <div id="clientLocationMap" class="h-[420px] bg-slate-100 dark:bg-slate-700"></div>
                <div class="p-5 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-4">
                    <p id="locationMapCoordinates" class="text-sm text-slate-500 dark:text-slate-400">Belum ada titik dipilih.</p>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeLocationMap()" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">Batal</button>
                        <button type="button" onclick="useMapLocation()" class="px-4 py-2 rounded-xl text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white">Gunakan Lokasi</button>
                    </div>
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
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nomor HP/WA <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="contacts[INDEX][phone]" required
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
                        placeholder="Misal: Penanggung Jawab / Istri">
                </div>
            </div>
            <div class="mt-2" id="primary-indicator">
                <!-- Will be shown for the first item -->
                <span class="text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded">Kontak
                    Utama</span>
            </div>
        </div>
    </template>

    <x-confirm-modal />

    @php
        $branchDefaults = [];

        foreach ($branches as $branch) {
            $branchDefaults[$branch->id] = [
                'province_code' => $branch->default_province_code,
                'regency_code' => $branch->default_regency_code,
                'latitude' => $branch->default_latitude,
                'longitude' => $branch->default_longitude,
            ];
        }

        $mapConfig = [
            'tileUrl' => config('maps.tile_url'),
            'attribution' => config('maps.attribution'),
        ];
    @endphp

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                const administrativeAreasUrl = '{{ route('administrative-areas.index') }}';
                const mapLocationSearchUrl = '{{ route('map-locations.search') }}';
                const branchDefaults = @json($branchDefaults);
                const mapConfig = @json($mapConfig);
                const centralJava = { latitude: -7.15000000, longitude: 110.14000000 };
                const clientTypeLabels = @json(\App\Models\Client::TYPE_OPTIONS);
                const escapeHtml = (value) => $('<div>').text(value || '').html();
                const areaChoices = {};
                const areaFields = {
                    province: { id: 'province_code', placeholder: 'Cari provinsi' },
                    regency: { id: 'regency_code', placeholder: 'Cari kabupaten/kota' },
                    district: { id: 'district_code', placeholder: 'Cari kecamatan' },
                    village: { id: 'village_code', placeholder: 'Cari kelurahan/desa' },
                };

                Object.values(areaFields).forEach(({ id, placeholder }) => {
                    areaChoices[id] = new Choices(document.getElementById(id), {
                        allowHTML: false,
                        searchEnabled: true,
                        shouldSort: false,
                        itemSelectText: '',
                        noResultsText: 'Data wilayah tidak ditemukan',
                        searchPlaceholderValue: placeholder,
                    });
                });

                function setAreaDisabled(id, disabled) {
                    const select = document.getElementById(id);
                    select.disabled = disabled;
                    disabled ? areaChoices[id].disable() : areaChoices[id].enable();
                }

                function resetAreaSelect(level) {
                    const { id, placeholder } = areaFields[level];
                    areaChoices[id].clearStore();
                    areaChoices[id].setChoices([{ value: '', label: placeholder, placeholder: true, selected: true }], 'value', 'label', true);
                    setAreaDisabled(id, level !== 'province');
                }

                async function loadAreas(level, parentCode = null, selectedCode = '') {
                    const { id, placeholder } = areaFields[level];
                    const params = new URLSearchParams({ level });
                    if (parentCode) params.set('parent_code', parentCode);

                    const response = await fetch(`${administrativeAreasUrl}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const payload = await response.json();
                    const choices = [{ value: '', label: placeholder, placeholder: true, selected: !selectedCode }]
                        .concat(payload.data.map((area) => ({
                            value: area.code,
                            label: area.name,
                            selected: area.code === selectedCode,
                        })));

                    areaChoices[id].clearStore();
                    areaChoices[id].setChoices(choices, 'value', 'label', true);
                    setAreaDisabled(id, false);
                }

                async function resetAddressAreas() {
                    resetAreaSelect('province');
                    resetAreaSelect('regency');
                    resetAreaSelect('district');
                    resetAreaSelect('village');
                    await loadAreas('province');
                }

                async function fillAddressAreas(item) {
                    resetAreaSelect('regency');
                    resetAreaSelect('district');
                    resetAreaSelect('village');
                    await loadAreas('province', null, item.province_code || '');
                    if (!item.province_code && !item.regency_code) {
                        await applyBranchAreaDefaults();
                        return;
                    }
                    if (!item.province_code) return;
                    await loadAreas('regency', item.province_code, item.regency_code || '');
                    if (!item.regency_code) return;
                    await loadAreas('district', item.regency_code, item.district_code || '');
                    if (!item.district_code) return;
                    await loadAreas('village', item.district_code, item.village_code || '');
                }

                async function applyBranchAreaDefaults() {
                    const branchId = document.getElementById('branch_id').value;
                    const defaults = branchDefaults[branchId];
                    const provinceSelect = document.getElementById('province_code');
                    const regencySelect = document.getElementById('regency_code');

                    if (!defaults || provinceSelect.value || regencySelect.value) return;

                    await loadAreas('province', null, defaults.province_code || '');
                    if (defaults.province_code && defaults.regency_code) {
                        await loadAreas('regency', defaults.province_code, defaults.regency_code);
                    }
                }

                let clientLocationMap;
                let clientLocationMarker;
                let selectedMapLocation;

                function updateMapCoordinates(position) {
                    selectedMapLocation = { latitude: position.lat, longitude: position.lng };
                    document.getElementById('locationMapCoordinates').textContent = `Lat ${position.lat.toFixed(8)}, Long ${position.lng.toFixed(8)}`;
                }

                function placeLocationMarker(position) {
                    if (!clientLocationMarker) {
                        clientLocationMarker = L.marker(position, { draggable: true }).addTo(clientLocationMap);
                        clientLocationMarker.on('dragend', (event) => updateMapCoordinates(event.target.getLatLng()));
                    } else {
                        clientLocationMarker.setLatLng(position);
                    }

                    updateMapCoordinates(position);
                }

                window.openLocationMap = function () {
                    if (!window.L) {
                        showToast('Peta belum dapat dimuat. Periksa koneksi internet lalu coba kembali.', 'error');
                        return;
                    }

                    const modal = document.getElementById('locationMapModal');
                    const backdrop = document.getElementById('locationMapBackdrop');
                    const panel = document.getElementById('locationMapPanel');
                    const branchDefault = branchDefaults[document.getElementById('branch_id').value] || centralJava;
                    const storedLatitudeValue = document.getElementById('latitude').value.trim();
                    const storedLongitudeValue = document.getElementById('longitude').value.trim();
                    const storedLatitude = storedLatitudeValue === '' ? Number.NaN : Number(storedLatitudeValue);
                    const storedLongitude = storedLongitudeValue === '' ? Number.NaN : Number(storedLongitudeValue);
                    const latitude = Number.isFinite(storedLatitude) ? storedLatitude : Number(branchDefault.latitude || centralJava.latitude);
                    const longitude = Number.isFinite(storedLongitude) ? storedLongitude : Number(branchDefault.longitude || centralJava.longitude);
                    const initialPosition = { lat: latitude, lng: longitude };

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');

                        if (!clientLocationMap) {
                            clientLocationMap = L.map('clientLocationMap').setView(initialPosition, 13);
                            L.tileLayer(mapConfig.tileUrl, { maxZoom: 19, attribution: mapConfig.attribution }).addTo(clientLocationMap);
                            clientLocationMap.on('click', (event) => placeLocationMarker(event.latlng));
                        } else {
                            clientLocationMap.setView(initialPosition, 13);
                            clientLocationMap.invalidateSize();
                        }

                        placeLocationMarker(initialPosition);
                    }, 10);
                    lucide.createIcons();
                };

                window.closeLocationMap = function () {
                    const modal = document.getElementById('locationMapModal');
                    const backdrop = document.getElementById('locationMapBackdrop');
                    const panel = document.getElementById('locationMapPanel');
                    backdrop.classList.add('opacity-0');
                    panel.classList.remove('scale-100', 'opacity-100');
                    panel.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                window.useMapLocation = function () {
                    if (!selectedMapLocation) return;
                    document.getElementById('latitude').value = selectedMapLocation.latitude.toFixed(8);
                    document.getElementById('longitude').value = selectedMapLocation.longitude.toFixed(8);
                    window.closeLocationMap();
                };

                function showLocationSearchMessage(message, type = 'error') {
                    const element = document.getElementById('locationSearchMessage');
                    element.textContent = message;
                    element.className = `text-sm ${type === 'error' ? 'text-red-600 dark:text-red-400' : 'text-slate-500 dark:text-slate-400'}`;
                    element.classList.remove('hidden');
                }

                window.selectLocationSearchResult = function (latitude, longitude, label) {
                    const position = { lat: latitude, lng: longitude };
                    clientLocationMap.setView(position, 16);
                    placeLocationMarker(position);
                    document.getElementById('locationSearchQuery').value = label;
                    document.getElementById('locationSearchResults').classList.add('hidden');
                    document.getElementById('locationSearchMessage').classList.add('hidden');
                };

                document.getElementById('locationSearchForm').addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const query = document.getElementById('locationSearchQuery').value.trim();
                    const button = document.getElementById('locationSearchButton');
                    const results = document.getElementById('locationSearchResults');
                    if (query.length < 3) {
                        showLocationSearchMessage('Masukkan minimal 3 karakter untuk mencari lokasi.');
                        return;
                    }

                    button.disabled = true;
                    button.innerHTML = '<i data-lucide="loader-circle" class="w-4 h-4 animate-spin"></i> Mencari';
                    results.classList.add('hidden');
                    document.getElementById('locationSearchMessage').classList.add('hidden');
                    lucide.createIcons();

                    try {
                        const response = await fetch(`${mapLocationSearchUrl}?q=${encodeURIComponent(query)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || 'Pencarian lokasi gagal.');
                        }

                        if (!payload.data.length) {
                            showLocationSearchMessage('Lokasi tidak ditemukan. Coba kata kunci yang lebih spesifik.', 'info');
                            return;
                        }

                        results.replaceChildren();
                        payload.data.forEach((item) => {
                            const resultButton = document.createElement('button');
                            resultButton.type = 'button';
                            resultButton.className = 'w-full px-4 py-2.5 text-left text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60';
                            resultButton.textContent = item.label;
                            resultButton.addEventListener('click', () => window.selectLocationSearchResult(item.latitude, item.longitude, item.label));
                            results.appendChild(resultButton);
                        });
                        results.classList.remove('hidden');
                    } catch (error) {
                        showLocationSearchMessage(error.message || 'Pencarian lokasi gagal. Tentukan titik secara manual di peta.');
                    } finally {
                        button.disabled = false;
                        button.innerHTML = '<i data-lucide="search" class="w-4 h-4"></i> Cari';
                        lucide.createIcons();
                    }
                });

                // Initialize DataTable
                table = $('#dataTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('clients.index') }}',
                        data: function (d) {
                            d.branch_id = '{{ request('branch_id') }}';
                            d.status = $('#filter_status').val();
                        }
                    },
                    columns: [
                        {
                            data: 'client_code',
                            name: 'client_code',
                            className: 'p-4 pl-6',
                            render: (data) => `<span class="font-mono text-sm font-bold text-slate-600 dark:text-slate-300">${data}</span>`
                        },
                        {
                            data: 'name',
                            name: 'name',
                            className: 'p-4',
                            render: (data, type, row) => `
                                <div class="font-bold text-slate-800 dark:text-white">${data}</div>
                                <div class="text-xs text-slate-500">${row.primary_contact_phone || '-'}</div>
                            `
                        },
                        {
                            data: 'branch_name', // Computed column
                            name: 'branch.name', // Searchable by relationship
                            className: 'p-4',
                            render: (data) => data !== '-' ? `<span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300">${data}</span>` : '-'
                        },
                        {
                            data: 'type_label',
                            name: 'type',
                            className: 'p-4',
                            render: (data, type, row) => escapeHtml(data || clientTypeLabels[row.type] || 'Tidak ditentukan')
                        },
                        {
                            data: 'status',
                            name: 'status',
                            className: 'p-4',
                            render: function (data) {
                                const styles = {
                                    'active': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'inactive': 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400',
                                    'suspended': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    'prospect': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                                };
                                const labels = {
                                    'active': 'Aktif',
                                    'inactive': 'Non-Aktif',
                                    'suspended': 'Suspend',
                                    'prospect': 'Leads'
                                };
                                return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${styles[data] || styles.inactive}">${labels[data] || data}</span>`;
                            }
                        },
                        {
                            data: null,
                            className: "p-4 pr-6 text-center",
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                return `
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="${baseUrl}/clients/${row.id}" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Lihat Detail">
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
                        searchPlaceholder: "Cari pelanggan...",
                        lengthMenu: "Tampilkan _MENU_",
                        info: "_START_ - _END_ dari _TOTAL_",
                        paginate: { first: "«", last: "»", next: "›", previous: "‹" },
                        processing: "Memuat data..."
                    },
                    drawCallback: function () { lucide.createIcons(); },
                    createdRow: function (row) { $(row).addClass('hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors'); }
                });

                // Filter Event Listeners
                $('#filter_status').on('change', function () {
                    table.draw();
                });

                // Tab Switcher
                window.switchTab = function (tabName) {
                    // Update buttons
                    document.getElementById('tab-main').classList.toggle('text-blue-600', tabName === 'main');
                    document.getElementById('tab-main').classList.toggle('border-blue-600', tabName === 'main');
                    document.getElementById('tab-main').classList.toggle('text-slate-500', tabName !== 'main');

                    document.getElementById('tab-address').classList.toggle('text-blue-600', tabName === 'address');
                    document.getElementById('tab-address').classList.toggle('border-blue-600', tabName === 'address');
                    document.getElementById('tab-address').classList.toggle('text-slate-500', tabName !== 'address');

                    document.getElementById('tab-contacts').classList.toggle('text-blue-600', tabName === 'contacts');
                    document.getElementById('tab-contacts').classList.toggle('border-blue-600', tabName === 'contacts');
                    document.getElementById('tab-contacts').classList.toggle('text-slate-500', tabName !== 'contacts');

                    // Update Content
                    document.getElementById('content-main').classList.toggle('hidden', tabName !== 'main');
                    document.getElementById('content-address').classList.toggle('hidden', tabName !== 'address');
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

                    // Hide trash button for first item, show primary indicator
                    const trashBtn = clone.querySelector('button');
                    const primaryInd = clone.querySelector('#primary-indicator');

                    if (isFirst) {
                        trashBtn.classList.add('hidden');
                        primaryInd.classList.remove('hidden');
                    } else {
                        trashBtn.classList.remove('hidden');
                        primaryInd.classList.add('hidden');
                    }

                    // Fill data if editing
                    if (data) {
                        clone.querySelector(`[name="contacts[${rowId}][name]"]`).value = data.name;
                        clone.querySelector(`[name="contacts[${rowId}][phone]"]`).value = data.phone;
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
                            if (!el.closest('.shrink-0')) { // Don't disable modal close click
                                el.disabled = true;
                            }
                        });
                        submitBtn.classList.add('hidden');
                        // Specialized check for 'Tambah Kontak Lain' which is a button
                        document.querySelector('button[onclick="addContactRow()"]').classList.add('hidden');
                        // Also hide trash buttons in contact rows
                        document.querySelectorAll('.contact-row button').forEach(b => b.classList.add('hidden'));
                    } else {
                        inputs.forEach(el => el.disabled = false);
                        submitBtn.classList.remove('hidden');
                        document.querySelector('button[onclick="addContactRow()"]').classList.remove('hidden');
                    }

                    if (mode === 'add') {
                        document.getElementById('modalTitle').innerText = 'Tambah Pelanggan Baru';
                        document.getElementById('submitText').innerText = 'Simpan Data';
                        form.reset();
                        document.getElementById('dataId').value = '';
                        document.getElementById('registered_at').value = new Date().toISOString().split('T')[0];
                        window.syncClientCustomType(true);
                        resetAddressAreas();

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

                window.syncClientCustomType = function (clearValue = false) {
                    const typeInput = document.getElementById('type');
                    const customTypeField = document.getElementById('customTypeField');
                    const customTypeInput = document.getElementById('custom_type');
                    const isOther = typeInput.value === 'other';

                    customTypeField.classList.toggle('hidden', !isOther);
                    customTypeInput.disabled = !isOther;

                    if (!isOther && clearValue) {
                        customTypeInput.value = '';
                    }
                };

                // Form Submit
                window.submitForm = function () {
                    document.getElementById('dataForm').dispatchEvent(new Event('submit'));
                }

                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('dataId').value;
                    const isUpdate = !!id;
                    const url = isUpdate ? `${baseUrl}/clients/${id}` : `${baseUrl}/clients`;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    const originalText = isUpdate ? 'Update Data' : 'Simpan Data';

                    setButtonLoading(btn, spinner, text, true, originalText);

                    const formData = new FormData(this);
                    if (isUpdate) formData.append('_method', 'PUT');

                    // Convert FormData to JSON (tricky with nested arrays contacts[])
                    // Standard FormData should work with simple POST. But for nested, Laravel handles it if name is contacts[0][name].
                    // Let's send FormData directly if not JSON? 
                    // Previously we used JSON.stringify. With nested objects, iterating FormData and building JSON is tedious.
                    // Let's try sending standard FormData via fetch (content-type should NOT be set manually).

                    // Actually, let's keep consistent with existing API that consumes JSON.
                    // We need to parse FormData to JSON object for `contacts` array.
                    const object = {};
                    formData.forEach((value, key) => {
                        if (key.includes('contacts[')) {
                            // Complex: contacts[0][name]
                            const match = key.match(/contacts\[(\d+)\]\[(\w+)\]/);
                            if (match) {
                                if (!object.contacts) object.contacts = [];
                                const idx = parseInt(match[1]); // This is the row ID, might not be sequential 0,1,2 if deleted.
                                // We need to push to array.
                                // But rowId is unique counter.
                                // Let's use an object first or safer: filter empty.
                                // Better: just reconstruct array on backend? No. 
                                // Let's simplify: send as JSON by reconstructing object correctly.
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
                            // Only include contact if Name OR Phone is filled
                            return (c.name && c.name.trim() !== '') || (c.phone && c.phone.trim() !== '');
                        });
                        delete object._contacts_temp;
                    }

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(object)
                    })
                        .then(r => r.json())
                        .then(res => {
                            setButtonLoading(btn, spinner, text, false, originalText);
                            if (res.success) {
                                showToast(isUpdate ? 'Data pelanggan berhasil diperbarui!' : 'Pelanggan berhasil ditambahkan!');
                                table.ajax.reload(null, false);
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
                    fetch(`${baseUrl}/clients/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(async item => {
                        await fillFormWithData(item);
                        document.getElementById('modalTitle').innerText = 'Detail Pelanggan';
                        window.openModal('view');
                    })
                    .catch(e => {
                        console.error(e);
                        showToast('Gagal memuat data pelanggan', 'error');
                    });
                };

                // Helper to fill form
                async function fillFormWithData(item) {
                    document.getElementById('dataId').value = item.id;
                    document.getElementById('branch_id').value = item.branch_id;
                    document.getElementById('name').value = item.name;
                    document.getElementById('type').value = item.type;
                    document.getElementById('custom_type').value = item.custom_type || '';
                    window.syncClientCustomType();
                    document.getElementById('identity_number').value = item.identity_number || '';
                    document.getElementById('address').value = item.address || '';
                    document.getElementById('rt').value = item.rt || '';
                    document.getElementById('rw').value = item.rw || '';
                    document.getElementById('city').value = item.city || '';
                    document.getElementById('postal_code').value = item.postal_code || '';
                    document.getElementById('latitude').value = item.latitude || '';
                    document.getElementById('longitude').value = item.longitude || '';
                    document.getElementById('registered_at').value = item.registered_at || '';
                    document.getElementById('status').value = item.status;
                    document.getElementById('notes').value = item.notes || '';
                    await fillAddressAreas(item);

                    // Fill Contacts
                    document.getElementById('contacts-container').innerHTML = '';
                    contactIndex = 0;
                    if (item.contacts && item.contacts.length > 0) {
                        const sortedContacts = [...item.contacts].sort((a, b) => b.is_primary - a.is_primary);
                        sortedContacts.forEach(contact => {
                            addContactRow(contact);
                        });
                    } else {
                        addContactRow();
                    }
                    switchTab('main');
                }

                // Edit Data
                window.editData = function (id) {
                    fetch(`${baseUrl}/clients/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(async item => {
                        await fillFormWithData(item);
                        document.getElementById('modalTitle').innerText = 'Edit Pelanggan';
                        document.getElementById('submitText').innerText = 'Update Data';
                        window.openModal('edit');
                        window.syncClientCustomType();
                    })
                    .catch(e => {
                        console.error(e);
                        showToast('Gagal memuat data pelanggan', 'error');
                    });
                };

                // Delete Data
                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Pelanggan?', 'Seluruh data langganan, tagihan, dan tiket terkait juga akan terhapus jika ada.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/clients/${deleteId}`, {
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
                                    table.ajax.reload(null, false); // Reload table, keep paging
                                    showToast('Pelanggan berhasil dihapus!');
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

                document.getElementById('type').addEventListener('change', () => window.syncClientCustomType(true));
                document.getElementById('branch_id').addEventListener('change', applyBranchAreaDefaults);
                document.getElementById('province_code').addEventListener('change', async (event) => {
                    resetAreaSelect('district');
                    resetAreaSelect('village');
                    if (event.target.value) {
                        await loadAreas('regency', event.target.value);
                    } else {
                        resetAreaSelect('regency');
                    }
                });
                document.getElementById('regency_code').addEventListener('change', async (event) => {
                    resetAreaSelect('village');
                    if (event.target.value) {
                        await loadAreas('district', event.target.value);
                    } else {
                        resetAreaSelect('district');
                    }
                });
                document.getElementById('district_code').addEventListener('change', async (event) => {
                    if (event.target.value) {
                        await loadAreas('village', event.target.value);
                    } else {
                        resetAreaSelect('village');
                    }
                });
                resetAddressAreas();
            })();
        </script>
    @endpush
</x-app-layout>
