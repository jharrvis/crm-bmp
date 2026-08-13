<?php

namespace App\Services;

use App\Models\HostingServer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZimbraService implements MailServerAdapter
{
    protected $server;
    protected $adminAccount;
    protected $adminPassword;
    protected $baseUrl;
    protected ?string $lastFaultCode = null;

    public function __construct(HostingServer $server)
    {
        $this->server = $server;
        $this->adminAccount = $server->username ?: $server->api_key;
        $this->adminPassword = $server->secret_key;

        $endpoint = $server->api_endpoint ?: '/service/admin/soap';
        $this->baseUrl = "https://{$server->host}:{$server->port}{$endpoint}";
    }

    public static function forgetAuthToken(HostingServer $server): void
    {
        $endpoint = $server->api_endpoint ?: '/service/admin/soap';
        $account = $server->username ?: $server->api_key;
        $baseUrl = "https://{$server->host}:{$server->port}{$endpoint}";

        Cache::forget('zimbra:token:'.sha1($baseUrl.'|'.$account));
        Cache::forget('zimbra:server-overview:'.$server->id);
    }

    /**
     * Send an SOAP request. Returns parsed SimpleXML object or false on failure.
     */
    protected function request(string $bodyXml, ?string $authToken = null)
    {
        $xml = $this->soapEnvelope($bodyXml, $authToken);
        $this->lastFaultCode = null;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/soap+xml; charset=UTF-8',
                ])
                ->withBody($xml, 'application/soap+xml')
                ->post($this->baseUrl);

            if ($response->failed()) {
                Log::warning('Zimbra HTTP request failed.', [
                    'server_id' => $this->server->id,
                    'status' => $response->status(),
                ]);

                return false;
            }

            $body = $response->body();
            $parsed = @simplexml_load_string($body);

            if ($parsed === false) {
                Log::warning('Zimbra returned an invalid SOAP response.', ['server_id' => $this->server->id]);

                return false;
            }

            $parsed->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $parsed->registerXPathNamespace('zimbra', 'urn:zimbra');
            $parsed->registerXPathNamespace('zimbraAdmin', 'urn:zimbraAdmin');

            $fault = $parsed->xpath('//soap:Fault');

            if (! empty($fault)) {
                $codeNodes = $fault[0]->xpath('.//*[local-name()="Code"]');
                $reasonNodes = $fault[0]->xpath('./soap:Reason/soap:Text');
                $code = (string) (end($codeNodes) ?: '');
                $reason = (string) ($reasonNodes[0] ?? 'unknown error');
                $this->lastFaultCode = $code;
                Log::warning('Zimbra SOAP fault.', ['server_id' => $this->server->id, 'code' => $code, 'reason' => $reason]);

                return false;
            }

            return $parsed;
        } catch (\Throwable $e) {
            Log::error('Zimbra request exception.', ['server_id' => $this->server->id, 'exception' => $e]);

            return false;
        }
    }

    /**
     * Build a full SOAP envelope.
     */
    protected function soapEnvelope(string $bodyXml, ?string $authToken = null): string
    {
        $header = $authToken
            ? '<context xmlns="urn:zimbra"><authToken>'.e($authToken).'</authToken><nosession>false</nosession><userAgent name="BMPnet CRM" version="1.0"/></context>'
            : '<context xmlns="urn:zimbra"><nosession/><userAgent name="BMPnet CRM" version="1.0"/></context>';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">'
            .'<soap:Header>'.$header.'</soap:Header>'
            .'<soap:Body>'.$bodyXml.'</soap:Body>'
            .'</soap:Envelope>';
    }

    /**
     * Obtain and cache the admin auth token (cached ~1 hour per server).
     */
    protected function authToken(): ?string
    {
        $cacheKey = 'zimbra:token:'.sha1($this->baseUrl.'|'.$this->adminAccount);

        return Cache::remember($cacheKey, now()->addMinutes(55), function () {
            $body = '<AuthRequest xmlns="urn:zimbraAdmin">'
                .'<name>'.e($this->adminAccount).'</name>'
                .'<password>'.e($this->adminPassword).'</password>'
                .'</AuthRequest>';

            $parsed = $this->request($body);

            if ($parsed === false) {
                return null;
            }

            $node = $parsed->xpath('//*[local-name()="authToken"]');

            if (empty($node)) {
                Log::error('Zimbra Auth token missing from response.');

                return null;
            }

            return (string) $node[0];
        });
    }

    public function ensureDomain(string $domain): bool
    {
        $token = $this->authToken();
        if (! $token) {
            return false;
        }

        $body = '<GetDomainRequest xmlns="urn:zimbraAdmin">'
            .'<domain by="name">'.e($domain).'</domain>'
            .'</GetDomainRequest>';

        $parsed = $this->request($body, $token);

        if ($parsed !== false) {
            return true;
        }

        if (! str_contains((string) $this->lastFaultCode, 'NO_SUCH_DOMAIN')) {
            return false;
        }

        return $this->createDomain($domain);
    }

    public function createDomain(string $domain): bool
    {
        $token = $this->authToken();
        if (! $token) {
            return false;
        }

        $body = '<CreateDomainRequest xmlns="urn:zimbraAdmin">'
            .'<name>'.e($domain).'</name>'
            .'</CreateDomainRequest>';

        return $this->request($body, $token) !== false;
    }

    public function createAccount(string $email, string $password, array $attributes = []): array
    {
        $token = $this->authToken();
        if (! $token) {
            return ['success' => false, 'id' => null, 'message' => 'Gagal autentikasi ke server Zimbra.'];
        }

        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= '<a n="'.e($key).'">'.e($value).'</a>';
        }

        $body = '<CreateAccountRequest xmlns="urn:zimbraAdmin" name="'.e($email).'" password="'.e($password).'">'
            .$attrs
            .'</CreateAccountRequest>';

        $parsed = $this->request($body, $token);

        if ($parsed === false) {
            // A retry may arrive after Zimbra created the account but before CRM received its response.
            $existing = $this->getAccountId($email, $token);
            if ($existing) {
                return ['success' => true, 'id' => $existing, 'message' => 'Akun sudah tersedia.'];
            }

            return ['success' => false, 'id' => null, 'message' => 'Gagal membuat akun di server Zimbra.'];
        }

        $node = $parsed->xpath('//*[local-name()="account"]');
        $accountId = ! empty($node) ? (string) $node[0]['id'] : null;

        return ['success' => true, 'id' => $accountId, 'message' => 'Akun berhasil dibuat.'];
    }

    protected function getAccountId(string $email, string $token): ?string
    {
        $body = '<GetAccountRequest xmlns="urn:zimbraAdmin">'
            .'<account by="name">'.e($email).'</account>'
            .'</GetAccountRequest>';
        $parsed = $this->request($body, $token);

        if ($parsed === false) {
            return null;
        }

        $node = $parsed->xpath('//*[local-name()="account"]');

        return empty($node) ? null : (string) $node[0]['id'];
    }

    public function setPassword(string $email, string $password): bool
    {
        $token = $this->authToken();
        if (! $token) {
            return false;
        }

        $body = '<SetPasswordRequest xmlns="urn:zimbraAdmin">'
            .'<account by="name">'.e($email).'</account>'
            .'<newPassword>'.e($password).'</newPassword>'
            .'</SetPasswordRequest>';

        return $this->request($body, $token) !== false;
    }

    public function setAccountStatus(string $email, string $status): bool
    {
        $token = $this->authToken();
        if (! $token) {
            return false;
        }

        $body = '<ModifyAccountRequest xmlns="urn:zimbraAdmin">'
            .'<account by="name">'.e($email).'</account>'
            .'<a n="zimbraAccountStatus">'.e($status).'</a>'
            .'</ModifyAccountRequest>';

        return $this->request($body, $token) !== false;
    }

    public function suspend(string $email): bool
    {
        return $this->setAccountStatus($email, 'maintenance');
    }

    public function activate(string $email): bool
    {
        return $this->setAccountStatus($email, 'active');
    }

    public function deleteAccount(string $email): bool
    {
        $token = $this->authToken();
        if (! $token) {
            return false;
        }

        $body = '<DeleteAccountRequest xmlns="urn:zimbraAdmin">'
            .'<account by="name">'.e($email).'</account>'
            .'</DeleteAccountRequest>';

        return $this->request($body, $token) !== false;
    }

    public function listAccounts(string $domain): array
    {
        $token = $this->authToken();
        if (! $token) {
            return ['success' => false, 'data' => [], 'message' => 'Gagal autentikasi ke server Zimbra.'];
        }

        $body = '<SearchAccountsRequest xmlns="urn:zimbraAdmin" applyCos="1" attrs="displayName,zimbraMailQuota,zimbraMailUsedQuota,zimbraAccountStatus">'
            .'<query>(mail=*@'.e($domain).')</query>'
            .'<limit>0</limit>'
            .'</SearchAccountsRequest>';

        $parsed = $this->request($body, $token);

        if ($parsed === false) {
            return ['success' => false, 'data' => [], 'message' => 'Zimbra tidak dapat mengambil daftar mailbox.'];
        }

        $nodes = $parsed->xpath('//*[local-name()="account"]');
        $accounts = [];

        foreach ($nodes as $node) {
            $attributes = [];
            foreach ($node->xpath('./*[local-name()="a"]') as $attribute) {
                $attributes[(string) $attribute['n']] = (string) $attribute;
            }

            $quotaBytes = $attributes['zimbraMailQuota'] ?? null;
            $usedQuotaBytes = $attributes['zimbraMailUsedQuota'] ?? null;
            $accounts[] = [
                'id' => (string) $node['id'],
                'email' => strtolower((string) $node['name']),
                'has_display_name' => array_key_exists('displayName', $attributes),
                'display_name' => $attributes['displayName'] ?? null,
                'has_quota' => array_key_exists('zimbraMailQuota', $attributes),
                'quota_mb' => is_numeric($quotaBytes) && (int) $quotaBytes > 0
                    ? (int) ceil(((int) $quotaBytes) / 1048576)
                    : 0,
                'has_used_quota' => array_key_exists('zimbraMailUsedQuota', $attributes),
                'used_quota_mb' => is_numeric($usedQuotaBytes) && (int) $usedQuotaBytes >= 0
                    ? (int) ceil(((int) $usedQuotaBytes) / 1048576)
                    : null,
                'has_status' => array_key_exists('zimbraAccountStatus', $attributes),
                'status' => strtolower($attributes['zimbraAccountStatus'] ?? 'active'),
            ];
        }

        return ['success' => true, 'data' => $accounts, 'message' => 'OK'];
    }

    /**
     * Read a small, safe subset of server metadata for the CRM detail page.
     * Failures are returned as warnings so unavailable optional API rights do not break the page.
     */
    public function serverOverview(): array
    {
        $cacheKey = 'zimbra:server-overview:'.$this->server->id;

        return Cache::remember($cacheKey, now()->addMinutes(5), function (): array {
            $token = $this->authToken();
            if (! $token) {
                return ['success' => false, 'message' => 'Autentikasi Zimbra gagal.', 'data' => []];
            }

            $warnings = [];
            $data = [
                'server_id' => null,
                'server_name' => $this->server->host,
                'attributes' => [],
                'version' => null,
                'services' => [],
            ];

            $requestedAttributes = implode(',', [
                'zimbraServiceHostname', 'zimbraServiceEnabled', 'zimbraMailHost', 'zimbraMtaMyHostname',
                'zimbraAdminPort', 'zimbraMailPort', 'zimbraMailSSLPort', 'zimbraImapBindPort',
                'zimbraImapSSLBindPort', 'zimbraPop3BindPort', 'zimbraPop3SSLBindPort', 'zimbraLmtpBindPort',
            ]);
            $serverResponse = $this->request(
                '<GetServerRequest xmlns="urn:zimbraAdmin" applyConfig="1" attrs="'.e($requestedAttributes).'">'
                .'<server by="name">'.e($this->server->host).'</server>'
                .'</GetServerRequest>',
                $token,
            );

            if ($serverResponse === false) {
                $warnings[] = 'Detail konfigurasi server tidak tersedia dari API.';
            } else {
                $serverNode = $serverResponse->xpath('//*[local-name()="server"]')[0] ?? null;
                if ($serverNode) {
                    $data['server_id'] = (string) $serverNode['id'];
                    $data['server_name'] = (string) $serverNode['name'] ?: $data['server_name'];
                    foreach ($serverNode->xpath('./*[local-name()="a"]') as $attribute) {
                        $name = (string) $attribute['n'];
                        $value = (string) $attribute;
                        if ($name === 'zimbraServiceEnabled') {
                            $data['attributes'][$name][] = $value;
                        } else {
                            $data['attributes'][$name] = $value;
                        }
                    }
                }
            }

            $versionResponse = $this->request('<GetVersionInfoRequest xmlns="urn:zimbraAdmin"/>', $token);
            if ($versionResponse === false) {
                $warnings[] = 'Versi Zimbra tidak diizinkan atau tidak tersedia dari API.';
            } else {
                $versionNode = $versionResponse->xpath('//*[local-name()="versionInfo" or local-name()="version"]')[0] ?? null;
                $data['version'] = $versionNode
                    ? (trim((string) $versionNode['version']) ?: trim((string) $versionNode))
                    : null;
            }

            $serviceResponse = $this->request('<GetServiceStatusRequest xmlns="urn:zimbraAdmin"/>', $token);
            if ($serviceResponse === false) {
                $warnings[] = 'Status service tidak tersedia dari API.';
            } else {
                $hostnames = array_filter([
                    strtolower($this->server->host),
                    strtolower((string) ($data['attributes']['zimbraServiceHostname'] ?? '')),
                    strtolower($data['server_name']),
                ]);
                foreach ($serviceResponse->xpath('//*[local-name()="status"]') as $status) {
                    if (! in_array(strtolower((string) $status['server']), $hostnames, true)) {
                        continue;
                    }
                    $data['services'][] = [
                        'name' => (string) $status['service'],
                        'running' => (string) $status === '1',
                        'checked_at' => (int) $status['t'],
                    ];
                }
            }

            return [
                'success' => true,
                'message' => $warnings === [] ? null : implode(' ', $warnings),
                'data' => $data,
            ];
        });
    }
}
