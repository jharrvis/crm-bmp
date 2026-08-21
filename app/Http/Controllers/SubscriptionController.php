<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\HostingServer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MetroEthernet;
use App\Models\Package;
use App\Models\Router;
use App\Models\Subscription;
use App\Models\SubscriptionConnectivity;
use App\Models\SubscriptionDomain;
use App\Models\SubscriptionHosting;
use App\Models\SubscriptionMailHosting;
use App\Models\Vendor;
use App\Jobs\EnsureMailDomainJob;
use App\Jobs\ProvisionHostingAccountJob;
use App\Jobs\ResetHostingAccountPasswordJob;
use App\Jobs\SetMailboxStatusJob;
use App\Services\ProrataCalculationService;
use App\Services\WebHostResolver;
use App\Services\ZabbixService;
use App\Services\ZimbraMailboxSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function __construct(
        protected ZabbixService $zabbixService,
        protected WebHostResolver $webHostResolver,
        protected ZimbraMailboxSyncService $zimbraMailboxSyncService
    ) {
        $this->middleware('permission:subscriptions.view')->only(['index', 'show']);
        $this->middleware('permission:subscriptions.create')->only(['store']);
        $this->middleware('permission:subscriptions.update')->only(['update']);
        $this->middleware('permission:subscriptions.delete')->only(['destroy']);
        $this->middleware('permission:subscriptions.create|subscriptions.update')->only(['hestiaUsers', 'hestiaUserDomains', 'clientMailDomains']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['client', 'package.service'])->latest();

        if ($request->has('service_id') && $request->service_id) {
            $query->whereHas('package', function ($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }

        if ($request->has('type') && $request->type) {
            $query->whereHas('package.service', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        $subscriptions = $query->get();
        // Pre-fetch data for modals
        $clients = Client::orderBy('name')->get();
        $packages = Package::with('service')->where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();
        $servers = HostingServer::where('is_active', true)->get();
        $mailServers = HostingServer::where('is_active', true)->where('type', 'zimbra')->get();
        // Fetch Metro Ethernets with Vendor for selection
        $metroEthernets = MetroEthernet::with('vendor')->latest()->get();
        $registrarAccounts = \App\Models\RegistrarAccount::where('is_active', true)->get();

        return view('subscriptions.index', compact('subscriptions', 'clients', 'packages', 'routers', 'servers', 'metroEthernets', 'mailServers', 'registrarAccounts'));
    }

    /**
     * Return only Hestia usernames for the existing-account picker.
     */
    public function hestiaUsers(HostingServer $server)
    {
        $this->ensureActiveHestiaServer($server);

        $cacheKey = "hestiacp:{$server->id}:linkable-users";
        $usernames = Cache::get($cacheKey);

        if ($usernames === null) {
            $result = $this->webHostResolver->resolve($server)->listUsers();

            if (! $result['success']) {
                return response()->json(['message' => 'Daftar user HestiaCP tidak dapat dimuat. Periksa koneksi dan permission API.'], 422);
            }

            $usernames = collect(array_keys((array) $result['data']))
                ->map(fn ($username) => strtolower((string) $username))
                ->filter()
                ->sort()
                ->values()
                ->all();

            Cache::put($cacheKey, $usernames, now()->addMinutes(2));
        }

        return response()->json(['users' => $usernames]);
    }

    /**
     * Return only domains owned by a selected Hestia user.
     */
    public function hestiaUserDomains(HostingServer $server, Request $request)
    {
        $this->ensureActiveHestiaServer($server);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9_]{1,32}$/'],
        ]);

        $username = strtolower($validated['username']);
        $cacheKey = "hestiacp:{$server->id}:user-domains:{$username}";
        $domains = Cache::get($cacheKey);

        if ($domains === null) {
            $result = $this->webHostResolver->resolve($server)->listWebDomains($username);

            if (! $result['success']) {
                return response()->json(['message' => 'Domain user HestiaCP tidak dapat dimuat. Periksa koneksi dan permission API.'], 422);
            }

            $domains = collect((array) $result['data'])
                ->map(fn ($domain) => strtolower(trim((string) $domain)))
                ->filter()
                ->sort()
                ->values()
                ->all();

            Cache::put($cacheKey, $domains, now()->addMinutes(2));
        }

        return response()->json(['domains' => $domains]);
    }

    /**
     * Return domains recorded under a client's domain subscriptions.
     */
    public function clientMailDomains(Client $client)
    {
        $domains = SubscriptionDomain::query()
            ->whereHas('subscription', fn ($query) => $query->where('client_id', $client->id))
            ->whereNotNull('domain_name')
            ->orderBy('domain_name')
            ->pluck('domain_name')
            ->map(fn ($domain) => strtolower(trim((string) $domain)))
            ->filter()
            ->unique()
            ->values();

        return response()->json(['domains' => $domains]);
    }

    /**
     * Return the locally encrypted mail-admin password only to server operators.
     */
    public function mailHostingAdminCredential(Subscription $subscription)
    {
        $this->authorize('servers.manage');

        $mailHosting = $subscription->mailHosting()->with('mailServer')->first();
        abort_if(! $mailHosting, 404, 'Langganan ini tidak memiliki layanan mail hosting.');
        abort_if(! $mailHosting->mailServer, 404, 'Server mail hosting tidak ditemukan.');

        activity('servers')
            ->performedOn($mailHosting->mailServer)
            ->causedBy(auth()->user())
            ->withProperties([
                'subject_label' => $mailHosting->mailServer->name,
                'event_label' => 'Akses kredensial admin mail hosting',
            ])
            ->log('Menyalin password admin mail hosting');

        return response()->json([
            'admin_email' => $mailHosting->mailServer->username ?: $mailHosting->mailServer->api_key,
            'password' => $mailHosting->mailServer->secret_key,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Initial validation
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'package_id' => 'required|exists:packages,id',
            'installed_at' => 'required|date',
            'status' => 'required|string|in:pending,active,suspended,terminated',
            'uses_ppn' => 'nullable|boolean',
            'uses_pph23' => 'nullable|boolean',
        ]);

        $package = Package::with('service')->findOrFail($request->package_id);
        $serviceType = $package->service->type; // connectivity or hosting

        // Conditional Validation based on Service Type
        if ($serviceType === 'connectivity') {
            $request->validate([
                'router_id' => 'nullable|exists:routers,id',
                'ip_address' => 'nullable|ipv4',
                'pppoe_user' => 'nullable|string|max:100',
                'ont_sn' => 'nullable|string|max:100',
                'vlan_id' => 'nullable|integer|min:1|max:4094',
                'zabbix_group_id' => 'nullable|string|max:255',
                'zabbix_group_name' => 'nullable|string|max:255',
                'zabbix_host_id' => 'nullable|string|max:255',
                'zabbix_host_name' => 'nullable|string|max:255',
                'zabbix_interfaces' => 'nullable|array',
                'zabbix_interfaces.*.graphid' => 'required_with:zabbix_interfaces|string|max:255',
                'zabbix_interfaces.*.name' => 'required_with:zabbix_interfaces|string|max:255',
                'zabbix_interfaces.*.itemIn' => 'required_with:zabbix_interfaces|string|max:255',
                'zabbix_interfaces.*.itemOut' => 'required_with:zabbix_interfaces|string|max:255',
                // Metro Ethernet Validation
                'metro_option' => 'nullable|string|in:existing,new',
                'metro_ethernet_id' => 'nullable|required_if:metro_option,existing|exists:metro_ethernets,id',
                'metro_name' => 'nullable|required_if:metro_option,new|string|max:255',
                'metro_vendor_id' => 'nullable|required_if:metro_option,new|exists:vendors,id',
                'metro_bandwidth' => 'nullable|required_if:metro_option,new|integer|min:0',
            ]);
        } elseif ($serviceType === 'hosting') {
            $request->mergeIfMissing(['hosting_account_mode' => 'new']);
            $hostingAccountMode = $request->input('hosting_account_mode', 'new');

            if ($hostingAccountMode === 'new' && blank($package->hestia_package)) {
                throw ValidationException::withMessages([
                    'package_id' => 'Paket hosting belum memiliki mapping paket HestiaCP.',
                ]);
            }

            $request->validate([
                'hosting_account_mode' => ['required', Rule::in(['new', 'existing'])],
                'hosting_server_id' => ['required', Rule::exists('hosting_servers', 'id')->where(fn ($query) => $query->where('type', 'hestiacp')->where('is_active', true))],
                'domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
                'username' => $hostingAccountMode === 'existing'
                    ? ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9_]{1,32}$/']
                    : ['required', 'string', 'max:32', 'regex:/^[a-z][a-z0-9_]{0,31}$/'],
                'password' => $hostingAccountMode === 'new' ? 'required|string|min:8|max:255' : 'nullable',
            ]);
        } elseif ($serviceType === 'domain') {
            $request->mergeIfMissing(['domain_account_mode' => 'new']);
            $domainAccountMode = $request->input('domain_account_mode', 'new');

            $request->validate([
                'domain_account_mode' => ['required', Rule::in(['new', 'existing'])],
                'registrar_account_id' => [
                    $domainAccountMode === 'existing' ? 'required' : 'nullable',
                    Rule::exists('registrar_accounts', 'id')->where(fn ($q) => $q->where('is_active', true)),
                ],
                'domain_name' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
                'registrar' => 'nullable|string|max:255',
                'registered_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:registered_at',
                'auth_code' => 'nullable|string|max:255',
                'domain_notes' => 'nullable|string',
            ]);

            // TLD soft warning (gTLD vs ccTLD) — not blocking, just flash warning via validation message
            if ($domainAccountMode === 'existing' && $request->filled('registrar_account_id') && config('domain-registrars.enabled')) {
                $account = \App\Models\RegistrarAccount::find($request->registrar_account_id);
                if ($account) {
                    $allowed = $account->allowedTlds();
                    if (! empty($allowed)) {
                        $lowerDomain = strtolower($request->domain_name);
                        $matched = collect($allowed)->contains(fn ($tld) => str_ends_with($lowerDomain, strtolower($tld)));
                        if (! $matched) {
                            session()->flash('warning', "TLD domain {$request->domain_name} tidak termasuk daftar akun {$account->name} (".implode(', ', $allowed)."). Periksa kembali pilihan akun gTLD/ccTLD.");
                        }
                    }
                }
            }
        } elseif ($serviceType === 'mail') {
            $request->validate([
                'mail_server_id' => ['required', Rule::exists('hosting_servers', 'id')->where(fn ($query) => $query->where('type', 'zimbra')->where('is_active', true))],
                'mail_domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
                'admin_email' => 'nullable|email|max:255',
                'admin_password' => 'nullable|string|max:255',
            ]);

            $domain = strtolower(trim($request->mail_domain));
            if (SubscriptionMailHosting::where('mail_server_id', $request->mail_server_id)->where('domain', $domain)->exists()) {
                throw ValidationException::withMessages([
                    'mail_domain' => 'Domain tersebut sudah digunakan oleh layanan mail hosting lain pada server ini.',
                ]);
            }
        }

        $mailDomainProvisioningId = null;
        $hostingProvisioningId = null;
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($request->client_id);
            $code = $this->generateSubscriptionCode($client, $package);
            $billingPeriodMonths = (int) ($request->billing_period_months ?? 1);
            $basePrice = (float) ($request->filled('custom_price')
                ? $request->custom_price
                : ($package->price * $billingPeriodMonths));
            $usesPpn = $request->boolean('uses_ppn');
            $ppnAmount = $usesPpn ? Subscription::calculatePpnAmount($basePrice) : null;
            $usesPph23 = $request->boolean('uses_pph23');
            $pph23Amount = $usesPph23 ? Subscription::calculatePph23Amount($basePrice) : null;

            // Calculate billing details (simple logic)
            $billingDay = \Carbon\Carbon::parse($request->installed_at)->day;
            // Limit to day 28 to avoid issues? Or standard Laravel implementation handles it.
            // Let's stick to true day.

            $nextBilling = \Carbon\Carbon::parse($request->installed_at)->addMonth();

            $subscription = Subscription::create([
                'client_id' => $request->client_id,
                'package_id' => $request->package_id,
                'subscription_code' => $code,
                'status' => $request->status,
                'installed_at' => $request->installed_at,
                'billing_cycle_day' => $billingDay,
                'next_billing_date' => $nextBilling,
                'price_at_subscription' => $package->price, // Lock price
                'custom_price' => $request->custom_price,
                'billing_period_months' => $billingPeriodMonths,
                'uses_ppn' => $usesPpn,
                'ppn_amount' => $ppnAmount,
                'uses_pph23' => $usesPph23,
                'pph23_amount' => $pph23Amount,
                'discount_percent' => null,
                'discount_notes' => null,
                'notes' => $request->notes,
            ]);

            // Save Details
            if ($serviceType === 'connectivity') {
                $connData = [
                    'subscription_id' => $subscription->id,
                    'router_id' => $request->router_id,
                    'ip_address' => $request->ip_address,
                    'ip_type' => $request->ip_type ?? 'dynamic',
                    'pppoe_user' => $request->pppoe_user,
                    'pppoe_secret' => $request->pppoe_secret ? encrypt($request->pppoe_secret) : null,
                    'ont_sn' => $request->ont_sn,
                    'vlan_id' => $request->vlan_id,
                    'signal_rx' => $request->signal_rx,
                    'zabbix_group_id' => $request->zabbix_group_id,
                    'zabbix_group_name' => $request->zabbix_group_name,
                    'zabbix_host_id' => $request->zabbix_host_id,
                    'zabbix_host_name' => $request->zabbix_host_name,
                    'zabbix_interfaces' => $this->validatedZabbixInterfaces(
                        $request->zabbix_host_id,
                        $request->input('zabbix_interfaces', [])
                    ),
                ];

                // Handle Metro Ethernet Logic
                if ($request->filled('metro_option')) {
                    if ($request->metro_option === 'new') {
                        $metro = MetroEthernet::create([
                            'name' => $request->metro_name,
                            'vendor_id' => $request->metro_vendor_id,
                            'cid' => $request->metro_cid,
                            'ip_address' => $request->metro_ip_address,
                            'bandwidth' => $request->metro_bandwidth,
                        ]);
                        $connData['metro_ethernet_id'] = $metro->id;
                    } else {
                        $connData['metro_ethernet_id'] = $request->metro_ethernet_id;
                    }
                }

                SubscriptionConnectivity::create($connData);
            } elseif ($serviceType === 'hosting') {
                $request->mergeIfMissing(['hosting_account_mode' => 'new']);
                $hostingAccountMode = $request->input('hosting_account_mode', 'new');

                if ($hostingAccountMode === 'new' && blank($package->hestia_package)) {
                    throw ValidationException::withMessages([
                        'package_id' => 'Paket hosting belum memiliki mapping paket HestiaCP.',
                    ]);
                }

                $server = HostingServer::findOrFail($request->hosting_server_id);

                abort_unless($server->is_active && $server->type === 'hestiacp', 422, 'Server hosting tidak aktif atau bukan HestiaCP.');

                $hostingUsername = strtolower(trim((string) $request->username));
                $hostingDomain = strtolower(trim((string) $request->domain));

                $this->ensureHostingUsernameIsAvailable($server, $hostingUsername);

                if ($hostingAccountMode === 'existing') {
                    $this->ensureExistingHestiaAccountOwnsDomain($server, $hostingUsername, $hostingDomain);
                }

                $hosting = SubscriptionHosting::create([
                    'subscription_id' => $subscription->id,
                    'hosting_server_id' => $request->hosting_server_id,
                    'domain' => $hostingDomain,
                    'username' => $hostingUsername,
                    'password_encrypted' => $hostingAccountMode === 'new' ? encrypt($request->password) : null,
                    'disk_quota_gb' => $request->disk_quota_gb ?? 0,
                    'email_accounts' => $request->email_accounts ?? 0,
                    'databases' => $request->databases ?? 0,
                    'ssl_expiry' => $request->ssl_expiry,
                    'provisioning_status' => $hostingAccountMode === 'new' ? 'pending' : 'ready',
                    'managed_by_crm' => $hostingAccountMode === 'new',
                    'hestia_package' => $hostingAccountMode === 'new' ? $package->hestia_package : null,
                    'provisioned_at' => $hostingAccountMode === 'existing' ? now() : null,
                ]);

                if ($hostingAccountMode === 'new') {
                    $hostingProvisioningId = $hosting->id;
                } else {
                    activity('subscriptions')
                        ->performedOn($subscription)
                        ->causedBy(auth()->user())
                        ->withProperties(['hosting_server_id' => $server->id, 'username' => $hostingUsername, 'domain' => $hostingDomain])
                        ->log('Menautkan akun HestiaCP existing ke layanan hosting');
                }
            } elseif ($serviceType === 'domain') {
                $domainAccountMode = $request->input('domain_account_mode', 'new');
                $normalizedDomain = strtolower(trim($request->domain_name));

                // Unique per-account check (like ensureHostingUsernameIsAvailable)
                if ($request->filled('registrar_account_id')) {
                    $conflict = \App\Models\SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$normalizedDomain])
                        ->where('registrar_account_id', $request->registrar_account_id)
                        ->exists();
                    if ($conflict) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'domain_name' => 'Domain tersebut sudah tertaut pada akun registrar ini.',
                        ]);
                    }
                }

                $domain = SubscriptionDomain::create([
                    'subscription_id' => $subscription->id,
                    'domain_name' => $normalizedDomain,
                    'registrar' => $request->registrar,
                    'auth_code_encrypted' => $request->auth_code ? encrypt($request->auth_code) : null,
                    'registered_at' => $request->registered_at,
                    'expires_at' => $request->expires_at,
                    'notes' => $request->domain_notes,
                    'registrar_account_id' => $request->registrar_account_id,
                    'domain_account_mode' => $domainAccountMode,
                    'managed_by_crm' => false, // Fase 1 read-only untuk kedua mode
                    'sync_status' => $request->filled('registrar_account_id') ? 'pending' : null,
                ]);

                if ($domain->registrar_account_id && config('domain-registrars.enabled')) {
                    // Queue sync to verify existence and fetch metadata (read-only)
                    \App\Jobs\SyncRegistrarDomain::dispatch($domain->id)->afterCommit();
                    activity('subscriptions')->performedOn($subscription)->causedBy(auth()->user())
                        ->withProperties(['domain' => $normalizedDomain, 'registrar_account_id' => $domain->registrar_account_id, 'mode' => $domainAccountMode])
                        ->log($domainAccountMode === 'existing' ? 'Menautkan domain existing dari registrar' : 'Mencatat domain baru (mode new, belum registrasi ke provider)');
                }
            } elseif ($serviceType === 'mail') {
                $server = HostingServer::findOrFail($request->mail_server_id);
                $mailHosting = SubscriptionMailHosting::create([
                    'subscription_id' => $subscription->id,
                    'mail_server_id' => $request->mail_server_id,
                    'domain' => strtolower(trim($request->mail_domain)),
                    'admin_email' => $request->admin_email,
                    'admin_password_encrypted' => $request->filled('admin_password')
                        ? $request->admin_password
                        : null,
                    'max_mailboxes' => $package->max_mailboxes ?? 0,
                    'mailbox_quota_mb' => $package->mailbox_quota_mb ?? 0,
                    'alias_max' => $package->alias_max ?? 0,
                    'mail_server_type' => $server->type,
                    'status' => 'active',
                    // Zimbra is integrated read-only: CRM records the service without provisioning the remote domain.
                    'provisioning_status' => $server->type === 'zimbra' ? 'ready' : 'pending',
                ]);
                $mailDomainProvisioningId = $server->type === 'zimbra' ? null : $mailHosting->id;
            }

            $prorataService = new ProrataCalculationService;
            $prorataItems = $prorataService->calculateNewSubscription($subscription);

            if ($prorataItems) {
                $subtotal = collect($prorataItems)->sum('amount');
                $taxAmount = $usesPpn ? round($subtotal * (\App\Models\SystemSetting::get('billing.ppn_rate', 11) / 100), 2) : 0;
                $totalAmount = $subtotal + $taxAmount;

                $branchCode = $client->branch ? $client->branch->code : 'GEN';
                $invoice = Invoice::create([
                    'client_id' => $subscription->client_id,
                    'invoice_number' => Invoice::generateInvoiceNumber($branchCode),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(\App\Models\SystemSetting::get('billing.default_due_days', 7)),
                    'subtotal_amount' => $subtotal,
                    'uses_tax' => $usesPpn,
                    'tax_rate' => $usesPpn ? \App\Models\SystemSetting::get('billing.ppn_rate', 11) : null,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'status' => 'unpaid',
                    'notes' => 'Prorata pendaftaran layanan baru.',
                ]);

                foreach ($prorataItems as $item) {
                    InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
                }
            }

            DB::commit();

            if ($mailDomainProvisioningId) {
                EnsureMailDomainJob::dispatch($mailDomainProvisioningId)->afterCommit();
            }

            if ($hostingProvisioningId) {
                ProvisionHostingAccountJob::dispatch($hostingProvisioningId)->afterCommit();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Layanan berhasil ditambahkan.',
                    'subscription' => $subscription->load('package.service', 'client'),
                ]);
            }

            return redirect()->route('subscriptions.index')->with('success', 'Layanan berhasil ditambahkan.');

        } catch (ValidationException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: '.$e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        $mailboxSyncWarning = null;
        $hostingUsage = null;
        $hostingUsageWarning = null;

        if (! (request()->wantsJson() || request()->ajax()) && auth()->user()?->can('mailboxes.view')) {
            $mailHosting = $subscription->mailHosting()->with('mailServer')->first();

            if ($mailHosting?->mailServer?->type === 'zimbra') {
                try {
                    $this->zimbraMailboxSyncService->sync($mailHosting);
                } catch (\Throwable $exception) {
                    report($exception);
                    $mailboxSyncWarning = 'Data mailbox dari Zimbra tidak dapat diperbarui saat ini. Data lokal terakhir tetap ditampilkan.';
                }
            }
        }

        // Load all relationships
        $subscription->load(['client', 'package.service', 'connectivity.metroEthernet.vendor', 'hosting.hostingServer', 'domain.registrarAccount', 'domain.registrarOperations', 'mailHosting.mailServer', 'mailHosting.mailboxes']);

        // Fase 2 — capability flags untuk operasi domain (dibaca controller saat eksekusi; UI hanya menampilkan tombol)
        $domainOps = [];
        if ($subscription->domain?->registrar_account_id) {
            $manager = app(\App\DomainRegistrars\DomainRegistrarManager::class);
            $domainOps = [
                'mode' => $manager->effectiveMode(),
                'enabled' => $manager->isEnabled(),
                'can_update_nameservers' => $manager->canPerform('update_nameservers') && auth()->user()?->can('domains.update_nameservers'),
                'can_view_epp' => $manager->canPerform('view_epp') && auth()->user()?->can('domains.view_epp'),
                'can_set_epp' => $manager->canPerform('set_epp') && auth()->user()?->can('domains.set_epp'),
                'can_request_renew' => auth()->user()?->can('domains.renew'),
                'can_approve_renew' => auth()->user()?->can('domains.approve_renew'),
                'can_get_dns' => $manager->canPerform('get_dns') && auth()->user()?->can('domains.manage_dns'),
                'can_edit_dns' => $manager->canPerform('manage_dns') && auth()->user()?->can('domains.manage_dns'),
            ];
            if (auth()->user()?->can('domains.manage_dns')) {
                $domainOps['can_toggle_dns'] = $manager->canPerform('manage_dns');
            }
        }

        // Resource usage remains an infrastructure concern. Expose a safe summary
        // here only to users who are already allowed to manage hosting servers.
        $hosting = $subscription->hosting;
        if (! (request()->wantsJson() || request()->ajax())
            && auth()->user()?->can('servers.manage')
            && $hosting?->username
            && $hosting->hostingServer?->is_active
            && $hosting->hostingServer?->type === 'hestiacp') {
            try {
                $cacheKey = "hestiacp:user-detail:{$hosting->hosting_server_id}:".strtolower($hosting->username);
                $detail = Cache::remember($cacheKey, 120, fn () => $this->webHostResolver
                    ->resolve($hosting->hostingServer)
                    ->userDetails($hosting->username));

                if ($detail['success'] ?? false) {
                    $hostingUsage = $detail['data'];
                } else {
                    $hostingUsageWarning = 'Pemakaian akun HestiaCP tidak dapat dimuat saat ini. Detail layanan lokal tetap tersedia.';
                }
            } catch (\Throwable $exception) {
                report($exception);
                $hostingUsageWarning = 'Pemakaian akun HestiaCP tidak dapat dimuat saat ini. Detail layanan lokal tetap tersedia.';
            }
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($subscription);
        }

        $clients = Client::all();
        $packages = Package::with('service')->get();
        $routers = Router::all();
        $servers = HostingServer::all();
        $mailServers = HostingServer::where('type', 'zimbra')->get();
        $metroEthernets = MetroEthernet::with('vendor')->latest()->get();
        $registrarAccounts = \App\Models\RegistrarAccount::where('is_active', true)->get();

        return view('subscriptions.show', compact('subscription', 'clients', 'packages', 'routers', 'servers', 'metroEthernets', 'mailServers', 'mailboxSyncWarning', 'hostingUsage', 'hostingUsageWarning', 'registrarAccounts', 'domainOps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|string|in:pending,active,suspended,terminated',
            'installed_at' => 'required|date',
            'custom_price' => 'nullable|numeric|min:0',
            'billing_period_months' => 'nullable|integer|min:1|max:120',
            'uses_ppn' => 'nullable|boolean',
            'uses_pph23' => 'nullable|boolean',
        ]);

        $currentPackage = $subscription->package()->with('service')->firstOrFail();
        $package = Package::with('service')->findOrFail($request->package_id);

        if ($currentPackage->service?->type !== $package->service?->type) {
            throw ValidationException::withMessages([
                'package_id' => 'Paket hanya dapat diganti ke paket dengan jenis layanan yang sama. Perubahan internet, hosting, atau domain harus melalui proses migrasi layanan agar data teknis tetap aman.',
            ]);
        }

        $serviceType = $package->service->type;

        $mailDomainProvisioningId = null;
        $hostingProvisioningId = null;
        $mailboxStatusJobs = [];
        DB::beginTransaction();
        try {
            $oldBillingPeriodMonths = max(1, (int) $subscription->billing_period_months);
            $oldPackagePrice = (float) ($subscription->price_at_subscription ?? $currentPackage->price ?? 0);
            $oldBasePrice = (float) ($subscription->custom_price ?? ($oldPackagePrice * $oldBillingPeriodMonths));
            $billingPeriodMonths = (int) ($request->billing_period_months ?? 1);
            $basePrice = (float) ($request->filled('custom_price')
                ? $request->custom_price
                : ($package->price * $billingPeriodMonths));
            $usesPpn = $request->boolean('uses_ppn');
            $ppnAmount = $usesPpn ? Subscription::calculatePpnAmount($basePrice) : null;
            $usesPph23 = $request->boolean('uses_pph23');
            $pph23Amount = $usesPph23 ? Subscription::calculatePph23Amount($basePrice) : null;
            $installedAt = \Carbon\Carbon::parse($request->installed_at);

            $subscription->update([
                'package_id' => $package->id,
                'status' => $request->status,
                'installed_at' => $installedAt,
                'billing_cycle_day' => $installedAt->day,
                'next_billing_date' => $installedAt->copy()->addMonth(),
                'price_at_subscription' => $package->price,
                'custom_price' => $request->custom_price,
                'billing_period_months' => $billingPeriodMonths,
                'uses_ppn' => $usesPpn,
                'ppn_amount' => $ppnAmount,
                'uses_pph23' => $usesPph23,
                'pph23_amount' => $pph23Amount,
                'discount_percent' => null,
                'discount_notes' => null,
                'notes' => $request->notes,
            ]);
            $subscription->setRelation('package', $package);

            // Update Details
            if ($serviceType === 'connectivity') {
                $request->validate([
                    'router_id' => 'nullable|exists:routers,id',
                    'ip_address' => 'nullable|ipv4',
                    'pppoe_user' => 'nullable|string|max:100',
                    'ont_sn' => 'nullable|string|max:100',
                    'vlan_id' => 'nullable|integer|min:1|max:4094',
                    'zabbix_group_id' => 'nullable|string|max:255',
                    'zabbix_group_name' => 'nullable|string|max:255',
                    'zabbix_host_id' => 'nullable|string|max:255',
                    'zabbix_host_name' => 'nullable|string|max:255',
                    'zabbix_interfaces' => 'nullable|array',
                    'zabbix_interfaces.*.graphid' => 'required_with:zabbix_interfaces|string|max:255',
                    'zabbix_interfaces.*.name' => 'required_with:zabbix_interfaces|string|max:255',
                    'zabbix_interfaces.*.itemIn' => 'required_with:zabbix_interfaces|string|max:255',
                    'zabbix_interfaces.*.itemOut' => 'required_with:zabbix_interfaces|string|max:255',
                    'metro_option' => 'nullable|string|in:existing,new',
                    'metro_ethernet_id' => 'nullable|required_if:metro_option,existing|exists:metro_ethernets,id',
                    'metro_name' => 'nullable|required_if:metro_option,new|string|max:255',
                    'metro_vendor_id' => 'nullable|required_if:metro_option,new|exists:vendors,id',
                    'metro_bandwidth' => 'nullable|required_if:metro_option,new|integer|min:0',
                ]);

                $subscription->connectivity()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'router_id' => $request->router_id,
                        'ip_address' => $request->ip_address,
                        'ip_type' => $request->ip_type ?? 'dynamic',
                        'pppoe_user' => $request->pppoe_user,
                        'pppoe_secret' => $request->pppoe_secret ? encrypt($request->pppoe_secret) : $subscription->connectivity?->pppoe_secret,
                        'ont_sn' => $request->ont_sn,
                        'router_model' => $request->router_model,
                        'vlan_id' => $request->vlan_id,
                        'signal_rx' => $request->signal_rx,
                        'zabbix_group_id' => $request->zabbix_group_id,
                        'zabbix_group_name' => $request->zabbix_group_name,
                        'zabbix_host_id' => $request->zabbix_host_id,
                        'zabbix_host_name' => $request->zabbix_host_name,
                        'zabbix_interfaces' => $this->validatedZabbixInterfaces(
                            $request->zabbix_host_id,
                            $request->input('zabbix_interfaces', [])
                        ),
                    ]
                );

                // Handle Metro update/change
                $connectivity = $subscription->connectivity;
                if ($request->input('metro_option') === 'new') {
                        $metro = MetroEthernet::create([
                            'name' => $request->metro_name,
                            'vendor_id' => $request->metro_vendor_id,
                            'cid' => $request->metro_cid,
                            'ip_address' => $request->metro_ip_address,
                            'bandwidth' => $request->metro_bandwidth,
                        ]);
                        $connectivity->update(['metro_ethernet_id' => $metro->id]);
                } elseif ($request->input('metro_option') === 'existing') {
                    $connectivity->update(['metro_ethernet_id' => $request->metro_ethernet_id]);
                } else {
                    $connectivity->update(['metro_ethernet_id' => null]);
                }
            } elseif ($serviceType === 'hosting') {
                $request->mergeIfMissing(['hosting_account_mode' => 'new']);
                $hostingAccountMode = $request->input('hosting_account_mode', 'new');

                $request->validate([
                    'hosting_account_mode' => ['required', Rule::in(['new', 'existing'])],
                    'hosting_server_id' => ['required', Rule::exists('hosting_servers', 'id')->where(fn ($query) => $query->where('type', 'hestiacp')->where('is_active', true))],
                    'domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
                    'username' => $hostingAccountMode === 'existing'
                        ? ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9_]{1,32}$/']
                        : ['required', 'string', 'max:32', 'regex:/^[a-z][a-z0-9_]{0,31}$/'],
                    'password' => 'nullable|string|min:8|max:255',
                ]);

                $hosting = $subscription->hosting;
                $server = HostingServer::findOrFail($request->hosting_server_id);
                $newUsername = strtolower(trim((string) $request->username));
                $newDomain = strtolower(trim((string) $request->domain));

                if ($hosting && ! $hosting->managed_by_crm) {
                    if ($hostingAccountMode !== 'existing'
                        || $hosting->hosting_server_id !== $server->id
                        || $hosting->username !== $newUsername
                        || $hosting->domain !== $newDomain) {
                        throw ValidationException::withMessages([
                            'hosting_server_id' => 'Akun HestiaCP yang ditautkan tidak dapat diubah dari form layanan. Gunakan proses relink atau migrasi hosting.',
                        ]);
                    }

                    if ($request->filled('password')) {
                        throw ValidationException::withMessages([
                            'password' => 'Password tidak dapat diubah untuk akun HestiaCP yang hanya ditautkan.',
                        ]);
                    }
                }

                if ($hosting && $hosting->managed_by_crm && $hostingAccountMode !== 'new') {
                    throw ValidationException::withMessages([
                        'hosting_account_mode' => 'Mode akun tidak dapat diubah setelah layanan hosting dibuat.',
                    ]);
                }

                if ($hosting && $hosting->managed_by_crm && $hosting->provisioning_status === 'ready' && $hosting->username !== $newUsername) {
                    throw ValidationException::withMessages([
                        'username' => 'Username tidak dapat diubah setelah akun diprovisikan. Gunakan proses migrasi hosting.',
                    ]);
                }

                if ($hosting && $hosting->managed_by_crm && $hosting->remote_user_created_at
                    && ($hosting->hosting_server_id !== (int) $request->hosting_server_id
                        || $hosting->username !== $newUsername
                        || $hosting->domain !== $newDomain)) {
                    throw ValidationException::withMessages([
                        'hosting_server_id' => 'Server, username, dan domain tidak dapat diubah setelah akun diprovisikan. Gunakan proses migrasi hosting.',
                    ]);
                }

                if ($hosting && $request->filled('password')) {
                    if (! $hosting->managed_by_crm || ! $hosting->remote_user_created_at || $hosting->provisioning_status !== 'ready') {
                        throw ValidationException::withMessages([
                            'password' => 'Password hanya dapat diubah untuk akun yang telah berhasil dibuat oleh CRM.',
                        ]);
                    }

                    $hosting->update(['password_encrypted' => encrypt($request->password)]);
                    ResetHostingAccountPasswordJob::dispatch($hosting->id)->afterCommit();
                }

                $this->ensureHostingUsernameIsAvailable($server, $newUsername, $hosting?->id);

                if (! $hosting && $hostingAccountMode === 'existing') {
                    $this->ensureExistingHestiaAccountOwnsDomain($server, $newUsername, $newDomain);
                }

                if (! $hosting && $hostingAccountMode === 'new' && blank($package->hestia_package)) {
                    throw ValidationException::withMessages([
                        'package_id' => 'Paket hosting belum memiliki mapping paket HestiaCP.',
                    ]);
                }

                $hosting = $subscription->hosting()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'hosting_server_id' => $request->hosting_server_id,
                        'domain' => $hosting && $hosting->provisioning_status === 'ready'
                            ? $hosting->domain
                            : $newDomain,
                        'username' => $hosting?->username ?? $newUsername,
                        'password_encrypted' => $hostingAccountMode === 'new' && $request->filled('password')
                            ? encrypt($request->password)
                            : $hosting?->password_encrypted,
                        'disk_quota_gb' => $request->disk_quota_gb ?? 0,
                        'email_accounts' => $request->email_accounts ?? 0,
                        'databases' => $request->databases ?? 0,
                        'ssl_expiry' => $request->ssl_expiry,
                        'provisioning_status' => $hosting?->provisioning_status ?? ($hostingAccountMode === 'new' ? 'pending' : 'ready'),
                        'managed_by_crm' => $hosting?->managed_by_crm ?? ($hostingAccountMode === 'new'),
                        'hestia_package' => $hosting?->hestia_package ?? ($hostingAccountMode === 'new' ? $package->hestia_package : null),
                        'provisioned_at' => $hosting?->provisioned_at ?? ($hostingAccountMode === 'existing' ? now() : null),
                    ]
                );

                if (! $hosting->remote_user_created_at && $hosting->provisioning_status === 'pending') {
                    $hostingProvisioningId = $hosting->id;
                } elseif ($hosting->wasRecentlyCreated && ! $hosting->managed_by_crm) {
                    activity('subscriptions')
                        ->performedOn($subscription)
                        ->causedBy(auth()->user())
                        ->withProperties(['hosting_server_id' => $server->id, 'username' => $newUsername, 'domain' => $newDomain])
                        ->log('Menautkan akun HestiaCP existing ke layanan hosting');
                }
            } elseif ($serviceType === 'domain') {
                $request->mergeIfMissing(['domain_account_mode' => $subscription->domain?->domain_account_mode ?? 'new']);
                $domainAccountMode = $request->input('domain_account_mode', 'new');

                $request->validate([
                    'domain_account_mode' => ['required', Rule::in(['new', 'existing'])],
                    'registrar_account_id' => ['nullable', Rule::exists('registrar_accounts', 'id')->where(fn ($q) => $q->where('is_active', true))],
                    'domain_name' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
                    'registrar' => 'nullable|string|max:255',
                    'registered_at' => 'nullable|date',
                    'expires_at' => 'nullable|date|after_or_equal:registered_at',
                    'auth_code' => 'nullable|string|max:255',
                    'domain_notes' => 'nullable|string',
                ]);

                $existingDomain = $subscription->domain;
                $normalizedDomain = strtolower(trim($request->domain_name));

                // Guard: jangan ubah registrar_account_id langsung jika sudah tertaut (harus via relink khusus)
                if ($existingDomain && $existingDomain->registrar_account_id) {
                    if ((int) $request->registrar_account_id !== (int) $existingDomain->registrar_account_id) {
                        throw ValidationException::withMessages([
                            'registrar_account_id' => 'Akun registrar tidak dapat diubah dari form layanan. Gunakan proses relink/migrasi domain.',
                        ]);
                    }
                    if ($normalizedDomain !== strtolower($existingDomain->domain_name)) {
                        throw ValidationException::withMessages([
                            'domain_name' => 'Nama domain tidak dapat diubah setelah tertaut. Gunakan proses relink/migrasi.',
                        ]);
                    }
                }

                // TLD warning
                if ($request->filled('registrar_account_id') && config('domain-registrars.enabled')) {
                    $account = \App\Models\RegistrarAccount::find($request->registrar_account_id);
                    if ($account) {
                        $allowed = $account->allowedTlds();
                        if (! empty($allowed)) {
                            $matched = collect($allowed)->contains(fn ($tld) => str_ends_with($normalizedDomain, strtolower($tld)));
                            if (! $matched) {
                                session()->flash('warning', "TLD {$normalizedDomain} tidak termasuk daftar akun {$account->name} (".implode(', ', $allowed).").");
                            }
                        }
                    }
                }

                $domain = $subscription->domain()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'domain_name' => $normalizedDomain,
                        'registrar' => $request->registrar,
                        'auth_code_encrypted' => $request->filled('auth_code')
                            ? encrypt($request->auth_code)
                            : $existingDomain?->auth_code_encrypted,
                        'registered_at' => $request->registered_at,
                        'expires_at' => $request->expires_at,
                        'notes' => $request->domain_notes,
                        'registrar_account_id' => $existingDomain?->registrar_account_id ?? $request->registrar_account_id,
                        'domain_account_mode' => $existingDomain?->domain_account_mode ?? $domainAccountMode,
                        'managed_by_crm' => $existingDomain?->managed_by_crm ?? false,
                    ]
                );

                if ($domain->registrar_account_id) {
                    \App\Jobs\SyncRegistrarDomain::dispatch($domain->id)->afterCommit();
                }
            } elseif ($serviceType === 'mail') {
                $request->validate([
                    'mail_server_id' => ['required', Rule::exists('hosting_servers', 'id')->where(fn ($query) => $query->where('type', 'zimbra')->where('is_active', true))],
                    'mail_domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
                    'admin_email' => 'nullable|email|max:255',
                    'admin_password' => 'nullable|string|max:255',
                ]);

                $server = HostingServer::findOrFail($request->mail_server_id);
                $mailHosting = $subscription->mailHosting;
                $domain = strtolower(trim($request->mail_domain));

                if (SubscriptionMailHosting::where('mail_server_id', $request->mail_server_id)
                    ->where('domain', $domain)
                    ->when($mailHosting, fn ($query) => $query->whereKeyNot($mailHosting->id))
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'mail_domain' => 'Domain tersebut sudah digunakan oleh layanan mail hosting lain pada server ini.',
                    ]);
                }

                if ($mailHosting && $mailHosting->mailboxes()->exists()
                    && ($mailHosting->mail_server_id !== (int) $request->mail_server_id || $mailHosting->domain !== $domain)) {
                    throw ValidationException::withMessages([
                        'mail_domain' => 'Server atau domain tidak dapat diubah setelah mailbox dibuat. Gunakan proses migrasi mail hosting.',
                    ]);
                }

                $requiresProvisioning = $server->type !== 'zimbra' && (! $mailHosting
                    || $mailHosting->mail_server_id !== (int) $request->mail_server_id
                    || $mailHosting->domain !== $domain);

                $mailHosting = $subscription->mailHosting()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'mail_server_id' => $request->mail_server_id,
                        'domain' => $domain,
                        'admin_email' => $request->admin_email,
                        'admin_password_encrypted' => $request->filled('admin_password')
                            ? $request->admin_password
                            : $mailHosting?->admin_password_encrypted,
                        'max_mailboxes' => $package->max_mailboxes ?? ($mailHosting?->max_mailboxes ?? 0),
                        'mailbox_quota_mb' => $package->mailbox_quota_mb ?? ($mailHosting?->mailbox_quota_mb ?? 0),
                        'alias_max' => $package->alias_max ?? ($mailHosting?->alias_max ?? 0),
                        'mail_server_type' => $server->type,
                        'status' => in_array($subscription->status, ['suspended', 'terminated'], true)
                            ? $subscription->status
                            : 'active',
                        'provisioning_status' => $requiresProvisioning
                            ? 'pending'
                            : ($mailHosting?->provisioning_status ?? 'ready'),
                        'provisioning_error' => $requiresProvisioning ? null : $mailHosting?->provisioning_error,
                    ]
                );

                if ($requiresProvisioning) {
                    $mailDomainProvisioningId = $mailHosting->id;
                }

                // Subscription status is local-only for mail hosting and must not alter Zimbra mailboxes.
            }

            $prorataService = new ProrataCalculationService;
            $prorataItems = null;

            if ($subscription->wasChanged(['package_id', 'custom_price', 'billing_period_months'])) {
                $prorataItems = $prorataService->calculateUpgradeDowngrade($subscription, $oldBasePrice, now(), $oldBillingPeriodMonths);
            } elseif ($subscription->wasChanged('status') && in_array($request->status, ['suspended', 'terminated'])) {
                $prorataItems = $prorataService->calculateSuspendTerminate($subscription, now());
            }

            if ($prorataItems) {
                $subtotal = collect($prorataItems)->sum('amount');
                $taxAmount = $usesPpn ? round($subtotal * (\App\Models\SystemSetting::get('billing.ppn_rate', 11) / 100), 2) : 0;
                $totalAmount = $subtotal + $taxAmount;

                $branchCode = $subscription->client->branch ? $subscription->client->branch->code : 'GEN';
                $invoice = Invoice::create([
                    'client_id' => $subscription->client_id,
                    'invoice_number' => Invoice::generateInvoiceNumber($branchCode),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(\App\Models\SystemSetting::get('billing.default_due_days', 7)),
                    'subtotal_amount' => $subtotal,
                    'uses_tax' => $usesPpn,
                    'tax_rate' => $usesPpn ? \App\Models\SystemSetting::get('billing.ppn_rate', 11) : null,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'status' => 'unpaid',
                    'notes' => 'Prorata perubahan/penghentian layanan.',
                ]);

                foreach ($prorataItems as $item) {
                    InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
                }
            }

            DB::commit();

            if ($mailDomainProvisioningId) {
                EnsureMailDomainJob::dispatch($mailDomainProvisioningId)->afterCommit();
            }

            if ($hostingProvisioningId) {
                ProvisionHostingAccountJob::dispatch($hostingProvisioningId)->afterCommit();
            }

            foreach ($mailboxStatusJobs as $mailboxId) {
                SetMailboxStatusJob::dispatch($mailboxId, $subscription->status === 'active', $subscription->status !== 'active')->afterCommit();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Layanan berhasil diperbarui.',
                    'subscription' => $subscription->load('package.service', 'client'),
                ]);
            }

            return redirect()->route('subscriptions.index')->with('success', 'Layanan berhasil diperbarui.');

        } catch (ValidationException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: '.$e->getMessage()], 500);
            }

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Subscription $subscription)
    {
        $mailHosting = $subscription->mailHosting;

        if ($mailHosting) {
            // Preserve local mail history and never propagate subscription removal to Zimbra.
            DB::transaction(function () use ($subscription, $mailHosting) {
                $subscription->update([
                    'status' => 'terminated',
                    'terminated_at' => $subscription->terminated_at ?? now(),
                    'termination_reason' => $subscription->termination_reason ?? 'Layanan mail hosting dihentikan dari CRM. Akun Zimbra dipertahankan.',
                ]);

                $mailHosting->update(['status' => 'terminated']);
                $mailHosting->mailboxes()
                    ->where('provisioning_status', 'deleting')
                    ->update([
                        'provisioning_status' => 'ready',
                        'provisioning_error' => 'Penghapusan dibatalkan karena layanan dihentikan dari CRM.',
                    ]);
            });

            $message = 'Layanan mail hosting ditandai berhenti dan diarsipkan. Data mailbox CRM serta akun Zimbra tetap dipertahankan.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'archived' => true, 'message' => $message]);
            }

            return redirect()->route('subscriptions.index')->with('success', $message);
        }

        $subscription->delete(); // Cascade defined in DB

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dihapus.',
            ]);
        }

        return redirect()->route('subscriptions.index');
    }

    protected function validatedZabbixInterfaces(?string $hostId, array $interfaces): array
    {
        if (empty($interfaces)) {
            return [];
        }

        if (! $hostId) {
            throw new \InvalidArgumentException('Host Zabbix wajib dipilih ketika interface monitoring diisi.');
        }

        $availableGraphs = collect($this->zabbixService->getGraphs($hostId))->keyBy('graphid');

        return collect($interfaces)
            ->map(function (array $selected) use ($availableGraphs) {
                $graphId = (string) ($selected['graphid'] ?? '');
                $graph = $availableGraphs->get($graphId);

                if (! $graph) {
                    throw new \InvalidArgumentException('Salah satu interface Zabbix tidak valid atau sudah berubah.');
                }

                return [
                    'graphid' => (string) $graph['graphid'],
                    'name' => (string) $graph['name'],
                    'itemIn' => (string) $graph['itemIn'],
                    'itemOut' => (string) $graph['itemOut'],
                ];
            })
            ->unique('graphid')
            ->values()
            ->all();
    }

    /**
     * Confirm that an existing Hestia account and its selected primary domain
     * are real before CRM stores a read-only link to them.
     */
    protected function ensureExistingHestiaAccountOwnsDomain(HostingServer $server, string $username, string $domain): void
    {
        try {
            $adapter = $this->webHostResolver->resolve($server);
            $user = $adapter->findUser($username);

            if (! $user['success']) {
                throw ValidationException::withMessages([
                    'username' => 'Akun HestiaCP tidak dapat diverifikasi saat ini. Periksa koneksi server lalu coba lagi.',
                ]);
            }

            if ($user['data'] === null) {
                throw ValidationException::withMessages([
                    'username' => 'Username tersebut tidak ditemukan pada server HestiaCP yang dipilih.',
                ]);
            }

            $domains = $adapter->listWebDomains($username);

            if (! $domains['success']) {
                throw ValidationException::withMessages([
                    'domain' => 'Domain akun HestiaCP tidak dapat diverifikasi saat ini. Periksa koneksi server lalu coba lagi.',
                ]);
            }

            $ownedDomains = collect($domains['data'])
                ->map(fn ($item) => strtolower(trim((string) $item)))
                ->all();

            if (! in_array($domain, $ownedDomains, true)) {
                throw ValidationException::withMessages([
                    'domain' => 'Domain tersebut tidak terdaftar pada username HestiaCP yang dipilih.',
                ]);
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'hosting_server_id' => 'Koneksi ke HestiaCP gagal saat memverifikasi akun existing. Periksa konfigurasi server lalu coba lagi.',
            ]);
        }
    }

    /**
     * Keep a remote Hestia account attached to exactly one CRM subscription.
     */
    protected function ensureHostingUsernameIsAvailable(HostingServer $server, string $username, ?int $ignoreHostingId = null): void
    {
        $existing = SubscriptionHosting::with('subscription')
            ->where('hosting_server_id', $server->id)
            ->where('username', $username)
            ->when($ignoreHostingId, fn ($query) => $query->whereKeyNot($ignoreHostingId))
            ->first();

        if (! $existing) {
            return;
        }

        $subscriptionCode = $existing->subscription?->subscription_code;
        $message = 'Username tersebut sudah terhubung pada layanan lain di server hosting ini.';

        if ($subscriptionCode) {
            $message .= " Layanan yang memakai akun ini: {$subscriptionCode}.";
        }

        $message .= ' Satu akun HestiaCP hanya dapat ditautkan ke satu layanan CRM untuk mencegah perubahan akun yang tidak disengaja.';

        throw ValidationException::withMessages(['username' => $message]);
    }

    protected function ensureActiveHestiaServer(HostingServer $server): void
    {
        abort_unless(
            $server->is_active && $server->type === 'hestiacp',
            404,
            'Server HestiaCP aktif tidak ditemukan.'
        );
    }

    protected function generateSubscriptionCode(Client $client, Package $package): string
    {
        $serviceCode = strtoupper((string) ($package->service?->code ?? 'SRV'));
        $basePrefix = $client->client_code.'-'.$serviceCode;

        $latestMatchingCode = Subscription::query()
            ->where('client_id', $client->id)
            ->whereHas('package', fn ($query) => $query->where('service_id', $package->service_id))
            ->where('subscription_code', 'like', $basePrefix.'%')
            ->select('subscription_code')
            ->orderByDesc('subscription_code')
            ->value('subscription_code');

        $nextNumber = 1;

        if ($latestMatchingCode && preg_match('/^'.preg_quote($basePrefix, '/').'(\d{2})$/', $latestMatchingCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $existingCount = Subscription::query()
                ->where('client_id', $client->id)
                ->whereHas('package', fn ($query) => $query->where('service_id', $package->service_id))
                ->count();

            $nextNumber = $existingCount + 1;
        }

        do {
            $subscriptionCode = sprintf('%s%02d', $basePrefix, $nextNumber);
            $nextNumber++;
        } while (Subscription::query()->where('subscription_code', $subscriptionCode)->exists());

        return $subscriptionCode;
    }
}
