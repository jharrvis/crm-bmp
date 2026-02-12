<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Vendor;
use App\Models\MetroEthernet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::with('metroEthernet.vendor')->latest()->get();
        $vendors = Vendor::all();
        return view('services.index', compact('services', 'vendors'));
    }

    public function create()
    {
        return redirect()->route('services.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:services,code',
            'type' => 'required|string',
            'description' => 'nullable|string',
            // Metro Ethernet Validation
            'metro_vendor_id' => 'nullable|required_if:type,connectivity|exists:vendors,id',
            'metro_cid' => 'nullable|string|max:100',
            'metro_ip_address' => 'nullable|string|max:45',
            'metro_bandwidth' => 'nullable|required_if:type,connectivity|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $service = Service::create([
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            // Create Metro Ethernet if data exists
            if ($request->type === 'connectivity' || $request->filled('metro_vendor_id')) {
                MetroEthernet::create([
                    'service_id' => $service->id,
                    'vendor_id' => $request->metro_vendor_id,
                    'cid' => $request->metro_cid,
                    'ip_address' => $request->metro_ip_address,
                    'bandwidth' => $request->metro_bandwidth ?? 0,
                ]);
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Layanan berhasil ditambahkan.',
                    'service' => $service->load('metroEthernet.vendor')
                ]);
            }

            return redirect()->route('services.index')->with('success', 'Layanan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan layanan.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Service $service)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($service->load('metroEthernet.vendor'));
        }
        return redirect()->route('services.index');
    }

    public function edit(Service $service)
    {
        return redirect()->route('services.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:services,code,' . $service->id,
            'type' => 'required|string',
            'description' => 'nullable|string',
            // Metro Ethernet Validation
            'metro_vendor_id' => 'nullable|required_if:type,connectivity|exists:vendors,id',
            'metro_cid' => 'nullable|string|max:100',
            'metro_ip_address' => 'nullable|string|max:45',
            'metro_bandwidth' => 'nullable|required_if:type,connectivity|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $service->fill([
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
                'description' => $request->description,
            ]);

            if ($request->has('is_active')) {
                $service->is_active = $request->boolean('is_active');
            } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
                // If checkbox is unchecked it's not sent in request, so we assume false if not present but strictly only if it was a form submit context which is hard to distinguish here wholly without hidden field.
                // But the JS sends checkbox value explicitly usually.
                $service->is_active = $request->boolean('is_active');
            }
            $service->save();

            // Handle Metro Ethernet
            if ($request->type === 'connectivity' || $request->filled('metro_vendor_id')) {
                // Check if exists
                if ($service->metroEthernet) {
                    $service->metroEthernet->update([
                        'vendor_id' => $request->metro_vendor_id,
                        'cid' => $request->metro_cid,
                        'ip_address' => $request->metro_ip_address,
                        'bandwidth' => $request->metro_bandwidth ?? 0,
                    ]);
                } else {
                    MetroEthernet::create([
                        'service_id' => $service->id,
                        'vendor_id' => $request->metro_vendor_id,
                        'cid' => $request->metro_cid,
                        'ip_address' => $request->metro_ip_address,
                        'bandwidth' => $request->metro_bandwidth ?? 0,
                    ]);
                }
            } else {
                // If type changed to something else, maybe delete the Metro Info?
                // For now, let's keep it or delete it? User didn't specify.
                // Safest is to keep it but maybe not update it. Or delete it if it's strictly bound to type.
                // Let's leave it for now.
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Layanan berhasil diperbarui.',
                    'service' => $service->load('metroEthernet.vendor')
                ]);
            }

            return redirect()->route('services.index')->with('success', 'Layanan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui layanan.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Service $service)
    {
        if ($service->packages()->count() > 0) {
            $msg = 'Layanan tidak dapat dihapus karena memiliki paket aktif.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg
                ], 400);
            }
            return redirect()->route('services.index')->with('error', $msg);
        }

        $service->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dihapus.'
            ]);
        }

        return redirect()->route('services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
