<?php

namespace Tests\Feature;

use App\Jobs\EditDomainDnsRecord;
use App\Jobs\UpdateDomainNameservers;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Package;
use App\Models\RegistrarAccount;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrarOperationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        config(['domain-registrars.enabled' => true]);
    }

    private function setMode(string $mode): void
    {
        // Mode disimpan di SystemSetting (diseed migration system_settings), bukan config murni
        \App\Models\SystemSetting::query()->where('key', 'domain_registrar.mode')->update(['value' => $mode]);
        \App\Models\SystemSetting::flushCache();
    }

    private function eppXml(string $code = 'AbCd1234'): string
    {
        return '<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><epp>'.$code.'</epp></resultData></epp>';
    }

    private function successXml(): string
    {
        return '<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result></epp>';
    }

    private function makeAccount(): RegistrarAccount
    {
        return RegistrarAccount::create([
            'provider' => 'srsx',
            'name' => 'Akun Operasi',
            'base_url' => 'https://api.srs-x.com',
            'is_active' => true,
            'api_username_encrypted' => 'user123',
            'api_password_encrypted' => 'pass123',
        ]);
    }

    private function makeSubscription(): Subscription
    {
        $suffix = (string) (Subscription::count() + 1);
        $service = Service::firstOrCreate(['code' => 'HST'], ['name' => 'Hosting', 'type' => 'hosting']);
        $package = Package::firstOrCreate(['service_id' => $service->id, 'name' => 'Hosting 1GB'], ['price' => 50000]);
        $branch = Branch::firstOrCreate(['code' => 'JKT'], ['name' => 'Jakarta']);
        $client = Client::create(['branch_id' => $branch->id, 'client_code' => 'C-'.$suffix, 'name' => 'PT Contoh '.$suffix]);

        return Subscription::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'subscription_code' => 'SUB-'.$suffix,
            'status' => 'active',
        ]);
    }

    private function makeLinkedDomain(Subscription $subscription, RegistrarAccount $account): SubscriptionDomain
    {
        return $subscription->domain()->create([
            'domain_name' => 'example.com',
            'registrar_account_id' => $account->id,
            'provider_domain_id' => 'example.com',
            'sync_status' => 'synced',
        ]);
    }

    // ===== Authorization =====

    public function test_cs_cannot_update_nameservers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS');
        $this->actingAs($user);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.nameservers', [$domain->subscription, $domain]), [
            'nameserver_1' => 'ns1.example.com', 'nameserver_2' => 'ns2.example.com',
            'confirm_domain' => 'example.com',
        ])->assertForbidden();
    }

    public function test_cs_cannot_fetch_epp(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS');
        $this->actingAs($user);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.epp.fetch', [$domain->subscription, $domain]))->assertForbidden();
    }

    public function test_cs_cannot_edit_dns(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS');
        $this->actingAs($user);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.dns.edit', [$domain->subscription, $domain]), [
            'dnsid' => 1, 'record' => 'www', 'confirm_domain' => 'example.com',
        ])->assertForbidden();
    }

    // P1 audit: permission `domains.set_epp` terpisah dari `domains.view_epp`.
    public function test_view_epp_without_set_epp_cannot_change_epp(): void
    {
        $this->setMode('managed');
        Http::fake(['https://api.srs-x.com/*' => Http::response($this->eppXml(), 200)]);
        Queue::fake();
        $user = User::factory()->create();
        $user->assignRole('Employee');
        $user->givePermissionTo('domains.view_epp');
        $this->actingAs($user);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        // Boleh melihat (fetch) EPP…
        $this->post(route('domain-operations.epp.fetch', [$domain->subscription, $domain]))->assertRedirect();

        // …tapi tidak boleh mengganti EPP.
        $this->post(route('domain-operations.epp.set', [$domain->subscription, $domain]), [
            'epp_code' => 'NewEpp42', 'confirm_domain' => 'example.com',
        ])->assertForbidden();

        Queue::assertNotPushed(\App\Jobs\SetDomainEpp::class);
    }

    // ===== Mode gate =====

    public function test_read_only_mode_blocks_update_nameservers(): void
    {
        $this->setMode('read_only');
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.nameservers', [$domain->subscription, $domain]), [
            'nameserver_1' => 'ns1.example.com', 'nameserver_2' => 'ns2.example.com',
            'confirm_domain' => 'example.com',
        ])->assertForbidden();
    }

    public function test_read_only_mode_allows_fetch_epp(): void
    {
        $this->setMode('read_only');
        Http::fake(['https://api.srs-x.com/*' => Http::response($this->eppXml(), 200)]);

        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.epp.fetch', [$domain->subscription, $domain]))
            ->assertRedirect();

        $domain->refresh();
        $this->assertSame('AbCd1234', $domain->auth_code);
        $this->assertDatabaseHas('registrar_operations', [
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'get_epp',
            'status' => 'completed',
        ]);
    }

    // ===== Confirmation + dispatch =====

    public function test_update_nameservers_requires_retype_confirmation(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.nameservers', [$domain->subscription, $domain]), [
            'nameserver_1' => 'ns1.example.com', 'nameserver_2' => 'ns2.example.com',
            'confirm_domain' => 'salah.com',
        ])->assertStatus(422);

        Queue::assertNotPushed(UpdateDomainNameservers::class);
    }

    public function test_owner_can_dispatch_update_nameservers_in_managed_mode(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.nameservers', [$domain->subscription, $domain]), [
            'nameserver_1' => 'NS1.EXAMPLE.COM', 'nameserver_2' => 'ns2.example.com',
            'nameserver_3' => 'ns3.example.com',
            'confirm_domain' => 'example.com',
        ])->assertRedirect();

        Queue::assertPushed(UpdateDomainNameservers::class, function ($job) {
            return $job->nameservers === ['ns1.example.com', 'ns2.example.com', 'ns3.example.com'];
        });
    }

public function test_set_epp_dispatch_in_managed_mode(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.epp.set', [$domain->subscription, $domain]), [
            'epp_code' => 'NewEpp42', 'confirm_domain' => 'example.com',
        ])->assertRedirect();

        // Job hanya membawa operation ID — secret tersimpan terenkripsi di operasi.
        Queue::assertPushed(\App\Jobs\SetDomainEpp::class, function ($job) {
            $this->assertTrue(property_exists($job, 'operationId'));
            $this->assertFalse(property_exists($job, 'eppCode'));
            $this->assertStringNotContainsString('NewEpp42', serialize($job));

            return true;
        });

        $this->assertDatabaseHas('registrar_operations', [
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'set_epp',
            'status' => 'queued',
        ]);
        $op = \App\Models\RegistrarOperation::where('subscription_domain_id', $domain->id)
            ->where('operation_type', 'set_epp')->first();
        $this->assertNotNull($op->request_secret_encrypted);
        $this->assertSame('NewEpp42', decrypt($op->request_secret_encrypted));
    }

    public function test_set_epp_rejects_double_submit_while_processing(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'set_epp',
            'status' => 'processing',
            'idempotency_key' => 'set_epp_'.$domain->id.'_x',
            'started_at' => now(),
        ]);

        $this->post(route('domain-operations.epp.set', [$domain->subscription, $domain]), [
            'epp_code' => 'Other99', 'confirm_domain' => 'example.com',
        ])->assertSessionHas('error');

        Queue::assertNotPushed(\App\Jobs\SetDomainEpp::class);
    }

    // ===== Retry aman (P0/P1 audit) =====

    private function makeFailedOperation(int $accountId, int $domainId, string $type, array $payload): \App\Models\RegistrarOperation
    {
        return \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $accountId,
            'subscription_domain_id' => $domainId,
            'operation_type' => $type,
            'status' => 'failed',
            'request_payload_redacted' => $payload,
            'started_at' => now(),
            'completed_at' => now(),
            'error_summary' => 'gagal uji',
        ]);
    }

    public function test_retry_rejects_completed_operation(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'completed',
            'request_payload_redacted' => ['nameservers' => ['ns1.example.com', 'ns2.example.com']],
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->post(route('domain-operations.operations.retry', [$domain->subscription, $domain, $op]), [
            'confirm_domain' => 'example.com',
        ])->assertStatus(422);

        Queue::assertNotPushed(UpdateDomainNameservers::class);
    }

    public function test_retry_requires_retype_confirmation(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = $this->makeFailedOperation($account->id, $domain->id, 'update_nameservers', ['nameservers' => ['ns1.example.com', 'ns2.example.com']]);

        $this->post(route('domain-operations.operations.retry', [$domain->subscription, $domain, $op]), [
            'confirm_domain' => 'salah.com',
        ])->assertStatus(422);

        Queue::assertNotPushed(UpdateDomainNameservers::class);
    }

    public function test_retry_nameservers_requires_managed_mode(): void
    {
        $this->setMode('read_only');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = $this->makeFailedOperation($account->id, $domain->id, 'update_nameservers', ['nameservers' => ['ns1.example.com', 'ns2.example.com']]);

        $this->post(route('domain-operations.operations.retry', [$domain->subscription, $domain, $op]), [
            'confirm_domain' => 'example.com',
        ])->assertForbidden();

        Queue::assertNotPushed(UpdateDomainNameservers::class);
    }

    public function test_retry_dns_reuses_full_validated_payload(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $subscription = $this->makeSubscription();
        $domain = $this->makeLinkedDomain($subscription, $account);
        $domain->update(['managed_dns_enabled' => true]);
        $op = $this->makeFailedOperation($account->id, $domain->id, 'manage_dns', [
            'dnsid' => 7, 'record' => 'www', 'type' => 'CNAME', 'destination' => 'target.example.com', 'ttl' => 7200, 'priority' => null,
        ]);

        $this->post(route('domain-operations.operations.retry', [$subscription, $domain, $op]), [
            'confirm_domain' => 'example.com',
        ])->assertRedirect();

        Queue::assertPushed(EditDomainDnsRecord::class, function ($job) {
            return ($job->record['dnsid'] ?? null) === 7
                && ($job->record['destination'] ?? null) === 'target.example.com'
                && ($job->record['ttl'] ?? null) === 7200
                && ($job->record['type'] ?? null) === 'CNAME';
        });
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'queued']);
    }

    public function test_retry_set_epp_redispatches_with_preserved_secret(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = $this->makeFailedOperation($account->id, $domain->id, 'set_epp', ['epp_changed' => true]);
        $op->update(['request_secret_encrypted' => encrypt('RetryEpp1')]);

        $this->post(route('domain-operations.operations.retry', [$domain->subscription, $domain, $op]), [
            'confirm_domain' => 'example.com',
        ])->assertRedirect();

        Queue::assertPushed(\App\Jobs\SetDomainEpp::class, fn ($job) => $job->operationId === $op->id);
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'queued']);
    }

    public function test_manual_retry_queues_and_executes_nameserver_operation_once(): void
    {
        $this->setMode('managed');
        Queue::fake();
        Http::fake(['https://api.srs-x.com/*' => Http::response($this->successXml(), 200)]);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $nameservers = ['ns1.example.com', 'ns2.example.com'];
        $op = \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'failed',
            'idempotency_key' => 'update_ns_'.$domain->id.'_'.md5(implode(',', $nameservers)),
            'request_payload_redacted' => ['nameservers' => $nameservers],
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
            'error_summary' => 'Gagal uji',
        ]);

        $this->post(route('domain-operations.operations.retry', [$domain->subscription, $domain, $op]), [
            'confirm_domain' => 'example.com',
        ])->assertRedirect();
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'queued']);

        (new UpdateDomainNameservers($domain->id, $nameservers, $owner->id))
            ->handle(app(\App\DomainRegistrars\DomainRegistrarManager::class), app(\App\Services\Admin\AdminNotificationService::class));

        Http::assertSentCount(1);
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'completed']);
    }

    public function test_update_nameservers_job_skips_completed_operation(): void
    {
        $this->setMode('managed');
        Http::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $key = 'update_ns_'.$domain->id.'_'.md5('ns1.example.com,ns2.example.com');
        \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'completed',
            'idempotency_key' => $key,
            'request_payload_redacted' => ['nameservers' => ['ns1.example.com', 'ns2.example.com']],
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $job = new UpdateDomainNameservers($domain->id, ['ns1.example.com', 'ns2.example.com']);
        $job->handle(app(\App\DomainRegistrars\DomainRegistrarManager::class), app(\App\Services\Admin\AdminNotificationService::class));

        // Operasi completed tidak boleh memicu request provider lagi (idempotency mutasi).
        Http::assertNothingSent();
    }

    // ===== Pemulihan operasi stale (P1 audit) =====

    public function test_update_nameservers_job_recovers_stale_processing_operation(): void
    {
        $this->setMode('managed');
        Http::fake(['https://api.srs-x.com/*' => Http::response($this->successXml(), 200)]);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $key = 'update_ns_'.$domain->id.'_'.md5('ns1.example.com,ns2.example.com');
        \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'processing',
            'idempotency_key' => $key,
            'request_payload_redacted' => ['nameservers' => ['ns1.example.com', 'ns2.example.com']],
            'started_at' => now()->subHours(2),
        ]);

        $job = new UpdateDomainNameservers($domain->id, ['ns1.example.com', 'ns2.example.com']);
        $job->handle(app(\App\DomainRegistrars\DomainRegistrarManager::class), app(\App\Services\Admin\AdminNotificationService::class));

        // Operasi stale ditandai failed, tetapi job lama tidak boleh mengulang mutasi.
        Http::assertNothingSent();
        $this->assertDatabaseHas('registrar_operations', ['idempotency_key' => $key, 'status' => 'failed']);
    }

    public function test_update_nameservers_job_skips_fresh_processing_operation(): void
    {
        $this->setMode('managed');
        Http::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $key = 'update_ns_'.$domain->id.'_'.md5('ns1.example.com,ns2.example.com');
        \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'processing',
            'idempotency_key' => $key,
            'request_payload_redacted' => ['nameservers' => ['ns1.example.com', 'ns2.example.com']],
            'started_at' => now(),
        ]);

        $job = new UpdateDomainNameservers($domain->id, ['ns1.example.com', 'ns2.example.com']);
        $job->handle(app(\App\DomainRegistrars\DomainRegistrarManager::class), app(\App\Services\Admin\AdminNotificationService::class));

        // Processing masih segar → jangan kirim ulang mutasi.
        Http::assertNothingSent();
        $this->assertDatabaseHas('registrar_operations', ['idempotency_key' => $key, 'status' => 'processing']);
    }

    public function test_edit_dns_job_recovers_stale_processing_operation(): void
    {
        $this->setMode('managed');
        Http::fake(['https://api.srs-x.com/*' => Http::response($this->successXml(), 200)]);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $domain->update(['managed_dns_enabled' => true]);
        $record = ['dnsid' => 3, 'record' => '@', 'type' => 'A', 'destination' => '9.9.9.9', 'ttl' => 3600, 'priority' => null];
        $key = 'edit_dns_'.$domain->id.'_'.md5(json_encode($record));
        \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'manage_dns',
            'status' => 'processing',
            'idempotency_key' => $key,
            'request_payload_redacted' => $record,
            'started_at' => now()->subHours(2),
        ]);

        $job = new EditDomainDnsRecord($domain->id, $record);
        $job->handle(app(\App\DomainRegistrars\DomainRegistrarManager::class), app(\App\Services\Admin\AdminNotificationService::class));

        Http::assertNothingSent();
        $this->assertDatabaseHas('registrar_operations', ['idempotency_key' => $key, 'status' => 'failed']);
    }

    public function test_set_epp_job_recovers_stale_processing_operation(): void
    {
        $this->setMode('managed');
        Http::fake(['https://api.srs-x.com/*' => Http::response($this->successXml(), 200)]);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'set_epp',
            'status' => 'processing',
            'request_secret_encrypted' => encrypt('OldEpp99'),
            'request_payload_redacted' => ['epp_changed' => true],
            'started_at' => now()->subHours(2),
        ]);

        $job = new \App\Jobs\SetDomainEpp($op->id);
        $job->handle(app(\App\DomainRegistrars\DomainRegistrarManager::class), app(\App\Services\Admin\AdminNotificationService::class));

        Http::assertNothingSent();
        $op->refresh();
        $this->assertSame('failed', $op->status);
        $this->assertSame('OldEpp99', decrypt($op->request_secret_encrypted));
    }

    public function test_set_epp_rejects_short_code(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.epp.set', [$domain->subscription, $domain]), [
            'epp_code' => 'ab', 'confirm_domain' => 'example.com',
        ])->assertSessionHasErrors('epp_code');

        Queue::assertNotPushed(\App\Jobs\SetDomainEpp::class);
    }

    // ===== DNS managed =====

    public function test_get_dns_requires_managed_dns_enabled(): void
    {
        $this->setMode('managed');
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.dns.info', [$domain->subscription, $domain]))->assertStatus(422);
    }

    public function test_owner_can_toggle_managed_dns_with_confirmation(): void
    {
        $this->setMode('managed');
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.dns.toggle', [$domain->subscription, $domain]), [
            'enabled' => '1', 'confirm_domain' => 'example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_domains', ['id' => $domain->id, 'managed_dns_enabled' => true]);
    }

    public function test_toggle_managed_dns_enable_requires_confirmation(): void
    {
        $this->setMode('managed');
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);

        $this->post(route('domain-operations.dns.toggle', [$domain->subscription, $domain]), [
            'enabled' => '1', 'confirm_domain' => 'wrong.com',
        ])->assertStatus(422);

        $this->assertDatabaseHas('subscription_domains', ['id' => $domain->id, 'managed_dns_enabled' => false]);
    }

    public function test_get_dns_stores_records_when_enabled(): void
    {
        $this->setMode('managed');
        Http::fake([
            'https://api.srs-x.com/*' => Http::response(
                '<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><dns0><line>1</line><type>A</type><record>@</record><destination>1.2.3.4</destination><ttl>3600</ttl></dns0><domain_ns1>ns1.example.com</domain_ns1></resultData></epp>',
                200
            ),
        ]);

        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $subscription = $this->makeSubscription();
        $domain = $this->makeLinkedDomain($subscription, $account);
        $domain->update(['managed_dns_enabled' => true]);

        $this->post(route('domain-operations.dns.info', [$subscription, $domain]))->assertRedirect();

        $domain->refresh();
        $this->assertCount(1, $domain->dns_records);
        $this->assertSame('A', $domain->dns_records[0]['type']);
        $this->assertDatabaseHas('registrar_operations', [
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'get_dns',
            'status' => 'completed',
        ]);
    }

    public function test_edit_dns_dispatch_when_enabled_and_confirmed(): void
    {
        $this->setMode('managed');
        Queue::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $subscription = $this->makeSubscription();
        $domain = $this->makeLinkedDomain($subscription, $account);
        $domain->update(['managed_dns_enabled' => true, 'dns_records' => [['dnsid' => 1, 'type' => 'A', 'record' => '@', 'destination' => '1.2.3.4', 'ttl' => 3600]]]);

        $this->post(route('domain-operations.dns.edit', [$subscription, $domain]), [
            'dnsid' => 1, 'record' => '@', 'type' => 'A', 'destination' => '5.6.7.8', 'ttl' => 7200,
            'confirm_domain' => 'example.com',
        ])->assertRedirect();

        Queue::assertPushed(EditDomainDnsRecord::class, function ($job) {
            return $job->record['dnsid'] === 1 && $job->record['destination'] === '5.6.7.8';
        });
    }

    // ===== Scheduled stale recovery (P1 audit) =====

    public function test_scheduled_recovery_marks_stale_operations_failed(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'processing',
            'request_payload_redacted' => ['nameservers' => ['ns1.ex.com', 'ns2.ex.com']],
            'started_at' => now()->subHours(2),
        ]);

        \Illuminate\Support\Facades\Artisan::call('registrar:recover-stale-operations');

        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'failed']);
        $op->refresh();
        $this->assertStringContainsString('gantung', $op->error_summary);
    }

    public function test_scheduled_recovery_leaves_fresh_operations_untouched(): void
    {
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'processing',
            'request_payload_redacted' => ['nameservers' => ['ns1.ex.com', 'ns2.ex.com']],
            'started_at' => now(),
        ]);

        \Illuminate\Support\Facades\Artisan::call('registrar:recover-stale-operations');

        $op->refresh();
        $this->assertSame('processing', $op->status, 'Fresh processing operations should not be marked failed');
    }

    public function test_scheduled_recovery_does_not_affect_completed_operations(): void
    {
        $account = $this->makeAccount();
        $domain = $this->makeLinkedDomain($this->makeSubscription(), $account);
        $op = \App\Models\RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'subscription_domain_id' => $domain->id,
            'operation_type' => 'update_nameservers',
            'status' => 'completed',
            'request_payload_redacted' => ['nameservers' => ['ns1.ex.com', 'ns2.ex.com']],
            'started_at' => now()->subHours(2),
        ]);

        \Illuminate\Support\Facades\Artisan::call('registrar:recover-stale-operations');

        $op->refresh();
        $this->assertSame('completed', $op->status, 'Completed operations should stay completed');
    }
}
