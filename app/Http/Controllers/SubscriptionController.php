<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Client;
use App\Models\MetroEthernet;
use App\Models\Package;
use App\Models\Router;
use App\Models\HostingServer;
use App\Models\SubscriptionConnectivity;
use App\Models\SubscriptionHosting;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
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

        $subscriptions = $query->get();
        // Pre-fetch data for modals
        $clients = Client::orderBy('name')->get();
        $packages = Package::with('service')->where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();
        $servers = HostingServer::where('is_active', true)->get();
        // Fetch Metro Ethernets with Vendor for selection
        $metroEthernets = MetroEthernet::with('vendor')->latest()->get();

        return view('subscriptions.index', compact('subscriptions', 'clients', 'packages', 'routers', 'servers', 'metroEthernets'));
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
            'status' => 'required|string|in:pending,active',
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
                // Metro Ethernet Validation
                'metro_option' => 'nullable|string|in:existing,new',
                'metro_ethernet_id' => 'nullable|required_if:metro_option,existing|exists:metro_ethernets,id',
                'metro_vendor_id' => 'nullable|required_if:metro_option,new|exists:vendors,id',
                'metro_bandwidth' => 'nullable|required_if:metro_option,new|integer|min:0',
            ]);
        } elseif ($serviceType === 'hosting') {
            $request->validate([
                'hosting_server_id' => 'nullable|exists:hosting_servers,id',
                'domain' => 'nullable|string|max:255',
                'username' => 'nullable|string|max:100',
            ]);
        }

        DB::beginTransaction();
        try {
            // Generate Code: SUB-{CLIENT}-{SEQUENCE}
            $client = Client::findOrFail($request->client_id);
            $count = Subscription::where('client_id', $client->id)->count() + 1;
            $code = sprintf("SUB-%s-%03d", $client->client_code, $count); // e.g., SUB-C-SMG-0001-001

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
                'billing_period_months' => $request->billing_period_months ?? 1,
                'discount_percent' => $request->discount_percent,
                'discount_notes' => $request->discount_notes,
                'notes' => $request->notes
            ]);

            // Save Details
            if ($serviceType === 'connectivity') {
                SubscriptionConnectivity::create([
                    'subscription_id' => $subscription->id,
                    'router_id' => $request->router_id,
                    'ip_address' => $request->ip_address,
                    'ip_type' => $request->ip_type ?? 'dynamic',
                    'pppoe_user' => $request->pppoe_user,
                    'pppoe_secret' => $request->pppoe_secret ? encrypt($request->pppoe_secret) : null,
                    'ont_sn' => $request->ont_sn,
                    'signal_rx' => $request->signal_rx,
                ];

                // Handle Metro Ethernet Logic
                if ($request->filled('metro_option')) {
                    if ($request->metro_option === 'new') {
                        $metro = MetroEthernet::create([
                            'vendor_id' => $request->metro_vendor_id,
                            'cid' => $request->metro_cid,
                            'ip_address' => $request->metro_ip_address,
                            'bandwidth' => $request->metro_bandwidth,
                            'service_id' => $package->service_id, // Link to service of package
                        ]);
                        $connData['metro_ethernet_id'] = $metro->id;
                    } else {
                        $connData['metro_ethernet_id'] = $request->metro_ethernet_id;
                    }
                }

                SubscriptionConnectivity::create($connData);
            } elseif ($serviceType === 'hosting') {
                // Call HestiaCP API
                $server = HostingServer::findOrFail($request->hosting_server_id);
                $hestia = new \App\Services\HestiaCPService($server);

                // Create User
                // Use default package 'default' or mapping from our package? 
                // For now use 'default' or maybe custom logic.
                // Assuming package name in Hestia matches our CRM package name could be a strategy, 
                // or just use 'default'. Let's use 'default' for stability first.
                $userPass = $request->password;
                $createRes = $hestia->createUser(
                    $request->username,
                    $userPass,
                    $client->email ?? 'admin@example.com',
                    $client->name,
                    'default'
                );

                if ($createRes['success']) {
                    // Create Domain
                    if ($request->domain) {
                        $hestia->createWebDomain($request->username, $request->domain);
                    }
                } else {
                    // Log error but continue saving local? Or throw?
                    // Better to throw so transaction determines fate.
                    // But maybe user already exists?
                    // Let's log warning and continue, marking status as pending/error?
                    \Illuminate\Support\Facades\Log::warning("Hestia User Creation Failed: " . $createRes['message']);
                    // Optional: $subscription->update(['notes' => $subscription->notes . " [Hestia Error: " . $createRes['message'] . "]"]);
                }

                SubscriptionHosting::create([
                    'subscription_id' => $subscription->id,
                    'hosting_server_id' => $request->hosting_server_id,
                    'domain' => $request->domain,
                    'username' => $request->username,
                    'password_encrypted' => $request->password ? encrypt($request->password) : null,
                    'disk_quota_gb' => $request->disk_quota_gb ?? 0,
                    'email_accounts' => $request->email_accounts ?? 0,
                    'databases' => $request->databases ?? 0,
                    'ssl_expiry' => $request->ssl_expiry,
                ]);
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Layanan berhasil ditambahkan.',
                    'subscription' => $subscription->load('package.service', 'client')
                ]);
            }
            return redirect()->route('subscriptions.index')->with('success', 'Layanan berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        // Load all relationships
        $subscription->load(['client', 'package.service', 'connectivity.metroEthernet.vendor', 'hosting', 'domain']);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($subscription);
        }

        return view('subscriptions.show', compact('subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'status' => 'required|string|in:pending,active,suspended,terminated',
            'installed_at' => 'required|date',
        ]);

        $package = $subscription->package; // Assume package doesn't change for update (usually upgrade is different process)
        // If package changes, usage type might change -> complicated. For MVP assume NO package change in Edit.

        $serviceType = $package->service->type;

        DB::beginTransaction();
        try {
            $subscription->update([
                'status' => $request->status,
                'installed_at' => $request->installed_at,
                // Recalculate billing dates if needed? 
                'custom_price' => $request->custom_price,
                'billing_period_months' => $request->billing_period_months ?? 1,
                'discount_percent' => $request->discount_percent,
                'discount_notes' => $request->discount_notes,
                'notes' => $request->notes
            ]);

            // Update Details
            if ($serviceType === 'connectivity') {
                $subscription->connectivity()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'router_id' => $request->router_id,
                        'ip_address' => $request->ip_address,
                        'ip_type' => $request->ip_type ?? 'dynamic',
                        'pppoe_user' => $request->pppoe_user,
                        'pppoe_secret' => $request->pppoe_secret ? encrypt($request->pppoe_secret) : $subscription->connectivity->pppoe_secret,
                        'ont_sn' => $request->ont_sn,
                        'router_model' => $request->router_model,
                        'vlan_id' => $request->vlan_id,
                        'signal_rx' => $request->signal_rx,
                    ]
                );

                // Handle Metro update/change
                if ($request->filled('metro_option')) {
                   $connectivity = $subscription->connectivity;
                   if ($request->metro_option === 'new') {
                        $metro = MetroEthernet::create([
                            'vendor_id' => $request->metro_vendor_id,
                            'cid' => $request->metro_cid,
                            'ip_address' => $request->metro_ip_address,
                            'bandwidth' => $request->metro_bandwidth,
                            'service_id' => $package->service_id,
                        ]);
                        $connectivity->update(['metro_ethernet_id' => $metro->id]);
                   } elseif ($request->metro_option === 'existing') {
                        $connectivity->update(['metro_ethernet_id' => $request->metro_ethernet_id]);
                   }
                }
            } elseif ($serviceType === 'hosting') {
                $hosting = $subscription->hosting;
                $server = HostingServer::findOrFail($request->hosting_server_id); // Assuming server might change? Probably not often.
                $hestia = new \App\Services\HestiaCPService($server);

                // Check for Status Change
                if ($subscription->wasChanged('status')) {
                    $newStatus = $request->status;
                    if ($newStatus === 'suspended') {
                        $hestia->suspendUser($hosting->username);
                    } elseif ($newStatus === 'active') { // And previous was suspended?
                        $hestia->unsuspendUser($hosting->username);
                    } elseif ($newStatus === 'terminated') {
                        // Optional: $hestia->deleteUser($hosting->username);
                        // Usually we keep terminated for a while, or maybe suspend first.
                        $hestia->suspendUser($hosting->username);
                    }
                }

                // Check if password changed
                if ($request->password) {
                    $hestia->changePassword($hosting->username, $request->password);
                }

                $subscription->hosting()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'hosting_server_id' => $request->hosting_server_id,
                        'domain' => $request->domain,
                        'username' => $request->username,
                        'password_encrypted' => $request->password ? encrypt($request->password) : $subscription->hosting->password_encrypted,
                        'disk_quota_gb' => $request->disk_quota_gb ?? 0,
                        'email_accounts' => $request->email_accounts ?? 0,
                        'databases' => $request->databases ?? 0,
                        'ssl_expiry' => $request->ssl_expiry,
                    ]
                );
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Layanan berhasil diperbarui.',
                    'subscription' => $subscription->load('package.service', 'client')
                ]);
            }
            return redirect()->route('subscriptions.index')->with('success', 'Layanan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Subscription $subscription)
    {
        $subscription->delete(); // Cascade defined in DB

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dihapus.'
            ]);
        }
        return redirect()->route('subscriptions.index');
    }
}
