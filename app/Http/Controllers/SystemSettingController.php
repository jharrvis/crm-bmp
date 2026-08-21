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

        // P2: Validasi ketat untuk pengaturan registrar
        $strictRules = [
            'domain_registrar.mode' => ['in:disabled,read_only,managed'],
            'domain_registrar.sync_interval_hours' => ['integer', 'min:1', 'max:720'],
            'domain_registrar.timeout' => ['integer', 'min:1', 'max:120'],
        ];
        foreach ($strictRules as $key => $rules) {
            if ($request->has("settings.{$key}")) {
                $val = $request->input("settings.{$key}");
                validator(['value' => $val], ['value' => $rules], [], ['value' => $key])->validate();
            }
        }

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
