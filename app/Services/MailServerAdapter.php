<?php

namespace App\Services;

interface MailServerAdapter
{
    /**
     * Ensure a domain exists without creating duplicates when a job is retried.
     */
    public function ensureDomain(string $domain): bool;

    /**
     * Ensure a domain exists on the mail server.
     */
    public function createDomain(string $domain): bool;

    /**
     * Create an email account. Returns ['success' => bool, 'id' => ?string, 'message' => ?string].
     */
    public function createAccount(string $email, string $password, array $attributes = []): array;

    /**
     * Change password of an email account.
     */
    public function setPassword(string $email, string $password): bool;

    /**
     * Suspend an email account (login denied, data retained).
     */
    public function suspend(string $email): bool;

    /**
     * Reactivate a suspended email account.
     */
    public function activate(string $email): bool;

    /**
     * Delete an email account permanently.
     */
    public function deleteAccount(string $email): bool;

    /**
     * List email accounts belonging to a domain.
     *
     * Returns ['success' => bool, 'data' => array, 'message' => string].
     */
    public function listAccounts(string $domain): array;
}
