<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Domain Registrar Integration
    |--------------------------------------------------------------------------
    |
    | enabled: Global kill-switch. Jika false, semua menu/API registrar
    |          dinonaktifkan, data lokal tetap read-only.
    | mode:    disabled | read_only | managed (lihat SystemSetting juga)
    | timeout: timeout HTTP ke provider (detik)
    | verify_ssl: verifikasi TLS ke base_url provider
    | providers: daftar adapter yang diizinkan (srsx dulu)
    |
    */

    'enabled' => env('DOMAIN_REGISTRAR_ENABLED', false),

    'mode' => env('DOMAIN_REGISTRAR_MODE', 'read_only'),

    'timeout' => env('DOMAIN_REGISTRAR_TIMEOUT', 30),

    'verify_ssl' => env('DOMAIN_REGISTRAR_VERIFY_SSL', true),

    // Berapa lama operasi boleh menggantung berstatus `processing` sebelum dianggap
    // stale (worker mati/timeout di tengah API call). Operasi stale akan ditandai
    // `failed` oleh job berikutnya sehingga bisa di-retry aman.
    'operation_stale_minutes' => env('DOMAIN_REGISTRAR_OPERATION_STALE_MINUTES', 30),

    'providers' => [
        'srsx' => [
            'class' => \App\DomainRegistrars\Srsx\SrsxDomainRegistrarProvider::class,
            'enabled' => env('DOMAIN_REGISTRAR_SRSX_ENABLED', true),
        ],
    ],

    // P3: hanya endpoint API resmi — kb.srs-x.com (host dokumentasi) tidak boleh
    //     menerima kredensial API bila admin salah memasukkan base URL.
    'allowed_base_urls' => [
        'https://api.srs-x.com',
    ],

    // Endpoint API reseller SRS-X memakai host khusus akun, mis. srb168.srs-x.com.
    // Pola dibatasi angka agar tidak membuka seluruh subdomain srs-x.com.
    'allowed_host_patterns' => [
        '/^srb[0-9]+\\.srs-x\\.com$/',
    ],
];
