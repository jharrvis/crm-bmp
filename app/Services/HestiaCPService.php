<?php

namespace App\Services;

use App\Models\HostingServer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HestiaCPService
{
    protected $server;
    protected $baseUrl;
    protected $authHash;

    public function __construct(HostingServer $server)
    {
        $this->server = $server;
        $this->baseUrl = "https://{$server->host}:{$server->port}/api/";

        // Construct hash for API authentication (AccessKey:SecretKey)
        // Ensure keys are decrypted (model cast handles this)
        $this->authHash = $server->api_key . ':' . $server->secret_key;
    }

    /**
     * Send Request to HestiaCP API
     */
    protected function request($cmd, $args = [])
    {
        // Prepare payload
        $payload = [
            'hash' => $this->authHash,
            'cmd' => $cmd,
        ] + $args; // Merge arguments (arg1, arg2...)

        // Log request (without sensitive info)
        Log::info("HestiaCP Request: {$cmd} to {$this->server->host}", ['user' => $args['arg1'] ?? '']);

        try {
            // HestiaCP API typically expects form-data or x-www-form-urlencoded
            // Since it uses self-signed certs often, we might need to verify=false (configurable?)
            // For production, verification is recommended.
            $response = Http::withoutVerifying()
                ->asForm()
                ->post($this->baseUrl, $payload);

            if ($response->failed()) {
                Log::error("HestiaCP Connection Failed: " . $response->body());
                return ['success' => false, 'message' => 'Connection failed: ' . $response->status()];
            }

            $body = $response->body();

            // HestiaCP returns "Error: message" or "OK" (sometimes just empty or specific return code)
            // v-add-user returns nothing on success (return code 0)

            // Analyze return code (Hestia usually just returns text body)
            if (str_starts_with($body, 'Error:')) {
                Log::error("HestiaCP Error: {$body}");
                return ['success' => false, 'message' => $body];
            }

            return ['success' => true, 'data' => $body];

        } catch (\Exception $e) {
            Log::error("HestiaCP Exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create User Account
     * v-add-user user password email package name
     */
    public function createUser($user, $password, $email, $name, $package = 'default')
    {
        return $this->request('v-add-user', [
            'arg1' => $user,
            'arg2' => $password,
            'arg3' => $email,
            'arg4' => $package,
            'arg5' => $name,
        ]);
    }

    /**
     * Create Web Domain
     * v-add-web-domain user domain
     */
    public function createWebDomain($user, $domain)
    {
        return $this->request('v-add-web-domain', [
            'arg1' => $user,
            'arg2' => $domain,
        ]);
    }

    /**
     * Suspend User
     * v-suspend-user user
     */
    public function suspendUser($user)
    {
        return $this->request('v-suspend-user', [
            'arg1' => $user,
        ]);
    }

    /**
     * Unsuspend User
     * v-unsuspend-user user
     */
    public function unsuspendUser($user)
    {
        return $this->request('v-unsuspend-user', [
            'arg1' => $user,
        ]);
    }

    /**
     * Delete User
     * v-delete-user user
     */
    public function deleteUser($user)
    {
        return $this->request('v-delete-user', [
            'arg1' => $user,
        ]);
    }

    /**
     * Change User Password
     * v-change-user-password user password
     */
    public function changePassword($user, $password)
    {
        return $this->request('v-change-user-password', [
            'arg1' => $user,
            'arg2' => $password,
        ]);
    }
}
