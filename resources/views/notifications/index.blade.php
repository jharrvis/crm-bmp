<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold">Pusat Notifikasi</h3>
                    <p class="text-sm text-slate-500">Domain expiry, SSL, sync gagal, konflik, dan system update — informatif & actionable.</p>
                </div>
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Tandai semua dibaca</button></form>
            </div>

            <div class="flex gap-2 mb-4">
                <a href="{{ route('notifications.index') }}" class="px-3 py-1.5 rounded-lg text-sm {{ !request('filter') ? 'bg-blue-600 text-white' : 'bg-slate-100' }}">Semua</a>
                <a href="{{ route('notifications.index', ['filter'=>'unread']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('filter')==='unread' ? 'bg-blue-600 text-white' : 'bg-slate-100' }}">Belum dibaca</a>
            </div>

            <div class="space-y-3">
                @forelse($notifications as $n)
                <div class="p-4 rounded-xl border {{ $n->read_at ? 'bg-white border-slate-200' : 'bg-blue-50 border-blue-200' }}">
                    <div class="flex justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-0.5 rounded font-mono {{ str_contains($n->type,'overdue') ? 'bg-red-100 text-red-700' : (str_contains($n->type,'expiry') ? 'bg-amber-100 text-amber-700' : 'bg-slate-100') }}">{{ $n->type }}</span>
                                <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                                @unless($n->read_at) <span class="w-2 h-2 bg-blue-600 rounded-full"></span> @endunless
                            </div>
                            <div class="font-semibold mt-1">{{ $n->title }}</div>
                            <div class="text-sm text-slate-600 mt-1">{{ $n->message }}</div>
                            @if($n->payload)
                            <div class="text-xs font-mono bg-slate-900 text-slate-100 rounded p-2 mt-2 overflow-x-auto">{{ json_encode($n->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1 shrink-0">
                            @if($n->payload['subscription_id'] ?? false)
                            <a href="{{ route('subscriptions.show', $n->payload['subscription_id']) }}" class="px-3 py-1 bg-slate-800 text-white rounded text-xs text-center">Lihat Layanan</a>
                            @endif
                            @unless($n->read_at)
                            <form method="POST" action="{{ route('notifications.read', $n) }}">@csrf<button class="px-3 py-1 border rounded text-xs w-full">Tandai dibaca</button></form>
                            @endunless
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
