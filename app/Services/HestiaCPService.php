<?php

namespace App\Services;

use App\Models\HostingServer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HestiaCPService implements WebHostingServerAdapter
{
    protected $server;
    protected $baseUrl;
    protected $authHash;

    public function __construct(HostingServer $server)
    {
        $this->server = $server;
        $this->baseUrl = "https://{$server->host}:{$server->port}/api/";

        // AccessKey:SecretKey format. Values are decrypted by the model cast.
        $this->authHash = ($server->api_key ?? '').':'.($server->secret_key ?? '');
    }

    /**
     * Send request to the HestiaCP API with a consistent return envelope.
     */
    protected function request(string $cmd, array $args = []): array
    {
        $payload = ['hash' => $this->authHash, 'cmd' => $cmd];

        // Hestia expects arg1, arg2, etc. Accept positional arguments internally
        // while ensuring they are never sent as numeric form keys.
        foreach ($args as $key => $value) {
            $payload[is_int($key) ? 'arg'.($key + 1) : $key] = $value;
        }

        Log::info('HestiaCP Request', [
            'server_id' => $this->server->id,
            'cmd' => $cmd,
            'user' => $args['arg1'] ?? null,
        ]);

        try {
            $http = Http::timeout(30)
                ->asForm();

            if (config('hestiacp.verify_ssl', true) === false) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->baseUrl, $payload);

            if ($response->failed()) {
                Log::error('HestiaCP Connection Failed', [
                    'server_id' => $this->server->id,
                    'cmd' => $cmd,
                    'status' => $response->status(),
                ]);

                return $this->error('Koneksi API HestiaCP ditolak atau tidak tersedia.', 'http_'.$response->status());
            }

            $body = trim($response->body());

            if ($body === '') {
                return ['success' => true, 'data' => null, 'code' => null, 'message' => 'OK'];
            }

            if (str_starts_with($body, 'Error:')) {
                Log::error('HestiaCP Error', [
                    'server_id' => $this->server->id,
                    'cmd' => $cmd,
                ]);

                return $this->error('Server HestiaCP menolak permintaan.', 'remote_rejected');
            }

            $decoded = json_decode($body, true);

            return [
                'success' => true,
                'data' => $decoded === null && $this->looksLikePlainText($body) ? $body : $decoded,
                'code' => null,
                'message' => 'OK',
            ];

        } catch (\Exception $e) {
            Log::error('HestiaCP Exception', [
                'server_id' => $this->server->id,
                'cmd' => $cmd,
                'exception' => $e->getMessage(),
            ]);

            return $this->error('Terjadi kesalahan koneksi ke server HestiaCP.', 'network_error');
        }
    }

    protected function error(string $message, ?string $code = null): array
    {
        return ['success' => false, 'data' => null, 'code' => $code, 'message' => $message];
    }

    protected function looksLikePlainText(string $body): bool
    {
        return ! str_contains($body, '{') && ! str_contains($body, '[');
    }

    /**
     * {@inheritDoc}
     */
    public function testConnection(): array
    {
        // Use a command required by this integration, so a green check also
        // proves the Access Key has a useful minimum permission.
        return $this->listUsers();
    }

    /**
     * {@inheritDoc}
     */
    public function listUsers(): array
    {
        $result = $this->request('v-list-users', ['arg1' => 'json']);

        if (! $result['success']) {
            return $result;
        }

        $users = [];
        foreach ((array) $result['data'] as $username => $row) {
            $users[$username] = $this->normaliseUser($username, (array) $row);
        }

        return ['success' => true, 'data' => $users, 'code' => null, 'message' => 'OK'];
    }

    /**
     * {@inheritDoc}
     */
    public function findUser(string $username): array
    {
        $result = $this->request('v-list-user', [$username, 'json']);

        if (! $result['success']) {
            return $result;
        }

        $data = (array) $result['data'];

        if ($data === []) {
            return ['success' => true, 'data' => null, 'code' => null, 'message' => 'OK'];
        }

        // v-list-user may return the row directly or keyed by username.
        $row = isset($data[$username]) && is_array($data[$username])
            ? $data[$username]
            : $data;

        return [
            'success' => true,
            'data' => $this->normaliseUser($username, $row),
            'code' => null,
            'message' => 'OK',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function listWebDomains(string $username): array
    {
        $result = $this->request('v-list-web-domains', [$username, 'json']);

        if (! $result['success']) {
            return $result;
        }

        $domains = array_keys((array) $result['data']);

        return ['success' => true, 'data' => $domains, 'code' => null, 'message' => 'OK'];
    }

    /**
     * {@inheritDoc}
     */
    public function listUserPackages(): array
    {
        $result = $this->request('v-list-user-packages', ['json']);

        if (! $result['success']) {
            return $result;
        }

        $packages = array_keys((array) $result['data']);

        return ['success' => true, 'data' => $packages, 'code' => null, 'message' => 'OK'];
    }

    /**
     * Normalise a Hestia user row into a safe, stable shape.
     */
    protected function normaliseUser(string $username, array $row): array
    {
        return [
            'username' => $row['USER'] ?? $username,
            'email' => $row['EMAIL'] ?? null,
            'name' => $row['NAME'] ?? null,
            'package' => $row['PACKAGE'] ?? null,
            'disk' => $row['DISK'] ?? $row['U_DISK'] ?? null,
            'quota' => $row['USED_QUOTA'] ?? $row['QUOTA'] ?? null,
            'suspended' => array_key_exists('SUSPENDED', $row)
                ? in_array($row['SUSPENDED'], ['yes', '1', 1, true], true)
                : (array_key_exists('SUSPENDED_USER', $row) && $row['SUSPENDED_USER'] !== 'no'),
            'created_at' => $row['CREATED'] ?? null,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function createUser(string $username, string $password, string $email, string $name, string $package): array
    {
        return $this->request('v-add-user', [
            'arg1' => $username,
            'arg2' => $password,
            'arg3' => $email,
            'arg4' => $package,
            'arg5' => $name,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function createWebDomain(string $username, string $domain): array
    {
        return $this->request('v-add-web-domain', [
            'arg1' => $username,
            'arg2' => $domain,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function suspendUser(string $username): bool
    {
        return $this->request('v-suspend-user', ['arg1' => $username])['success'];
    }

    /**
     * {@inheritDoc}
     */
    public function unsuspendUser(string $username): bool
    {
        return $this->request('v-unsuspend-user', ['arg1' => $username])['success'];
    }

    /**
     * {@inheritDoc}
     */
    public function changePassword(string $username, string $password): bool
    {
        return $this->request('v-change-user-password', [
            'arg1' => $username,
            'arg2' => $password,
        ])['success'];
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(string $username): bool
    {
        return $this->request('v-delete-user', ['arg1' => $username])['success'];
    }
}
