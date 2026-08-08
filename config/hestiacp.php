<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HestiaCP Integration
    |--------------------------------------------------------------------------
    |
    | verify_ssl: aktifkan TLS verification terhadap server HestiaCP.
    | Aktif secara default. Hanya matikan untuk bootstrap pada CA internal yang
    | memang disengaja, bukan untuk produksi umum.
    |
    */

    'verify_ssl' => env('HESTIACP_VERIFY_SSL', true),

];