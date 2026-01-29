<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::with('service')->latest()->get();
        $services = Service::where('is_active', true)->get();
        return view('packages.index', compact('packages', 'services'));
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
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bandwidth_down' => 'nullable|string',
            'bandwidth_up' => 'nullable|string',
            'quota' => 'nullable|string',
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

        return redirect()->route('packages.index')->with('success', 'Paket berhasil ditambahkan.');
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
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bandwidth_down' => 'nullable|string',
            'bandwidth_up' => 'nullable|string',
            'quota' => 'nullable|string',
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

        $syncedCount = 0;
        $errors = [];

        // Ensure "Hosting" service category exists
        $service = Service::firstOrCreate(
            ['code' => 'HST-01', 'type' => 'hosting'],
            ['name' => 'Web Hosting', 'description' => 'Layanan Web Hosting HestiaCP', 'is_active' => true]
        );

        foreach ($servers as $server) {
            $hestia = new \App\Services\HestiaCPService($server);
            $result = $hestia->listPackages();

            if (!$result['success']) {
                $errors[] = "Server {$server->name}: " . ($result['message'] ?? 'Unknown error');
                continue;
            }

            // Hestia returns JSON string in 'data' key? Or direct array if parsed?
            // HestiaCPService returns 'data' as the body string.
            $data = json_decode($result['data'], true);

            if (!is_array($data)) {
                $errors[] = "Server {$server->name}: Invalid JSON format";
                continue;
            }

            foreach ($data as $pkgName => $pkgDetails) {
                // Update or Create Package
                // Mapping:
                // name -> pkgName
                // bandwidth -> bandwidth_up/down (Hestia uses 'bandwidth' total)
                // quota -> quota (disk quota)

                Package::updateOrCreate(
                    [
                        'name' => $pkgName,
                        'service_id' => $service->id
                    ],
                    [
                        'quota' => $pkgDetails['DISK_QUOTA'] ?? '0',
                        'bandwidth_down' => $pkgDetails['BANDWIDTH'] ?? '0', // Using generic bandwidth for both?
                        'description' => "Synced from {$server->name}. Web Domains: {$pkgDetails['WEB_DOMAINS']}, E-mails: {$pkgDetails['MAIL_ACCOUNTS']}",
                        'price' => 0, // Hestia doesn't store price, keeping/defaulting to 0 for manual update
                        'is_active' => true
                    ]
                );
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
