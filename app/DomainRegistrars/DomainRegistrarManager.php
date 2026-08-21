<?php

namespace App\DomainRegistrars;

use App\DomainRegistrars\Contracts\DomainRegistrarProvider;
use App\Models\RegistrarAccount;
use InvalidArgumentException;

class DomainRegistrarManager
{
    public function providerFor(RegistrarAccount $account): DomainRegistrarProvider
    {
        $providers = config('domain-registrars.providers', []);
        $entry = $providers[$account->provider] ?? null;

        if (! $entry || ! ($entry['enabled'] ?? false)) {
            throw new InvalidArgumentException("Provider '{$account->provider}' tidak aktif atau tidak terdaftar.");
        }

        $class = $entry['class'];

        return app($class);
    }

    public function isProviderEnabled(string $provider): bool
    {
        $providers = config('domain-registrars.providers', []);
        return (bool) ($providers[$provider]['enabled'] ?? false);
    }

    public function effectiveMode(): string
    {
        if (! config('domain-registrars.enabled')) {
            return 'disabled';
        }
        $dbMode = \App\Models\SystemSetting::get('domain_registrar.mode', null);
        $mode = $dbMode ?? config('domain-registrars.mode', 'read_only');
        return in_array($mode, ['disabled', 'read_only', 'managed'], true) ? $mode : 'read_only';
    }

    public function isEnabled(): bool
    {
        return $this->effectiveMode() !== 'disabled';
    }

    public function canPerform(string $operation): bool
    {
        $mode = $this->effectiveMode();
        if ($mode === 'disabled') {
            return false;
        }
        $readOps = ['test_connection', 'check', 'checkAvailability', 'getDomain', 'listDomains', 'sync', 'sync_dry_run', 'view_epp', 'get_dns'];
        if ($mode === 'read_only') {
            return in_array($operation, $readOps, true);
        }
        // managed allows all (termasuk update_nameservers, set_epp, manage_dns, renew, dll)
        return true;
    }

    public function replacePlaceholders(array $payload): array
    {
        // Future: encrypt redaction, etc.
        return $payload;
    }
}
