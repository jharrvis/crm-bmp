<?php

namespace App\DomainRegistrars\Srsx;

use App\DomainRegistrars\Contracts\DomainRegistrarCapabilities;
use App\DomainRegistrars\Contracts\DomainRegistrarProvider;
use App\Models\RegistrarAccount;

class SrsxDomainRegistrarProvider implements DomainRegistrarProvider
{
    public function __construct(
        protected SrsxApiClient $client,
        protected SrsxResponseMapper $mapper,
    ) {}

    public function capabilities(): DomainRegistrarCapabilities
    {
        return new DomainRegistrarCapabilities(
            checkAvailability: true,
            getDomain: true,
            listDomains: false, // P1: matikan sampai endpoint inventory tervalidasi UAT — hindari loop gagal tiap jam
            testConnection: true,
            renew: false, // Fase 3
            updateNameservers: true, // Fase 2 — api/domain/updatens (mode managed)
            manageDns: true, // Fase 2 — api/dns/info + api/dns/edit (hanya managed_dns_enabled=true)
            manageContacts: false, // tidak ada endpoint WHOIS/contact via API SRS-X
            viewEpp: true, // Fase 2 — api/domain/getepp + api/domain/setepp
        );
    }

    public function testConnection(RegistrarAccount $account): array
    {
        return $this->client->testConnection($account);
    }

    public function checkAvailability(RegistrarAccount $account, string $domain): array
    {
        return $this->client->checkAvailability($account, $domain);
    }

    public function getDomain(RegistrarAccount $account, string $domain): array
    {
        return $this->client->getDomain($account, $domain);
    }

    public function getContact(RegistrarAccount $account, string $contactId): array
    {
        return $this->client->getContact($account, $contactId);
    }

    public function listDomains(RegistrarAccount $account, array $filter = []): array
    {
        return $this->client->listDomains($account, $filter);
    }

    public function renew(RegistrarAccount $account, string $domain, int $years): array
    {
        return ['success' => false, 'message' => 'Perpanjangan belum diaktifkan (Fase 3).'];
    }

    public function updateNameservers(RegistrarAccount $account, string $domain, array $nameservers): array
    {
        return $this->client->updateNameservers($account, $domain, $nameservers);
    }

    public function getEpp(RegistrarAccount $account, string $domain): array
    {
        return $this->client->getEpp($account, $domain);
    }

    public function setEpp(RegistrarAccount $account, string $domain, string $eppCode): array
    {
        return $this->client->setEpp($account, $domain, $eppCode);
    }

    public function getDnsInfo(RegistrarAccount $account, string $domain): array
    {
        return $this->client->getDnsInfo($account, $domain);
    }

    public function editDnsRecord(RegistrarAccount $account, string $domain, array $record): array
    {
        return $this->client->editDnsRecord($account, $domain, $record);
    }
}
