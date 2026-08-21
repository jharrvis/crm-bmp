<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Detail Pelanggan</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="font-mono text-sm font-bold text-slate-500">{{ $client->client_code }}</span>
                    <span id="statusBadge" class="px-2 py-0.5 rounded-full text-xs font-bold 
                        @if($client->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif($client->status === 'inactive') bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400
                        @elseif($client->status === 'suspended') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
                        {{ ucfirst($client->status) }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $client)
                    <!-- Edit Mode Buttons -->
                    <button id="editBtn" onclick="enableEditMode()"
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                        Edit
                    </button>
                    <div id="editActions" class="hidden flex items-center gap-2">
                        <button onclick="saveChanges()"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Save
                        </button>
                        <button onclick="cancelEdit()"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            Cancel
                        </button>
                    </div>
                @endcan
                <a href="{{ route('clients.index') }}"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <!-- Tabs Header -->
            <div class="flex border-b border-slate-100 dark:border-slate-700">
                <button onclick="switchTab('info')" id="tab-info"
                    class="px-6 py-4 text-sm font-bold border-b-2 text-blue-600 border-blue-600 transition-colors">
                    Data Utama
                </button>
                <button onclick="switchTab('contacts')" id="tab-contacts"
                    class="px-6 py-4 text-sm font-bold border-b-2 text-slate-500 border-transparent hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    Daftar Kontak
                </button>
                <button onclick="switchTab('services')" id="tab-services"
                    class="px-6 py-4 text-sm font-bold border-b-2 text-slate-500 border-transparent hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    Layanan Pelanggan
                </button>
                @can('invoices.view')
                <button onclick="switchTab('invoices')" id="tab-invoices"
                    class="px-6 py-4 text-sm font-bold border-b-2 text-slate-500 border-transparent hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    Tagihan
                </button>
                @endcan
                @role('Owner|Admin')
                <button onclick="switchTab('portal')" id="tab-portal"
                    class="px-6 py-4 text-sm font-bold border-b-2 text-slate-500 border-transparent hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    Portal Client
                </button>
                @endrole
            </div>

            <!-- Tab Content -->
            <div class="p-6 md:p-8">
                <!-- 1. Data Utama -->
                <div id="content-info" class="space-y-6">
                    <form id="clientForm" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama
                                        Lengkap / Perusahaan <span class="text-red-500">*</span></label>
                                    <p id="view-name" class="text-lg font-semibold text-slate-800 dark:text-white">
                                        {{ $client->name }}</p>
                                    <input type="text" id="edit-name" name="name" value="{{ $client->name }}" required
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Cabang
                                        <span class="text-red-500">*</span></label>
                                    <p id="view-branch" class="text-base text-slate-700 dark:text-slate-300">
                                        {{ $client->branch->name ?? '-' }}</p>
                                    <select id="edit-branch" name="branch_id" required
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                        @foreach(\App\Models\Branch::all() as $branch)
                                            <option value="{{ $branch->id }}" {{ $client->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tipe
                                        Pelanggan</label>
                                    <p id="view-type" class="text-base text-slate-700 dark:text-slate-300">
                                        {{ $client->type_label }}
                                    </p>
                                    <select id="edit-type" name="type" required
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                        @foreach(\App\Models\Client::TYPE_OPTIONS as $value => $label)
                                            <option value="{{ $value }}" {{ $client->type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div id="customTypeEditField" class="mt-2 hidden">
                                        <input type="text" id="edit-custom-type" name="custom_type" value="{{ $client->custom_type }}" maxlength="100"
                                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                                            placeholder="Kategori pelanggan custom">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nomor
                                        Identitas</label>
                                    <p id="view-identity" class="text-base text-slate-700 dark:text-slate-300">
                                        {{ $client->identity_number ?? '-' }}</p>
                                    <input type="text" id="edit-identity" name="identity_number"
                                        value="{{ $client->identity_number }}"
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                                    <p id="view-status-text" class="text-base text-slate-700 dark:text-slate-300">
                                        {{ ucfirst($client->status) }}</p>
                                    <select id="edit-status" name="status" required
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>
                                            Non-Aktif</option>
                                        <option value="prospect" {{ $client->status === 'prospect' ? 'selected' : '' }}>
                                            Calon Pelanggan (Prospect)</option>
                                        <option value="suspended" {{ $client->status === 'suspended' ? 'selected' : '' }}>
                                            Ditangguhkan (Suspend)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Alamat
                                        Lengkap</label>
                                    <p id="view-address" class="text-base text-slate-700 dark:text-slate-300">
                                        {{ $client->address ?? '-' }}</p>
                                    <textarea id="edit-address" name="address" rows="3"
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">{{ $client->address }}</textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Provinsi</label>
                                        <p class="text-base text-slate-700 dark:text-slate-300">{{ $client->province?->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kabupaten/Kota</label>
                                        <p class="text-base text-slate-700 dark:text-slate-300">{{ $client->regency?->name ?? $client->city ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kecamatan</label>
                                        <p class="text-base text-slate-700 dark:text-slate-300">{{ $client->district?->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kelurahan/Desa</label>
                                        <p class="text-base text-slate-700 dark:text-slate-300">{{ $client->village?->name ?? '-' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">RT/RW</label>
                                    <p class="text-base text-slate-700 dark:text-slate-300">{{ $client->rt || $client->rw ? sprintf('%s / %s', $client->rt ?: '-', $client->rw ?: '-') : '-' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kota</label>
                                        <p id="view-city" class="text-base text-slate-700 dark:text-slate-300">
                                            {{ $client->city ?? '-' }}</p>
                                        <input type="text" id="edit-city" name="city" value="{{ $client->city }}"
                                            class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kode
                                            Pos</label>
                                        <p id="view-postal" class="text-base text-slate-700 dark:text-slate-300">
                                            {{ $client->postal_code ?? '-' }}</p>
                                        <input type="text" id="edit-postal" name="postal_code"
                                            value="{{ $client->postal_code }}"
                                            class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Latitude</label>
                                        <p id="view-lat" class="text-base text-slate-700 dark:text-slate-300">
                                            {{ $client->latitude ?? '-' }}</p>
                                        <input type="text" id="edit-lat" name="latitude" value="{{ $client->latitude }}"
                                            class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Longitude</label>
                                        <p id="view-lng" class="text-base text-slate-700 dark:text-slate-300">
                                            {{ $client->longitude ?? '-' }}</p>
                                        <input type="text" id="edit-lng" name="longitude"
                                            value="{{ $client->longitude }}"
                                            class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Catatan</label>
                                    <p id="view-notes" class="text-base text-slate-600 dark:text-slate-400 italic">
                                        {{ $client->notes ?? 'Tidak ada catatan khusus.' }}</p>
                                    <textarea id="edit-notes" name="notes" rows="2"
                                        class="hidden w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">{{ $client->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 2. Daftar Kontak -->
                <div id="content-contacts" class="hidden space-y-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white">Kontak Tersimpan</h3>
                        <button id="addContactBtn" onclick="openContactModal()"
                            class="hidden bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Tambah Kontak</span>
                        </button>
                    </div>

                    @if($client->contacts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($client->contacts as $contact)
                                <div
                                    class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 relative group hover:shadow-md transition-all">
                                    @if($contact->is_primary)
                                        <div class="absolute top-0 right-0">
                                            <span
                                                class="bg-blue-600 text-white text-[10px] uppercase font-bold px-2 py-1 rounded-bl-lg rounded-tr-lg">Utama</span>
                                        </div>
                                    @endif

                                    <div class="flex items-start gap-4">
                                        <div class="bg-white dark:bg-slate-800 p-2.5 rounded-full shadow-sm text-slate-500">
                                            <i data-lucide="user" class="w-6 h-6"></i>
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <h4 class="font-bold text-slate-800 dark:text-white truncate"
                                                title="{{ $contact->name }}">{{ $contact->name }}</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2 truncate">
                                                {{ $contact->position ?? 'Tidak ada jabatan' }}</p>

                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                                    <span class="truncate">{{ $contact->phone }}</span>
                                                </div>
                                                @if($contact->email)
                                                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                                        <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                                        <span class="truncate">{{ $contact->email }}</span>
                                                    </div>
                                                @endif
                                                @if($contact->whatsapp)
                                                    <div class="flex items-center gap-2 text-sm text-green-600">
                                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}"
                                                            target="_blank"
                                                            class="hover:underline truncate">{{ $contact->whatsapp }}</a>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Contact Actions (Hidden by default) -->
                                            <div
                                                class="contact-actions hidden flex items-center gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                                <button onclick="editContact({{ json_encode($contact) }})"
                                                    class="flex-1 px-3 py-1.5 bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 rounded-lg text-xs font-bold transition-colors text-center">
                                                    Edit
                                                </button>
                                                <button onclick="deleteContact({{ $contact->id }})"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="text-center py-12 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <div
                                class="bg-slate-100 dark:bg-slate-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="users" class="w-8 h-8 text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200">Belum ada kontak</h3>
                            <p class="text-slate-500 dark:text-slate-400">Tambahkan kontak melalui menu edit pelanggan.</p>
                        </div>
                    @endif
                </div>

                <!-- 3. Layanan (Subscriptions) - VIEW ONLY -->
                <div id="content-services" class="hidden space-y-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white">Layanan Aktif</h3>
                        <a href="{{ route('subscriptions.index') }}?client={{ $client->id }}"
                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            <span>Manage All Services</span>
                        </a>
                    </div>

                    <!-- Services Table -->
                    @if($client->subscriptions && $client->subscriptions->count() > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">Kode Layanan</th>
                                        <th class="p-4">Paket / Layanan</th>
                                        <th class="p-4">Domain</th>
                                        <th class="p-4">Tgl. Pasang</th>
                                        <th class="p-4">Biaya</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach($client->subscriptions as $sub)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="p-4 font-mono text-sm font-bold text-slate-600 dark:text-slate-300">
                                                {{ $sub->subscription_code }}
                                            </td>
                                            <td class="p-4">
                                                <div class="font-bold text-slate-800 dark:text-white">
                                                    {{ $sub->package->name ?? 'Unknown Package' }}</div>
                                                <div class="text-xs text-slate-500">{{ $sub->package->service->name ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                @if($sub->domain?->domain_name)
                                                    <div class="flex items-center gap-1.5">
                                                        <i data-lucide="globe" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                        <span class="font-mono text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $sub->domain->domain_name }}</span>
                                                    </div>
                                                    @if($sub->domain->expires_at)
                                                        <div class="text-[11px] text-slate-400">Exp: {{ $sub->domain->expires_at->format('d M Y') }}</div>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="p-4 text-sm text-slate-600 dark:text-slate-300">
                                                {{ $sub->installed_at ? $sub->installed_at->format('d M Y') : '-' }}
                                            </td>
                                            <td class="p-4 font-bold text-slate-700 dark:text-slate-200">
                                                Rp {{ number_format($sub->price_at_subscription, 0, ',', '.') }}
                                            </td>
                                            <td class="p-4">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($sub->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                                    @elseif($sub->status === 'suspended') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                                    @else bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400 @endif">
                                                    {{ ucfirst($sub->status) }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <a href="{{ route('subscriptions.show', $sub->id) }}"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg inline-flex items-center gap-1 text-sm font-bold transition-colors"
                                                    title="Lihat Detail Layanan">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div
                            class="text-center py-12 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <div
                                class="bg-slate-100 dark:bg-slate-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="package-search" class="w-8 h-8 text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200">Belum ada layanan aktif</h3>
                            <p class="text-slate-500 dark:text-slate-400 mb-4">Pelanggan ini belum berlangganan layanan
                                apapun.</p>
                            <a href="{{ route('subscriptions.index') }}?client={{ $client->id }}"
                                class="inline-flex items-center gap-2 text-blue-600 font-bold hover:underline">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Tambah Layanan di Halaman Subscriptions
                            </a>
                        </div>
                    @endif
                </div>

                @can('invoices.view')
                <!-- 4. Tagihan (Invoices) -->
                <div id="content-invoices" class="hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Tagihan</p>
                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $invoices->count() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lunas</p>
                            <p class="mt-2 text-2xl font-black text-green-600 dark:text-green-400">{{ $invoices->where('status', 'paid')->count() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Belum Lunas</p>
                            <p class="mt-2 text-2xl font-black text-red-600 dark:text-red-400">{{ $invoices->whereIn('status', ['unpaid', 'overdue'])->count() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Nilai Outstanding</p>
                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                                Rp {{ number_format($invoices->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    @if($invoices->count() > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">No. Invoice</th>
                                        <th class="p-4">Tanggal</th>
                                        <th class="p-4">Jatuh Tempo</th>
                                        <th class="p-4">Total</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach($invoices as $invoice)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="p-4 font-mono text-sm font-bold text-slate-600 dark:text-slate-300">
                                                {{ $invoice->invoice_number }}
                                            </td>
                                            <td class="p-4 text-sm text-slate-600 dark:text-slate-300">
                                                {{ $invoice->invoice_date?->format('d M Y') ?? '-' }}
                                            </td>
                                            <td class="p-4 text-sm text-slate-600 dark:text-slate-300">
                                                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                                            </td>
                                            <td class="p-4 font-bold text-slate-700 dark:text-slate-200">
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="p-4">
                                                @php
                                                    $statusClasses = [
                                                        'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                                        'unpaid' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                        'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                        'overdue' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                                                        'cancelled' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
                                                    ];
                                                    $labels = [
                                                        'draft' => 'Draft',
                                                        'unpaid' => 'Belum Lunas',
                                                        'paid' => 'Lunas',
                                                        'overdue' => 'Terlambat',
                                                        'cancelled' => 'Batal',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$invoice->status] ?? 'bg-slate-100 text-slate-600' }}">
                                                    {{ $labels[$invoice->status] ?? ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <a href="{{ route('invoices.show', $invoice) }}"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg inline-flex items-center gap-1 text-sm font-bold transition-colors"
                                                    title="Lihat Detail Invoice">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <div class="bg-slate-100 dark:bg-slate-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="file-text" class="w-8 h-8 text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200">Belum ada tagihan</h3>
                            <p class="text-slate-500 dark:text-slate-400">Pelanggan ini belum memiliki tagihan apapun.</p>
                        </div>
                    @endif
                </div>
                @endcan

                @role('Owner|Admin')
                @php
                    $portalAccount = $client->portalAccount;
                    $activePortalSessions = $portalAccount
                        ? $portalAccount->sessions->whereNull('revoked_at')->filter(fn ($session) => $session->expires_at?->isFuture())->count()
                        : 0;
                    $defaultPortalEmail = $portalAccount?->email ?? $client->primaryContact?->email ?? '';
                    $portalUnreadNotifications = \App\Models\ClientPortalNotification::query()
                        ->where('client_id', $client->id)
                        ->whereNull('read_at')
                        ->count();
                @endphp
                <div id="content-portal" class="hidden space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Status Akun</p>
                            <p id="portalStatusBadge" class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                @if(($portalAccount?->status) === 'active') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                @elseif(($portalAccount?->status) === 'suspended') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @elseif($portalAccount) bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                @else bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 @endif">
                                {{ $portalAccount ? strtoupper($portalAccount->status) : 'BELUM DIBUAT' }}
                            </p>
                            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Akun portal client terhubung ke data CRM dan dipakai untuk login Email OTP.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Login Terakhir</p>
                            <p id="portalLastLogin" class="mt-3 text-base font-bold text-slate-800 dark:text-white">
                                {{ $portalAccount?->last_login_at ? $portalAccount->last_login_at->format('d M Y H:i') : '-' }}
                            </p>
                            <p id="portalLastLoginIp" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                IP: {{ $portalAccount?->last_login_ip ?? '-' }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 p-5">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Sesi & Notifikasi</p>
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Sesi aktif</span>
                                <span id="portalActiveSessions" class="font-bold text-slate-800 dark:text-white">{{ $activePortalSessions }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Unread notif</span>
                                <span id="portalUnreadNotifications" class="font-bold text-slate-800 dark:text-white">{{ $portalUnreadNotifications }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pengaturan Akun Portal</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Atur email login portal client dan status aksesnya dari CRM.</p>
                            </div>
                            @if($portalAccount)
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" onclick="generatePortalOtp()"
                                        class="px-4 py-2 rounded-xl font-bold text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors">
                                        Generate OTP Manual
                                    </button>
                                    <button type="button" onclick="revokePortalSessions()"
                                        class="px-4 py-2 rounded-xl font-bold text-sm bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors">
                                        Revoke Semua Sesi
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($portalAccount)
                            <div id="portalOtpResult" class="hidden mb-6 rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-950/20 p-5">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-blue-500">OTP Manual</p>
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                            Gunakan kode berikut untuk membantu client login tanpa email. OTP tetap mengikuti masa berlaku normal.
                                        </p>
                                        <p id="portalOtpVisibilityInfo" class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                            Panel ini akan disembunyikan otomatis untuk keamanan.
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a id="portalOtpVerifyLink" href="#" target="_blank"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold text-sm bg-white text-blue-600 hover:bg-blue-100 dark:bg-slate-900 dark:text-blue-400 dark:hover:bg-slate-800 transition-colors">
                                            Buka Verifikasi Portal
                                        </a>
                                        <button type="button" id="portalOtpCopyCodeBtn" onclick="copyPortalOtpCode()"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                                            Copy OTP
                                        </button>
                                        <button type="button" id="portalOtpCopyLinkBtn" onclick="copyPortalVerifyLink()"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                            Copy Link Verifikasi
                                        </button>
                                        <button type="button" id="portalOtpCopyBundleBtn" onclick="copyPortalOtpBundle()"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold text-sm bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                                            Copy OTP + Link
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="rounded-2xl bg-white/80 dark:bg-slate-900/60 border border-blue-100 dark:border-slate-800 p-4">
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Kode OTP</p>
                                        <p id="portalOtpCode" class="mt-3 text-3xl font-black tracking-[0.3em] text-slate-900 dark:text-white">-</p>
                                    </div>
                                    <div class="rounded-2xl bg-white/80 dark:bg-slate-900/60 border border-blue-100 dark:border-slate-800 p-4">
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Email Login</p>
                                        <p id="portalOtpEmail" class="mt-3 text-sm font-bold text-slate-800 dark:text-white break-all">-</p>
                                    </div>
                                    <div class="rounded-2xl bg-white/80 dark:bg-slate-900/60 border border-blue-100 dark:border-slate-800 p-4">
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Berlaku Sampai</p>
                                        <p id="portalOtpExpiresAt" class="mt-3 text-sm font-bold text-slate-800 dark:text-white">-</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form id="portalAccountForm" class="space-y-5">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Login Portal <span class="text-red-500">*</span></label>
                                    <input type="email" id="portal_email" name="email" value="{{ $defaultPortalEmail }}"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                                        placeholder="client@example.com">
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Default diambil dari kontak utama pelanggan jika tersedia.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status Akses</label>
                                    <select id="portal_status" name="status"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="pending" {{ ($portalAccount?->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="active" {{ ($portalAccount?->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="suspended" {{ ($portalAccount?->status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan Internal</label>
                                <textarea id="portal_notes" name="notes" rows="3"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Catatan admin terkait akses portal client">{{ $portalAccount?->notes }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" id="portalSaveBtn" onclick="savePortalAccount()"
                                    class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                                    {{ $portalAccount ? 'Simpan Perubahan' : 'Buat Akun Portal' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endrole
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="contactModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300 flex flex-col"
                id="contactModalPanel">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 id="contactModalTitle" class="text-lg font-bold text-slate-800 dark:text-white">Tambah Kontak
                        Baru</h3>
                    <button onclick="closeContactModal()" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form id="contactForm" onsubmit="saveContact(event)" class="space-y-4">
                        <input type="hidden" id="contactId" name="id">

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nomor Telepon
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Jabatan</label>
                            <input type="text" name="position"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email</label>
                                <input type="email" name="email"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">WhatsApp</label>
                                <input type="text" name="whatsapp"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_primary" id="is_primary" value="1"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="is_primary" class="text-sm font-bold text-slate-700 dark:text-slate-300">Jadikan
                                Kontak Utama</label>
                        </div>

                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" onclick="closeContactModal()"
                                class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50">Batal</button>
                            <button type="submit" id="saveContactBtn"
                                class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none">Simpan
                                Kontak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const baseUrl = '{{ url('/') }}';
            const clientId = {{ $client->id }};
            let isEditMode = false;
            let originalData = {};
            const hasPortalAccount = @json((bool) $client->portalAccount);
            let portalOtpHideTimer = null;

            function syncDetailCustomType(clearValue = false) {
                const typeInput = document.getElementById('edit-type');
                const customTypeField = document.getElementById('customTypeEditField');
                const customTypeInput = document.getElementById('edit-custom-type');
                const isOther = typeInput.value === 'other';

                customTypeField.classList.toggle('hidden', !isOther);

                if (!isOther && clearValue) {
                    customTypeInput.value = '';
                }
            }

            document.getElementById('edit-type').addEventListener('change', () => syncDetailCustomType(true));

            async function switchTab(tabName) {
                // If in edit mode and switching tabs, cancel edit
                if (isEditMode && tabName !== 'info' && tabName !== 'contacts') {
                    const confirmed = await window.confirmAction('Batalkan Edit?', 'Ada perubahan yang belum disimpan. Batalkan mode edit?');
                    if (!confirmed) {
                        return;
                    }
                    cancelEdit();
                }

                ['info', 'contacts', 'services', 'invoices', 'portal'].forEach(t => {
                    const btn = document.getElementById(`tab-${t}`);
                    const content = document.getElementById(`content-${t}`);

                    if (!btn || !content) {
                        return;
                    }

                    if (t === tabName) {
                        btn.classList.add('text-blue-600', 'border-blue-600');
                        btn.classList.remove('text-slate-500', 'border-transparent');
                        content.classList.remove('hidden');
                    } else {
                        btn.classList.remove('text-blue-600', 'border-blue-600');
                        btn.classList.add('text-slate-500', 'border-transparent');
                        content.classList.add('hidden');
                    }
                });

                lucide.createIcons();
            }

            function enableEditMode() {
                isEditMode = true;

                // Save original data
                const form = document.getElementById('clientForm');
                const formData = new FormData(form);
                originalData = Object.fromEntries(formData.entries());

                // Hide Edit button, show Save/Cancel
                document.getElementById('editBtn').classList.add('hidden');
                document.getElementById('editActions').classList.remove('hidden');
                document.getElementById('editActions').classList.add('flex');

                // Toggle all view/edit elements in Data Utama
                document.querySelectorAll('[id^="view-"]').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[id^="edit-"]').forEach(el => el.classList.remove('hidden'));
                syncDetailCustomType();

                // Show Contact Actions
                document.getElementById('addContactBtn').classList.remove('hidden');
                document.querySelectorAll('.contact-actions').forEach(el => el.classList.remove('hidden'));

                lucide.createIcons();
            }

            function cancelEdit() {
                isEditMode = false;

                // Show Edit button, hide Save/Cancel
                document.getElementById('editBtn').classList.remove('hidden');
                document.getElementById('editActions').classList.add('hidden');

                // Toggle back to view mode
                document.querySelectorAll('[id^="edit-"]').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[id^="view-"]').forEach(el => el.classList.remove('hidden'));

                // Hide Contact Actions
                document.getElementById('addContactBtn').classList.add('hidden');
                document.querySelectorAll('.contact-actions').forEach(el => el.classList.add('hidden'));

                // Reset form to original values
                if (originalData) {
                    Object.keys(originalData).forEach(key => {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input) input.value = originalData[key];
                    });
                }

                syncDetailCustomType();

                lucide.createIcons();
            }

            function saveChanges() {
                const form = document.getElementById('clientForm');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // Show loading
                const saveBtn = document.querySelector('#editActions button[onclick="saveChanges()"]');
                const originalHtml = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';

                fetch(`${baseUrl}/clients/${clientId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ...data, _method: 'PUT' })
                })
                    .then(r => r.json())
                    .then(res => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalHtml;

                        if (res.success) {
                            showToast('Data pelanggan berhasil diperbarui!');
                            // Update view elements with new data
                            updateViewElements(res.client);
                            // Standard exit edit mode (we reload anyway)
                            setTimeout(() => location.reload(), 500);
                        } else {
                            let errorMsg = res.message || 'Gagal menyimpan data';
                            if (res.errors) errorMsg = Object.values(res.errors).flat().join(', ');
                            showToast(errorMsg, 'error');
                        }
                    })
                    .catch(error => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalHtml;
                        console.error(error);
                        showToast('Terjadi kesalahan!', 'error');
                    });
            }

            function updateViewElements(client) {
                // ... same as before
                document.getElementById('view-name').textContent = client.name;
                // ... (rest of updates handled by reload primarily, but good for SPA feel)
            }

            function savePortalAccount() {
                const form = document.getElementById('portalAccountForm');
                const button = document.getElementById('portalSaveBtn');
                const originalText = button.textContent;
                const data = Object.fromEntries(new FormData(form).entries());
                const url = hasPortalAccount
                    ? `${baseUrl}/clients/${clientId}/portal-account`
                    : `${baseUrl}/clients/${clientId}/portal-account`;
                const method = hasPortalAccount ? 'PUT' : 'POST';

                button.disabled = true;
                button.textContent = hasPortalAccount ? 'Menyimpan...' : 'Membuat...';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ...data, _method: method })
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) {
                            throw payload;
                        }
                        return payload;
                    })
                    .then(res => {
                        showToast(res.message || 'Akun portal berhasil disimpan.');
                        setTimeout(() => location.reload(), 400);
                    })
                    .catch(error => {
                        const message = error?.message || (error?.errors ? Object.values(error.errors).flat().join(', ') : 'Gagal menyimpan akun portal.');
                        showToast(message, 'error');
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.textContent = originalText;
                    });
            }

            async function revokePortalSessions() {
                const confirmed = await window.confirmAction('Cabut Sesi Portal?', 'Cabut semua sesi portal client yang sedang aktif?');
                if (!confirmed) return;

                fetch(`${baseUrl}/clients/${clientId}/portal-account/revoke-sessions`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) {
                            throw payload;
                        }
                        return payload;
                    })
                    .then(res => {
                        showToast(res.message || 'Sesi portal berhasil dicabut.');
                        setTimeout(() => location.reload(), 400);
                    })
                    .catch(error => {
                        const message = error?.message || 'Gagal mencabut sesi portal client.';
                        showToast(message, 'error');
                    });
            }

            async function generatePortalOtp() {
                const confirmed = await window.confirmAction('Generate OTP Manual?', 'Buat OTP manual untuk client ini? OTP sebelumnya yang belum dipakai akan kedaluwarsa.');
                if (!confirmed) return;

                fetch(`${baseUrl}/clients/${clientId}/portal-account/generate-otp`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) {
                            throw payload;
                        }
                        return payload;
                    })
                    .then(res => {
                        const otpPanel = document.getElementById('portalOtpResult');
                        document.getElementById('portalOtpCode').textContent = res.otp.code || '-';
                        document.getElementById('portalOtpEmail').textContent = res.otp.email || '-';
                        document.getElementById('portalOtpExpiresAt').textContent = res.otp.expires_at_human || '-';
                        document.getElementById('portalOtpVerifyLink').href = res.otp.verify_url || '#';
                        document.getElementById('portalOtpCopyLinkBtn').dataset.href = res.otp.verify_url || '';
                        document.getElementById('portalOtpCopyCodeBtn').dataset.code = res.otp.code || '';
                        document.getElementById('portalOtpCopyBundleBtn').dataset.code = res.otp.code || '';
                        document.getElementById('portalOtpCopyBundleBtn').dataset.href = res.otp.verify_url || '';
                        document.getElementById('portalOtpVisibilityInfo').textContent = 'Panel ini akan disembunyikan otomatis dalam 3 menit untuk keamanan.';
                        otpPanel.classList.remove('hidden');
                        schedulePortalOtpAutoHide();
                        showToast(res.message || 'OTP manual berhasil dibuat.');
                    })
                    .catch(error => {
                        const message = error?.message || (error?.errors ? Object.values(error.errors).flat().join(', ') : 'Gagal membuat OTP manual.');
                        showToast(message, 'error');
                    });
            }

            async function copyPortalVerifyLink() {
                const button = document.getElementById('portalOtpCopyLinkBtn');
                const url = button?.dataset?.href;

                if (!url) {
                    showToast('Generate OTP manual terlebih dahulu.', 'error');
                    return;
                }

                try {
                    await navigator.clipboard.writeText(url);
                    showToast('Link verifikasi berhasil disalin.');
                } catch (error) {
                    showToast('Gagal menyalin link verifikasi.', 'error');
                }
            }

            async function copyPortalOtpCode() {
                const button = document.getElementById('portalOtpCopyCodeBtn');
                const code = button?.dataset?.code;

                if (!code) {
                    showToast('Generate OTP manual terlebih dahulu.', 'error');
                    return;
                }

                try {
                    await navigator.clipboard.writeText(code);
                    showToast('Kode OTP berhasil disalin.');
                } catch (error) {
                    showToast('Gagal menyalin kode OTP.', 'error');
                }
            }

            async function copyPortalOtpBundle() {
                const button = document.getElementById('portalOtpCopyBundleBtn');
                const code = button?.dataset?.code;
                const url = button?.dataset?.href;
                const email = document.getElementById('portalOtpEmail')?.textContent?.trim();
                const expiresAt = document.getElementById('portalOtpExpiresAt')?.textContent?.trim();

                if (!code || !url) {
                    showToast('Generate OTP manual terlebih dahulu.', 'error');
                    return;
                }

                const message = [
                    'Berikut akses login Portal Client BMPnet:',
                    `Email: ${email || '-'}`,
                    `OTP: ${code}`,
                    `Berlaku sampai: ${expiresAt || '-'}`,
                    `Link verifikasi: ${url}`,
                ].join('\n');

                try {
                    await navigator.clipboard.writeText(message);
                    showToast('OTP dan link verifikasi berhasil disalin.');
                } catch (error) {
                    showToast('Gagal menyalin OTP dan link.', 'error');
                }
            }

            function schedulePortalOtpAutoHide() {
                if (portalOtpHideTimer) {
                    clearTimeout(portalOtpHideTimer);
                }

                portalOtpHideTimer = setTimeout(() => {
                    clearPortalOtpPanel();
                    showToast('Panel OTP disembunyikan otomatis untuk keamanan.');
                }, 3 * 60 * 1000);
            }

            function clearPortalOtpPanel() {
                const otpPanel = document.getElementById('portalOtpResult');
                if (!otpPanel) return;

                otpPanel.classList.add('hidden');
                document.getElementById('portalOtpCode').textContent = '-';
                document.getElementById('portalOtpEmail').textContent = '-';
                document.getElementById('portalOtpExpiresAt').textContent = '-';
                document.getElementById('portalOtpVerifyLink').href = '#';
                document.getElementById('portalOtpCopyLinkBtn').dataset.href = '';
                document.getElementById('portalOtpCopyCodeBtn').dataset.code = '';
                document.getElementById('portalOtpCopyBundleBtn').dataset.code = '';
                document.getElementById('portalOtpCopyBundleBtn').dataset.href = '';
                document.getElementById('portalOtpVisibilityInfo').textContent = 'Panel ini akan disembunyikan otomatis untuk keamanan.';
            }

            // Contact Modal Functions
            function openContactModal() {
                document.getElementById('contactForm').reset();
                document.getElementById('contactId').value = '';
                document.getElementById('contactModalTitle').textContent = 'Tambah Kontak Baru';

                showModal('contactModal');
            }

            function editContact(contact) {
                const form = document.getElementById('contactForm');
                form.id.value = contact.id;
                form.name.value = contact.name;
                form.phone.value = contact.phone;
                form.position.value = contact.position || '';
                form.email.value = contact.email || '';
                form.whatsapp.value = contact.whatsapp || '';
                form.is_primary.checked = contact.is_primary;

                document.getElementById('contactModalTitle').textContent = 'Edit Kontak';
                showModal('contactModal');
            }

            function closeContactModal() {
                hideModal('contactModal');
            }

            function saveContact(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                data.is_primary = form.is_primary.checked ? 1 : 0;

                const id = document.getElementById('contactId').value;
                const url = id
                    ? `${baseUrl}/clients/${clientId}/contacts/${id}`
                    : `${baseUrl}/clients/${clientId}/contacts`;
                const method = id ? 'PUT' : 'POST';

                const btn = document.getElementById('saveContactBtn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Menyimpan...';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ...data, _method: method })
                })
                    .then(r => r.json())
                    .then(res => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;

                        if (res.success) {
                            showToast('Kontak berhasil disimpan!');
                            closeContactModal();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast(res.message || 'Gagal menyimpan kontak', 'error');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        console.error(err);
                        showToast('Terjadi kesalahan server', 'error');
                    });
            }

            async function deleteContact(id) {
                const confirmed = await window.confirmAction('Hapus Kontak?', 'Apakah Anda yakin ingin menghapus kontak ini?');
                if (!confirmed) return;

                fetch(`${baseUrl}/clients/${clientId}/contacts/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('Kontak berhasil dihapus');
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast(res.message || 'Gagal menghapus kontak', 'error');
                        }
                    })
                    .catch(err => showToast('Terjadi kesalahan server', 'error'));
            }

            // Helper for Modals
            function showModal(id) {
                const modal = document.getElementById(id);
                const backdrop = document.getElementById(id + 'Backdrop');
                const panel = document.getElementById(id + 'Panel');

                modal.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function hideModal(id) {
                const modal = document.getElementById(id);
                const backdrop = document.getElementById(id + 'Backdrop');
                const panel = document.getElementById(id + 'Panel');

                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
        </script>
    @endpush
</x-app-layout>
