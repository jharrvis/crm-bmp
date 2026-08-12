<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mailbox — {{ $subscription->client->name }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $subscription->package->name }} · {{ $mailHosting->domain }}
                            @if($mailHosting->mailServer)
                                · Server: {{ $mailHosting->mailServer->name }}
                            @endif
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('subscriptions.show', $subscription) }}" class="text-sm text-blue-600 hover:underline">← Kembali ke detail layanan</a>
                        </div>
                        @if($mailHosting->mailboxes_last_synced_at)
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                Metadata Zimbra terakhir diperbarui {{ $mailHosting->mailboxes_last_synced_at->diffForHumans() }}.
                            </p>
                        @endif
                    </div>
                    @can('mailboxes.sync')
                        @if($mailHosting->mailServer?->type === 'zimbra')
                        <form method="POST" action="{{ route('subscriptions.mailboxes.sync', $subscription) }}"
                            data-confirm-title="Sinkronkan Mailbox Zimbra?"
                            data-confirm-text="CRM hanya akan membaca akun pada domain ini dan menambahkan record lokal yang belum ada. Tidak ada akun Zimbra yang dibuat, diubah, atau dihapus.">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                                <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                                Sinkronkan dari Zimbra
                            </button>
                        </form>
                        @endif
                    @endcan
                </div>
            </div>

            @if(session('success'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($syncWarning ?? false)
                <div class="mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                    {{ $syncWarning }}
                </div>
            @endif

            @can('mailboxes.create')
                <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Tambah Mailbox</h2>
                    <form method="POST" action="{{ route('subscriptions.mailboxes.store', $subscription) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                            <input type="email" name="email" required placeholder="nama@{{ $mailHosting->domain }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Password</label>
                            <input type="password" name="password" required minlength="6" autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Quota (MB)</label>
                            <input type="number" name="quota_mb" min="1"
                                value="{{ $mailHosting->mailbox_quota_mb ?: '' }}"
                                placeholder="Contoh: 1024"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Display Name</label>
                            <input type="text" name="display_name" maxlength="255"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Tambah Mailbox
                            </button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="px-6 py-4 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-md">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Cari alamat email..."
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $mailboxes->total() }} mailbox ditemukan</p>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Email</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Display Name</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Quota</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Alias</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($mailboxes as $mailbox)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-mono text-slate-800 dark:text-slate-200">{{ $mailbox->email }}</td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">{{ $mailbox->display_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">{{ $mailbox->quota_mb }}{{ $mailbox->quota_mb > 0 ? ' MB' : '' }}</td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">{{ $mailbox->alias_count }}</td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-bold',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' => $mailbox->provisioning_status === 'ready' && $mailbox->is_active,
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' => in_array($mailbox->provisioning_status, ['pending', 'deleting']),
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' => in_array($mailbox->provisioning_status, ['failed', 'delete_failed']) || ! $mailbox->is_active,
                                    ])>
                                        {{ match($mailbox->provisioning_status) {
                                            'pending' => 'Menunggu provisioning',
                                            'deleting' => 'Menunggu penghapusan',
                                            'failed' => 'Provisioning gagal',
                                            'delete_failed' => 'Hapus gagal',
                                            default => match($mailbox->remote_status) {
                                                'maintenance' => 'Maintenance',
                                                'locked' => 'Terkunci',
                                                'closed' => 'Ditutup',
                                                'lockout' => 'Lockout',
                                                'unknown' => 'Status tidak diketahui',
                                                default => $mailbox->is_active ? 'Aktif' : 'Nonaktif',
                                            },
                                        } }}
                                    </span>
                                    @if($mailbox->provisioning_error)
                                        <p class="mt-1 max-w-48 whitespace-normal text-xs text-red-600 dark:text-red-400">{{ $mailbox->provisioning_error }}</p>
                                    @endif
                                    @if(! $mailbox->managed_by_crm)
                                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Read-only dari Zimbra</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($mailbox->managed_by_crm)
                                        @can('mailboxes.update')
                                            @if($mailbox->provisioning_status === 'ready')
                                            @if($mailbox->is_active)
                                                <form method="POST" action="{{ route('subscriptions.mailboxes.suspend', [$subscription, $mailbox]) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors"
                                                        title="Nonaktifkan">
                                                        <i data-lucide="pause" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('subscriptions.mailboxes.activate', [$subscription, $mailbox]) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 hover:bg-green-50 dark:hover:bg-green-900/30 text-green-600 rounded-lg transition-colors"
                                                        title="Aktifkan">
                                                        <i data-lucide="play" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @endif
                                        @endcan
                                        @can('mailboxes.delete')
                                            <form method="POST" action="{{ route('subscriptions.mailboxes.destroy', [$subscription, $mailbox]) }}"
                                                data-confirm-title="Hapus Mailbox?"
                                                data-confirm-text="Mailbox akan dihapus permanen dari server mail.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" @disabled($mailbox->provisioning_status === 'deleting')
                                                    class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors"
                                                    title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada mailbox untuk layanan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($mailboxes->hasPages())
                <div class="px-6 py-4 md:px-8 border-t border-slate-200 dark:border-slate-700">
                    {{ $mailboxes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
