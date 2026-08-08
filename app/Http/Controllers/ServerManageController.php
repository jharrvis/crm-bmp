<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteHostingAccountJob;
use App\Jobs\RefreshHestiaServerSnapshotJob;
use App\Jobs\ResetHostingAccountPasswordJob;
use App\Jobs\SetHostingAccountStatusJob;
use App\Models\HostingServer;
use App\Models\HostingServerSnapshot;
use App\Models\Subscription;
use App\Models\SubscriptionHosting;
use App\Services\WebHostResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ServerManageController extends Controller
{
    protected const USERNAME_PATTERN = '/^[a-zA-Z0-9_]{1,32}$/';
    protected const NEW_USERNAME_PATTERN = '/^[a-z][a-z0-9_]{0,31}$/';

    public function __construct(protected WebHostResolver $resolver)
    {
    }

    protected function authorizeServer(HostingServer $server): void
    {
        abort_unless($server->is_active && $server->type === 'hestiacp', 404, 'Server HestiaCP tidak ditemukan atau tidak aktif.');
    }

    protected function ensureHestiaUserExists(HostingServer $server, string $username): void
    {
        $result = $this->resolver->resolve($server)->findUser($username);

        if (! $result['success'] || $result['data'] === null) {
            abort(422, 'User tidak ditemukan di server HestiaCP.');
        }
    }

    /**
     * Manage overview page: snapshot summary + test/refresh actions.
     */
    public function show(HostingServer $server)
    {
        $this->authorize('servers.manage');

        $this->authorizeServer($server);

        $snapshot = HostingServerSnapshot::where('hosting_server_id', $server->id)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $linkableSubscriptions = Subscription::with(['client', 'package.service'])
            ->where('status', 'active')
            ->whereDoesntHave('hosting')
            ->get()
            ->filter(fn ($subscription) => $subscription->package?->service?->type === 'hosting')
            ->values();

        return view('servers.manage', compact('server', 'snapshot', 'linkableSubscriptions'));
    }

    /**
     * Test connection to the server.
     */
    public function testConnection(HostingServer $server)
    {
        $this->authorize('servers.connect');

        $this->authorizeServer($server);

        $result = $this->resolver->resolve($server)->testConnection();

        activity('servers')
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->withProperties(['server_id' => $server->id, 'status' => $result['success'] ? 'ok' : 'error'])
            ->log($result['success'] ? 'Tes koneksi server HestiaCP berhasil' : 'Tes koneksi server HestiaCP gagal');

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Koneksi ke server berhasil.'
                : 'Koneksi ke server gagal. Periksa kredensial dan IP whitelist.',
        ]);
    }

    /**
     * Queue a fresh snapshot for the server.
     */
    public function refresh(HostingServer $server)
    {
        $this->authorize('servers.manage');

        $this->authorizeServer($server);

        RefreshHestiaServerSnapshotJob::dispatch($server->id)->afterCommit();

        return back()->with('success', 'Refresh data server masuk antrean.');
    }

    /**
     * User list page with live data and short cache.
     */
    public function users(HostingServer $server, Request $request)
    {
        $this->authorize('servers.manage');

        $this->authorizeServer($server);

        $search = strtolower(trim((string) $request->query('search')));

        $loadError = null;
        $users = [];

        try {
            $users = Cache::remember("hestiacp:users:{$server->id}", 120, function () use ($server) {
                $result = $this->resolver->resolve($server)->listUsers();

                if (! $result['success']) {
                    throw new \RuntimeException($result['message'] ?? 'Gagal mengambil data user.');
                }

                return (array) $result['data'];
            });
        } catch (\Throwable $e) {
            $loadError = 'Data user tidak dapat dimuat saat ini. Periksa koneksi dan kredensial server.';
        }

        $hostingMap = SubscriptionHosting::where('hosting_server_id', $server->id)
            ->whereNotNull('username')
            ->get()
            ->keyBy(fn ($item) => strtolower((string) $item->username));

        $users = collect($users)
            ->map(function ($user, $username) use ($hostingMap, $server) {
                $linked = $hostingMap->get(strtolower($username));

                return [
                    'username' => $username,
                    'email' => $user['email'] ?? null,
                    'name' => $user['name'] ?? null,
                    'package' => $user['package'] ?? null,
                    'suspended' => (bool) ($user['suspended'] ?? false),
                    'linked' => $linked !== null,
                    'managed_by_crm' => (bool) ($linked?->managed_by_crm ?? false),
                    'provisioning_status' => $linked?->provisioning_status,
                    'lifecycle_available' => (bool) ($linked?->managed_by_crm
                        && $linked?->remote_user_created_at
                        && $linked?->provisioning_status === 'ready'),
                    'subscription_id' => $linked?->subscription_id,
                    'domain' => $linked?->domain,
                    'hosting_id' => $linked?->id,
                ];
            })
            ->when($search !== '', fn ($collection) => $collection->filter(
                fn ($user) => str_contains(strtolower($user['username']), $search)
                    || str_contains(strtolower((string) $user['email']), $search)
                    || str_contains(strtolower((string) $user['name']), $search)
            ))
            ->values();

        return view('servers.users', compact('server', 'users', 'search', 'loadError'));
    }

    /**
     * Link an existing Hestia user to a subscription with hosting service.
     */
    public function link(HostingServer $server, Request $request)
    {
        $this->authorize('servers.provision');

        $this->authorizeServer($server);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:32', 'regex:'.self::USERNAME_PATTERN],
            'subscription_id' => ['required', 'exists:subscriptions,id'],
        ]);

        $username = strtolower($validated['username']);

        $subscription = Subscription::with('package.service')->findOrFail($validated['subscription_id']);

        abort_unless($subscription->package?->service?->type === 'hosting', 422, 'Subscription tidak memiliki jenis layanan hosting.');
        abort_if($subscription->hosting()->exists(), 422, 'Subscription sudah memiliki detail hosting.');

        $alreadyLinked = SubscriptionHosting::where('hosting_server_id', $server->id)
            ->where('username', $username)
            ->exists();

        abort_if($alreadyLinked, 422, 'User tersebut sudah terhubung pada server ini.');

        $this->ensureHestiaUserExists($server, $username);

        $hosting = SubscriptionHosting::create([
            'subscription_id' => $subscription->id,
            'hosting_server_id' => $server->id,
            'username' => $username,
            'provisioning_status' => 'ready',
            'provisioning_error' => null,
            'managed_by_crm' => false,
            'provisioned_at' => now(),
        ]);

        activity('servers')
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->withProperties(['server_id' => $server->id, 'username' => $username, 'subscription_id' => $subscription->id])
            ->log('Menautkan user HestiaCP ke subscription');

        return back()->with('success', "User {$username} berhasil ditautkan ke subscription.");
    }

    /**
     * Suspend a CRM-managed account.
     */
    public function suspend(HostingServer $server, Request $request)
    {
        $this->authorize('servers.suspend');

        $this->authorizeServer($server);

        $hosting = $this->resolveManagedHosting($server, $request);

        SetHostingAccountStatusJob::dispatch($hosting->id, false)->afterCommit();

        activity('servers')
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->withProperties(['server_id' => $server->id, 'username' => $hosting->username, 'action' => 'suspend'])
            ->log('Permintaan menonaktifkan akun HestiaCP dikirim');

        return back()->with('success', 'Permintaan menonaktifkan akun masuk antrean.');
    }

    /**
     * Activate a CRM-managed account.
     */
    public function activate(HostingServer $server, Request $request)
    {
        $this->authorize('servers.suspend');

        $this->authorizeServer($server);

        $hosting = $this->resolveManagedHosting($server, $request);

        SetHostingAccountStatusJob::dispatch($hosting->id, true)->afterCommit();

        activity('servers')
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->withProperties(['server_id' => $server->id, 'username' => $hosting->username, 'action' => 'activate'])
            ->log('Permintaan mengaktifkan akun HestiaCP dikirim');

        return back()->with('success', 'Permintaan mengaktifkan akun masuk antrean.');
    }

    /**
     * Reset password of a CRM-managed account.
     */
    public function resetPassword(HostingServer $server, Request $request)
    {
        $this->authorize('servers.reset_password');

        $this->authorizeServer($server);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:32', 'regex:'.self::USERNAME_PATTERN],
            'password' => 'required|string|min:8|max:255',
        ]);

        $hosting = $this->findManagedHosting($server, strtolower($validated['username']));

        $hosting->update(['password_encrypted' => $validated['password']]);

        ResetHostingAccountPasswordJob::dispatch($hosting->id)->afterCommit();

        activity('servers')
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->withProperties(['server_id' => $server->id, 'username' => $hosting->username, 'action' => 'reset_password'])
            ->log('Permintaan reset password akun HestiaCP dikirim');

        return back()->with('success', 'Permintaan reset password masuk antrean.');
    }

    /**
     * Delete a CRM-managed account.
     */
    public function destroy(HostingServer $server, Request $request)
    {
        $this->authorize('servers.delete_user');

        abort_unless(auth()->user()?->hasRole('Owner'), 403, 'Penghapusan akun hosting hanya tersedia untuk Owner.');

        $this->authorizeServer($server);

        $hosting = $this->resolveManagedHosting($server, $request);

        abort_if($hosting->username === 'admin', 422, 'User admin sistem tidak boleh dihapus.');

        $confirmation = strtolower(trim((string) $request->input('confirmation')));
        abort_unless(hash_equals($hosting->username, $confirmation), 422, 'Ketik ulang username akun untuk mengonfirmasi penghapusan.');

        $hosting->update([
            'provisioning_status' => 'deleting',
            'provisioning_error' => null,
            'delete_requested_at' => now(),
        ]);

        DeleteHostingAccountJob::dispatch($hosting->id)->afterCommit();

        activity('servers')
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->withProperties(['server_id' => $server->id, 'username' => $hosting->username, 'action' => 'delete'])
            ->log('Permintaan menghapus akun HestiaCP dikirim');

        return back()->with('success', 'Permintaan menghapus akun masuk antrean.');
    }

    protected function resolveManagedHosting(HostingServer $server, Request $request): SubscriptionHosting
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:32', 'regex:'.self::USERNAME_PATTERN],
        ]);

        $hosting = $this->findManagedHosting($server, strtolower($validated['username']));

        abort_if(! $hosting->managed_by_crm || ! $hosting->remote_user_created_at || $hosting->provisioning_status !== 'ready', 422, 'Akun belum siap atau tidak dikelola CRM.');

        return $hosting;
    }

    protected function findManagedHosting(HostingServer $server, string $username): SubscriptionHosting
    {
        $hosting = SubscriptionHosting::where('hosting_server_id', $server->id)
            ->where('username', $username)
            ->first();

        abort_unless($hosting, 422, 'User tidak terhubung pada server ini.');

        return $hosting;
    }
}
