<div class="overflow-x-auto">
    <table class="w-full text-left text-sm whitespace-nowrap">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Email</th>
                <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Display Name</th>
                <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Pemakaian Storage</th>
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
                    <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                        @if($mailbox->used_quota_mb !== null)
                            <p class="font-medium">{{ number_format($mailbox->used_quota_mb, 0, ',', '.') }} MB{{ $mailbox->quota_mb > 0 ? ' / '.number_format($mailbox->quota_mb, 0, ',', '.').' MB' : '' }}</p>
                            @if($mailbox->quota_mb > 0)
                                <div class="mt-1 h-1.5 w-28 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full {{ $mailbox->used_quota_mb > $mailbox->quota_mb ? 'bg-red-500' : 'bg-blue-500' }}" style="width: {{ min(100, (int) round(($mailbox->used_quota_mb / $mailbox->quota_mb) * 100)) }}%"></div>
                                </div>
                            @endif
                        @elseif($mailbox->quota_mb > 0)
                            <span class="text-slate-500 dark:text-slate-400">- / {{ number_format($mailbox->quota_mb, 0, ',', '.') }} MB</span>
                        @else
                            <span class="text-slate-500 dark:text-slate-400">Belum tersedia</span>
                        @endif
                    </td>
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
                                    'maintenance' => 'Maintenance', 'locked' => 'Terkunci', 'closed' => 'Ditutup',
                                    'lockout' => 'Lockout', 'unknown' => 'Status tidak diketahui',
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
                        @if($mailbox->managed_by_crm && $mailHosting->mailServer?->type !== 'zimbra')
                            <div class="flex items-center justify-end gap-2">
                                @can('mailboxes.update')
                                    @if($mailbox->provisioning_status === 'ready')
                                        <form method="POST" action="{{ route($mailbox->is_active ? 'subscriptions.mailboxes.suspend' : 'subscriptions.mailboxes.activate', [$subscription, $mailbox]) }}">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-lg transition-colors {{ $mailbox->is_active ? 'text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30' : 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30' }}" title="{{ $mailbox->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i data-lucide="{{ $mailbox->is_active ? 'pause' : 'play' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('mailboxes.delete')
                                    <form method="POST" action="{{ route('subscriptions.mailboxes.destroy', [$subscription, $mailbox]) }}" data-confirm-title="Hapus Mailbox?" data-confirm-text="Mailbox akan dihapus permanen dari server mail.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" @disabled($mailbox->provisioning_status === 'deleting') class="p-2 rounded-lg text-red-600 transition-colors hover:bg-red-50 dark:hover:bg-red-900/30" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @else
                            <span class="text-xs text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">Belum ada mailbox untuk layanan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($mailboxes->hasPages())
    <div class="px-6 py-4 md:px-8 border-t border-slate-200 dark:border-slate-700">{{ $mailboxes->links() }}</div>
@endif
