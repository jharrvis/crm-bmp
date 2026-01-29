<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::with('contacts')->latest()->get();
        return view('vendors.index', compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cid' => 'nullable|string|max:100', // Circuit ID
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:50',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $vendor = Vendor::create([
                'name' => $request->name,
                'cid' => $request->cid,
                'address' => $request->address,
                'notes' => $request->notes,
            ]);

            if (!empty($request->contacts)) {
                foreach ($request->contacts as $contactData) {
                    // Filter out empty rows if any
                    if (empty($contactData['name']))
                        continue;

                    VendorContact::create([
                        'vendor_id' => $vendor->id,
                        'name' => $contactData['name'],
                        'phone' => $contactData['phone'] ?? null,
                        'email' => $contactData['email'] ?? null,
                        'position' => $contactData['position'] ?? null,
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Vendor berhasil ditambahkan.', 'data' => $vendor->load('contacts')]);
            }
            return redirect()->route('vendors.index')->with('success', 'Vendor berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        if (request()->wantsJson()) {
            return response()->json($vendor->load('contacts'));
        }
        return response()->json($vendor->load('contacts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cid' => 'nullable|string|max:100',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $vendor->update([
                'name' => $request->name,
                'cid' => $request->cid,
                'address' => $request->address,
                'notes' => $request->notes,
            ]);

            // Sync Contacts: Delete all and recreate
            $vendor->contacts()->delete();

            if (!empty($request->contacts)) {
                foreach ($request->contacts as $contactData) {
                    if (empty($contactData['name']))
                        continue;

                    VendorContact::create([
                        'vendor_id' => $vendor->id,
                        'name' => $contactData['name'],
                        'phone' => $contactData['phone'] ?? null,
                        'email' => $contactData['email'] ?? null,
                        'position' => $contactData['position'] ?? null,
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Vendor berhasil diperbarui.', 'data' => $vendor->load('contacts')]);
            }
            return redirect()->route('vendors.index')->with('success', 'Vendor berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vendor berhasil dihapus.']);
        }
        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil dihapus.');
    }
}
