<?php

namespace App\DomainRegistrars\Contracts;

use App\Models\RegistrarAccount;

interface DomainRegistrarProvider
{
    public function capabilities(): DomainRegistrarCapabilities;

    /**
     * @return array{success: bool, message: string, code?: string}
     */
    public function testConnection(RegistrarAccount $account): array;

    /**
     * @return array{success: bool, available?: bool, message: string, code?: string}
     */
    public function checkAvailability(RegistrarAccount $account, string $domain): array;

    /**
     * @return array{success: bool, data?: array, message: string, code?: string}
     */
    public function getDomain(RegistrarAccount $account, string $domain): array;

    /**
     * Ambil detail contact registrar/WHOIS berdasarkan ID contact dari info domain.
     * Operasi ini read-only dan tidak boleh mengubah data contact provider.
     *
     * @return array{success: bool, data?: array, message: string, code?: string}
     */
    public function getContact(RegistrarAccount $account, string $contactId): array;

    /**
     * @return array{success: bool, data?: array, message: string, code?: string, next_cursor?: string}
     */
    public function listDomains(RegistrarAccount $account, array $filter = []): array;

    /**
     * Fase 2+ — renew, updateNameservers, dll.
     * @return array{success: bool, message: string}
     */
    public function renew(RegistrarAccount $account, string $domain, int $years): array;

    /**
     * Update nameservers domain (mutasi — mode managed).
     * @return array{success: bool, message: string, code?: string}
     */
    public function updateNameservers(RegistrarAccount $account, string $domain, array $nameservers): array;

    /**
     * Ambil EPP code dari provider (read-only, mode read_only|managed).
     * @return array{success: bool, epp?: string, message: string, code?: string}
     */
    public function getEpp(RegistrarAccount $account, string $domain): array;

    /**
     * Ganti EPP code domain (mutasi — mode managed).
     * @return array{success: bool, message: string, code?: string}
     */
    public function setEpp(RegistrarAccount $account, string $domain, string $eppCode): array;

    /**
     * Ambil informasi DNS (managed DNS SRS-X) — read-only.
     * @return array{success: bool, data?: array, message: string, code?: string}
     */
    public function getDnsInfo(RegistrarAccount $account, string $domain): array;

    /**
     * Edit satu record DNS (mutasi — mode managed, wajib managed_dns_enabled=true).
     * @return array{success: bool, message: string, code?: string}
     */
    public function editDnsRecord(RegistrarAccount $account, string $domain, array $record): array;
}
