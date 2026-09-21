<?php

namespace App\Http\Controllers;

use App\Models\InternetBackup;
use App\Models\Subscription;
use App\Models\SubscriptionConnectivity;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InternetBackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:internet_backups.view')->only(['index', 'show']);
        $this->middleware('permission:internet_backups.create')->only(['store']);
        $this->middleware('permission:internet_backups.update')->only(['update']);
        $this->middleware('permission:internet_backups.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = InternetBackup::with(['vendor', 'subscription.client'])->select('internet_backups.*');

            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->integer('vendor_id'));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('subscription_id')) {
                $query->where('subscription_id', $request->integer('subscription_id'));
            }

            $totalBandwidth = (clone $query)->sum('bandwidth_mbps');
            $totalCost = (clone $query)->sum('monthly_cost');

            return DataTables::of($query)
                ->escapeColumns([])
                ->addColumn('vendor_name', fn (InternetBackup $backup) => $backup->vendor?->name ?? '-')
                ->addColumn('subscription_code', fn (InternetBackup $backup) => $backup->subscription?->subscription_code ?? '-')
                ->addColumn('client_name', fn (InternetBackup $backup) => $backup->subscription?->client?->name ?? '-')
                ->addColumn('bandwidth_formatted', fn (InternetBackup $backup) => number_format($backup->bandwidth_mbps).' Mbps')
                ->addColumn('cost_formatted', fn (InternetBackup $backup) => $backup->monthly_cost !== null ? 'Rp '.number_format((float) $backup->monthly_cost, 0, ',', '.') : '-')
                ->addColumn('status_label', fn (InternetBackup $backup) => $backup->status_label)
                ->with('total_bandwidth', $totalBandwidth)
                ->with('total_cost', $totalCost)
                ->make(true);
        }

        return view('internet_backups.index', [
            'vendors' => Vendor::orderBy('name')->get(),
            'subscriptions' => Subscription::whereHas('connectivity')->with('client')->orderBy('subscription_code')->get(),
            'statuses' => InternetBackup::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($data['status'] === 'active' && empty($data['subscription_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Backup aktif harus memiliki subscription.',
            ], 422);
        }

        InternetBackup::create($data);

        return response()->json(['success' => true, 'message' => 'Internet Backup berhasil ditambahkan.']);
    }

    public function show(Request $request, InternetBackup $internetBackup)
    {
        $internetBackup->load(['vendor', 'subscription.client']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($internetBackup);
        }

        $vendors = Vendor::orderBy('name')->get();
        $subscriptions = Subscription::whereHas('connectivity')->with('client')->orderBy('subscription_code')->get();
        $statuses = InternetBackup::STATUS_OPTIONS;

        return view('internet_backups.show', compact('internetBackup', 'vendors', 'subscriptions', 'statuses'));
    }

    public function update(Request $request, InternetBackup $internetBackup)
    {
        $data = $this->validatedData($request);

        if ($data['status'] === 'active' && empty($data['subscription_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Backup aktif harus memiliki subscription.',
            ], 422);
        }

        $internetBackup->update($data);

        return response()->json(['success' => true, 'message' => 'Internet Backup berhasil diperbarui.']);
    }

    public function destroy(InternetBackup $internetBackup)
    {
        $internetBackup->delete();

        return response()->json(['success' => true, 'message' => 'Internet Backup berhasil dihapus.']);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'name' => 'required|string|max:150',
            'circuit_id' => 'nullable|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'gateway' => 'nullable|string|max:45',
            'bandwidth_mbps' => 'required|integer|min:1',
            'monthly_cost' => 'nullable|numeric|min:0',
            'active_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:planned,active,suspended,terminated',
            'notes' => 'nullable|string|max:1000',
        ], [
            'vendor_id.required' => 'Vendor wajib dipilih.',
            'vendor_id.exists' => 'Vendor tidak valid.',
            'subscription_id.exists' => 'Subscription tidak valid.',
            'name.required' => 'Nama koneksi wajib diisi.',
            'name.max' => 'Nama koneksi maksimal 150 karakter.',
            'bandwidth_mbps.required' => 'Bandwidth wajib diisi.',
            'bandwidth_mbps.integer' => 'Bandwidth harus berupa angka.',
            'bandwidth_mbps.min' => 'Bandwidth minimal 1 Mbps.',
            'monthly_cost.numeric' => 'Biaya bulanan harus berupa angka.',
            'monthly_cost.min' => 'Biaya bulanan tidak boleh negatif.',
            'status.in' => 'Status tidak valid.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        // Validate subscription must be connectivity type
        if (!empty($data['subscription_id'])) {
            $hasConnectivity = SubscriptionConnectivity::where('subscription_id', $data['subscription_id'])->exists();
            if (!$hasConnectivity) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'subscription_id' => 'Subscription harus ber jenis layanan connectivity.',
                ]);
            }
        }

        return $data;
    }
}
