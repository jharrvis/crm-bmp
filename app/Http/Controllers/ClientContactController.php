<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clients.update');
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'position' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:50',
            'is_primary' => 'boolean'
        ]);

        try {
            // Handle primary contact logic
            if ($request->boolean('is_primary')) {
                $client->contacts()->update(['is_primary' => false]);
                $validated['is_primary'] = true;
            } else {
                // If this is the first contact, make it primary automatically
                if ($client->contacts()->count() === 0) {
                    $validated['is_primary'] = true;
                } else {
                    $validated['is_primary'] = false;
                }
            }

            $validated['client_id'] = $client->id;
            $contact = ClientContact::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Kontak berhasil ditambahkan.',
                'contact' => $contact
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan kontak.'], 500);
        }
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, Client $client, ClientContact $contact)
    {
        // Ensure contact belongs to client
        if ($contact->client_id !== $client->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'position' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:50',
            'is_primary' => 'boolean'
        ]);

        try {
            DB::transaction(function () use ($client, $contact, $validated, $request) {
                if ($request->boolean('is_primary')) {
                    $client->contacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
                }

                // If unsetting primary, ensure there's at least one primary? 
                // Typically we don't allow unsetting primary directly without setting another. 
                // But let's just save.

                $contact->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Kontak berhasil diperbarui.',
                'contact' => $contact->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui kontak.'], 500);
        }
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Client $client, ClientContact $contact)
    {
        if ($contact->client_id !== $client->id) {
            abort(403);
        }

        if ($contact->is_primary) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus kontak utama.'], 422);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kontak berhasil dihapus.'
        ]);
    }
}
