<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\Branch;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:routers.view')->only(['index', 'show']);
        $this->middleware('permission:routers.create')->only(['create', 'store']);
        $this->middleware('permission:routers.update')->only(['edit', 'update']);
        $this->middleware('permission:routers.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routers = Router::with('branch')->latest()->get();
        $branches = Branch::all();
        return view('routers.index', compact('routers', 'branches'));
    }

    public function create()
    {
        return redirect()->route('routers.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'user' => 'required|string|max:255',
            'password' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $router = Router::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Router berhasil ditambahkan.',
                'router' => $router->load('branch')
            ]);
        }

        return redirect()->route('routers.index')->with('success', 'Router berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Router $router)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($router->load('branch'));
        }

        $router->load('branch');
        $connectivities = $router->connectivities()
            ->with([
                'subscription:id,client_id,package_id,subscription_code,status',
                'subscription.client:id,name,client_code',
                'subscription.package:id,name',
            ])
            ->latest()
            ->paginate(15);

        return view('routers.show', compact('router', 'connectivities'));
    }

    public function edit(Router $router)
    {
        return redirect()->route('routers.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'user' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = $request->password;
        }

        $router->fill($validated);
        if ($request->has('is_active')) {
            $router->is_active = $request->boolean('is_active');
        } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
            // If checkbox is unchecked, it comes as null or missing.
            // We only want to set to false if it's explicitly an update form submission logic where missing means unchecked.
            // But be careful not to unset it if we send partial data via JSON.
            // For now, assuming Full Form Submission via AJAX:
            $router->is_active = $request->has('is_active');
        }
        $router->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Router berhasil diperbarui.',
                'router' => $router->load('branch')
            ]);
        }

        return redirect()->route('routers.index')->with('success', 'Router berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Router $router)
    {
        $router->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Router berhasil dihapus.'
            ]);
        }

        return redirect()->route('routers.index')->with('success', 'Router berhasil dihapus.');
    }
}
