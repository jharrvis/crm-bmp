<?php

namespace App\Jobs;

use App\Models\HostingServer;
use App\Models\HostingServerSnapshot;
use App\Models\SubscriptionHosting;
use App\Services\WebHostResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class RefreshHestiaServerSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $serverId)
    {
    }

    public function handle(WebHostResolver $resolver): void
    {
        $lock = Cache::lock("hestiacp:snapshot:{$this->serverId}", 120);

        if (! $lock->get()) {
            return;
        }

        try {
        $server = HostingServer::findOrFail($this->serverId);

        if (! $server->is_active || $server->type !== 'hestiacp') {
            throw new \RuntimeException('Server bukan HestiaCP aktif.');
        }

        $snapshot = HostingServerSnapshot::create([
            'hosting_server_id' => $server->id,
            'status' => 'pending',
            'summary_json' => null,
            'is_active' => false,
        ]);

        $service = $resolver->resolve($server);
        $usersResult = $service->listUsers();

        if (! $usersResult['success']) {
            $snapshot->update([
                'status' => 'failed',
                'error_message' => 'Gagal mengambil data user dari server HestiaCP.',
            ]);

            throw new \RuntimeException($usersResult['message'] ?? 'Gagal mengambil user.');
        }

        $users = (array) $usersResult['data'];

        $linkedUsernames = SubscriptionHosting::where('hosting_server_id', $server->id)
            ->whereNotNull('username')
            ->pluck('username')
            ->map(fn ($name) => strtolower($name))
            ->values();

        $summary = [
            'total_users' => count($users),
            'linked_users' => $linkedUsernames->filter(fn ($name) => isset($users[$name]))->count(),
            'domain_count' => 0,
            'capacity' => $server->max_accounts,
            'suspended_users' => collect($users)->filter(fn ($user) => ! empty($user['suspended']))->count(),
        ];

        DB::transaction(function () use ($snapshot, $server, $summary) {
            HostingServerSnapshot::where('hosting_server_id', $server->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $snapshot->update([
                'status' => 'synced',
                'summary_json' => $summary,
                'last_synced_at' => now(),
                'error_message' => null,
                'is_active' => true,
            ]);
        });
        } finally {
            $lock->release();
        }
    }

    public function failed(Throwable $exception): void
    {
        HostingServerSnapshot::where('hosting_server_id', $this->serverId)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'error_message' => 'Gagal menyegarkan snapshot server HestiaCP.',
            ]);
    }
}
