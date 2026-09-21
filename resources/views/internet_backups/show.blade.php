<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('internet-backups.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Internet Backup
                </a>
                <h1 class="mt-3 text-2xl font-bold text-slate-800 dark:text-white">{{ $internetBackup->name }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detail koneksi internet backup dari provider.</p>
            </div>
            <div class="flex gap-2">
                @can('internet_backups.update')
                <button onclick="window.editData({{ $internetBackup->id }})"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-yellow-200 hover:bg-yellow-50 hover:text-yellow-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-yellow-500/40 dark:hover:bg-yellow-900/20 dark:hover:text-yellow-300">
                    <i data-lucide="pencil" class="h-4 w-4"></i>
                    Edit
                </button>
                @endcan
                <a href="{{ route('internet-backups.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-blue-500/40 dark:hover:bg-blue-900/20 dark:hover:text-blue-300">
                    <i data-lucide="settings-2" class="h-4 w-4"></i>
                    Kelola Internet Backup
                </a>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                        <i data-lucide="shield" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-white">Informasi Koneksi</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Data koneksi backup dari provider.</p>
                    </div>
                </div>
            </div>

            <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Nama Koneksi</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $internetBackup->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Vendor</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $internetBackup->vendor?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Status</dt>
                    <dd class="mt-1">
                        @php
                            $statusColors = [
                                'planned' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'active' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'suspended' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                'terminated' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            ];
                            $cls = $statusColors[$internetBackup->status] ?? 'bg-slate-50 text-slate-700';
                        @endphp
                        <span class="px-2 py-1 rounded font-bold text-xs {{ $cls }}">{{ $internetBackup->status_label }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Subscription</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                        @if($internetBackup->subscription)
                            <span class="font-mono">{{ $internetBackup->subscription->subscription_code }}</span>
                            <span class="text-slate-500 dark:text-slate-400 text-sm"> — {{ $internetBackup->subscription->client?->name ?? '-' }}</span>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Circuit ID</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $internetBackup->circuit_id ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Bandwidth</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ number_format($internetBackup->bandwidth_mbps) }} Mbps</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">IP Address</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $internetBackup->ip_address ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Gateway</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $internetBackup->gateway ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Biaya Bulanan</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                        {{ $internetBackup->monthly_cost !== null ? 'Rp '.number_format((float) $internetBackup->monthly_cost, 0, ',', '.') : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Tanggal Aktif</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                        {{ $internetBackup->active_date?->translatedFormat('d M Y') ?? '-' }}
                    </dd>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Alamat / Lokasi</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $internetBackup->address ?: '-' }}</dd>
                </div>
                @if($internetBackup->notes)
                <div class="sm:col-span-2 lg:col-span-3">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Catatan</dt>
                    <dd class="mt-1 text-slate-700 dark:text-slate-300">{{ $internetBackup->notes }}</dd>
                </div>
                @endif
            </dl>
        </section>
    </div>

    <!-- Edit Modal -->
    <div id="formModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="formModalBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[calc(100vh-2rem)] transform scale-95 opacity-0 transition-all duration-300 flex flex-col"
                id="formModalPanel">

                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Edit Data</h3>
                    <button onclick="window.closeModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <form id="dataForm" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf
                        <input type="hidden" id="dataId" name="id">

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Koneksi <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Vendor <span class="text-red-500">*</span></label>
                            <select id="vendor_id" name="vendor_id" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subscription</label>
                            <select id="subscription_id" name="subscription_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                <option value="">Pilih Subscription</option>
                                @foreach($subscriptions as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->subscription_code }} — {{ $sub->client?->name ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status <span class="text-red-500">*</span></label>
                            <select id="status" name="status" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Circuit ID</label>
                            <input type="text" id="circuit_id" name="circuit_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Bandwidth (Mbps) <span class="text-red-500">*</span></label>
                            <input type="number" id="bandwidth_mbps" name="bandwidth_mbps" required min="1"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">IP Address</label>
                            <input type="text" id="ip_address" name="ip_address"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gateway</label>
                            <input type="text" id="gateway" name="gateway"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Biaya Bulanan (Rp)</label>
                            <input type="number" id="monthly_cost" name="monthly_cost" min="0" step="0.01"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal Aktif</label>
                            <input type="date" id="active_date" name="active_date"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat / Lokasi</label>
                            <input type="text" id="address" name="address"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400"></textarea>
                        </div>
                    </form>
                </div>

                <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="window.closeModal()"
                        class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                    <button type="button" onclick="submitForm()" id="submitBtn"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                        <svg id="submitSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="submitText">Update Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-confirm-modal />

    @push('scripts')
        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                const backupData = @json($internetBackup->toArray());

                window.openModal = function () {
                    const modal = document.getElementById('formModal');
                    const backdrop = document.getElementById('formModalBackdrop');
                    const panel = document.getElementById('formModalPanel');

                    document.getElementById('dataId').value = backupData.id;
                    document.getElementById('vendor_id').value = backupData.vendor_id || '';
                    document.getElementById('subscription_id').value = backupData.subscription_id || '';
                    document.getElementById('name').value = backupData.name || '';
                    document.getElementById('circuit_id').value = backupData.circuit_id || '';
                    document.getElementById('ip_address').value = backupData.ip_address || '';
                    document.getElementById('gateway').value = backupData.gateway || '';
                    document.getElementById('bandwidth_mbps').value = backupData.bandwidth_mbps || '';
                    document.getElementById('monthly_cost').value = backupData.monthly_cost || '';
                    document.getElementById('active_date').value = backupData.active_date ? backupData.active_date.split('T')[0] : '';
                    document.getElementById('address').value = backupData.address || '';
                    document.getElementById('status').value = backupData.status || 'planned';
                    document.getElementById('notes').value = backupData.notes || '';

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        panel.classList.remove('scale-95', 'opacity-0');
                        panel.classList.add('scale-100', 'opacity-100');
                    }, 10);
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

                window.submitForm = function () {
                    document.getElementById('dataForm').dispatchEvent(new Event('submit'));
                }

                document.getElementById('dataForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const id = document.getElementById('dataId').value;
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');

                    setButtonLoading(btn, spinner, text, true, 'Update Data');

                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');

                    const object = {};
                    formData.forEach((value, key) => object[key] = value);
                    object._token = document.querySelector('input[name="_token"]').value;

                    fetch(`${baseUrl}/internet-backups/${id}`, {
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
                            setButtonLoading(btn, spinner, text, false, 'Update Data');
                            if (res.success) {
                                showToast(res.message);
                                setTimeout(() => location.reload(), 500);
                            } else {
                                let errorMsg = res.message || 'Gagal menyimpan data';
                                if (res.errors) errorMsg = Object.values(res.errors).flat().join(', ');
                                showToast(errorMsg, 'error');
                            }
                        })
                        .catch(error => {
                            setButtonLoading(btn, spinner, text, false, 'Update Data');
                            console.error(error);
                            showToast('Terjadi kesalahan!', 'error');
                        });
                });

                window.editData = window.openModal;

                let deleteId = null;
                window.deleteData = function (id) {
                    deleteId = id;
                    showConfirmModal('Hapus Data?', 'Data tidak dapat dikembalikan.', () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/internet-backups/${deleteId}`, {
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
                                    window.location.href = `${baseUrl}/internet-backups`;
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
            })();
        </script>
    @endpush
</x-app-layout>
