<?php

namespace App\DomainRegistrars\Srsx;

use App\Models\RegistrarAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SrsxApiClient
{
    public function __construct(protected SrsxResponseMapper $mapper)
    {
    }

    protected function http()
    {
        // P2: timeout dari SystemSetting domain_registrar.timeout fallback config
        $timeout = (int) \App\Models\SystemSetting::get('domain_registrar.timeout', config('domain-registrars.timeout', 30));
        $http = Http::timeout($timeout);

        if (config('domain-registrars.verify_ssl') === false) {
            $http = $http->withoutVerifying();
        }

        return $http;
    }

    /**
     * P0-2: Validasi host HTTPS ketat — blokir SSRF, bukan warning.
     */
    protected function assertHttpsAllowed(string $url): void
    {
        $parsed = parse_url($url);
        if (($parsed['scheme'] ?? null) !== 'https') {
            throw new \InvalidArgumentException('Base URL harus HTTPS.');
        }
        $host = strtolower($parsed['host'] ?? '');
        if ($host === '') {
            throw new \InvalidArgumentException('Base URL tidak valid.');
        }

        $allowed = config('domain-registrars.allowed_base_urls', []);
        if (empty($allowed)) {
            return;
        }

        $allowedHosts = collect($allowed)->map(function ($item) {
            $p = parse_url($item);
            return strtolower($p['host'] ?? $item);
        })->filter()->values();

        // Allow exact host atau subdomain dari allowed host.
        $isAllowed = $allowedHosts->contains(function ($allowedHost) use ($host) {
            return $host === $allowedHost || str_ends_with($host, '.'.$allowedHost);
        });
        if (! $isAllowed) {
            $isAllowed = collect(config('domain-registrars.allowed_host_patterns', []))
                ->contains(fn (string $pattern) => preg_match($pattern, $host) === 1);
        }
        if (! $isAllowed) {
            throw new \InvalidArgumentException("Host '{$host}' tidak ada di allowlist registrar. Periksa konfigurasi base_url.");
        }
    }

    /**
     * Bangun POST fields sesuai kontrak SRS-X: username + password=sha256, domain, dll.
     * Semua endpoint SRS-X memakai POST form-encoded dan response XML.
     */
    protected function srsxPost(RegistrarAccount $account, string $path, array $extra = []): array
    {
        $base = rtrim($account->base_url, '/');
        $this->assertHttpsAllowed($base);
        $url = $base . '/' . ltrim($path, '/');

        $username = $account->api_username_encrypted;
        $passwordPlain = $account->api_password_encrypted;

        if (blank($username) || blank($passwordPlain)) {
            return ['success' => false, 'message' => 'Kredensial registrar belum diisi.', 'code' => 'missing_credential'];
        }

        $post = array_merge([
            'username' => $username,
            'password' => hash('sha256', $passwordPlain),
        ], $extra);

        // Redacted log — jangan tulis password
        Log::info('registrar: SRS-X request', [
            'provider' => $account->provider,
            'account_id' => $account->id,
            'path' => $path,
            'domain' => $extra['domain'] ?? null,
        ]);

        try {
            $response = $this->http()->asForm()->post($url, $post);

            if ($response->failed()) {
                Log::warning('registrar: HTTP failed', [
                    'provider' => $account->provider,
                    'account_id' => $account->id,
                    'status' => $response->status(),
                    'path' => $path,
                ]);
                return $this->mapper->mapHttpError($response->status(), $response->body());
            }

            $body = $response->body();
            return $this->mapper->mapXml($body);
        } catch (\Throwable $e) {
            Log::error('registrar: exception', [
                'provider' => $account->provider,
                'account_id' => $account->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Koneksi ke registrar gagal. Periksa whitelist IP atau base URL.', 'code' => 'network_error'];
        }
    }

    public function testConnection(RegistrarAccount $account): array
    {
        $result = $this->srsxPost($account, 'api/domain/check', ['domain' => 'example-'.time().'.com']);
        return $this->mapper->mapTestConnection($result);
    }

    public function checkAvailability(RegistrarAccount $account, string $domain): array
    {
        $result = $this->srsxPost($account, 'api/domain/check', ['domain' => strtolower($domain)]);
        return $this->mapper->mapAvailability($result);
    }

    public function getDomain(RegistrarAccount $account, string $domain): array
    {
        $result = $this->srsxPost($account, 'api/domain/info', ['domain' => strtolower($domain), 'api_id' => 1]);
        return $this->mapper->mapDomainInfo($result);
    }

    public function getContact(RegistrarAccount $account, string $contactId): array
    {
        $result = $this->srsxPost($account, 'api/contact/info', ['contactid' => $contactId]);
        return $this->mapper->mapContactInfo($result);
    }

    // ===== Fase 2: operasi terkontrol =====

    public function updateNameservers(RegistrarAccount $account, string $domain, array $nameservers): array
    {
        $ns = array_values(array_filter($nameservers, fn ($n) => ! blank($n)));
        if (count($ns) < 2) {
            return ['success' => false, 'message' => 'Minimal 2 nameserver wajib diisi.', 'code' => 'invalid_input'];
        }
        $result = $this->srsxPost($account, 'api/domain/updatens', [
            'domain' => strtolower($domain),
            'api_id' => 1,
            'nameserver' => implode(',', $ns),
        ]);
        return $this->mapper->mapNameservers($result);
    }

    public function getEpp(RegistrarAccount $account, string $domain): array
    {
        $result = $this->srsxPost($account, 'api/domain/getepp', ['domain' => strtolower($domain), 'api_id' => 1]);
        return $this->mapper->mapEpp($result);
    }

    public function setEpp(RegistrarAccount $account, string $domain, string $eppCode): array
    {
        if (blank($eppCode) || strlen($eppCode) < 4 || strlen($eppCode) > 16) {
            return ['success' => false, 'message' => 'EPP code harus 4–16 karakter.', 'code' => 'invalid_input'];
        }
        $result = $this->srsxPost($account, 'api/domain/setepp', [
            'domain' => strtolower($domain),
            'api_id' => 1,
            'eppcode' => $eppCode,
        ]);
        return $this->mapper->mapSetEpp($result);
    }

    public function getDnsInfo(RegistrarAccount $account, string $domain): array
    {
        $result = $this->srsxPost($account, 'api/dns/info', ['domain' => strtolower($domain)]);
        return $this->mapper->mapDnsInfo($result);
    }

    public function editDnsRecord(RegistrarAccount $account, string $domain, array $record): array
    {
        if (blank($record['dnsid'] ?? null) || blank($record['record'] ?? null)) {
            return ['success' => false, 'message' => 'dnsid dan record wajib diisi.', 'code' => 'invalid_input'];
        }
        $payload = [
            'domain' => strtolower($domain),
            'dnsid' => $record['dnsid'],
            'record' => $record['record'],
            'type' => $record['type'] ?? null,
            'destination' => $record['destination'] ?? null,
            'ttl' => $record['ttl'] ?? null,
        ];
        if (isset($record['priority'])) {
            $payload['priority'] = $record['priority'];
        }
        $result = $this->srsxPost($account, 'api/dns/edit', array_filter($payload, fn ($v) => $v !== null));
        return $this->mapper->mapDnsEdit($result);
    }

    public function registerDomain(RegistrarAccount $account, array $payload): array
    {
        $result = $this->srsxPost($account, 'api/domain/register', $payload);
        return $this->mapper->mapRegister($result);
    }

    public function listDomains(RegistrarAccount $account, array $filter = []): array
    {
        // P1: Coba endpoint inventory yang plausible; jika belum tervalidasi, fallback ke not_validated dengan instruksi manual import
        // SRS-X docs tidak eksplisit menampilkan domain/list, tetapi beberapa reseller memakai /api/domain/list
        // Kita coba dan jika 404, beri pesan manual import (sudah fungsional via manualImport)
        $attempts = ['api/domain/list', 'api/domains/list'];
        $lastResult = null;
        foreach ($attempts as $path) {
            $result = $this->srsxPost($account, $path, $filter);
            // Jika bukan 404 dan bukan not_validated, kembalikan
            if (($result['code'] ?? null) !== 'http_404' && ($result['code'] ?? null) !== 'not_validated') {
                // Jika XML berhasil atau gagal dengan code spesifik, kembalikan
                if (isset($result['code']) && $result['code'] !== 'http_404') {
                    // Jika success atau failed dengan code, kembalikan; untuk list, kita butuh data
                    return $result;
                }
            }
            $lastResult = $result;
        }
        // Fallback: endpoint belum tervalidasi — instruksikan manual import yang sudah fungsional
        return [
            'success' => false,
            'message' => 'Inventory domain via API belum tervalidasi untuk akun ini. Gunakan Import Manual di halaman Akun Registrar (paste daftar domain) — fitur tersebut sudah fungsional dan mencatat staging review.',
            'code' => 'not_validated',
            'data' => [],
            'hint' => 'manual_import',
        ];
    }
}
