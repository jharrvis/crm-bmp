<?php

namespace App\Jobs;

use App\Models\SubscriptionHosting;
use App\Services\WebHostResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProvisionHostingAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $hostingId)
    {
    }

    public function handle(WebHostResolver $resolver): void
    {
        $hosting = SubscriptionHosting::with(['hostingServer', 'subscription.client.primaryContact'])->findOrFail($this->hostingId);

        if (in_array($hosting->provisioning_status, ['ready', 'provisioning'], true)) {
            return;
        }

        $server = $hosting->hostingServer;

        if (! $server || ! $server->is_active || $server->type !== 'hestiacp') {
            throw new \RuntimeException('Server hosting tidak aktif atau tidak mendukung provisioning.');
        }

        $service = $resolver->resolve($server);

        $hosting->update([
            'provisioning_status' => 'provisioning',
            'provisioning_error' => null,
        ]);

        $password = $hosting->password_encrypted;

        if (! $password) {
            throw new \RuntimeException('Password akun hosting tidak tersedia.');
        }

        $email = $hosting->subscription?->client?->primaryContact?->email;

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Email kontak utama pelanggan diperlukan untuk provisioning hosting.');
        }

        if (blank($hosting->hestia_package)) {
            throw new \RuntimeException('Mapping paket Hestia wajib diisi sebelum provisioning hosting.');
        }

        $packages = $service->listUserPackages();
        if (! $packages['success'] || ! in_array($hosting->hestia_package, (array) $packages['data'], true)) {
            throw new \RuntimeException('Paket Hestia belum tersedia pada server tujuan.');
        }

        $existingUser = $service->findUser($hosting->username);
        if (! $existingUser['success']) {
            throw new \RuntimeException('Status username pada server HestiaCP tidak dapat diverifikasi.');
        }

        if ($existingUser['data'] !== null && ! $hosting->remote_user_created_at) {
            // Never adopt or modify an existing remote account from a provisioning retry.
            throw new \RuntimeException('Username sudah digunakan di server HestiaCP. Akun existing harus ditautkan secara manual.');
        }

        if ($existingUser['data'] === null) {
            $users = $service->listUsers();
            if (! $users['success']) {
                throw new \RuntimeException('Kapasitas server HestiaCP tidak dapat diverifikasi.');
            }

            if ($server->max_accounts > 0 && count((array) $users['data']) >= $server->max_accounts) {
                throw new \RuntimeException('Kapasitas akun pada server HestiaCP telah penuh.');
            }

            $createResult = $service->createUser(
                $hosting->username,
                $password,
                $email,
                $hosting->subscription?->client?->name ?? $hosting->username,
                $hosting->hestia_package
            );

            if (! $createResult['success']) {
                throw new \RuntimeException($createResult['message'] ?? 'HestiaCP tidak dapat membuat user.');
            }

            // This marker is only written after Hestia confirms the create call.
            $hosting->update(['remote_user_created_at' => now()]);
        }

        $domains = $service->listWebDomains($hosting->username);
        $domainExists = $domains['success'] && in_array($hosting->domain, (array) $domains['data'], true);

        if (! $domainExists) {
            $domainResult = $service->createWebDomain($hosting->username, $hosting->domain);

            if (! $domainResult['success']) {
                SubscriptionHosting::whereKey($this->hostingId)->update([
                    'provisioning_status' => 'failed',
                    'provisioning_error' => 'User berhasil dibuat, namun domain gagal diprovisikan. Periksa koneksi server.',
                ]);

                // Do not retry automatically: the user now exists remotely and needs review.
                return;
            }
        }

        $hosting->update([
            'provisioning_status' => 'ready',
            'provisioning_error' => null,
            'managed_by_crm' => true,
            'provisioned_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        SubscriptionHosting::whereKey($this->hostingId)->update([
            'provisioning_status' => 'failed',
            'provisioning_error' => 'User hosting belum dapat diprovisikan. Periksa koneksi server.',
        ]);
    }
}
