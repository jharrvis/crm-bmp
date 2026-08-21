<?php

namespace Tests\Feature;

use App\Models\RegistrarAccount;
use App\Models\RegistrarOperation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    private function makeAccount(array $overrides = []): RegistrarAccount
    {
        return RegistrarAccount::create(array_merge([
            'provider' => 'srsx',
            'name' => 'Test',
            'base_url' => 'https://api.srs-x.com',
            'is_active' => true,
            'api_username_encrypted' => 'user123',
            'api_password_encrypted' => 'pass123',
        ], $overrides));
    }

    private function makeOperation(RegistrarAccount $account): RegistrarOperation
    {
        return RegistrarOperation::create([
            'registrar_account_id' => $account->id,
            'operation_type' => 'manual_import',
            'status' => 'manual_review',
            'requested_by' => auth()->id(),
            'request_payload_redacted' => ['domains' => ['example.com'], 'warnings' => []],
            'response_payload_redacted' => ['new' => ['example.com'], 'conflicts' => [], 'existing' => []],
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function test_guest_cannot_access_registrar(): void
    {
        $this->get(route('registrar-accounts.index'))->assertRedirect(route('login'));
    }

    public function test_unauthorized_role_cannot_test_connection(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS'); // CS tidak punya registrar_accounts.test
        $account = $this->makeAccount();

        $this->actingAs($user)->post(route('registrar-accounts.test-connection', $account))->assertForbidden();
    }

    public function test_unauthorized_role_cannot_import_manual(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS'); // CS tidak punya domains.sync
        $account = $this->makeAccount();

        $this->actingAs($user)->post(route('registrar-accounts.import-manual', $account), ['domains' => 'example.com'])->assertForbidden();
    }

    public function test_unauthorized_role_cannot_review_operation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS');
        $account = $this->makeAccount();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $op = $this->makeOperation($account);

        $this->actingAs($user)->get(route('registrar-accounts.operations.show', [$account, $op]))->assertForbidden();
    }

    public function test_unauthorized_role_cannot_link_domain(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS');
        $account = $this->makeAccount();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $op = $this->makeOperation($account);

        $this->actingAs($user)->post(route('registrar-accounts.operations.link', [$account, $op]), ['domain' => 'example.com', 'subscription_id' => 1])->assertForbidden();
    }

    public function test_unauthorized_role_cannot_check_domain(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CS'); // CS tidak punya domains.view? CS punya subscriptions.view tapi bukan domains.view
        $account = $this->makeAccount();

        $this->actingAs($user)->get(route('registrar-accounts.check', $account).'?domain=example.com')->assertForbidden();
    }

    public function test_owner_can_view_registrar(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner)->get(route('registrar-accounts.index'))->assertOk();
    }

    public function test_owner_can_review_operation(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $this->actingAs($owner);
        $account = $this->makeAccount();
        $op = $this->makeOperation($account);

        $this->actingAs($owner)->get(route('registrar-accounts.operations.show', [$account, $op]))->assertOk();
    }

    public function test_credential_hidden_from_json(): void
    {
        $account = $this->makeAccount(['name' => 'Test2']);
        $json = $account->toArray();
        $this->assertArrayNotHasKey('api_username_encrypted', $json);
        $this->assertArrayNotHasKey('api_password_encrypted', $json);
        $this->assertArrayNotHasKey('settings_encrypted', $json);
    }

    public function test_domain_auth_code_hidden(): void
    {
        $this->makeAccount(['name' => 'Test3']);
        // Need a subscription and client to create domain, so just test model hidden via make
        $domain = new \App\Models\SubscriptionDomain([
            'subscription_id' => 1,
            'domain_name' => 'example.com',
            'auth_code_encrypted' => encrypt('secret'),
        ]);
        $arr = $domain->toArray();
        $this->assertArrayNotHasKey('auth_code_encrypted', $arr);
    }
}
