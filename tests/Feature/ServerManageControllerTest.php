<?php

namespace Tests\Feature;

use App\Models\HostingServer;
use App\Models\User;
use App\Services\HestiaCPService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServerManageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createHestiaServer(): HostingServer
    {
        return HostingServer::create([
            'name' => 'Hestia Alpha',
            'host' => 'hestia.bmpnet.local',
            'port' => 8083,
            'type' => 'hestiacp',
            'max_accounts' => 0,
            'is_active' => true,
        ]);
    }

    protected function setUpUserWithPermission(string $permission): User
    {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
        $role = Role::create(['name' => 'TestRole']);
        $role->givePermissionTo([$permission]);
        $user = User::factory()->create();

        return $user->assignRole($role);
    }

    public function test_user_without_permission_cannot_manage_server(): void
    {
        $server = $this->createHestiaServer();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/servers/{$server->id}/manage");

        $response->assertForbidden();
    }

    public function test_user_with_manage_permission_can_view_manage_page(): void
    {
        $server = $this->createHestiaServer();
        $user = $this->setUpUserWithPermission('servers.manage');

        $response = $this->actingAs($user)->get("/servers/{$server->id}/manage");

        $response->assertOk();
    }

    public function test_non_hestia_server_returns_404_on_manage_page(): void
    {
        $mailServer = HostingServer::create([
            'name' => 'Zimbra Mail',
            'host' => 'zimbra.bmpnet.local',
            'port' => 7071,
            'type' => 'zimbra',
            'max_accounts' => 0,
            'is_active' => true,
        ]);
        $user = $this->setUpUserWithPermission('servers.manage');

        $response = $this->actingAs($user)->get("/servers/{$mailServer->id}/manage");

        $response->assertNotFound();
    }

    public function test_noc_role_cannot_delete_access_guard(): void
    {
        // The permission seeder must NOT grant servers.delete_user to NOC.
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $noc = Role::findByName('NOC');
        $this->assertFalse($noc ? $noc->hasPermissionTo('servers.delete_user') : true);
    }

    public function test_admin_role_does_not_receive_hosting_user_delete_permission(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $admin = Role::findByName('Admin');
        $this->assertFalse($admin ? $admin->hasPermissionTo('servers.delete_user') : true);
    }

    public function test_hestia_positional_arguments_are_sent_as_api_argument_fields(): void
    {
        $server = $this->createHestiaServer();
        $server->update(['api_key' => 'access-key', 'secret_key' => 'secret-key']);

        Http::fake([
            '*' => Http::response('{"default": {}}', 200),
        ]);

        $result = (new HestiaCPService($server))->listUserPackages();

        $this->assertTrue($result['success']);
        $this->assertSame(['default'], $result['data']);
        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $payload['cmd'] === 'v-list-user-packages'
                && $payload['arg1'] === 'json'
                && ! array_key_exists(0, $payload);
        });
    }

    public function test_user_detail_displays_read_only_hestia_resource_usage(): void
    {
        $server = $this->createHestiaServer();
        $user = $this->setUpUserWithPermission('servers.manage');

        Http::fake(function ($request) {
            return match ($request->data()['cmd'] ?? null) {
                'v-list-user' => Http::response(json_encode([
                    'USER' => 'client01',
                    'NAME' => 'Client One',
                    'CONTACT' => 'client@example.test',
                    'PACKAGE' => 'basic',
                    'U_DISK' => '120',
                    'DISK_QUOTA' => '1024',
                    'U_DISK_WEB' => '90',
                    'U_DISK_DB' => '30',
                    'U_BANDWIDTH' => '400',
                    'BANDWIDTH' => '5000',
                    'U_WEB_DOMAINS' => '1',
                    'WEB_DOMAINS' => '5',
                    'U_DATABASES' => '1',
                    'DATABASES' => '5',
                    'SUSPENDED' => 'no',
                ]), 200),
                'v-list-web-domains' => Http::response(json_encode([
                    'example.test' => [
                        'U_DISK' => '90',
                        'U_BANDWIDTH' => '400',
                        'SSL' => 'yes',
                        'LETSENCRYPT' => 'yes',
                        'SUSPENDED' => 'no',
                    ],
                ]), 200),
                'v-list-databases' => Http::response(json_encode([
                    'client01_app' => [
                        'DATABASE' => 'client01_app',
                        'DBUSER' => 'client01_app',
                        'HOST' => 'localhost',
                        'TYPE' => 'mysql',
                        'U_DISK' => '30',
                        'SUSPENDED' => 'no',
                    ],
                ]), 200),
                default => Http::response('Error: unexpected command', 400),
            };
        });

        $response = $this->actingAs($user)->get("/servers/{$server->id}/users/client01");

        $response->assertOk()
            ->assertSee('Client One')
            ->assertSee('120 MB / 1.024 MB')
            ->assertSee('client01_app')
            ->assertSee('example.test');

        Http::assertSentCount(3);
    }

    public function test_seeder_does_not_use_sync_permissions_for_default_roles(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $seed = file_get_contents(app_path('../database/seeders/PermissionSeeder.php'));

        $this->assertStringContainsString('givePermissionTo', $seed);
        $this->assertStringNotContainsString('syncPermissions', $seed);
    }
}
