<x-app-layout>
    @php
        $remote = $overview['data'] ?? [];
        $attributes = $remote['attributes'] ?? [];
        $services = $remote['services'] ?? [];
        $serviceNames = $attributes['zimbraServiceEnabled'] ?? [];
        $portLabels = [
            'zimbraAdminPort' => 'Admin SOAP', 'zimbraMailPort' => 'Webmail HTTP', 'zimbraMailSSLPort' => 'Webmail HTTPS',
            'zimbraImapBindPort' => 'IMAP', 'zimbraImapSSLBindPort' => 'IMAPS', 'zimbraPop3BindPort' => 'POP3',
            'zimbraPop3SSLBindPort' => 'POP3S', 'zimbraLmtpBindPort' => 'LMTP',
        ];
        $availablePorts = collect($portLabels)->filter(fn ($label, $key) => filled($attributes[$key] ?? null));
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('servers.index', ['category' => 'mail']) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300"><i data-lucide="arrow-left" class="h-4 w-4"></i>Kembali ke Server Mail</a>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $server->name }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $server->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $server->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detail konfigurasi lokal dan metadata read-only dari Zimbra Admin SOAP API.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('servers.show', ['server' => $server, 'refresh' => 1]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><i data-lucide="refresh-cw" class="h-4 w-4"></i>Refresh Data</a>
                @can('servers.update')
                    <a href="{{ route('servers.index', ['category' => 'mail', 'edit' => $server->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700"><i data-lucide="pencil" class="h-4 w-4"></i>Edit Server</a>
                @endcan
            </div>
        </div>

        @if(! ($overview['success'] ?? false) || filled($overview['message'] ?? null))
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">{{ $overview['message'] ?? 'Data remote tidak dapat dimuat. Menampilkan konfigurasi lokal.' }}</div>
        @endif

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2 dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700"><h2 class="font-bold text-slate-800 dark:text-white">Informasi Server</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Konfigurasi yang tersimpan di CRM dan atribut server Zimbra yang aman dibaca.</p></div>
                <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Engine</dt><dd class="mt-1 font-semibold uppercase text-slate-800 dark:text-slate-100">Zimbra</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Versi Zimbra</dt><dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $remote['version'] ?: 'Tidak tersedia dari API' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Hostname</dt><dd class="mt-1 break-all font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $remote['server_name'] ?? $server->host }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">ID Zimbra</dt><dd class="mt-1 break-all font-mono text-sm text-slate-800 dark:text-slate-100">{{ $remote['server_id'] ?: 'Tidak tersedia dari API' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Endpoint Admin SOAP</dt><dd class="mt-1 break-all font-mono text-sm text-slate-800 dark:text-slate-100">https://{{ $server->host }}:{{ $server->port }}{{ $server->api_endpoint ?: '/service/admin/soap' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Lokasi</dt><dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $server->location ?: 'Tidak dicatat' }}</dd></div>
                    @can('servers.manage')
                        <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Admin Account</dt><dd class="mt-1 break-all font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $server->username ?: $server->api_key ?: 'Tidak dicatat' }}</dd></div>
                    @endcan
                    <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Deskripsi</dt><dd class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $server->description ?: 'Tidak ada deskripsi.' }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700"><h2 class="font-bold text-slate-800 dark:text-white">Status API</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Data remote di-cache hingga 5 menit.</p></div>
                <div class="space-y-5 p-6">
                    <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Koneksi Metadata</p><p class="mt-1 font-semibold {{ ($overview['success'] ?? false) ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">{{ ($overview['success'] ?? false) ? 'Terhubung' : 'Tidak tersedia' }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Service Terlapor</p><p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ count($services) ?: 'Tidak tersedia' }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Service Diaktifkan</p><p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ count($serviceNames) ?: 'Tidak tersedia' }}</p></div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700"><h2 class="font-bold text-slate-800 dark:text-white">Status Service Zimbra</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dibaca dari `GetServiceStatusRequest` tanpa mengubah konfigurasi server.</p></div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($services as $service)
                        <div class="flex items-center justify-between gap-4 px-6 py-4"><span class="font-mono text-sm text-slate-800 dark:text-slate-200">{{ $service['name'] }}</span><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $service['running'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">{{ $service['running'] ? 'Running' : 'Stopped' }}</span></div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Status service belum tersedia untuk hostname ini.</div>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700"><h2 class="font-bold text-slate-800 dark:text-white">Port Service</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Atribut konfigurasi dari `GetServerRequest`.</p></div>
                <dl class="grid grid-cols-1 divide-y divide-slate-100 dark:divide-slate-700 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    @forelse($availablePorts as $key => $label)
                        <div class="px-6 py-4"><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</dt><dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $attributes[$key] }}</dd></div>
                    @empty
                        <div class="col-span-2 px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Port service tidak tersedia dari API.</div>
                    @endforelse
                </dl>
            </section>
        </div>

        @if($serviceNames !== [])
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"><div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700"><h2 class="font-bold text-slate-800 dark:text-white">Service Yang Diaktifkan</h2></div><div class="flex flex-wrap gap-2 p-6">@foreach($serviceNames as $name)<span class="rounded-full bg-blue-50 px-3 py-1.5 font-mono text-xs font-semibold text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{{ $name }}</span>@endforeach</div></section>
        @endif
    </div>
</x-app-layout>
