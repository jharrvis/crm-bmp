<?php

namespace App\DomainRegistrars\Contracts;

class DomainRegistrarCapabilities
{
    public function __construct(
        public bool $checkAvailability = true,
        public bool $getDomain = true,
        public bool $listDomains = true,
        public bool $testConnection = true,
        public bool $renew = false,
        public bool $updateNameservers = false,
        public bool $manageDns = false,
        public bool $manageContacts = false,
        public bool $viewEpp = true,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}