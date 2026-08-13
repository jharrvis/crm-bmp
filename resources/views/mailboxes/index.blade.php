<x-app-layout>
    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-200 px-6 py-6 md:px-8 dark:border-slate-700">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mailbox - {{ $subscription->client->name }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $subscription->package->name }} · {{ $mailHosting->domain }}
                            @if($mailHosting->mailServer) · Server: {{ $mailHosting->mailServer->name }} @endif
                        </p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Pemakaian akun: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $mailboxUsageCount }} / {{ $mailHosting->max_mailboxes > 0 ? $mailHosting->max_mailboxes : 'Tidak dibatasi' }}</span>
                            @if($mailHosting->mailServer?->type === 'zimbra') · disinkronkan dari Zimbra saat halaman dibuka. @endif
                        </p>
                        <a href="{{ route('subscriptions.show', $subscription) }}" class="mt-2 inline-block text-sm text-blue-600 hover:underline">← Kembali ke detail layanan</a>
                    </div>
                    @can('mailboxes.sync')
                        @if($mailHosting->mailServer?->type === 'zimbra')
                            <form method="POST" action="{{ route('subscriptions.mailboxes.sync', $subscription) }}" data-confirm-title="Sinkronkan Mailbox Zimbra?" data-confirm-text="CRM hanya membaca metadata akun pada domain ini. Tidak ada akun Zimbra yang dibuat, diubah, atau dihapus.">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                                    <i data-lucide="refresh-cw" class="h-4 w-4"></i> Sinkronkan ulang
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            @foreach(['success' => 'green', 'error' => 'red'] as $messageType => $color)
                @if(session($messageType))
                    <div class="mx-6 mt-4 rounded-xl border p-4 {{ $color === 'green' ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300' }}">{{ session($messageType) }}</div>
                @endif
            @endforeach
            @if($errors->any())
                <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"><ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if($syncWarning ?? false)
                <div class="mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">{{ $syncWarning }}</div>
            @endif

            @can('mailboxes.create')
                @if($mailHosting->mailServer?->type !== 'zimbra')
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-6 md:px-8 dark:border-slate-700 dark:bg-slate-800/50">
                        <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-500">Tambah Mailbox</h2>
                        <form method="POST" action="{{ route('subscriptions.mailboxes.store', $subscription) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            @csrf
                            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-300">Email</label><input type="email" name="email" required placeholder="nama@{{ $mailHosting->domain }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></div>
                            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-300">Password</label><input type="password" name="password" required minlength="6" autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></div>
                            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-300">Quota (MB)</label><input type="number" name="quota_mb" min="1" value="{{ $mailHosting->mailbox_quota_mb ?: '' }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></div>
                            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-300">Display Name</label><input type="text" name="display_name" maxlength="255" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></div>
                            <div class="flex justify-end md:col-span-2 xl:col-span-4"><button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 font-bold text-white transition-all hover:bg-blue-700"><i data-lucide="plus" class="h-4 w-4"></i>Tambah Mailbox</button></div>
                        </form>
                    </div>
                @else
                    <div class="mx-6 mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">Mailbox Zimbra dikelola langsung dari panel Zimbra. CRM hanya membaca dan menyinkronkan data agar tidak ada risiko perubahan tidak sengaja.</div>
                @endif
            @endcan

            <div class="border-b border-slate-200 px-6 py-4 md:px-8 dark:border-slate-700">
                <form id="mailbox-search-form" method="GET" action="{{ route('subscriptions.mailboxes.index', $subscription) }}" class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:max-w-md"><i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input id="mailbox-search" type="search" name="search" value="{{ $search }}" placeholder="Cari alamat email..." autocomplete="off" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></div>
                    <p id="mailbox-result-count" class="text-sm text-slate-500 dark:text-slate-400">{{ $mailboxes->total() }} mailbox ditemukan</p>
                </form>
            </div>

            <div id="mailbox-results">@include('mailboxes._table')</div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('mailbox-search-form');
                const input = document.getElementById('mailbox-search');
                const results = document.getElementById('mailbox-results');
                const count = document.getElementById('mailbox-result-count');
                let timer;

                const search = async () => {
                    const url = new URL(form.action, window.location.origin);
                    if (input.value.trim()) url.searchParams.set('search', input.value.trim());
                    try {
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (!response.ok) throw new Error('Search failed');
                        const payload = await response.json();
                        results.innerHTML = payload.html;
                        count.textContent = `${payload.total} mailbox ditemukan`;
                        window.history.replaceState({}, '', url);
                        window.lucide?.createIcons();
                    } catch (_) {
                        form.submit();
                    }
                };

                form.addEventListener('submit', (event) => { event.preventDefault(); search(); });
                input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(search, 300); });
            });
        </script>
    @endpush
</x-app-layout>
