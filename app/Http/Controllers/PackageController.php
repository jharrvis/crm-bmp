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
}
