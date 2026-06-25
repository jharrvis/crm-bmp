<?php

namespace App\Http\Controllers;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.view');
    }

    public function index()
    {
        return view('settings.index');
    }
}
