<x-app-layout>
    <div class="space-y-6 max-w-3xl mx-auto">
        <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Pusat Notifikasi</a>
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-mono px-2 py-1 bg-slate-100 rounded">{{ $notification->type }}</span>
                    <h3 class="text-xl font-bold mt-2">{{ $notification->title }}</h3>
                    <p class="text-sm text-slate-400">{{ $notification->created_at->format('d M Y H:i') }} — {{ $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}</p>
                </div>
                <div class="flex gap-2">
                    @unless($notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf<button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">Tandai dibaca</button></form>
                    @endunless
                    <form method="POST" action="{{ route('notifications.dismiss', $notification) }}">@csrf<button class="px-3 py-1.5 border rounded text-sm text-red-600">Hapus</button></form>
                </div>
            </div>
            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-sm">
                {{ $notification->message }}
            </div>
            @if($notification->payload)
            <div class="mt-4">
                <h4 class="text-sm font-bold mb-2">Payload</h4>
                <pre class="text-xs font-mono bg-slate-900 text-slate-100 rounded p-4 overflow-x-auto">{{ json_encode($notification->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
            @if(($notification->payload['subscription_id'] ?? null) || ($notification->payload['subscription_domain_id'] ?? null))
            <div class="mt-4 flex gap-2">
                @if($notification->payload['subscription_id'] ?? null)
                <a href="{{ route('subscriptions.show', $notification->payload['subscription_id']) }}" class="px-4 py-2 bg-slate-800 text-white rounded text-sm">Lihat Layanan</a>
                @endif
                @if($notification->payload['registrar_account_id'] ?? null)
                <a href="{{ route('registrar-accounts.show', $notification->payload['registrar_account_id']) }}" class="px-4 py-2 border rounded text-sm">Lihat Akun Registrar</a>
                @endif
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
