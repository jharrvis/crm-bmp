<x-app-layout>
    <div class="space-y-6 max-w-3xl mx-auto">
        <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Pusat Notifikasi</a>
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs font-mono px-2 py-1 bg-slate-100 rounded">{{ $notification->type }}</span>
                        @if($notification->category)<span class="text-xs px-2 py-1 bg-slate-800 text-white rounded">{{ $notification->category }}</span>@endif
                        @if($notification->severity)
                            <span class="text-xs px-2 py-1 rounded {{ match($notification->severity){'critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','warning'=>'bg-amber-100 text-amber-700', default=>'bg-slate-100'} }}">{{ $notification->severity }}</span>
                        @endif
                        @if($notification->action_required)<span class="text-xs px-2 py-1 bg-orange-600 text-white rounded">Perlu Tindakan</span>@endif
                    </div>
                    <h3 class="text-xl font-bold mt-2">{{ $notification->title }}</h3>
                    <p class="text-sm text-slate-400">{{ $notification->created_at->format('d M Y H:i') }} — {{ $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca' }} @if($notification->resolved_at) • <span class="text-emerald-600">Selesai {{ $notification->resolved_at->diffForHumans() }}</span>@endif @if($notification->snoozed_until && $notification->snoozed_until->isFuture()) • Ditunda sampai {{ $notification->snoozed_until->format('d M Y H:i') }}@endif</p>
                    @if($notification->source_type)<p class="text-xs font-mono text-slate-400 mt-1">source: {{ $notification->source_type }} #{{ $notification->source_id }} • dedupe: {{ Str::limit($notification->dedupe_key ?? '-', 16) }}</p>@endif
                </div>
                <div class="flex flex-col gap-2">
                    @unless($notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf<button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">Tandai dibaca</button></form>
                    @endunless
                    @if(!$notification->resolved_at && $notification->action_required)
                    <form method="POST" action="{{ route('notifications.resolve', $notification) }}">@csrf<button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Tandai selesai</button></form>
                    @endif
                    <form method="POST" action="{{ route('notifications.dismiss', $notification) }}">@csrf<button class="px-3 py-1.5 border rounded text-sm text-red-600">Hapus</button></form>
                </div>
            </div>
            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-sm">
                {{ $notification->message }}
            </div>
            @if($notification->payload)
            <div class="mt-4">
                <h4 class="text-sm font-bold mb-2">Detail (redacted)</h4>
                <div class="text-xs bg-slate-50 dark:bg-slate-700/50 rounded p-4 space-y-1">
                    @php $payload = $notification->payload; @endphp
                    @foreach(['domain_name','expires_at','days_left','client_code','subscription_code','error_summary','payload'] as $k)
                        @if(isset($payload[$k]) && $payload[$k] !== null && $payload[$k] !== '')
                            <div><span class="font-mono text-slate-400">{{ $k }}:</span> {{ is_array($payload[$k]) ? json_encode($payload[$k], JSON_UNESCAPED_UNICODE) : e($payload[$k]) }}</div>
                        @endif
                    @endforeach
                    @if(isset($payload['subscription_id']))<div><span class="font-mono text-slate-400">subscription_id:</span> {{ $payload['subscription_id'] }}</div>@endif
                    @if(isset($payload['registrar_account_id']))<div><span class="font-mono text-slate-400">registrar_account_id:</span> {{ $payload['registrar_account_id'] }}</div>@endif
                </div>
                <p class="text-xs text-slate-400 mt-2">Payload sensitif (auth_code, provider_metadata, identity_number) tidak ditampilkan.</p>
            </div>
            @endif
            @if($resolvedAction)
            <div class="mt-4">
                <a href="{{ $resolvedAction['url'] }}" class="inline-flex px-4 py-2 bg-blue-600 text-white rounded text-sm font-semibold">{{ $resolvedAction['label'] }}</a>
            </div>
            @elseif(($notification->payload['subscription_id'] ?? null) && auth()->user()->can('subscriptions.view'))
            <div class="mt-4 flex gap-2">
                <a href="{{ route('subscriptions.show', $notification->payload['subscription_id']) }}" class="px-4 py-2 bg-slate-800 text-white rounded text-sm">Lihat Layanan</a>
                @if($notification->payload['registrar_account_id'] ?? null)
                @can('registrar_accounts.view')
                <a href="{{ route('registrar-accounts.show', $notification->payload['registrar_account_id']) }}" class="px-4 py-2 border rounded text-sm">Lihat Akun Registrar</a>
                @endcan
                @endif
            </div>
            @endif
        </div>
    </div>
</x-app-layout>