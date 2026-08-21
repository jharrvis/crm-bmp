<?php

namespace Tests\Feature;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Jobs\SyncRegistrarDomain;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Package;
use App\Models\RegistrarAccount;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionDomain;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrarDomainSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_normalizes_srsx_dates_and_reads_contact_details(): void
    {
        config(['domain-registrars.enabled' => true]);

        $branch = Branch::create(['name' => 'Jakarta', 'code' => 'JKT']);
        $client = Client::create(['branch_id' => $branch->id, 'client_code' => 'C-1', 'name' => 'PT Contoh']);
        $service = Service::create(['code' => 'DOM', 'name' => 'Domain', 'type' => 'domain']);
        $package = Package::create(['service_id' => $service->id, 'name' => 'Domain Tahunan', 'price' => 100000]);
        $subscription = Subscription::create(['client_id' => $client->id, 'package_id' => $package->id, 'subscription_code' => 'C-1-DOM01', 'status' => 'active']);
        $account = RegistrarAccount::create([
            'provider' => 'srsx',
            'name' => 'SRS-X Test',
            'base_url' => 'https://api.srs-x.com',
            'is_active' => true,
            'api_username_encrypted' => 'user',
            'api_password_encrypted' => 'password',
        ]);
        $domain = SubscriptionDomain::create([
            'subscription_id' => $subscription->id,
            'domain_name' => 'example.com',
            'registrar_account_id' => $account->id,
        ]);

        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/api/contact/info')) {
                return Http::response('<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><contactid>11</contactid><fname>Jane</fname><lname>Doe</lname><email>jane@example.com</email><company>PT Contoh</company></resultData></epp>', 200);
            }

            return Http::response('<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><domainid>35</domainid><domain>example.com</domain><startdate>2024-01-10</startdate><enddate>2025-01-10</enddate><authcode>SecretEpp1</authcode><contact_registrant>11</contact_registrant><contact_admin>11</contact_admin><ns1>ns1.example.com</ns1><ns2>ns2.example.com</ns2><status>active</status></resultData></epp>', 200);
        });

        (new SyncRegistrarDomain($domain->id))->handle(
            app(DomainRegistrarManager::class),
            app(AdminNotificationService::class),
        );

        $domain->refresh();

        $this->assertSame('2024-01-10', $domain->registered_at?->toDateString());
        $this->assertSame('2025-01-10', $domain->expires_at?->toDateString());
        $this->assertSame('35', $domain->provider_domain_id);
        $this->assertSame(['ns1.example.com', 'ns2.example.com'], $domain->provider_metadata['nameservers']);
        $this->assertSame('Jane', $domain->provider_metadata['contacts']['registrant']['fname']);
        $this->assertSame('Jane', $domain->provider_metadata['contacts']['admin']['fname']);
        $this->assertArrayNotHasKey('authcode', $domain->provider_metadata);
        $this->assertNull($domain->auth_code_encrypted);
        Http::assertSentCount(2);
    }
}
