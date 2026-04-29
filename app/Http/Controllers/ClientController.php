<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Branch;
use App\Models\ClientContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Client::with(['branch', 'primaryContact'])->select('clients.*');

            if ($request->has('branch_id') && $request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addColumn('branch_name', function ($row) {
                    return $row->branch ? $row->branch->name : '-';
                })
                ->addColumn('primary_contact_phone', function ($row) {
                    return $row->primaryContact ? $row->primaryContact->phone : '-';
                })
                ->make(true);
        }

        $branches = Branch::all();
        return view('clients.index', compact('branches'));
    }

    public function create()
    {
        return redirect()->route('clients.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:personal,business',
            'identity_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|string|in:active,inactive,suspended,prospect',
            'notes' => 'nullable|string',
            // Contacts validation
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:255',
            'contacts.*.phone' => 'required_with:contacts|string|max:50',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
            'contacts.*.whatsapp' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Generate Client Code
            $branch = Branch::findOrFail($validated['branch_id']);
            $count = Client::where('branch_id', $branch->id)->count() + 1;
            $client_code = sprintf("C-%s-%04d", strtoupper(Str::slug($branch->name)), $count);
            // Ensure unique just in case
            while (Client::where('client_code', $client_code)->exists()) {
                $count++;
                $client_code = sprintf("C-%s-%04d", strtoupper(Str::slug($branch->name)), $count);
            }

            $client = Client::create([
                'client_code' => $client_code,
                ...$validated
            ]);

            // Save Contacts
            if (!empty($request->contacts)) {
                foreach ($request->contacts as $index => $contactData) {
                    ClientContact::create([
                        'client_id' => $client->id,
                        'name' => $contactData['name'],
                        'phone' => $contactData['phone'],
                        'email' => $contactData['email'] ?? null,
                        'position' => $contactData['position'] ?? null,
                        'whatsapp' => $contactData['whatsapp'] ?? null,
                        'is_primary' => $index === 0 // First contact is primary by default/logic
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Klien berhasil ditambahkan.',
                    'client' => $client->load('branch', 'primaryContact')
                ]);
            }
            return redirect()->route('clients.index')->with('success', 'Klien berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal menambahkan klien: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan klien: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Client $client)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($client->load('branch', 'contacts'));
        }

        $client->load(['branch', 'contacts', 'subscriptions.package.service', 'portalAccount.sessions']);
        $packages = \App\Models\Package::where('is_active', true)->get(); // For "Add Service" modal if needed here

        return view('clients.show', compact('client', 'packages'));
    }

    public function edit(Client $client)
    {
        return redirect()->route('clients.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:personal,business',
            'identity_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|string|in:active,inactive,suspended,prospect',
            'notes' => 'nullable|string',
            // Contacts validation
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:255',
            'contacts.*.phone' => 'required_with:contacts|string|max:50',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
            'contacts.*.whatsapp' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Update Client
            $client->update($validated);

            // If branch changed, we might want to regenerate code? No, usually ID sticks.
            // Let's keep code permanent for now.

            // Sync Contacts
            // Strategy: Delete all existing and re-create, OR update existing. 
            // For simplicity in this modal UI, we might delete all and recreate is easiest BUT loses timestamps/ids.
            // Better: Since form sends all current contacts, we can delete those not in list, update those with ID, create new.
            // But modal UI implementation for "Update" typically needs to send IDs.
            // For MVP Phase 3 -> Let's do simple: Delete all and recreate.
            // Sync Contacts only if provided in request
            if ($request->has('contacts')) {
                // Determine if we are replacing all or just updating specific ones. 
                // Given the current logic is delete-all-and-recreate, let's keep it but only if 'contacts' key exists.
                // This allows atomic updates to other fields without wiping contacts.
                $client->contacts()->delete();

                if (!empty($request->contacts)) {
                    foreach ($request->contacts as $index => $contactData) {
                        ClientContact::create([
                            'client_id' => $client->id,
                            'name' => $contactData['name'],
                            'phone' => $contactData['phone'],
                            'email' => $contactData['email'] ?? null,
                            'position' => $contactData['position'] ?? null,
                            'whatsapp' => $contactData['whatsapp'] ?? null,
                            'is_primary' => $index === 0
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Klien berhasil diperbarui.',
                    'client' => $client->load('branch', 'primaryContact', 'contacts')
                ]);
            }
            return redirect()->route('clients.index')->with('success', 'Klien berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal update klien: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal update klien: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Client $client)
    {
        $client->delete(); // Cascade delete contacts defined in migration

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Klien berhasil dihapus.'
            ]);
        }
        return redirect()->route('clients.index')->with('success', 'Klien berhasil dihapus.');
    }
}
