<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:logs.view');
    }

    public function index(Request $request)
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->string('log_name')->toString());
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->integer('causer_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event')->toString());
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', '%' . $search . '%')
                    ->orWhere('subject_type', 'like', '%' . $search . '%')
                    ->orWhere('causer_type', 'like', '%' . $search . '%');
            });
        }

        $activities = $query->paginate(30)->withQueryString();
        $logNames = Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name');
        $events = Activity::query()->whereNotNull('event')->distinct()->orderBy('event')->pluck('event');
        $causers = Activity::query()
            ->with('causer')
            ->whereNotNull('causer_id')
            ->latest('causer_id')
            ->get()
            ->pluck('causer')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('activity_logs.index', compact('activities', 'logNames', 'events', 'causers'));
    }
}
