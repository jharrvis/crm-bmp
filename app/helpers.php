<?php

use App\Models\SystemSetting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return SystemSetting::get($key, $default);
    }
}
