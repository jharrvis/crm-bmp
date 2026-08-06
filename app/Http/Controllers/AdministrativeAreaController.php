<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeArea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdministrativeAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clients.view');
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'level' => ['required', 'string', Rule::in(AdministrativeArea::LEVELS)],
            'parent_code' => 'nullable|string|max:20',
            'q' => 'nullable|string|max:100',
        ]);

        $level = $validated['level'];
        $parentCode = $validated['parent_code'] ?? null;

        if ($level !== AdministrativeArea::LEVEL_PROVINCE && blank($parentCode)) {
            return response()->json(['data' => []]);
        }

        $areas = AdministrativeArea::query()
            ->where('level', $level)
            ->when($level !== AdministrativeArea::LEVEL_PROVINCE, fn ($query) => $query->where('parent_code', $parentCode))
            ->when(filled($validated['q'] ?? null), fn ($query) => $query->where('name', 'like', '%'.$validated['q'].'%'))
            ->orderBy('name')
            ->limit(500)
            ->get(['code', 'name']);

        return response()->json(['data' => $areas]);
    }
}
