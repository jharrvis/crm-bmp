<?php

namespace App\Http\Controllers;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Jobs\EditDomainDnsRecord;
use App\Jobs\SetDomainEpp;
use App\Jobs\SyncRegistrarDomain;
use App\Jobs\UpdateDomainNameservers;
use App\Models\RegistrarOperation;
use App\Models\Subscription;
use App\Models\SubscriptionDomain;
use Illuminate\Http\Request;

/**
 * Fase 2: operasi domain terkontrol (nameserver, EPP, DNS managed).
 * Setiap mutasi wajib: permission khusus + mode managed + konfirmasi ketik ulang nama domain + job queue.
 */
class SubscriptionDomainOperationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:domains.update_nameservers')->only(['updateNameservers']);
        $this->middleware('permission:domains.view_epp')->only(['fetchEpp']);
        $this->middleware('permission:domains.set_epp')->only(['setEpp']);
        $this->middleware('permission:domains.manage_dns')->only(['getDns', 'editDns', 'toggleManagedDns']);
        $this->middleware('permission:domains.sync')->only(['sync']);
    }

    private function resolve(Subscription $subscription, SubscriptionDomain $domain): SubscriptionDomain
    {
        abort_unless($domain->subscription_id === $subscription->id, 404);
        abort_unless($domain->registrar_account_id, 422, 'Domain belum tertaut ke akun registrar.');
        return $domain;
    }

    private function assertConfirmed(Request $request, SubscriptionDomain $domain): void
    {
        $confirmed = strtolower(trim((string) $request->input('confirm_domain', '')));
        if ($confirmed !== strtolower($domain->domain_name)) {
            abort(422, 'Konfirmasi gagal: ketik ulang nama domain persis seperti ditampilkan.');
        }
    }

    /** Sinkronisasi metadata provider read-only untuk satu domain. */
    public function sync(Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('sync'), 403, 'Sinkronisasi tidak diizinkan pada mode '.$manager->effectiveMode().'.');

        SyncRegistrarDomain::dispatch($domain->id)->afterCommit();

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'registrar_account_id' => $domain->registrar_account_id])
            ->log("Sinkronisasi detail domain {$domain->domain_name} masuk antrean");

        return back()->with('success', "Sinkronisasi detail {$domain->domain_name} masuk antrean. Tanggal, nameserver, dan contact akan diperbarui tanpa mengubah data di SRS-X.");
    }

    public function updateNameservers(Request $request, Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('update_nameservers'), 403, 'Update nameserver hanya diizinkan pada mode managed.');
        $this->assertConfirmed($request, $domain);

        $validated = $request->validate([
            'nameserver_1' => 'required|string|max:255',
            'nameserver_2' => 'required|string|max:255',
            'nameserver_3' => 'nullable|string|max:255',
            'nameserver_4' => 'nullable|string|max:255',
        ]);

        $nameservers = array_values(array_filter([
            strtolower(trim($validated['nameserver_1'])),
            strtolower(trim($validated['nameserver_2'])),
            strtolower(trim($validated['nameserver_3'] ?? '')),
            strtolower(trim($validated['nameserver_4'] ?? '')),
        ]));

        if (count($nameservers) < 2) {
            return back()->withErrors(['nameserver_2' => 'Minimal 2 nameserver wajib diisi.'])->withInput();
        }
        foreach ($nameservers as $ns) {
            if (! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $ns)) {
                return back()->withErrors(['nameserver_1' => 'Format nameserver tidak valid: '.$ns])->withInput();
            }
        }

        UpdateDomainNameservers::dispatch($domain->id, $nameservers, auth()->id())->afterCommit();

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'nameservers' => $nameservers])
            ->log("Update nameserver {$domain->domain_name} — antrean");

        return back()->with('success', "Update nameserver {$domain->domain_name} diantrekan ke job queue. Cek status operasi di panel Domain.");
    }

    public function fetchEpp(Request $request, Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('view_epp'), 403, 'Ambil EPP tidak diizinkan pada mode '.$manager->effectiveMode().'.');

        $account = $domain->registrarAccount;
        $op = RegistrarOperation::firstOrCreate(
            ['idempotency_key' => 'get_epp_'.$domain->id.'_'.now()->format('Y-m-d_H:i')],
            [
                'registrar_account_id' => $account->id,
                'subscription_domain_id' => $domain->id,
                'operation_type' => 'get_epp',
                'status' => 'processing',
                'requested_by' => auth()->id(),
                'started_at' => now(),
            ]
        );
        if (! $op->wasRecentlyCreated) {
            $op->update(['status' => 'processing', 'started_at' => now(), 'completed_at' => null, 'error_summary' => null]);
        }

        $result = $manager->providerFor($account)->getEpp($account, $domain->domain_name);

        if (! ($result['success'] ?? false)) {
            $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => $result['message'] ?? 'Gagal', 'response_payload_redacted' => ['message' => $result['message'] ?? '']]);
            return back()->with('error', 'Gagal mengambil EPP code: '.($result['message'] ?? 'unknown'));
        }

        // Simpan terenkripsi, jangan pernah log nilainya
        $domain->update(['auth_code_encrypted' => encrypt($result['epp'])]);
        $op->update(['status' => 'completed', 'completed_at' => now(), 'response_payload_redacted' => ['fetched' => true]]);

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name])
            ->log("Ambil EPP code {$domain->domain_name} (tersimpan terenkripsi)");

        return back()->with('epp_fetched', true)->with('success', 'EPP code berhasil diambil dan disimpan terenkripsi.');
    }

    public function setEpp(Request $request, Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('set_epp'), 403, 'Ganti EPP hanya diizinkan pada mode managed.');
        $this->assertConfirmed($request, $domain);

        $validated = $request->validate([
            'epp_code' => 'required|string|min:4|max:16',
        ]);

        // Cegah double-submit: satu operasi set_epp aktif per domain.
        if (RegistrarOperation::where('subscription_domain_id', $domain->id)
            ->where('operation_type', 'set_epp')
            ->whereIn('status', ['processing', 'queued'])
            ->exists()) {
            return back()->with('error', "Ganti EPP {$domain->domain_name} sudah berjalan. Tunggu hingga selesai.");
        }

        // Secret disimpan terenkripsi di operasi; job hanya membawa operation ID.
        $op = RegistrarOperation::create([
            'registrar_account_id' => $domain->registrar_account_id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'set_epp',
            'status' => 'queued',
            'requested_by' => auth()->id(),
            'idempotency_key' => 'set_epp_'.$domain->id.'_'.uniqid('', true),
            'request_secret_encrypted' => encrypt($validated['epp_code']),
            'request_payload_redacted' => ['epp_changed' => true],
        ]);

        SetDomainEpp::dispatch($op->id)->afterCommit();

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'operation_id' => $op->id])
            ->log("Ganti EPP code {$domain->domain_name} — antrean");

        return back()->with('success', "Ganti EPP code {$domain->domain_name} diantrekan ke job queue. Nilai tidak pernah ditampilkan di log.");
    }

    public function getDns(Request $request, Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('get_dns'), 403, 'Ambil DNS tidak diizinkan pada mode '.$manager->effectiveMode().'.');
        if (! $domain->managed_dns_enabled) {
            abort(422, 'DNS SRS-X belum diaktifkan untuk domain ini. Aktifkan opsi Managed DNS terlebih dahulu.');
        }

        $account = $domain->registrarAccount;
        $op = RegistrarOperation::firstOrCreate(
            ['idempotency_key' => 'get_dns_'.$domain->id.'_'.now()->format('Y-m-d_H:i')],
            [
                'registrar_account_id' => $account->id,
                'subscription_domain_id' => $domain->id,
                'operation_type' => 'get_dns',
                'status' => 'processing',
                'requested_by' => auth()->id(),
                'started_at' => now(),
            ]
        );
        if (! $op->wasRecentlyCreated) {
            $op->update(['status' => 'processing', 'started_at' => now(), 'completed_at' => null, 'error_summary' => null]);
        }

        $result = $manager->providerFor($account)->getDnsInfo($account, $domain->domain_name);

        if (! ($result['success'] ?? false)) {
            $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => $result['message'] ?? 'Gagal', 'response_payload_redacted' => ['message' => $result['message'] ?? '']]);
            return back()->with('error', 'Gagal mengambil data DNS: '.($result['message'] ?? 'unknown'));
        }

        $dnsData = $result['data'] ?? [];
        $domain->update([
            'dns_records' => $dnsData['records'] ?? [],
            'provider_metadata' => array_merge($domain->provider_metadata ?? [], ['dns_nameservers' => $dnsData['nameservers'] ?? []]),
        ]);
        $op->update(['status' => 'completed', 'completed_at' => now(), 'response_payload_redacted' => ['records' => count($dnsData['records'] ?? [])]]);

        return back()->with('dns_data', $dnsData)->with('success', 'Data DNS berhasil disinkronkan dari SRS-X ('.count($dnsData['records'] ?? []).' record).');
    }

    public function editDns(Request $request, Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('manage_dns'), 403, 'Edit DNS hanya diizinkan pada mode managed.');
        if (! $domain->managed_dns_enabled) {
            abort(422, 'DNS SRS-X belum diaktifkan untuk domain ini.');
        }
        $this->assertConfirmed($request, $domain);

        $validated = $request->validate([
            'dnsid' => 'required|integer',
            'record' => 'required|string|max:255',
            'type' => 'nullable|string|max:16',
            'destination' => 'nullable|string|max:255',
            'ttl' => 'nullable|integer|min:60',
            'priority' => 'nullable|integer',
        ]);

        EditDomainDnsRecord::dispatch($domain->id, $validated, auth()->id())->afterCommit();

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'dnsid' => $validated['dnsid']])
            ->log("Edit DNS record #{$validated['dnsid']} {$domain->domain_name} — antrean");

        return back()->with('success', "Edit record DNS #{$validated['dnsid']} diantrekan ke job queue.");
    }

    public function toggleManagedDns(Request $request, Subscription $subscription, SubscriptionDomain $domain, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');
        abort_unless($manager->canPerform('manage_dns'), 403, 'Kelola DNS hanya diizinkan pada mode managed.');

        $enable = (bool) $request->input('enabled', false);
        if ($enable) {
            // Opt-in eksplisit memerlukan konfirmasi ketik ulang nama domain
            $this->assertConfirmed($request, $domain);
        }

        $domain->update(['managed_dns_enabled' => $enable]);

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'managed_dns_enabled' => $enable])
            ->log(($enable ? 'Aktifkan' : 'Nonaktifkan')." managed DNS {$domain->domain_name}");

        return back()->with('success', 'Managed DNS SRS-X '.($enable ? 'diaktifkan' : 'dinonaktifkan')." untuk {$domain->domain_name}.");
    }

    public function retryOperation(Request $request, Subscription $subscription, SubscriptionDomain $domain, RegistrarOperation $operation, DomainRegistrarManager $manager)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($operation->subscription_domain_id === $domain->id, 404);
        abort_unless($manager->isEnabled(), 403, 'Integrasi registrar dinonaktifkan.');

        // P0/P1 retry aman: hanya operasi failed, wajib konfirmasi ulang nama domain,
        // dan mode/kemampuan diverifikasi ulang sesuai tipe operasi.
        abort_unless($operation->status === 'failed', 422, 'Hanya operasi dengan status failed yang dapat di-retry.');
        $this->assertConfirmed($request, $domain);

        $payload = $operation->request_payload_redacted ?? [];
        $retryType = $operation->operation_type;
        switch ($operation->operation_type) {
            case 'update_nameservers':
                abort_unless(auth()->user()->can('domains.update_nameservers'), 403);
                abort_unless($manager->canPerform('update_nameservers'), 403, 'Retry nameserver hanya diizinkan pada mode managed.');
                break;
            case 'set_epp':
                abort_unless(auth()->user()->can('domains.set_epp'), 403);
                abort_unless($manager->canPerform('set_epp'), 403, 'Retry EPP hanya diizinkan pada mode managed.');
                abort_unless($operation->request_secret_encrypted, 422, 'Payload EPP tidak tersedia. Ulangi dari form Ganti EPP.');
                break;
            case 'manage_dns':
                abort_unless(auth()->user()->can('domains.manage_dns'), 403);
                abort_unless($manager->canPerform('manage_dns'), 403, 'Retry DNS hanya diizinkan pada mode managed.');
                abort_unless($domain->managed_dns_enabled, 422, 'Managed DNS sudah nonaktif. Aktifkan kembali sebelum retry.');
                break;
            default:
                return back()->with('error', 'Operasi '.$operation->operation_type.' tidak dapat di-retry dari sini.');
        }

        // Retry harus diminta eksplisit oleh user berwenang. Claim bersyarat mencegah
        // double-click atau request paralel mengantrekan mutasi dua kali. Payload request
        // dipertahankan agar retry DNS selalu mengirim record yang sama.
        $retried = RegistrarOperation::query()
            ->whereKey($operation->id)
            ->where('status', 'failed')
            ->update([
                'status' => 'queued',
                'requested_by' => auth()->id(),
                'started_at' => null,
                'completed_at' => null,
                'error_summary' => null,
                'response_payload_redacted' => null,
            ]);
        abort_unless($retried === 1, 422, 'Operasi sudah berubah status. Muat ulang halaman lalu coba kembali.');

        match ($retryType) {
            'update_nameservers' => UpdateDomainNameservers::dispatch($domain->id, $payload['nameservers'] ?? [], auth()->id())->afterCommit(),
            'set_epp' => SetDomainEpp::dispatch($operation->id)->afterCommit(),
            // Payload lengkap (destination/ttl/priority) diambil dari operasi asli agar record tidak berubah data.
            'manage_dns' => EditDomainDnsRecord::dispatch($domain->id, $payload, auth()->id())->afterCommit(),
        };

        return back()->with('success', 'Operasi #'.$operation->id.' di-retry.');
    }
}
