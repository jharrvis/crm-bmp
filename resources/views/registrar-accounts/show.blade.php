<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold">{{ $account->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $account->provider }} — {{ $account->base_url }}</p>
                    <p class="text-xs mt-1">TLD: {{ implode(', ', $account->allowedTlds() ?: ['-']) }} | Status: {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                </div>
                <a href="{{ route('registrar-accounts.index') }}" class="px-4 py-2 border rounded-lg text-sm">Kembali</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-sm">
                <div class="p-3 bg-slate-50 rounded-xl"><div class="text-slate-400 text-xs">Domain Tertaut</div><div class="font-bold text-lg">{{ $account->subscription_domains_count }}</div></div>
                <div class="p-3 bg-slate-50 rounded-xl"><div class="text-slate-400 text-xs">Last Synced</div><div class="font-semibold">{{ $account->last_synced_at?->format('d M Y H:i') ?? '-' }}</div></div>
                <div class="p-3 bg-slate-50 rounded-xl"><div class="text-slate-400 text-xs">Last Tested</div><div class="font-semibold">{{ $account->last_tested_at?->format('d M Y H:i') ?? '-' }}</div></div>
                <div class="p-3 bg-slate-50 rounded-xl"><div class="text-slate-400 text-xs">Last Error</div><div class="text-xs text-red-600">{{ $account->last_error_summary ?? '-' }}</div></div>
            </div>
            @can('registrar_accounts.manage')
            <form method="POST" action="{{ route('registrar-accounts.update', $account) }}" class="mt-6 space-y-4 border-t pt-6">
                @csrf @method('PUT')
                <h4 class="font-bold">Edit Akun</h4>
                <div class="grid md:grid-cols-2 gap-4">
                    <div><label class="text-sm font-semibold">Nama</label><input name="name" value="{{ old('name', $account->name) }}" class="w-full border rounded-lg p-2.5" required></div>
                    <div><label class="text-sm font-semibold">Base URL</label><input name="base_url" value="{{ old('base_url', $account->base_url) }}" class="w-full border rounded-lg p-2.5" required></div>
                    <div><label class="text-sm font-semibold">API Username (kosongkan jika tidak ganti)</label><input name="api_username" class="w-full border rounded-lg p-2.5" placeholder="***"></div>
                    <div><label class="text-sm font-semibold">API Password (kosongkan jika tidak ganti)</label><input type="password" name="api_password" class="w-full border rounded-lg p-2.5" placeholder="***"></div>
                    <div class="md:col-span-2"><label class="text-sm font-semibold">Allowed TLDs</label><input name="allowed_tlds" value="{{ old('allowed_tlds', implode(',', $account->allowedTlds())) }}" class="w-full border rounded-lg p-2.5"></div>
                    <div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($account->is_active)> Aktif</label></div>
                </div>
                <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Simpan Perubahan</button>
            </form>

            @can('domains.sync')
            <div class="mt-6 border-t pt-6">
                <h4 class="font-bold">Import Manual (Fallback — API list belum tervalidasi)</h4>
                <p class="text-xs text-slate-500 mb-2">Paste daftar domain (pisah koma/baris). Sistem cek TLD, konflik dua akun, dan staging review sebelum link ke subscription.</p>
                @if(session('manual_import_result'))
                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded text-xs">
                        <div>{{ session('success') }}</div>
                        @if(!empty(session('manual_import_result')['warnings']))
                            <ul class="list-disc pl-5 mt-1 text-amber-700">@foreach(session('manual_import_result')['warnings'] as $w)<li>{{ $w }}</li>@endforeach</ul>
                        @endif
                        @if(!empty(session('manual_import_result')['conflicts']))
                            <div class="mt-1 text-red-600">Konflik: {{ implode(', ', session('manual_import_result')['conflicts']) }}</div>
                        @endif
                    </div>
                @endif
                <form method="POST" action="{{ route('registrar-accounts.import-manual', $account) }}">
                    @csrf
                    <textarea name="domains" rows="4" placeholder="example.com, example.co.id&#10;my.id, test.org" class="w-full border rounded-lg p-2.5 font-mono text-sm" required></textarea>
                    <button class="mt-2 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm">Proses Import Manual</button>
                </form>
            </div>
            @endcan

            <form method="POST" action="{{ route('registrar-accounts.destroy', $account) }}" onsubmit="return confirm('Hapus akun? Pastikan tidak ada domain tertaut.')" class="mt-4">
                @csrf @method('DELETE')<button class="text-sm text-red-600">Hapus akun</button>
            </form>
            @endcan
        </div>
    </div>
</x-app-layout>
