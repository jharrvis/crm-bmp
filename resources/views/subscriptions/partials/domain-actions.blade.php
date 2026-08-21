{{-- Fase 2: Operasi Domain Terkontrol (nameserver, EPP, DNS managed SRS-X) --}}
@php
    $d = $subscription->domain;
    $ops = $domainOps ?? [];
    $mode = $ops['mode'] ?? 'disabled';
    $records = $d->dns_records ?? [];
    $recentOps = $d->registrarOperations ?? collect();
    $dnsData = session('dns_data') ?? null;
@endphp

<div class="mt-6 border-t pt-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h4 class="font-bold flex items-center gap-2">
            <i data-lucide="settings-2" class="w-4 h-4"></i>
            Operasi Domain (Registrar)
        </h4>
        @if($mode !== 'managed')
            <span class="px-2 py-1 rounded text-xs bg-amber-100 text-amber-700">Mode {{ $mode }} — operasi mutasi (nameserver/EPP/DNS) hanya aktif di mode managed</span>
        @endif
    </div>

    @if($d->managed_dns_enabled)
        <div class="px-3 py-2 rounded bg-blue-50 text-blue-700 text-xs">Managed DNS SRS-X aktif untuk {{ $d->domain_name }}. DNS hanya dikelola via CRM jika diaktifkan eksplisit.</div>
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Nameserver --}}
        @if($ops['can_update_nameservers'] ?? false)
        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
            <p class="text-sm font-bold mb-2">Update Nameserver</p>
            <form method="POST" action="{{ route('domain-operations.nameservers', [$subscription, $d]) }}">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input name="nameserver_1" value="{{ old('nameserver_1') }}" placeholder="ns1.example.com" class="col-span-2 border rounded-lg p-2 text-sm" required>
                    <input name="nameserver_2" value="{{ old('nameserver_2') }}" placeholder="ns2.example.com" class="col-span-2 border rounded-lg p-2 text-sm" required>
                    <input name="nameserver_3" value="{{ old('nameserver_3') }}" placeholder="ns3 (opsional)" class="col-span-2 border rounded-lg p-2 text-sm">
                    <input name="nameserver_4" value="{{ old('nameserver_4') }}" placeholder="ns4 (opsional)" class="col-span-2 border rounded-lg p-2 text-sm">
                </div>
                <input name="confirm_domain" type="text" placeholder="Ketik ulang: {{ $d->domain_name }}" class="mt-2 w-full border rounded-lg p-2 text-sm" required>
                <button class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Simpan Nameserver</button>
            </form>
        </div>
        @endif

        {{-- EPP --}}
        @if(($ops['can_view_epp'] ?? false) || ($ops['can_set_epp'] ?? false))
        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
            <p class="text-sm font-bold mb-2">EPP Code (Auth Code)</p>
            @if($ops['can_view_epp'] ?? false)
            <div class="flex items-center gap-3 mb-3">
                <code class="text-xs bg-white border rounded px-2 py-1">{{ $d->auth_code ? '•••••••••• (tersimpan, ' . strlen($d->auth_code) . ' karakter)' : 'Belum ada' }}</code>
                <form method="POST" action="{{ route('domain-operations.epp.fetch', [$subscription, $d]) }}">
                    @csrf
                    <button class="px-3 py-1.5 bg-slate-700 text-white rounded-lg text-xs">Ambil dari SRS-X</button>
                </form>
            </div>
            @endif
            @if($ops['can_set_epp'] ?? false)
            <form method="POST" action="{{ route('domain-operations.epp.set', [$subscription, $d]) }}">
                @csrf
                <input name="epp_code" type="text" placeholder="EPP baru (4-16 karakter)" class="w-full border rounded-lg p-2 text-sm" required>
                <input name="confirm_domain" type="text" placeholder="Ketik ulang: {{ $d->domain_name }}" class="mt-2 w-full border rounded-lg p-2 text-sm" required>
                <button class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Ganti EPP</button>
            </form>
            @endif
            <p class="mt-2 text-xs text-slate-400">EPP tersimpan terenkripsi dan tidak pernah ditulis ke log.</p>
        </div>
        @endif
    </div>

    {{-- DNS Managed --}}
    @if(($ops['can_toggle_dns'] ?? false) || ($ops['can_get_dns'] ?? false) || ($ops['can_edit_dns'] ?? false))
    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-bold">DNS Managed SRS-X</p>
            <div class="flex items-center gap-2">
                @if($ops['can_toggle_dns'] ?? false)
                <form method="POST" action="{{ route('domain-operations.dns.toggle', [$subscription, $d]) }}">
                    @csrf
                    <input type="hidden" name="enabled" value="{{ $d->managed_dns_enabled ? '0' : '1' }}">
                    @if(!$d->managed_dns_enabled)
                    <input name="confirm_domain" type="text" placeholder="Ketik ulang: {{ $d->domain_name }}" class="border rounded-lg p-1.5 text-xs" required>
                    @endif
                    <button class="px-3 py-1.5 rounded-lg text-xs {{ $d->managed_dns_enabled ? 'bg-red-600 text-white' : 'bg-green-600 text-white' }}">
                        {{ $d->managed_dns_enabled ? 'Nonaktifkan' : 'Aktifkan' }} Managed DNS
                    </button>
                </form>
                @endif
                @if(($ops['can_get_dns'] ?? false) && $d->managed_dns_enabled)
                <form method="POST" action="{{ route('domain-operations.dns.info', [$subscription, $d]) }}">
                    @csrf
                    <button class="px-3 py-1.5 bg-slate-700 text-white rounded-lg text-xs">Sync DNS Info</button>
                </form>
                @endif
            </div>
        </div>

        @if(($ops['can_get_dns'] ?? false) && $d->managed_dns_enabled)
        <div class="mt-3 text-xs text-slate-400">{{ count($records) }} record tersimpan lokal.</div>
        <div class="mt-2 overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="text-slate-400 uppercase text-[10px]">
                    <tr><th class="text-left p-1">Type</th><th class="text-left p-1">Record</th><th class="text-left p-1">Destination</th><th class="text-left p-1">TTL</th><th class="text-left p-1"></th></tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                    <tr class="border-t border-slate-200 dark:border-slate-600">
                        <td class="p-1">{{ $r['type'] ?? '-' }}</td>
                        <td class="p-1 font-mono">{{ $r['record'] ?? '-' }}</td>
                        <td class="p-1 font-mono">{{ $r['destination'] ?? '-' }}</td>
                        <td class="p-1">{{ $r['ttl'] ?? '-' }}</td>
                        <td class="p-1 text-right">
                            @if(($ops['can_edit_dns'] ?? false) && in_array(strtoupper($r['type'] ?? ''), ['A','CNAME','MX','TXT','SRV','NS'], true))
                            <details class="text-left">
                                <summary class="text-blue-600 cursor-pointer">Edit</summary>
                                <form method="POST" action="{{ route('domain-operations.dns.edit', [$subscription, $d]) }}" class="mt-2 space-y-1">
                                    @csrf
                                    <input type="hidden" name="dnsid" value="{{ $r['dnsid'] ?? '' }}">
                                    <input type="hidden" name="type" value="{{ $r['type'] ?? '' }}">
                                    <input name="record" value="{{ $r['record'] ?? '' }}" class="w-full border rounded p-1.5 text-xs" required>
                                    <input name="destination" value="{{ $r['destination'] ?? '' }}" placeholder="destination" class="w-full border rounded p-1.5 text-xs">
                                    <div class="flex gap-1">
                                        <input name="ttl" value="{{ $r['ttl'] ?? '' }}" type="number" min="60" placeholder="TTL" class="w-1/2 border rounded p-1.5 text-xs">
                                        @if(strtoupper($r['type'] ?? '') === 'MX' || strtoupper($r['type'] ?? '') === 'SRV')
                                        <input name="priority" value="{{ $r['priority'] ?? '' }}" type="number" placeholder="priority" class="w-1/2 border rounded p-1.5 text-xs">
                                        @endif
                                    </div>
                                    <input name="confirm_domain" type="text" placeholder="Ketik ulang: {{ $d->domain_name }}" class="w-full border rounded p-1.5 text-xs" required>
                                    <button class="px-3 py-1 bg-blue-600 text-white rounded text-xs">Simpan Record</button>
                                </form>
                            </details>
                            @elseif(in_array(strtoupper($r['type'] ?? ''), ['SOA','MX','SRV'], true))
                            <span class="text-slate-400">lihat di panel SRS-X</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-2 text-slate-400">Belum ada record — klik Sync DNS Info.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @elseif(!$d->managed_dns_enabled && ($ops['can_toggle_dns'] ?? false))
        <p class="mt-3 text-xs text-slate-400">Aktifkan Managed DNS untuk melihat & mengelola record DNS via SRS-X.</p>
        @endif
    </div>
    @endif

    {{-- Riwayat operasi + retry --}}
    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
        <p class="text-sm font-bold mb-2">Riwayat Operasi Registrar</p>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="text-slate-400 uppercase text-[10px]">
                    <tr><th class="text-left p-1">#</th><th class="text-left p-1">Tipe</th><th class="text-left p-1">Status</th><th class="text-left p-1">Waktu</th><th class="text-left p-1">Error</th><th class="text-left p-1"></th></tr>
                </thead>
                <tbody>
                    @forelse($recentOps->take(15) as $o)
                    <tr class="border-t border-slate-200 dark:border-slate-600">
                        <td class="p-1">{{ $o->id }}</td>
                        <td class="p-1 font-mono">{{ $o->operation_type }}</td>
                        <td class="p-1"><span class="px-1.5 py-0.5 rounded text-[10px] {{ in_array($o->status, ['failed']) ? 'bg-red-100 text-red-700' : (in_array($o->status, ['completed','synced']) ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700') }}">{{ $o->status }}</span></td>
                        <td class="p-1">{{ $o->updated_at?->format('d M H:i') }}</td>
                        <td class="p-1 max-w-[220px] truncate text-red-600">{{ $o->error_summary ?? '-' }}</td>
                        <td class="p-1 text-right">
                            @if($o->status === 'failed'
                                && (($o->operation_type === 'update_nameservers' && ($ops['can_update_nameservers'] ?? false))
                                    || ($o->operation_type === 'set_epp' && ($ops['can_set_epp'] ?? false))
                                    || ($o->operation_type === 'manage_dns' && ($ops['can_edit_dns'] ?? false))))
                            <details class="text-left">
                                <summary class="text-blue-600 cursor-pointer">Retry</summary>
                                <form method="POST" action="{{ route('domain-operations.operations.retry', [$subscription, $d, $o]) }}" class="mt-1 space-y-1">
                                    @csrf
                                    <input name="confirm_domain" type="text" placeholder="Ketik ulang: {{ $d->domain_name }}" class="w-full border rounded p-1.5 text-xs" required>
                                    <button class="px-3 py-1 bg-blue-600 text-white rounded text-xs">Retry #{{ $o->id }}</button>
                                </form>
                            </details>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-2 text-slate-400">Belum ada operasi untuk domain ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>