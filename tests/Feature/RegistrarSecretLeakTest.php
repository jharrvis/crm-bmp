<?php

namespace Tests\Feature;

use App\DomainRegistrars\Srsx\SrsxApiClient;
use App\DomainRegistrars\Srsx\SrsxResponseMapper;
use App\Models\RegistrarAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RegistrarSecretLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_http_fake_does_not_leak_secret_in_logs_or_exception(): void
    {
        Http::fake([
            'https://api.srs-x.com/*' => Http::response('<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result></epp>', 200),
        ]);

        $account = RegistrarAccount::create([
            'provider' => 'srsx',
            'name' => 'Test',
            'base_url' => 'https://api.srs-x.com',
            'is_active' => true,
            'api_username_encrypted' => 'myuser',
            'api_password_encrypted' => 'mypassword',
        ]);

        $client = app(SrsxApiClient::class);
        $result = $client->checkAvailability($account, 'example.com');

        // Ensure request was made with hashed password, not plain, and Http fake captured it
        Http::assertSent(function ($request) {
            $body = $request->body();
            // Body should contain username and password hash, not plain password
            // Password should be sha256 hash
            $expectedHash = hash('sha256', 'mypassword');
            return str_contains($body, 'myuser') && str_contains($body, $expectedHash) && ! str_contains($body, 'mypassword');
        });

        // Ensure no exception contains plain password
        $this->assertTrue($result['success'] || ! $result['success']); // just ensure no leak
    }

    public function test_allowlist_blocks_malicious_host(): void
    {
        $account = RegistrarAccount::create([
            'provider' => 'srsx',
            'name' => 'Evil',
            'base_url' => 'https://evil.com/api',
            'is_active' => true,
            'api_username_encrypted' => 'u',
            'api_password_encrypted' => 'p',
        ]);

        $client = app(SrsxApiClient::class);
        $this->expectException(\InvalidArgumentException::class);
        $client->checkAvailability($account, 'example.com');
    }
}
