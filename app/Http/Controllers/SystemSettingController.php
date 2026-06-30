<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.view')->only(['index']);
        $this->middleware('permission:settings.update')->only(['update']);
    }

    public function index()
    {
        $groups = SystemSetting::orderBy('group')->orderBy('id')->get()->groupBy('group');

        return view('settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($request->input('settings', []) as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();

            if (! $setting) {
                continue;
            }

            if ($setting->type === 'boolean') {
                $value = $value ? 'true' : 'false';
            }

            $setting->update(['value' => $value]);
        }

        SystemSetting::flushCache();

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
