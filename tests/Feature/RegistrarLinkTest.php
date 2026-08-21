<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Package;
use App\Models\RegistrarAccount;
use App\Models\RegistrarOperation;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrarLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        config(['domain-registrars.enabled' => true]);
    }

    private function successInfoXml(): string
    {
        return '<?xml version="1.0"?><epp><result><resultCode>1000</resultCode><resultMsg>Domain info</resultMsg></result><resultData><domain>example.com</domain><status>ok</status><expires_at>2027-08-20</expires_at></resultData></epp>';
    }

    private function notFoundXml(): string
    {
        return '<?xml version="1.0"?><epp><result><resultCode>1001</resultCode><resultMsg>Command Failed</resultMsg></result></epp>';
    }

    private function makeAccount(): RegistrarAccount
    {
        return RegistrarAccount::create([
            'provider' => 'srsx',
            'name' => 'Akun A gTLD',
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

    private function makeOperation(RegistrarAccount $account, int $requestedBy): RegistrarOperation
    {
        return RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'operation_type' => 'manual_import',
            'status' => 'manual_review',
            'requested_by' => $requestedBy,
            'request_payload_redacted' => ['domains' => ['example.com'], 'warnings' => []],
            'response_payload_redacted' => ['new' => ['example.com'], 'conflicts' => [], 'existing' => []],
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function test_owner_can_link_domain_after_live_verification(): void
    {
        Queue::fake();
        Http::fake([
            'https://api.srs-x.com/*' => Http::response($this->successInfoXml(), 200),
        ]);

        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);

        $account = $this->makeAccount();
        $subscription = $this->makeSubscription();
        $op = $this->makeOperation($account, $owner->id);

        $this->post(route('registrar-accounts.operations.link', [$account, $op]), [
            'domain' => 'example.com',
            'subscription_id' => $subscription->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_domains', [
            'subscription_id' => $subscription->id,
            'domain_name' => 'example.com',
            'registrar_account_id' => $account->id,
            'provider_domain_id' => 'example.com',
            'sync_status' => 'pending',
        ]);

        // Operasi selesai — tidak ada domain baru tersisa
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'completed']);
    }

    public function test_link_rejects_domain_not_found_in_provider(): void
    {
        Queue::fake();
        Http::fake([
            'https://api.srs-x.com/*' => Http::response($this->notFoundXml(), 200),
        ]);

        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);

        $account = $this->makeAccount();
        $subscription = $this->makeSubscription();
        $op = $this->makeOperation($account, $owner->id);

        $this->post(route('registrar-accounts.operations.link', [$account, $op]), [
            'domain' => 'example.com',
            'subscription_id' => $subscription->id,
        ])->assertSessionHasErrors('domain');

        // Tidak ada SubscriptionDomain yang dibuat — verifikasi live menolak
        $this->assertDatabaseMissing('subscription_domains', [
            'subscription_id' => $subscription->id,
            'domain_name' => 'example.com',
        ]);

        // Operasi tetap manual_review — belum ada yang ditautkan
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'manual_review']);
    }

    public function test_link_keeps_partially_completed_while_other_new_domains_remain(): void
    {
        Queue::fake();
        Http::fake([
            'https://api.srs-x.com/*' => Http::response($this->successInfoXml(), 200),
        ]);

        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);

        $account = $this->makeAccount();
        $subscription = $this->makeSubscription();
        // staging punya 2 domain baru, baru 1 ditautkan
        $op = RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'operation_type' => 'manual_import',
            'status' => 'manual_review',
            'requested_by' => $owner->id,
            'request_payload_redacted' => ['domains' => ['example.com', 'second.com'], 'warnings' => []],
            'response_payload_redacted' => ['new' => ['example.com', 'second.com'], 'conflicts' => [], 'existing' => []],
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->post(route('registrar-accounts.operations.link', [$account, $op]), [
            'domain' => 'example.com',
            'subscription_id' => $subscription->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_domains', [
            'subscription_id' => $subscription->id,
            'domain_name' => 'example.com',
            'registrar_account_id' => $account->id,
        ]);
        $this->assertDatabaseHas('registrar_operations', ['id' => $op->id, 'status' => 'partially_completed', 'completed_at' => null]);
    }

    public function test_link_rejects_domain_already_linked_to_another_registrar_account(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);

        $firstAccount = $this->makeAccount();
        $secondAccount = RegistrarAccount::create([
            'provider' => 'srsx',
            'name' => 'Akun B ccTLD',
            'base_url' => 'https://api.srs-x.com',
            'is_active' => true,
            'api_username_encrypted' => 'user456',
            'api_password_encrypted' => 'pass456',
        ]);
        $linkedSubscription = $this->makeSubscription();
        $targetSubscription = $this->makeSubscription();

        $linkedSubscription->domain()->create([
            'domain_name' => 'example.com',
            'registrar_account_id' => $firstAccount->id,
            'provider_domain_id' => 'example.com',
        ]);
        $op = $this->makeOperation($secondAccount, $owner->id);

        $this->post(route('registrar-accounts.operations.link', [$secondAccount, $op]), [
            'domain' => 'example.com',
            'subscription_id' => $targetSubscription->id,
        ])->assertSessionHasErrors('domain');

        $this->assertDatabaseMissing('subscription_domains', [
            'subscription_id' => $targetSubscription->id,
            'registrar_account_id' => $secondAccount->id,
        ]);
    }
}
