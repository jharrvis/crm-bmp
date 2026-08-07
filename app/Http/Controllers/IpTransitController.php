<?php

namespace App\Http\Controllers;

use App\Models\IpTransit;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IpTransitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ip_transits.view')->only(['index', 'show']);
        $this->middleware('permission:ip_transits.create')->only(['store']);
        $this->middleware('permission:ip_transits.update')->only(['update']);
        $this->middleware('permission:ip_transits.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = IpTransit::with('vendor')->select('ip_transits.*');

            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->integer('vendor_id'));
            }

            $totalBandwidth = (clone $query)->sum('bandwidth');

            return DataTables::of($query)
                ->addColumn('vendor_name', fn (IpTransit $transit) => $transit->vendor?->name ?? '-')
                ->addColumn('bandwidth_formatted', fn (IpTransit $transit) => number_format($transit->bandwidth).' Mbps')
                ->with('total_bandwidth', $totalBandwidth)
                ->make(true);
        }

        return view('ip_transits.index', ['vendors' => Vendor::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        IpTransit::create($this->validatedData($request));

        return response()->json(['success' => true, 'message' => 'IP Transit berhasil ditambahkan.']);
    }

    public function show(Request $request, IpTransit $ipTransit)
    {
        $ipTransit->load('vendor');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($ipTransit);
        }

        return view('ip_transits.show', compact('ipTransit'));
    }

    public function update(Request $request, IpTransit $ipTransit)
    {
        $ipTransit->update($this->validatedData($request));

        return response()->json(['success' => true, 'message' => 'IP Transit berhasil diperbarui.']);
    }

    public function destroy(IpTransit $ipTransit)
    {
        $ipTransit->delete();

        return response()->json(['success' => true, 'message' => 'IP Transit berhasil dihapus.']);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:150',
            'cid' => 'nullable|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'ip_gateway' => 'nullable|ip',
            'as_number' => ['nullable', 'string', 'max:20', 'regex:/^(AS)?[0-9]+$/i'],
            'bandwidth' => 'required|integer|min:1',
        ], [
            'ip_gateway.ip' => 'IP Gateway harus berupa alamat IP yang valid.',
            'as_number.regex' => 'AS Number harus berupa angka atau format AS12345.',
        ]);
    }
}
