<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold">Pusat Notifikasi</h3>
                    <p class="text-sm text-slate-500">Domain expiry, SSL, sync gagal, konflik, invoice, tiket, dan system update — informatif & actionable.</p>
                </div>
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Tandai semua dibaca</button></form>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('notifications.index') }}" class="px-3 py-1.5 rounded-lg text-sm {{ !request('filter') && !request('category') && !request('severity') ? 'bg-blue-600 text-white' : 'bg-slate-100' }}">Semua</a>
                <a href="{{ route('notifications.index', ['filter'=>'unread']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('filter')==='unread' ? 'bg-blue-600 text-white' : 'bg-slate-100' }}">Belum dibaca</a>
                <a href="{{ route('notifications.index', ['filter'=>'action_required']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('filter')==='action_required' ? 'bg-orange-600 text-white' : 'bg-slate-100' }}">Perlu Tindakan</a>
                <a href="{{ route('notifications.index', ['filter'=>'unresolved']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('filter')==='unresolved' ? 'bg-blue-600 text-white' : 'bg-slate-100' }}">Belum selesai</a>
                @foreach(['domain','billing','ticket','infrastructure','system','approval'] as $cat)
                    <a href="{{ route('notifications.index', ['category'=>$cat]) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('category')===$cat ? 'bg-slate-800 text-white' : 'bg-slate-100' }}">{{ ucfirst($cat) }}</a>
                @endforeach
            </div>
            @if(request('severity'))
                <div class="text-xs text-slate-500 mb-2">Filter severity: <span class="font-bold">{{ request('severity') }}</span> <a href="{{ route('notifications.index') }}" class="underline">hapus</a></div>
            @endif

            <div class="space-y-3">
                @forelse($notifications as $n)
                @php
                    $severityClass = match($n->severity) {
                        'critical' => 'bg-red-100 text-red-700',
                        'high' => 'bg-orange-100 text-orange-700',
                        'warning' => 'bg-amber-100 text-amber-700',
                        default => 'bg-slate-100 text-slate-600',
                    };
                    $payload = $n->payload ?? [];
                    $safeKeys = ['domain_name','expires_at','days_left','client_code','subscription_code','subscription_id','subscription_domain_id','registrar_account_id','invoice_id','ticket_id','error_summary','payload'];
                    // Redaksi: hanya tampilkan safe keys, escape
                @endphp
                <div class="p-4 rounded-xl border {{ $n->read_at ? 'bg-white border-slate-200' : 'bg-blue-50 border-blue-200' }} {{ $n->resolved_at ? 'opacity-60' : '' }}">
                    <div class="flex justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs px-2 py-0.5 rounded font-mono {{ str_contains($n->type,'overdue') ? 'bg-red-100 text-red-700' : (str_contains($n->type,'expiry') ? 'bg-amber-100 text-amber-700' : 'bg-slate-100') }}">{{ $n->type }}</span>
                                @if($n->category)<span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-white">{{ $n->category }}</span>@endif
                                @if($n->severity)<span class="text-xs px-2 py-0.5 rounded {{ $severityClass }}">{{ $n->severity }}</span>@endif
                                @if($n->action_required)<span class="text-xs px-2 py-0.5 rounded bg-orange-600 text-white">Perlu Tindakan</span>@endif
                                @if($n->resolved_at)<span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Selesai</span>
                                @elseif($n->snoozed_until && $n->snoozed_until->isFuture())<span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-700">Ditunda</span>@endif
                                <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                                @unless($n->read_at) <span class="w-2 h-2 bg-blue-600 rounded-full"></span> @endunless
                            </div>
                            <div class="font-semibold mt-1">{{ $n->title }}</div>
                            <div class="text-sm text-slate-600 mt-1">{{ $n->message }}</div>
                            @if($payload)
                            <div class="mt-2 text-xs bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3 space-y-1">
                                @foreach($safeKeys as $k)
                                    @if(isset($payload[$k]) && $payload[$k] !== null && $payload[$k] !== '')
                                        <div><span class="font-mono text-slate-400">{{ $k }}:</span> <span class="font-medium">{{ is_array($payload[$k]) ? json_encode($payload[$k]) : e($payload[$k]) }}</span></div>
                                    @endif
                                @endforeach
                                @if($n->source_type)<div><span class="font-mono text-slate-400">source:</span> {{ $n->source_type }} #{{ $n->source_id }}</div>@endif
                                @if($n->dedupe_key)<div class="text-slate-400 font-mono text-[10px]">dedupe: {{ Str::limit($n->dedupe_key, 16) }}</div>@endif
                            </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1 shrink-0 min-w-[140px]">
                            @php
                                $cta = null;
                                if ($n->action_key) {
                                    $cta = \App\Services\Admin\NotificationTypeRegistry::resolveAction($n->action_key, $payload, auth()->user());
                                }
                            @endphp
                            @if($cta)
                                <a href="{{ $cta['url'] }}" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs text-center font-semibold">{{ $cta['label'] }}</a>
                            @elseif(($payload['subscription_id'] ?? false) && auth()->user()->can('subscriptions.view'))
                                <a href="{{ route('subscriptions.show', $payload['subscription_id']) }}" class="px-3 py-1 bg-slate-800 text-white rounded text-xs text-center">Lihat Layanan</a>
                            @endif
                            @unless($n->read_at)
                            <form method="POST" action="{{ route('notifications.read', $n) }}">@csrf<button class="px-3 py-1 border rounded text-xs w-full">Tandai dibaca</button></form>
                            @endunless
                            @if(!$n->resolved_at && $n->action_required)
                            <form method="POST" action="{{ route('notifications.resolve', $n) }}">@csrf<button class="px-3 py-1 bg-emerald-600 text-white rounded text-xs w-full">Tandai selesai</button></form>
                            @endif
                            @if(!$n->isSnoozed())
                            <form method="POST" action="{{ route('notifications.snooze', $n) }}">@csrf<input type="hidden" name="hours" value="24"><button class="px-3 py-1 border rounded text-xs w-full">Tunda 24j</button></form>
                            @endif
                            <form method="POST" action="{{ route('notifications.dismiss', $n) }}">@csrf<button class="px-3 py-1 text-red-600 text-xs">Hapus</button></form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400">Belum ada notifikasi.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>