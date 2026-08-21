<?php

namespace App\Http\Controllers;

use App\Models\RegistrarOperation;
use App\Models\Subscription;
use App\Models\SubscriptionDomain;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionDomainRenewalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:domains.renew')->only('requestRenewal');
        $this->middleware('permission:domains.approve_renew')->only('approveRenewal');
    }

    private function resolve(Subscription $subscription, SubscriptionDomain $domain): SubscriptionDomain
    {
        abort_unless($domain->subscription_id === $subscription->id, 404);
        abort_unless($domain->registrar_account_id, 422, 'Domain belum tertaut ke akun registrar.');

        return $domain;
    }

    private function assertConfirmed(Request $request, SubscriptionDomain $domain): void
    {
        abort_unless(
            strtolower(trim((string) $request->input('confirm_domain'))) === strtolower($domain->domain_name),
            422,
            'Konfirmasi gagal: ketik ulang nama domain persis seperti ditampilkan.'
        );
    }

    /**
     * Fase 3a: hanya mencatat permintaan dan meminta persetujuan.
     * Tidak pernah menghubungi provider atau membuat tagihan otomatis.
     */
    public function requestRenewal(Request $request, Subscription $subscription, SubscriptionDomain $domain, AdminNotificationService $notifications)
    {
        $domain = $this->resolve($subscription, $domain);
        $this->assertConfirmed($request, $domain);

        $validated = $request->validate([
            'years' => ['required', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $hasActiveRequest = RegistrarOperation::query()
            ->where('subscription_domain_id', $domain->id)
            ->where('operation_type', 'renew')
            ->whereIn('status', ['awaiting_payment', 'awaiting_approval', 'queued', 'processing'])
            ->exists();
        if ($hasActiveRequest) {
            return back()->with('error', 'Masih ada permintaan perpanjangan aktif untuk domain ini.');
        }

        $operation = RegistrarOperation::create([
            'registrar_account_id' => $domain->registrar_account_id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'renew',
            'status' => 'awaiting_approval',
            'requested_by' => auth()->id(),
            'idempotency_key' => 'renew_request_'.$domain->id.'_'.Str::uuid(),
            'request_payload_redacted' => [
                'years' => $validated['years'],
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'operation_id' => $operation->id, 'years' => $validated['years']])
            ->log("Ajukan perpanjangan domain {$domain->domain_name}");

        $notifications->notifyRoles(
            ['Owner', 'Admin'],
            'domain_renew_requested',
            "Persetujuan perpanjangan {$domain->domain_name}",
            "Permintaan perpanjangan {$domain->domain_name} selama {$validated['years']} tahun menunggu persetujuan.",
            ['subscription_domain_id' => $domain->id, 'domain_name' => $domain->domain_name, 'operation_id' => $operation->id]
        );

        return back()->with('success', 'Permintaan perpanjangan dibuat dan menunggu persetujuan. Belum ada perubahan di SRS-X atau tagihan.');
    }

    /**
     * Approval hanya mengizinkan tindak lanjut manual. Eksekusi provider tetap
     * terkunci sampai UAT dan kebijakan pembayaran fase berikutnya disetujui.
     */
    public function approveRenewal(Request $request, Subscription $subscription, SubscriptionDomain $domain, RegistrarOperation $operation)
    {
        $domain = $this->resolve($subscription, $domain);
        abort_unless($operation->subscription_domain_id === $domain->id && $operation->operation_type === 'renew', 404);
        $this->assertConfirmed($request, $domain);

        $approved = RegistrarOperation::query()
            ->whereKey($operation->id)
            ->where('status', 'awaiting_approval')
            ->update([
                'status' => 'manual_review',
                'approved_by' => auth()->id(),
                'completed_at' => now(),
                'response_payload_redacted' => ['approved_for_manual_review' => true],
            ]);
        abort_unless($approved === 1, 422, 'Permintaan sudah berubah status. Muat ulang halaman lalu coba kembali.');

        activity('registrar_accounts')->performedOn($domain)->causedBy(auth()->user())
            ->withProperties(['domain' => $domain->domain_name, 'operation_id' => $operation->id])
            ->log("Setujui perpanjangan domain {$domain->domain_name} untuk tindak lanjut manual");

        return back()->with('success', 'Permintaan disetujui untuk tindak lanjut manual. CRM belum menghubungi SRS-X.');
    }
}
