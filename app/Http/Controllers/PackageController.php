<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Service;
use App\Services\WebHostResolver;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function __construct(protected WebHostResolver $webHostResolver)
    {
        $this->middleware('permission:packages.view')->only(['index', 'show']);
        $this->middleware('permission:packages.create')->only(['create', 'store', 'syncPackages']);
        $this->middleware('permission:packages.update')->only(['edit', 'update']);
        $this->middleware('permission:packages.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        $packagesQuery = Package::with('service')->latest();
        $servicesQuery = Service::where('is_active', true);

        if ($type) {
            $packagesQuery->whereHas('service', function ($q) use ($type) {
                $q->where('type', $type);
            });
            $servicesQuery->where('type', $type);
        }

        $packages = $packagesQuery->get();
        $services = $servicesQuery->get();

        return view('packages.index', compact('packages', 'services', 'type'));
    }

    public function create()
    {
        return redirect()->route('packages.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'hestia_package' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50', // Added unit validation
            'bandwidth_down' => 'nullable|string',
            'bandwidth_up' => 'nullable|string',
            'quota' => 'nullable|string',
            'max_mailboxes' => 'nullable|integer|min:0',
            'mailbox_quota_mb' => 'nullable|integer|min:0',
            'alias_max' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $package = Package::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil ditambahkan.',
                'package' => $package->load('service')
            ]);
        }

        return redirect()->back()->with('success', 'Paket berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Package $package)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($package->load('service'));
        }
        return redirect()->route('packages.index');
    }

    public function edit(Package $package)
    {
        return redirect()->route('packages.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'hestia_package' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bandwidth_down' => 'nullable|string',
            'bandwidth_up' => 'nullable|string',
            'quota' => 'nullable|string',
            'max_mailboxes' => 'nullable|integer|min:0',
            'mailbox_quota_mb' => 'nullable|integer|min:0',
            'alias_max' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $package->fill($validated);
        if ($request->has('is_active')) {
            $package->is_active = $request->boolean('is_active');
        } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
            $package->is_active = $request->has('is_active');
        }
        $package->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil diperbarui.',
                'package' => $package->load('service')
            ]);
        }

        return redirect()->route('packages.index')->with('success', 'Paket berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Package $package)
    {
        $package->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil dihapus.'
            ]);
        }

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dihapus.');
    }
    /**
     * Sync packages from HestiaCP Servers
     */
    public function syncPackages()
    {
        // Fix: Type is 'hestiacp' based on UI select options, not 'web_hosting'
        $servers = \App\Models\HostingServer::where('is_active', true)->where('type', 'hestiacp')->get();
        if ($servers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada server hosting (HestiaCP) aktif. Pastikan Tipe server adalah "hestiacp".'], 404);
        }

        if ($servers->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sinkronisasi otomatis dinonaktifkan bila ada lebih dari satu server HestiaCP. Isi mapping paket Hestia secara manual agar tidak tertukar antar server.',
            ], 422);
        }

        $syncedCount = 0;
        $errors = [];

        // Ensure "Hosting" service category exists
        $service = Service::firstOrCreate(
            ['code' => 'HST-01', 'type' => 'hosting'],
            ['name' => 'Web Hosting', 'description' => 'Layanan Web Hosting HestiaCP', 'is_active' => true]
        );

        foreach ($servers as $server) {
            $result = $this->webHostResolver->resolve($server)->listUserPackages();

            if (!$result['success']) {
                $errors[] = "Server {$server->name}: " . ($result['message'] ?? 'Unknown error');
                continue;
            }

            foreach ((array) $result['data'] as $pkgName) {
                $package = Package::firstOrCreate(
                    [
                        'name' => $pkgName,
                        'service_id' => $service->id
                    ],
                    [
                        'hestia_package' => $pkgName,
                        'description' => "Paket HestiaCP {$pkgName} dari {$server->name}. Harga dan kuota CRM harus diatur manual.",
                        'price' => 0,
                        'is_active' => true
                    ]
                );

                // Never overwrite commercial pricing or quota maintained in CRM.
                $package->update(['hestia_package' => $pkgName]);
                $syncedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sync complete. {$syncedCount} packages processed.",
            'errors' => $errors
        ]);
    }
}
