<?php

namespace App\Services;

use App\Models\HostingServer;
use DomainException;

class WebHostResolver
{
    public function resolve(HostingServer $server): WebHostingServerAdapter
    {
        if (! $server->is_active) {
            throw new DomainException('Server hosting web tidak aktif.');
        }

        return match ($server->type) {
            'hestiacp' => new HestiaCPService($server),
            default => throw new DomainException('Tipe server hosting web tidak didukung.'),
        };
    }
}