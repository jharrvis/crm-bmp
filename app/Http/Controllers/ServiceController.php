<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::latest()->get();
        return view('services.index', compact('services'));
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
        ]);

        $service = Service::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil ditambahkan.',
                'service' => $service
            ]);
        }

        return redirect()->route('services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Service $service)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($service);
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
        ]);

        $service->fill($validated);
        if ($request->has('is_active')) {
            $service->is_active = $request->boolean('is_active');
        } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
            $service->is_active = $request->has('is_active');
        }
        $service->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diperbarui.',
                'service' => $service
            ]);
        }

        return redirect()->route('services.index')->with('success', 'Layanan berhasil diperbarui.');
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
