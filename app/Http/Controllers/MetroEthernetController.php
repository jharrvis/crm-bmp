<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MetroEthernet;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MetroEthernetController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:metro_ethernets.view')->only(['index', 'show']);
        $this->middleware('permission:metro_ethernets.create')->only(['store']);
        $this->middleware('permission:metro_ethernets.update')->only(['update']);
        $this->middleware('permission:metro_ethernets.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MetroEthernet::with('vendor')->select('metro_ethernets.*');

            if ($request->has('vendor_id') && $request->vendor_id) {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Calculate total bandwidth for the filtered result
            // Note: DataTables logic typically paginates. To get total of filtered, we might need a separate query or use `with` meta data.
            // However, Yajra DataTables allows sending extra data.
            // But doing a separate aggregate query on the same filtered scope is needed.

            // We can use `with` method on DataTables instance to send extra JSON data.
            // To do this efficiently, we clone the query before pagination.
            $totalBandwidth = $query->clone()->sum('bandwidth');

            return DataTables::of($query)
                ->addColumn('vendor_name', function ($row) {
                    return $row->vendor ? $row->vendor->name : '-';
                })
                ->addColumn('bandwidth_formatted', function ($row) {
                    return $row->bandwidth . ' Mbps';
                })
                ->with('total_bandwidth', $totalBandwidth) // Send total bandwidth
                ->make(true);
        }

        $vendors = Vendor::all();
        return view('metro_ethernets.index', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'cid' => 'nullable|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'bandwidth' => 'required|integer|min:0',
        ]);

        MetroEthernet::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Metro Ethernet berhasil ditambahkan.']);
        }
        return redirect()->route('metro-ethernets.index')->with('success', 'Data Metro Ethernet berhasil ditambahkan.');
    }

    public function show(Request $request, MetroEthernet $metroEthernet)
    {
        $metroEthernet->load('vendor');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($metroEthernet);
        }

        return view('metro_ethernets.show', compact('metroEthernet'));
    }

    public function update(Request $request, MetroEthernet $metroEthernet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'cid' => 'nullable|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'bandwidth' => 'required|integer|min:0',
        ]);

        $metroEthernet->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Metro Ethernet berhasil diperbarui.']);
        }
        return redirect()->route('metro-ethernets.index')->with('success', 'Data Metro Ethernet berhasil diperbarui.');
    }

    public function destroy(MetroEthernet $metroEthernet)
    {
        $metroEthernet->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Metro Ethernet berhasil dihapus.']);
        }
        return redirect()->route('metro-ethernets.index')->with('success', 'Data Metro Ethernet berhasil dihapus.');
    }
}
