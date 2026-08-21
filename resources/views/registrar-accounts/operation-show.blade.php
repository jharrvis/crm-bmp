<x-app-layout>
    <div class="space-y-6 max-w-4xl mx-auto">
        <a href="{{ route('registrar-accounts.show', $registrarAccount) }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke {{ $registrarAccount->name }}</a>

        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border p-6">
            <h3 class="text-xl font-bold">Review Import — Operasi #{{ $operation->id }}</h3>
            <p class="text-sm text-slate-500">Akun {{ $registrarAccount->name }} — {{ $operation->operation_type }} — Status: <span class="px-2 py-1 rounded text-xs {{ in_array($operation->status, ['manual_review', 'partially_completed']) ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">{{ $operation->status }}</span></p>

            @if(session('success')) <div class="mt-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded text-sm">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded text-sm"><ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div> @endif

            @php
                $payload = $operation->request_payload_redacted ?? [];
                $response = $operation->response_payload_redacted ?? [];
                $domains = $payload['domains'] ?? [];
                $warnings = $payload['warnings'] ?? [];
                $new = $response['new'] ?? [];
                $conflicts = $response['conflicts'] ?? [];
                $existing = $response['existing'] ?? [];
            @endphp

            @if(!empty($warnings))
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800">
                    <div class="font-bold">Peringatan TLD</div>
                    <ul class="list-disc pl-5 mt-1">@foreach($warnings as $w)<li>{{ $w }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
                <div class="p-3 bg-slate-50 rounded-xl"><div class="text-xs text-slate-400">Belum ditautkan</div><div class="font-bold text-lg">{{ count($availableNew) }}</div><div class="text-xs mt-1">{{ implode(', ', array_slice($availableNew->all(),0,5)) }}{{ count($availableNew) > 5 ? ' ...' : '' }}</div></div>
                <div class="p-3 bg-red-50 rounded-xl"><div class="text-xs text-red-400">Konflik (akun lain)</div><div class="font-bold text-lg">{{ count($conflicts) }}</div><div class="text-xs mt-1">{{ implode(', ', array_slice($conflicts,0,5)) }}</div></div>
                <div class="p-3 bg-green-50 rounded-xl"><div class="text-xs text-green-600">Sudah tertaut</div><div class="font-bold text-lg">{{ count($existing) }}</div></div>
            </div>

            @if(count($availableNew) > 0)
            <div class="mt-6">
                <h4 class="font-bold mb-2">Tautkan domain ke layanan</h4>
                <p class="text-xs text-slate-500 mb-3">Pilih domain dari hasil import dan layanan pelanggan yang akan dikelola. Hanya layanan tanpa domain registrar yang ditampilkan.</p>
                <form method="POST" action="{{ route('registrar-accounts.operations.link', [$registrarAccount, $operation]) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold">Domain</label>
                        <select name="domain" required class="border rounded-lg p-2 text-sm">
                            @foreach($availableNew as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Subscription</label>
                        <select name="subscription_id" required class="border rounded-lg p-2 text-sm min-w-[280px]">
                            <option value="">— Pilih Layanan —</option>
                            @foreach($linkable as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->subscription_code }} — {{ $sub->client->name ?? $sub->client_id }} @if($sub->package) ({{ $sub->package->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Tautkan</button>
                </form>
            </div>
            @else
                <p class="mt-6 text-sm text-slate-400">Semua domain baru sudah ditautkan. Tidak ada sisa untuk direview.</p>
            @endif

            <div class="mt-6 border-t pt-4">
                <h4 class="font-bold text-sm">Detail Operasi</h4>
                <pre class="text-xs font-mono bg-slate-900 text-slate-100 rounded p-3 mt-2 overflow-x-auto">{{ json_encode($operation->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
</x-app-layout>
