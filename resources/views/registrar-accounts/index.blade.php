<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Akun Registrar</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                        Kelola akun SRS-X (gTLD vs ccTLD). Kredensial terenkripsi, hanya isi/ganti.
                        @if(!config('domain-registrars.enabled'))
                            <span class="text-amber-600 font-semibold">Integrasi dinonaktifkan (DOMAIN_REGISTRAR_ENABLED=false).</span>
                        @endif
                    </p>
                </div>
                @can('registrar_accounts.manage')
                <button onclick="document.getElementById('formModal').classList.remove('hidden')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg">
                    <i data-lucide="plus" class="w-5 h-5"></i> <span>Tambah Akun</span>
                </button>
                @endcan
            </div>

            @if(session('success')) <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">{{ session('error') }}</div> @endif
            @if($errors->any()) <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm"><ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div> @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">Nama Akun</th>
                            <th class="p-4">Provider</th>
                            <th class="p-4">Base URL</th>
                            <th class="p-4">TLD</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Domain Tertaut</th>
                            <th class="p-4">Sync Terakhir</th>
                            <th class="p-4 pr-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($accounts as $account)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 pl-6 font-semibold">{{ $account->name }}</td>
                            <td class="p-4"><span class="px-2 py-1 bg-slate-100 rounded text-xs font-mono">{{ $account->provider }}</span></td>
                            <td class="p-4 text-sm font-mono truncate max-w-[200px]">{{ $account->base_url }}</td>
                            <td class="p-4 text-xs">{{ implode(', ', $account->allowedTlds() ?: ['-']) }}</td>
                            <td class="p-4">
                                @if($account->is_active) <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Aktif</span>
                                @else <span class="px-2 py-1 bg-slate-200 text-slate-600 rounded text-xs">Nonaktif</span> @endif
                                @if($account->last_error_at) <div class="text-xs text-red-600 mt-1">{{ Str::limit($account->last_error_summary, 60) }}</div> @endif
                            </td>
                            <td class="p-4 text-center">{{ $account->subscription_domains_count }}</td>
                            <td class="p-4 text-xs">{{ $account->last_synced_at?->diffForHumans() ?? '-' }} @if($account->last_tested_at)<br><span class="text-slate-400">test {{ $account->last_tested_at->diffForHumans() }}</span>@endif</td>
                            <td class="p-4 pr-6">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    @can('registrar_accounts.test')
                                    <form method="POST" action="{{ route('registrar-accounts.test-connection', $account) }}">@csrf<button class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Test</button></form>
                                    @endcan
                                    @can('domains.sync')
                                    <form method="POST" action="{{ route('registrar-accounts.sync', $account) }}">@csrf<input type="hidden" name="dry_run" value="1"><button class="px-2 py-1 bg-amber-600 text-white rounded text-xs">Dry-run</button></form>
                                    <form method="POST" action="{{ route('registrar-accounts.sync', $account) }}">@csrf<input type="hidden" name="dry_run" value="0"><button class="px-2 py-1 bg-emerald-600 text-white rounded text-xs">Sync</button></form>
                                    @endcan
                                    @can('registrar_accounts.manage')
                                    <a href="{{ route('registrar-accounts.show', $account) }}" class="px-2 py-1 bg-slate-700 text-white rounded text-xs">Detail</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="p-8 text-center text-slate-400">Belum ada akun registrar. Tambahkan Akun A (gTLD) dan Akun B (ccTLD).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('registrar_accounts.manage')
    <div id="formModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/40" onclick="document.getElementById('formModal').classList.add('hidden')"></div>
        <div class="relative max-w-2xl mx-auto mt-10 bg-white rounded-2xl p-6 shadow-xl">
            <h3 class="font-bold text-lg mb-4">Tambah Akun Registrar</h3>
            <form method="POST" action="{{ route('registrar-accounts.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-sm font-semibold">Provider</label><select name="provider" class="w-full border rounded-lg p-2.5"><option value="srsx" selected>SRS-X</option></select></div>
                    <div><label class="text-sm font-semibold">Nama Akun</label><input name="name" placeholder="SRS-X gTLD / ccTLD" required class="w-full border rounded-lg p-2.5"></div>
                    <div class="md:col-span-2"><label class="text-sm font-semibold">Base URL (https)</label><input name="base_url" placeholder="https://api.srs-x.com" required class="w-full border rounded-lg p-2.5"></div>
                    <div><label class="text-sm font-semibold">API Username</label><input name="api_username" class="w-full border rounded-lg p-2.5"></div>
                    <div><label class="text-sm font-semibold">API Password</label><input type="password" name="api_password" class="w-full border rounded-lg p-2.5"></div>
                    <div class="md:col-span-2"><label class="text-sm font-semibold">Allowed TLDs (comma separated)</label><input name="allowed_tlds" placeholder=".com,.net atau .co.id,.my.id" class="w-full border rounded-lg p-2.5"><p class="text-xs text-slate-400 mt-1">Contoh Akun A: .com,.net,.org | Akun B: .co.id,.my.id,.id</p></div>
                    <div><label class="text-sm font-semibold flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked> Aktif</label></div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('formModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border">Batal</button>
                    <button class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</x-app-layout>
