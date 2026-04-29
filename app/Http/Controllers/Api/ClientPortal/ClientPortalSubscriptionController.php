<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalAccount;
use App\Models\Subscription;
use App\Services\ZabbixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ClientPortalSubscriptionController extends Controller
{
    public function __construct(
        protected ZabbixService $zabbixService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        $subscriptions = Subscription::query()
            ->with(['package', 'connectivity'])
            ->where('client_id', $account->client_id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $subscriptions->map(fn ($subscription) => [
                'id' => $subscription->id,
                'subscription_code' => $subscription->subscription_code,
                'status' => $subscription->status,
                'package_name' => $subscription->package?->name,
                'service_name' => $subscription->package?->service?->name,
                'next_billing_date' => $subscription->next_billing_date?->toDateString(),
                'effective_price' => (float) $subscription->effective_price,
                'has_usage' => ! empty($subscription->connectivity?->zabbix_interfaces),
            ]),
        ]);
    }

    public function show(Request $request, Subscription $subscription): JsonResponse
    {
        $subscription = $this->authorizedSubscription($request, $subscription);
        $subscription->load(['package.service', 'connectivity']);

        return response()->json([
            'data' => [
                'id' => $subscription->id,
                'subscription_code' => $subscription->subscription_code,
                'status' => $subscription->status,
                'installed_at' => $subscription->installed_at?->toDateString(),
                'next_billing_date' => $subscription->next_billing_date?->toDateString(),
                'package' => $subscription->package ? [
                    'id' => $subscription->package->id,
                    'name' => $subscription->package->name,
                    'service_name' => $subscription->package->service?->name,
                ] : null,
                'connectivity' => $subscription->connectivity ? [
                    'ip_address' => $subscription->connectivity->ip_address,
                    'pppoe_user' => $subscription->connectivity->pppoe_user,
                    'zabbix_group_name' => $subscription->connectivity->zabbix_group_name,
                    'zabbix_host_name' => $subscription->connectivity->zabbix_host_name,
                ] : null,
            ],
        ]);
    }

    public function usage(Request $request, Subscription $subscription): JsonResponse
    {
        $subscription = $this->authorizedSubscription($request, $subscription);

        return response()->json([
            'subscription_id' => $subscription->id,
            'subscription_code' => $subscription->subscription_code,
            'service_name' => $subscription->package?->service?->name,
            'package_name' => $subscription->package?->name,
            'interfaces' => array_values($subscription->connectivity?->zabbix_interfaces ?? []),
        ]);
    }

    public function chart(Request $request, Subscription $subscription): JsonResponse
    {
        $subscription = $this->authorizedSubscription($request, $subscription);

        $validated = $request->validate([
            'graphid' => 'nullable|string',
            'itemin' => 'nullable|string',
            'itemout' => 'nullable|string',
            'mode' => 'nullable|in:preset,custom',
            'period' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $selectedInterface = $this->resolveInterfaceSelection($subscription, $validated);

        try {
            $payload = $this->zabbixService->getBandwidthData(
                $selectedInterface['itemIn'],
                $selectedInterface['itemOut'],
                $validated['mode'] ?? 'preset',
                $validated['period'] ?? '24h',
                $validated['from'] ?? null,
                $validated['to'] ?? null
            );

            $payload['interface'] = [
                'graphid' => $selectedInterface['graphid'] ?? null,
                'name' => $selectedInterface['name'] ?? null,
                'itemIn' => $selectedInterface['itemIn'],
                'itemOut' => $selectedInterface['itemOut'],
            ];

            return response()->json($payload);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function authorizedSubscription(Request $request, Subscription $subscription): Subscription
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        abort_unless($subscription->client_id === $account->client_id, 404);

        return $subscription->loadMissing(['package.service', 'connectivity']);
    }

    private function resolveInterfaceSelection(Subscription $subscription, array $validated): array
    {
        $interfaces = array_values($subscription->connectivity?->zabbix_interfaces ?? []);

        if (empty($interfaces)) {
            throw ValidationException::withMessages([
                'graphid' => 'Subscription ini belum memiliki interface monitoring.',
            ]);
        }

        foreach ($interfaces as $interface) {
            $graphMatches = isset($validated['graphid']) && ($interface['graphid'] ?? null) === $validated['graphid'];
            $itemMatches = isset($validated['itemin'], $validated['itemout'])
                && ($interface['itemIn'] ?? null) === $validated['itemin']
                && ($interface['itemOut'] ?? null) === $validated['itemout'];

            if ($graphMatches || $itemMatches) {
                return $interface;
            }
        }

        if (! isset($validated['graphid'], $validated['itemin'], $validated['itemout'])) {
            return $interfaces[0];
        }

        throw ValidationException::withMessages([
            'graphid' => 'Interface monitoring yang dipilih tidak valid untuk subscription ini.',
        ]);
    }
}
