<?php

namespace App\Http\Controllers;

use App\Services\OperationalMapService;
use Illuminate\Http\Request;

class OperationalMapController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:maps.view');
    }

    public function index(Request $request)
    {
        $branches = \App\Models\Branch::orderBy('name')->get(['id', 'name']);
        $services = \App\Models\Service::orderBy('name')->get(['id', 'name']);
        return view('operational-map.index', compact('branches', 'services'));
    }

    public function locations(Request $request, OperationalMapService $service)
    {
        $filters = $request->only(['branch_id', 'status', 'subscription_status', 'service_id', 'province_code', 'regency_code', 'mapped', 'q', 'bbox', 'limit', 'include_unmapped']);
        $data = $service->locations($filters, $request->user());
        return response()->json($data);
    }

    public function summary(Request $request, OperationalMapService $service)
    {
        $filters = $request->only(['branch_id', 'status', 'service_id', 'province_code']);
        $data = $service->summary($filters, $request->user());
        return response()->json($data);
    }
}