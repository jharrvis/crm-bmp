<?php

namespace App\Services;

interface WebHostingServerAdapter
{
    /**
     * Test connectivity to the server with configured credentials.
     */
    public function testConnection(): array;

    /**
     * List users with their basic info. Returns ['success' => bool, 'data' => array, 'message' => string].
     */
    public function listUsers(): array;

    /**
     * Find a single user by username. Returns ['success' => bool, 'data' => ?array, 'message' => string].
     */
    public function findUser(string $username): array;

    /**
     * Return read-only account usage and resource detail for one user.
     */
    public function userDetails(string $username): array;

    /**
     * List web domains owned by a user. Returns ['success' => bool, 'data' => array, 'message' => string].
     */
    public function listWebDomains(string $username): array;

    /**
     * List available Hestia packages. Returns ['success' => bool, 'data' => array, 'message' => string].
     */
    public function listUserPackages(): array;

    /**
     * Create a user account idempotently. Returns ['success' => bool, 'data' => ?array, 'message' => string].
     */
    public function createUser(string $username, string $password, string $email, string $name, string $package): array;

    /**
     * Create a web domain for a user. Returns ['success' => bool, 'data' => ?array, 'message' => string].
     */
    public function createWebDomain(string $username, string $domain): array;

    /**
     * Suspend a user. Returns bool.
     */
    public function suspendUser(string $username): bool;

    /**
     * Reactivate a suspended user. Returns bool.
     */
    public function unsuspendUser(string $username): bool;

    /**
     * Change a user's password. Returns bool.
     */
    public function changePassword(string $username, string $password): bool;

    /**
     * Delete a user and all its resources. Returns bool.
     */
    public function deleteUser(string $username): bool;
}
