<?php

namespace App\DomainRegistrars\Srsx;

class SrsxResponseMapper
{
    /**
     * P0-1: Parser XML SRS-X (resultCode 1000 = success, lainnya failed)
     * Contoh sukses: <epp><result><resultCode>1000</resultCode><resultMsg>...</resultMsg></result><resultData>...</resultData></epp>
     */
    public function mapXml(string $xmlBody): array
    {
        $body = trim($xmlBody);
        if ($body === '') {
            return ['success' => false, 'message' => 'Response kosong dari provider.', 'code' => 'empty_response'];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        if ($xml === false) {
            return ['success' => false, 'message' => 'Response bukan XML valid.', 'code' => 'invalid_xml', 'raw' => mb_substr($body, 0, 500)];
        }

        $code = (string) ($xml->result->resultCode ?? $xml->resultCode ?? '');
        $msg = (string) ($xml->result->resultMsg ?? $xml->resultMsg ?? 'Unknown');
        $dataNode = $xml->resultData ?? $xml->resultData ?? null;

        $data = [];
        if ($dataNode) {
            $data = $this->xmlToArray($dataNode);
        }

        // P1: Jangan anggap 1001 sukses global — mapping harus per endpoint
        // Generic: hanya 1000 = success, 1001 ditangani spesifik per endpoint (check vs register)
        $success = $code === '1000';

        return [
            'success' => $success,
            'message' => $msg,
            'code' => $code,
            'data' => $data,
            'raw' => $body,
        ];
    }

    private function xmlToArray(\SimpleXMLElement $node): array
    {
        $arr = [];
        foreach ($node->children() as $child) {
            $key = $child->getName();
            if ($child->count() > 0) {
                $arr[$key] = $this->xmlToArray($child);
            } else {
                $arr[$key] = (string) $child;
            }
        }
        return $arr;
    }

    public function mapSuccess(array $data): array
    {
        if (isset($data['success'])) {
            return $data;
        }
        if (isset($data['status']) && $data['status'] === 'success') {
            return ['success' => true, 'data' => $data['data'] ?? $data, 'message' => $data['message'] ?? 'OK'];
        }
        if (isset($data['error']) || isset($data['message'])) {
            return ['success' => false, 'message' => $data['message'] ?? $data['error'] ?? 'Error', 'code' => $data['code'] ?? 'remote_rejected', 'data' => $data];
        }
        return ['success' => true, 'data' => $data, 'message' => 'OK'];
    }

    public function mapHttpError(int $status, string $body): array
    {
        return match (true) {
            $status === 401 => ['success' => false, 'message' => 'Kredensial ditolak. Periksa API username/password.', 'code' => 'http_401'],
            $status === 403 => ['success' => false, 'message' => 'Akses ditolak. Periksa whitelist IP.', 'code' => 'http_403'],
            $status === 404 => ['success' => false, 'message' => 'Endpoint tidak ditemukan. Periksa base URL.', 'code' => 'http_404'],
            $status === 429 => ['success' => false, 'message' => 'Rate limit provider. Coba lagi nanti.', 'code' => 'http_429'],
            default => ['success' => false, 'message' => 'Koneksi ditolak (HTTP '.$status.').', 'code' => 'http_'.$status],
        };
    }

    public function mapAvailability(array $result): array
    {
        // P1: Spesifik per endpoint check — 1000 = Available (success), 1001 = not available (success untuk check, available false)
        $code = $result['code'] ?? null;
        $msg = strtolower($result['message'] ?? '');
        if ($code === '1000' && str_contains($msg, 'available')) {
            return ['success' => true, 'available' => true, 'message' => 'Domain tersedia.', 'code' => $code, 'data' => $result['data'] ?? []];
        }
        if ($code === '1001') {
            // Untuk check: 1001 Command Failed biasanya berarti domain taken (bukan auth fail)
            // Bedakan dengan 1001 yang message "Create Domain Failed" untuk register — itu failure
            // Untuk check, anggap 1001 sebagai success dengan available false
            return ['success' => true, 'available' => false, 'message' => 'Domain tidak tersedia.', 'code' => $code, 'data' => $result['data'] ?? []];
        }
        if (! ($result['success'] ?? false)) {
            return $result;
        }
        $data = $result['data'] ?? [];
        $available = $data['available'] ?? $data['is_available'] ?? null;
        if ($available === null && isset($data['status'])) {
            $available = in_array(strtolower($data['status']), ['available', 'free'], true);
        }
        return ['success' => true, 'available' => $available, 'message' => $available ? 'Domain tersedia.' : 'Domain tidak tersedia.', 'data' => $data, 'code' => $code];
    }

    public function mapDomainInfo(array $result): array
    {
        // Info: hanya 1000 success, 1001 Command Failed = not found
        $code = $result['code'] ?? null;
        if ($code === '1001') {
            return ['success' => false, 'message' => $result['message'] ?? 'Domain tidak ditemukan.', 'code' => $code, 'data' => $result['data'] ?? []];
        }
        if (! ($result['success'] ?? false)) {
            return $result;
        }
        return $result;
    }

    public function mapRegister(array $result): array
    {
        // Register: 1000 success, 1001 dengan "waiting for the complete document" = success (pending doc), lainnya failed
        $code = $result['code'] ?? null;
        $msg = strtolower($result['message'] ?? '');
        if ($code === '1001' && str_contains($msg, 'waiting for the complete document')) {
            return ['success' => true, 'message' => $result['message'], 'code' => $code, 'data' => $result['data'] ?? [], 'pending_doc' => true];
        }
        if ($code === '1001') {
            // 1001 Create Domain Failed = failure
            return ['success' => false, 'message' => $result['message'] ?? 'Create Domain Failed', 'code' => $code, 'data' => $result['data'] ?? []];
        }
        return $result;
    }

    public function mapTestConnection(array $result): array
    {
        // P2: Validasi 1001 tidak false positive untuk auth/validasi error
        // Hanya 1000 (available) atau 1001 dengan message yang jelas "Domain" sebagai indikasi koneksi OK
        // Jika 1001 dengan message mengandung auth/IP/username/password, itu bukan success
        $code = $result['code'] ?? null;
        $msg = strtolower($result['message'] ?? '');
        $isAuthError = str_contains($msg, 'auth') || str_contains($msg, 'username') || str_contains($msg, 'password') || str_contains($msg, 'whitelist') || str_contains($msg, 'ip not allowed') || str_contains($msg, 'invalid');
        if ($isAuthError) {
            return ['success' => false, 'message' => $result['message'] ?? 'Koneksi gagal (auth/IP)', 'code' => $code ?? 'auth_error'];
        }
        if ($code === '1000') {
            return ['success' => true, 'message' => 'Koneksi berhasil.', 'code' => $code];
        }
        if ($code === '1001' && (str_contains($msg, 'domain') || str_contains($msg, 'command failed'))) {
            // Untuk dummy available domain, 1001 Command Failed berarti taken — tapi tetap indikasi koneksi & auth berhasil
            // Namun untuk testConnection kita harapkan available (1000); 1001 taken untuk dummy yang seharusnya available adalah anomali, tapi tetap koneksi OK
            return ['success' => true, 'message' => 'Koneksi berhasil.', 'code' => $code];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Koneksi gagal', 'code' => $code ?? 'unknown'];
    }

    /**
     * Fase 2 — update nameservers (api/domain/updatens).
     * 1000 = success, selainnya (1001/2303/dll) gagal dengan pesan provider.
     */
    public function mapNameservers(array $result): array
    {
        $code = $result['code'] ?? null;
        if ($code === '1000') {
            return ['success' => true, 'message' => $result['message'] ?? 'Nameserver berhasil diperbarui.', 'code' => $code, 'data' => $result['data'] ?? []];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Update nameserver gagal.', 'code' => $code ?? 'remote_rejected', 'data' => $result['data'] ?? []];
    }

    /**
     * Fase 2 — get EPP (api/domain/getepp). 1000 + data.epp = sukses.
     */
    public function mapEpp(array $result): array
    {
        $code = $result['code'] ?? null;
        $data = $result['data'] ?? [];
        if ($code === '1000') {
            $epp = $data['epp'] ?? null;
            if (blank($epp)) {
                return ['success' => false, 'message' => 'Response EPP kosong dari provider.', 'code' => 'empty_epp'];
            }
            return ['success' => true, 'epp' => (string) $epp, 'message' => 'EPP code berhasil diambil.', 'code' => $code];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Gagal mengambil EPP code.', 'code' => $code ?? 'remote_rejected'];
    }

    /**
     * Fase 2 — set EPP (api/domain/setepp). 1000 = sukses.
     */
    public function mapSetEpp(array $result): array
    {
        if (($result['code'] ?? null) === '1000') {
            return ['success' => true, 'message' => $result['message'] ?? 'EPP code berhasil diganti.', 'code' => $result['code']];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Ganti EPP code gagal.', 'code' => $result['code'] ?? 'remote_rejected'];
    }

    /**
     * Fase 2 — get DNS info (api/dns/info). 1000 = sukses (bisa DNS belum initialized).
     * resultData berisi dns0..n (records) + domain_ns1..4.
     */
    public function mapDnsInfo(array $result): array
    {
        $code = $result['code'] ?? null;
        $data = $result['data'] ?? [];
        if ($code === '1000') {
            // Normalisasi records: urutkan berdasarkan index dnsN, jadikan list
            $records = [];
            foreach ($data as $key => $value) {
                if (preg_match('/^dns\d+$/', (string) $key) && is_array($value)) {
                    $records[] = $value;
                }
            }
            usort($records, function ($a, $b) {
                return (int) ($a['line'] ?? 0) <=> (int) ($b['line'] ?? 0);
            });
            $nameservers = [
                'ns1' => $data['domain_ns1'] ?? null,
                'ns2' => $data['domain_ns2'] ?? null,
                'ns3' => $data['domain_ns3'] ?? null,
                'ns4' => $data['domain_ns4'] ?? null,
            ];
            return [
                'success' => true,
                'message' => $result['message'] ?? 'Data DNS berhasil diambil.',
                'code' => $code,
                'data' => ['records' => $records, 'nameservers' => array_values(array_filter($nameservers))],
            ];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Gagal mengambil data DNS.', 'code' => $code ?? 'remote_rejected', 'data' => $data];
    }

    /**
     * Fase 2 — edit DNS record (api/dns/edit). 1000 = sukses.
     */
    public function mapDnsEdit(array $result): array
    {
        if (($result['code'] ?? null) === '1000') {
            return ['success' => true, 'message' => $result['message'] ?? 'Record DNS berhasil diperbarui.', 'code' => $result['code']];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Edit record DNS gagal.', 'code' => $result['code'] ?? 'remote_rejected'];
    }

    public function redact(array $payload): array
    {
        $secrets = ['password', 'api_key', 'api_user', 'api_password', 'auth_code', 'epp', 'secret', 'username'];
        foreach ($secrets as $key) {
            if (isset($payload[$key])) {
                $payload[$key] = '***';
            }
        }
        return $payload;
    }
}
