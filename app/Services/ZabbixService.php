<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZabbixService
{
    public function getGroups(): array
    {
        return Cache::remember('zabbix:groups', now()->addMinutes(5), function () {
            return $this->request('hostgroup.get', [
                'output' => ['groupid', 'name'],
                'sortfield' => 'name',
                'sortorder' => 'ASC',
            ]);
        });
    }

    public function getHosts(?string $groupId = null): array
    {
        $groupId = $groupId !== null ? preg_replace('/\D/', '', $groupId) : null;

        return Cache::remember(
            'zabbix:hosts:' . ($groupId ?: 'all'),
            now()->addMinutes(2),
            function () use ($groupId) {
                $params = [
                    'output' => ['hostid', 'name'],
                    'filter' => ['status' => 0],
                    'sortfield' => 'name',
                    'sortorder' => 'ASC',
                ];

                if ($groupId) {
                    $params['groupids'] = [$groupId];
                }

                return $this->request('host.get', $params);
            }
        );
    }

    public function getGraphs(string $hostId): array
    {
        $hostId = preg_replace('/\D/', '', $hostId);
        if ($hostId === '') {
            throw new RuntimeException('Host ID wajib diisi.');
        }

        return Cache::remember("zabbix:graphs:{$hostId}", now()->addMinutes(2), function () use ($hostId) {
            $graphs = $this->request('graph.get', [
                'output' => ['graphid', 'name'],
                'hostids' => [$hostId],
                'sortfield' => 'name',
                'sortorder' => 'ASC',
            ]);

            if (empty($graphs)) {
                return [];
            }

            $graphIds = array_column($graphs, 'graphid');
            $graphItems = $this->request('graphitem.get', [
                'output' => ['graphid', 'itemid'],
                'graphids' => $graphIds,
            ]);

            $itemIds = array_values(array_unique(array_column($graphItems, 'itemid')));
            if (empty($itemIds)) {
                return [];
            }

            $items = $this->request('item.get', [
                'output' => ['itemid', 'name', 'key_', 'status', 'state'],
                'itemids' => $itemIds,
            ]);

            $itemsById = [];
            foreach ($items as $item) {
                $itemsById[$item['itemid']] = $item;
            }

            $graphItemMap = [];
            foreach ($graphItems as $graphItem) {
                $graphId = $graphItem['graphid'];
                $itemId = $graphItem['itemid'];

                if (! isset($itemsById[$itemId])) {
                    continue;
                }

                $graphItemMap[$graphId][] = $itemsById[$itemId];
            }

            $result = [];
            foreach ($graphs as $graph) {
                $itemsForGraph = $graphItemMap[$graph['graphid']] ?? [];
                if (count($itemsForGraph) < 2 || ! $this->isNetworkGraph($graph['name'], $itemsForGraph)) {
                    continue;
                }

                $itemIn = null;
                $itemOut = null;

                foreach ($itemsForGraph as $item) {
                    if ($itemIn === null && $this->isInboundItem($item)) {
                        $itemIn = $item['itemid'];
                    }
                    if ($itemOut === null && $this->isOutboundItem($item)) {
                        $itemOut = $item['itemid'];
                    }
                }

                if ($itemIn === null || $itemOut === null) {
                    continue;
                }

                $result[] = [
                    'graphid' => $graph['graphid'],
                    'name' => $graph['name'],
                    'itemIn' => $itemIn,
                    'itemOut' => $itemOut,
                ];
            }

            return $result;
        });
    }

    public function getBandwidthData(
        string $itemIn,
        string $itemOut,
        string $mode = 'preset',
        string $period = '24h',
        ?string $from = null,
        ?string $to = null
    ): array {
        $itemIn = preg_replace('/\D/', '', $itemIn);
        $itemOut = preg_replace('/\D/', '', $itemOut);

        if ($itemIn === '' || $itemOut === '') {
            throw new RuntimeException('Item IN dan OUT wajib dipilih.');
        }

        if ($mode === 'custom' && $from && $to) {
            $timeFrom = Carbon::parse($from)->startOfDay()->timestamp;
            $timeTo = Carbon::parse($to)->endOfDay()->timestamp;
            $rangeLabel = Carbon::parse($from)->format('d M Y') . ' - ' . Carbon::parse($to)->format('d M Y');
            $isLive = false;
        } else {
            $periodMap = [
                '1h' => 3600,
                '6h' => 21600,
                '24h' => 86400,
                '7d' => 604800,
                '30d' => 2592000,
                '90d' => 7776000,
                '180d' => 15552000,
                '1y' => 31536000,
                '2y' => 63072000,
            ];

            $seconds = $periodMap[$period] ?? 86400;
            $timeFrom = now()->subSeconds($seconds)->timestamp;
            $timeTo = now()->timestamp;
            $rangeLabel = "Last {$period}";
            $isLive = in_array($period, ['1h', '6h', '24h'], true);
        }

        $range = $timeTo - $timeFrom;
        $useTrend = $range > 6912000;

        if ($range <= 86400) {
            $fmt = 'H:i';
            $limit = 500;
        } elseif ($range <= 604800) {
            $fmt = 'D H:i';
            $limit = 700;
        } elseif ($range <= 2592000) {
            $fmt = 'd M';
            $limit = 900;
        } elseif ($range <= 7776000) {
            $fmt = 'd M Y';
            $limit = 1000;
        } else {
            $fmt = 'd M Y';
            $limit = 2000;
        }

        $rawIn = $useTrend
            ? $this->getTrend($itemIn, $timeFrom, $timeTo, $limit)
            : $this->getHistory($itemIn, $timeFrom, $timeTo, $limit);
        $rawOut = $useTrend
            ? $this->getTrend($itemOut, $timeFrom, $timeTo, $limit)
            : $this->getHistory($itemOut, $timeFrom, $timeTo, $limit);

        $labels = [];
        $dataIn = [];
        $dataOut = [];
        $dataInMin = [];
        $dataInMax = [];
        $dataOutMin = [];
        $dataOutMax = [];

        if ($useTrend) {
            foreach ($rawIn as $row) {
                $labels[] = Carbon::createFromTimestamp((int) $row['clock'])->format($fmt);
                $dataIn[] = round(((float) $row['value_avg']) / 1000000, 3);
                $dataInMin[] = round(((float) $row['value_min']) / 1000000, 3);
                $dataInMax[] = round(((float) $row['value_max']) / 1000000, 3);
            }

            foreach ($rawOut as $row) {
                $dataOut[] = round(((float) $row['value_avg']) / 1000000, 3);
                $dataOutMin[] = round(((float) $row['value_min']) / 1000000, 3);
                $dataOutMax[] = round(((float) $row['value_max']) / 1000000, 3);
            }
        } else {
            foreach ($rawIn as $row) {
                $labels[] = Carbon::createFromTimestamp((int) $row['clock'])->format($fmt);
                $dataIn[] = round(((float) $row['value']) / 1000000, 3);
            }

            foreach ($rawOut as $row) {
                $dataOut[] = round(((float) $row['value']) / 1000000, 3);
            }
        }

        $curIn = ! empty($dataIn) ? end($dataIn) : 0;
        $curOut = ! empty($dataOut) ? end($dataOut) : 0;
        $maxIn = ! empty($dataIn) ? max($useTrend ? $dataInMax : $dataIn) : 0;
        $maxOut = ! empty($dataOut) ? max($useTrend ? $dataOutMax : $dataOut) : 0;
        $avgIn = ! empty($dataIn) ? round(array_sum($dataIn) / count($dataIn), 2) : 0;
        $avgOut = ! empty($dataOut) ? round(array_sum($dataOut) / count($dataOut), 2) : 0;

        $response = [
            'labels' => $labels,
            'dataIn' => $dataIn,
            'dataOut' => $dataOut,
            'stats' => compact('curIn', 'curOut', 'maxIn', 'maxOut', 'avgIn', 'avgOut'),
            'isLive' => $isLive,
            'rangeLabel' => $rangeLabel,
            'updatedAt' => now()->format('d M Y H:i:s'),
            'points' => count($dataIn),
            'dataMode' => $useTrend ? 'trend' : 'history',
        ];

        if ($useTrend) {
            $response['dataInMin'] = $dataInMin;
            $response['dataInMax'] = $dataInMax;
            $response['dataOutMin'] = $dataOutMin;
            $response['dataOutMax'] = $dataOutMax;
        }

        return $response;
    }

    protected function getHistory(string $itemId, int $timeFrom, int $timeTo, int $limit): array
    {
        foreach ([3, 0] as $historyType) {
            $response = $this->request('history.get', [
                'itemids' => [$itemId],
                'history' => $historyType,
                'sortfield' => 'clock',
                'sortorder' => 'ASC',
                'time_from' => $timeFrom,
                'time_till' => $timeTo,
                'output' => 'extend',
                'limit' => $limit,
            ]);

            if (! empty($response)) {
                return $response;
            }
        }

        return [];
    }

    protected function getTrend(string $itemId, int $timeFrom, int $timeTo, int $limit): array
    {
        return $this->request('trend.get', [
            'itemids' => [$itemId],
            'sortfield' => 'clock',
            'sortorder' => 'ASC',
            'time_from' => $timeFrom,
            'time_till' => $timeTo,
            'output' => 'extend',
            'limit' => $limit,
        ]);
    }

    protected function request(string $method, array $params, bool $retry = true): mixed
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1,
        ];

        if ($method !== 'user.login') {
            $payload['auth'] = $this->getAuthToken();
        }

        $client = Http::timeout(20)->acceptJson();
        if (! filter_var(env('ZABBIX_VERIFY_SSL', false), FILTER_VALIDATE_BOOL)) {
            $client = $client->withoutVerifying();
        }

        $response = $client->post((string) env('ZABBIX_API_URL'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal menghubungi Zabbix API.');
        }

        $decoded = $response->json();

        if (isset($decoded['error'])) {
            $message = $decoded['error']['data'] ?? $decoded['error']['message'] ?? 'Unknown Zabbix API error';

            if ($retry && str_contains(strtolower((string) $message), 'session terminated')) {
                Cache::forget($this->tokenCacheKey());

                return $this->request($method, $params, false);
            }

            throw new RuntimeException("Zabbix API error: {$message}");
        }

        return $decoded['result'] ?? [];
    }

    protected function getAuthToken(): string
    {
        $url = (string) env('ZABBIX_API_URL');
        $username = (string) env('ZABBIX_USERNAME');
        $password = (string) env('ZABBIX_PASSWORD');

        if ($url === '' || $username === '' || $password === '') {
            throw new RuntimeException('Konfigurasi Zabbix belum lengkap di file .env.');
        }

        return Cache::remember($this->tokenCacheKey(), now()->addMinutes(10), function () use ($username, $password) {
            $result = $this->request('user.login', [
                'username' => $username,
                'password' => $password,
            ], false);

            if (! is_string($result) || $result === '') {
                throw new RuntimeException('Autentikasi Zabbix gagal.');
            }

            return $result;
        });
    }

    protected function tokenCacheKey(): string
    {
        return 'zabbix:token:' . sha1((string) env('ZABBIX_API_URL') . '|' . (string) env('ZABBIX_USERNAME'));
    }

    protected function isNetworkGraph(string $graphName, array $items): bool
    {
        $graphText = $this->normalizeText($graphName);
        $joinedItems = $this->normalizeText(implode(' ', array_map(
            fn(array $item) => ($item['name'] ?? '') . ' ' . ($item['key_'] ?? ''),
            $items
        )));

        $hasNetworkContext = false;
        foreach (['bits', 'bytes', 'interface', 'network', 'traffic', 'bandwidth', 'net.if'] as $needle) {
            if (str_contains($graphText, $needle) || str_contains($joinedItems, $needle)) {
                $hasNetworkContext = true;
                break;
            }
        }

        if (! $hasNetworkContext) {
            return false;
        }

        $hasIn = false;
        $hasOut = false;

        foreach ($items as $item) {
            $hasIn = $hasIn || $this->isInboundItem($item);
            $hasOut = $hasOut || $this->isOutboundItem($item);
        }

        return $hasIn && $hasOut;
    }

    protected function isInboundItem(array $item): bool
    {
        $haystack = $this->normalizeText(($item['name'] ?? '') . ' ' . ($item['key_'] ?? ''));

        foreach (['received', 'in bits', 'inbound', 'ifin', 'net.if.in', 'incoming'] as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function isOutboundItem(array $item): bool
    {
        $haystack = $this->normalizeText(($item['name'] ?? '') . ' ' . ($item['key_'] ?? ''));

        foreach (['sent', 'out bits', 'outbound', 'ifout', 'net.if.out', 'outgoing'] as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeText(string $value): string
    {
        return strtolower(trim($value));
    }
}
