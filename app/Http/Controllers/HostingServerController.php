<?php

namespace App\Http\Controllers;

use App\Models\HostingServer;
use Illuminate\Http\Request;

class HostingServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:servers.view')->only(['index', 'show']);
        $this->middleware('permission:servers.create')->only(['create', 'store']);
        $this->middleware('permission:servers.update')->only(['edit', 'update']);
        $this->middleware('permission:servers.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servers = HostingServer::latest()->get();
        return view('servers.index', compact('servers'));
    }

    public function create()
    {
        return redirect()->route('servers.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|string',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'max_accounts' => 'required|integer',
            'username' => 'nullable|string|max:255',
            'api_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $server = HostingServer::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Server berhasil ditambahkan.',
                'server' => $server
            ]);
        }

        return redirect()->route('servers.index')->with('success', 'Server berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, HostingServer $server)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($server);
        }
        return redirect()->route('servers.index');
    }

    public function edit(HostingServer $server)
    {
        return redirect()->route('servers.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HostingServer $server)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|string',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'max_accounts' => 'required|integer',
            'username' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->filled('api_key')) {
            $validated['api_key'] = $request->api_key;
        }
        if ($request->filled('secret_key')) {
            $validated['secret_key'] = $request->secret_key;
        }

        $server->fill($validated);
        if ($request->has('is_active')) {
            $server->is_active = $request->boolean('is_active');
        } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
            $server->is_active = $request->has('is_active');
        }
        $server->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Server berhasil diperbarui.',
                'server' => $server
            ]);
        }

        return redirect()->route('servers.index')->with('success', 'Server berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, HostingServer $server)
    {
        $server->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Server berhasil dihapus.'
            ]);
        }
        return redirect()->route('servers.index')->with('success', 'Server berhasil dihapus.');
    }
}
