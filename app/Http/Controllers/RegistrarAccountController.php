<?php

namespace App\Http\Controllers;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Jobs\SyncRegistrarAccountDomains;
use App\Models\RegistrarAccount;
use App\Models\RegistrarOperation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegistrarAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:registrar_accounts.view')->only(['index', 'show', 'domains']);
        $this->middleware('permission:registrar_accounts.manage')->only(['store', 'update', 'destroy']);
        $this->middleware('permission:registrar_accounts.test')->only(['testConnection']);
        // P1: import/review/link hanya untuk yang punya domains.sync; check memerlukan domains.view
        $this->middleware('permission:domains.sync')->only(['sync', 'manualImport', 'showOperation', 'linkOperationDomain']);
        $this->middleware('permission:domains.view')->only(['checkDomain']);
    }

    public function index()
    {
        if (! config('domain-registrars.enabled')) {
            session()->flash('info', 'Integrasi registrar dinonaktifkan (DOMAIN_REGISTRAR_ENABLED=false). Data ditampilkan read-only.');
        }

        $accounts = RegistrarAccount::withCount('subscriptionDomains')->latest()->get();

        return view('registrar-accounts.index', compact('accounts'));
    }

    public function show(RegistrarAccount $registrarAccount)
    {
        $registrarAccount->loadCount('subscriptionDomains');

        return view('registrar-accounts.show', ['account' => $registrarAccount]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(array_keys(config('domain-registrars.providers', ['srsx' => []])))],
            'name' => 'required|string|max:255',
            'base_url' => 'required|url|starts_with:https://',
            'is_active' => 'nullable|boolean',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'allowed_tlds' => 'nullable|string|max:1000',
        ]);

        $allowedTlds = collect(explode(',', $validated['allowed_tlds'] ?? ''))->map(fn ($t) => strtolower(trim($t)))->filter()->values()->all();

        $account = RegistrarAccount::create([
            'provider' => $validated['provider'],
            'name' => $validated['name'],
            'base_url' => $validated['base_url'],
            'is_active' => $request->boolean('is_active', true),
            'api_username_encrypted' => $validated['api_username'] ?? null,
            'api_password_encrypted' => $validated['api_password'] ?? null,
            'settings_encrypted' => ['allowed_tlds' => $allowedTlds],
        ]);

        activity('registrar_accounts')->performedOn($account)->causedBy(auth()->user())
            ->log('Menambahkan akun registrar '.$account->name);

        return redirect()->route('registrar-accounts.index')->with('success', 'Akun registrar berhasil ditambahkan.');
    }

    public function update(Request $request, RegistrarAccount $registrarAccount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|url|starts_with:https://',
            'is_active' => 'nullable|boolean',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'allowed_tlds' => 'nullable|string|max:1000',
        ]);

        $allowedTlds = collect(explode(',', $validated['allowed_tlds'] ?? ''))->map(fn ($t) => strtolower(trim($t)))->filter()->values()->all();

        $registrarAccount->name = $validated['name'];
        $registrarAccount->base_url = $validated['base_url'];
        $registrarAccount->is_active = $request->boolean('is_active', true);
        $settings = $registrarAccount->settings_encrypted ?? [];
        $settings['allowed_tlds'] = $allowedTlds;
        $registrarAccount->settings_encrypted = $settings;

        if ($request->filled('api_username')) {
            $registrarAccount->api_username_encrypted = $request->api_username;
        }
        if ($request->filled('api_password')) {
            $registrarAccount->api_password_encrypted = $request->api_password;
        }

        $registrarAccount->save();

        activity('registrar_accounts')->performedOn($registrarAccount)->causedBy(auth()->user())
            ->log('Memperbarui akun registrar '.$registrarAccount->name);

        return redirect()->route('registrar-accounts.index')->with('success', 'Akun registrar berhasil diperbarui.');
    }

    public function destroy(RegistrarAccount $registrarAccount)
    {
        $linked = $registrarAccount->subscriptionDomains()->count();
        if ($linked > 0) {
            return back()->withErrors(['account' => "Akun masih tertaut ke {$linked} domain. Lepaskan tautan terlebih dahulu."]);
        }
        $ops = $registrarAccount->operations()->count();
        if ($ops > 0) {
            return back()->withErrors(['account' => "Akun memiliki {$ops} operasi audit. Arsipkan (nonaktifkan) daripada menghapus untuk menjaga audit trail."]);
        }

        $name = $registrarAccount->name;
        $registrarAccount->delete();

        activity('registrar_accounts')->causedBy(auth()->user())->log("Menghapus akun registrar {$name}");

        return redirect()->route('registrar-accounts.index')->with('success', 'Akun registrar dihapus.');
    }

    public function testConnection(Request $request, RegistrarAccount $registrarAccount, DomainRegistrarManager $manager)
    {
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan (mode disabled).');
        abort_unless($manager->canPerform('test_connection'), 403, 'Operasi tidak diizinkan pada mode '.$manager->effectiveMode().'.');

        $key = 'test_'.$registrarAccount->id.'_'.now()->format('Y-m-d_H:i');
        $op = RegistrarOperation::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'registrar_account_id' => $registrarAccount->id,
                'operation_type' => 'test_connection',
                'status' => 'processing',
                'requested_by' => auth()->id(),
                'started_at' => now(),
            ]
        );
        if (! $op->wasRecentlyCreated) {
            $op->update(['status' => 'processing', 'started_at' => now(), 'completed_at' => null, 'error_summary' => null]);
        }

        try {
            $provider = $manager->providerFor($registrarAccount);
            $result = $provider->testConnection($registrarAccount);

            $registrarAccount->update([
                'last_tested_at' => now(),
                'last_error_at' => ($result['success'] ?? false) ? null : now(),
                'last_error_summary' => ($result['success'] ?? false) ? null : ($result['message'] ?? 'Test gagal'),
            ]);

            $op->update([
                'status' => ($result['success'] ?? false) ? 'completed' : 'failed',
                'completed_at' => now(),
                'error_summary' => ($result['success'] ?? false) ? null : ($result['message'] ?? 'Test gagal'),
                'response_payload_redacted' => ['message' => $result['message'] ?? ''],
            ]);

            activity('registrar_accounts')->performedOn($registrarAccount)->causedBy(auth()->user())
                ->withProperties(['success' => $result['success'] ?? false])
                ->log(($result['success'] ?? false) ? 'Test koneksi registrar berhasil' : 'Test koneksi registrar gagal');

            if ($request->wantsJson()) {
                return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
            }

            return back()->with(($result['success'] ?? false) ? 'success' : 'error', $result['message'] ?? '');
        } catch (\Throwable $e) {
            $registrarAccount->update(['last_tested_at' => now(), 'last_error_at' => now(), 'last_error_summary' => $e->getMessage()]);
            $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => $e->getMessage()]);
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['connection' => $e->getMessage()]);
        }
    }

    public function sync(Request $request, RegistrarAccount $registrarAccount, DomainRegistrarManager $manager)
    {
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('sync'), 403, 'Sinkronisasi tidak diizinkan pada mode '.$manager->effectiveMode().'.');

        $dryRun = $request->boolean('dry_run', true);
        $key = 'sync_'.$registrarAccount->id.'_'.now()->format('Y-m-d').'_'.($dryRun ? 'dry' : 'full');
        $op = RegistrarOperation::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'registrar_account_id' => $registrarAccount->id,
                'operation_type' => $dryRun ? 'sync_dry_run' : 'sync',
                'status' => 'queued',
                'requested_by' => auth()->id(),
                'started_at' => now(),
            ]
        );
        if (! $op->wasRecentlyCreated) {
            $op->update(['status' => 'queued', 'started_at' => now(), 'completed_at' => null]);
        }
        SyncRegistrarAccountDomains::dispatch($registrarAccount->id, $dryRun)->afterCommit();

        activity('registrar_accounts')->performedOn($registrarAccount)->causedBy(auth()->user())
            ->withProperties(['dry_run' => $dryRun, 'operation_id' => $op->id])
            ->log(($dryRun ? 'Dry-run import' : 'Sync') . ' domain registrar masuk antrean');

        return back()->with('success', ($dryRun ? 'Dry-run import' : 'Sinkronisasi').' masuk antrean. Cek Activity Log dan notifikasi.');
    }

    /**
     * AJAX: list remote domains for picker (cached 2 min, like hestiaUsers).
     */
    public function domains(Request $request, RegistrarAccount $registrarAccount, DomainRegistrarManager $manager)
    {
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('listDomains'), 403, 'List domain tidak diizinkan pada mode '.$manager->effectiveMode().'.');
        abort_unless($registrarAccount->is_active, 422, 'Akun registrar tidak aktif.');

        $cacheKey = "registrar:{$registrarAccount->id}:domains";
        $domains = Cache::get($cacheKey);

        if ($domains === null) {
            $provider = $manager->providerFor($registrarAccount);
            $result = $provider->listDomains($registrarAccount, ['limit' => 200]);

            if (! ($result['success'] ?? false)) {
                return response()->json(['message' => $result['message'] ?? 'Gagal memuat domain'], 422);
            }

            $raw = $result['data'] ?? [];
            $domains = collect($raw)->map(fn ($item) => is_string($item) ? strtolower($item) : strtolower($item['domain'] ?? $item['domain_name'] ?? ''))->filter()->sort()->values()->all();
            Cache::put($cacheKey, $domains, now()->addMinutes(2));
        }

        return response()->json(['domains' => $domains]);
    }

    public function manualImport(Request $request, RegistrarAccount $registrarAccount, DomainRegistrarManager $manager)
    {
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('sync'), 403, 'Import tidak diizinkan pada mode '.$manager->effectiveMode().'.');
        $validated = $request->validate([
            'domains' => 'required|string',
        ]);
        $raw = preg_split('/[\s,;]+/', $validated['domains']);
        $domains = collect($raw)->map(fn ($d) => strtolower(trim($d)))->filter(fn ($d) => $d !== '' && preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i', $d))->unique()->values();
        if ($domains->isEmpty()) {
            return back()->withErrors(['domains' => 'Tidak ada domain valid. Pisahkan dengan koma/baris baru.']);
        }
        // TLD check per domain
        $allowed = $registrarAccount->allowedTlds();
        $warnings = [];
        foreach ($domains as $d) {
            if (! empty($allowed) && ! collect($allowed)->contains(fn ($t) => str_ends_with($d, strtolower($t)))) {
                $warnings[] = "{$d} tidak cocok TLD akun {$registrarAccount->name} (".implode(', ', $allowed).")";
            }
        }
        // Detect conflicts
        $conflicts = [];
        $existing = [];
        foreach ($domains as $d) {
            $found = \App\Models\SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$d])->first();
            if ($found) {
                if ($found->registrar_account_id && $found->registrar_account_id !== $registrarAccount->id) {
                    $conflicts[] = $d;
                } else {
                    $existing[] = $d;
                }
            }
        }
        $new = $domains->diff($conflicts)->diff($existing);

        $key = 'manual_import_'.$registrarAccount->id.'_'.md5($domains->implode(','));
        $op = \App\Models\RegistrarOperation::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'registrar_account_id' => $registrarAccount->id,
                'operation_type' => 'manual_import',
                'status' => 'manual_review',
                'requested_by' => auth()->id(),
                'request_payload_redacted' => ['domains' => $domains->toArray(), 'warnings' => $warnings],
                'response_payload_redacted' => ['new' => $new->values()->toArray(), 'conflicts' => $conflicts, 'existing' => $existing],
                'started_at' => now(),
                'completed_at' => now(),
            ]
        );
        if (! $op->wasRecentlyCreated) {
            $op->update([
                'request_payload_redacted' => ['domains' => $domains->toArray(), 'warnings' => $warnings],
                'response_payload_redacted' => ['new' => $new->values()->toArray(), 'conflicts' => $conflicts, 'existing' => $existing],
            ]);
        }

        activity('registrar_accounts')->performedOn($registrarAccount)->causedBy(auth()->user())
            ->withProperties(['domains_count' => $domains->count(), 'new' => $new->count(), 'conflicts' => count($conflicts)])
            ->log('Manual import domain registrar — staging review');

        return redirect()->route('registrar-accounts.operations.show', [$registrarAccount, $op])->with('success', "Import manual: {$domains->count()} domain diproses — {$new->count()} baru, ".count($conflicts)." konflik, ".count($existing)." sudah tertaut.")->with('manual_import_result', ['domains' => $domains, 'new' => $new, 'conflicts' => $conflicts, 'existing' => $existing, 'warnings' => $warnings]);
    }

    public function showOperation(RegistrarAccount $registrarAccount, \App\Models\RegistrarOperation $operation)
    {
        abort_unless($operation->registrar_account_id === $registrarAccount->id, 404);
        $operation->load('requestedBy');
        $linkable = \App\Models\Subscription::with('client')->whereDoesntHave('domain')->limit(100)->get();
        // Domain baru yang belum ditautkan (tersisa untuk review)
        $response = $operation->response_payload_redacted ?? [];
        $new = collect($response['new'] ?? [])->map(fn ($d) => strtolower($d));
        $availableNew = $new->filter(function ($d) use ($registrarAccount) {
            return ! \App\Models\SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$d])
                ->where('registrar_account_id', $registrarAccount->id)->exists();
        })->values();

        return view('registrar-accounts.operation-show', compact('registrarAccount', 'operation', 'linkable', 'availableNew'));
    }

    public function linkOperationDomain(Request $request, RegistrarAccount $registrarAccount, \App\Models\RegistrarOperation $operation, DomainRegistrarManager $manager)
    {
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('sync'), 403, 'Link tidak diizinkan pada mode '.$manager->effectiveMode().'.');
        abort_unless($operation->registrar_account_id === $registrarAccount->id, 404);
        $validated = $request->validate([
            'domain' => 'required|string|max:253',
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);
        $domain = strtolower(trim($validated['domain']));
        $subscription = \App\Models\Subscription::findOrFail($validated['subscription_id']);

        // Pastikan subscription belum punya domain tertaut
        if ($subscription->domain && $subscription->domain->registrar_account_id) {
            return back()->withErrors(['subscription_id' => 'Subscription sudah tertaut ke domain registrar lain.']);
        }
        // Pastikan domain ada di payload operasi (staging)
        $payload = $operation->request_payload_redacted ?? [];
        $response = $operation->response_payload_redacted ?? [];
        $allowedDomains = collect($payload['domains'] ?? [])->map(fn ($d) => strtolower($d))->all();
        if (! in_array($domain, $allowedDomains, true)) {
            return back()->withErrors(['domain' => 'Domain tidak ada di staging import ini.']);
        }
        // Domain registrar harus unik secara global. Jangan hanya mengandalkan
        // akun yang dipilih, karena dua akun tidak boleh mengklaim domain sama.
        $conflict = \App\Models\SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$domain])
            ->whereNotNull('registrar_account_id')
            ->exists();
        if ($conflict) {
            return back()->withErrors(['domain' => 'Domain sudah tertaut ke akun registrar atau layanan lain. Lepaskan/selesaikan relasi lama terlebih dahulu.']);
        }

        // P1: Verifikasi live ke SRS-X sebelum menautkan — jangan hubungkan domain yang belum terdaftar di akun ini
        $provider = $manager->providerFor($registrarAccount);
        $info = $provider->getDomain($registrarAccount, $domain);
        if (! ($info['success'] ?? false)) {
            $operation->refresh();
            return back()->withErrors(['domain' => 'Verifikasi SRS-X gagal untuk '.$domain.' ('.$registrarAccount->name.'): '.($info['message'] ?? 'Domain tidak ditemukan').' — pastikan domain terdaftar dan dimiliki akun ini sebelum ditautkan.']);
        }
        $providerDomainId = $info['data']['domain'] ?? $info['data']['domain_id'] ?? $domain;

        try {
            // Buat/update SubscriptionDomain + status operasi dalam satu transaksi (integritas multi-akun)
            $subDomain = DB::transaction(function () use ($subscription, $domain, $providerDomainId, $registrarAccount, $response, $operation) {
                // Buat atau update SubscriptionDomain — kelola dari CRM
                $subDomain = $subscription->domain()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'domain_name' => $domain,
                        'registrar_account_id' => $registrarAccount->id,
                        'provider_domain_id' => $providerDomainId,
                        'domain_account_mode' => 'existing',
                        'managed_by_crm' => false,
                        'sync_status' => 'pending',
                        'registrar' => $registrarAccount->name,
                    ]
                );

                // Dispatch sync untuk verifikasi data (afterCommit)
                \App\Jobs\SyncRegistrarDomain::dispatch($subDomain->id)->afterCommit();

                // Update status operasi — completed hanya jika seluruh domain baru sudah ditautkan
                $newList = collect($response['new'] ?? [])->map(fn ($d) => strtolower($d))->filter();
                $remainingNew = $newList->filter(function ($nd) use ($registrarAccount) {
                    return ! \App\Models\SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$nd])
                        ->where('registrar_account_id', $registrarAccount->id)->exists();
                })->count();
                $operation->update([
                    'status' => $remainingNew > 0 ? 'partially_completed' : 'completed',
                    'completed_at' => $remainingNew > 0 ? null : now(),
                ]);

                activity('registrar_accounts')->performedOn($registrarAccount)->causedBy(auth()->user())
                    ->withProperties(['domain' => $domain, 'subscription_id' => $subscription->id])
                    ->log("Menautkan domain {$domain} ke layanan {$subscription->subscription_code}");

                return $subDomain;
            });
        } catch (QueryException $e) {
if ((int) $e->errorInfo[1] === 1062 || str_contains($e->getMessage(), 'UNIQUE')) {
                $operation->refresh();
                return back()->withErrors(['domain' => 'Domain sudah ditautkan (kendala unik di database). Muat ulang halaman review untuk melihat status terbaru.']);
            }
            throw $e;
        }

        return redirect()->route('registrar-accounts.operations.show', [$registrarAccount, $operation])->with('success', "Domain {$domain} berhasil ditautkan ke layanan {$subscription->subscription_code}.");
    }

    public function checkDomain(Request $request, RegistrarAccount $registrarAccount, DomainRegistrarManager $manager)
    {
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('check'), 403, 'Check domain tidak diizinkan pada mode '.$manager->effectiveMode().'.');

        $validated = $request->validate(['domain' => 'required|string|max:253']);
        $domain = strtolower(trim($validated['domain']));

        // TLD soft warning
        $allowed = $registrarAccount->allowedTlds();
        $tldWarning = null;
        if (! empty($allowed)) {
            $matched = collect($allowed)->contains(fn ($t) => str_ends_with($domain, strtolower($t)));
            if (! $matched) {
                $tldWarning = "TLD domain {$domain} tidak termasuk daftar akun ini (".implode(', ', $allowed)."). Sebaiknya pakai akun lain.";
            }
        }

        $provider = $manager->providerFor($registrarAccount);
        $result = $provider->checkAvailability($registrarAccount, $domain);

        return response()->json(array_merge($result, ['tld_warning' => $tldWarning]));
    }
}
