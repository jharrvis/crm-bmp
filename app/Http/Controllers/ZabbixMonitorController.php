<?php

namespace App\Http\Controllers;

use App\Services\ZabbixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ZabbixMonitorController extends Controller
{
    public function __construct(
        protected ZabbixService $zabbixService
    ) {
        $this->middleware('permission:zabbix_monitors.view');
    }

    public function index()
    {
        return view('zabbix_monitors.index');
    }

    public function groups(): JsonResponse
    {
        try {
            return response()->json($this->zabbixService->getGroups());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function hosts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupid' => 'nullable|string',
        ]);

        try {
            return response()->json($this->zabbixService->getHosts($validated['groupid'] ?? null));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function graphs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hostid' => 'required|string',
        ]);

        try {
            return response()->json($this->zabbixService->getGraphs($validated['hostid']));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function chartData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'itemin' => 'required|string',
            'itemout' => 'required|string',
            'mode' => 'nullable|in:preset,custom',
            'period' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $itemIn = $validated['itemin'];
        $itemOut = $validated['itemout'];
        $mode = $validated['mode'] ?? 'preset';
        $period = $validated['period'] ?? '24h';
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        try {
            $payload = $this->zabbixService->getBandwidthData($itemIn, $itemOut, $mode, $period, $from, $to);

            return response()->json($payload);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
