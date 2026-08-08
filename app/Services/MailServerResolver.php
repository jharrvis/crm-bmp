<?php

namespace App\Services;

use App\Models\HostingServer;
use DomainException;

class MailServerResolver
{
    public function resolve(HostingServer $server): MailServerAdapter
    {
        if (! $server->is_active) {
            throw new DomainException('Server mail tidak aktif.');
        }

        return match ($server->type) {
            'zimbra' => new ZimbraService($server),
            default => throw new DomainException('Tipe server mail tidak didukung.'),
        };
    }
}
