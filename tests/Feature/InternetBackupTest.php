<?php

namespace Tests\Feature;

use App\Models\InternetBackup;
use App\Models\Subscription;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class InternetBackupTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermission(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::create(['name' => 'test-role-' . uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get(route('internet-backups.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_view_permission_gets_403(): void
    {
        $user = $this->createUserWithPermission([]);
        $this->actingAs($user)->get(route('internet-backups.index'))->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_index(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.view']);
        $this->actingAs($user)->get(route('internet-backups.index'))->assertOk();
    }

    public function test_user_without_create_permission_cannot_store(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.view']);
        $vendor = Vendor::factory()->create();

        $data = [
            'vendor_id' => $vendor->id,
            'name' => 'Test Backup',
            'bandwidth_mbps' => 10,
            'status' => 'planned',
        ];
        $this->actingAs($user)->postJson(route('internet-backups.store'), $data)->assertForbidden();
    }

    public function test_user_with_create_permission_can_store(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.view', 'internet_backups.create']);
        $vendor = Vendor::factory()->create();

        $data = [
            'vendor_id' => $vendor->id,
            'name' => 'Backup Indihome',
            'bandwidth_mbps' => 20,
            'status' => 'planned',
        ];
        $this->actingAs($user)->postJson(route('internet-backups.store'), $data)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('internet_backups', [
            'name' => 'Backup Indihome',
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_vendor_is_required(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.create']);

        $data = [
            'name' => 'Test',
            'bandwidth_mbps' => 10,
            'status' => 'planned',
        ];
        $this->actingAs($user)->postJson(route('internet-backups.store'), $data)
            ->assertJsonValidationErrors('vendor_id');
    }

    public function test_subscription_must_exist_if_provided(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.create']);
        $vendor = Vendor::factory()->create();

        $data = [
            'vendor_id' => $vendor->id,
            'name' => 'Test',
            'subscription_id' => 99999,
            'bandwidth_mbps' => 10,
            'status' => 'planned',
        ];
        $this->actingAs($user)->postJson(route('internet-backups.store'), $data)
            ->assertJsonValidationErrors('subscription_id');
    }

    public function test_active_backup_must_have_subscription(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.create']);
        $vendor = Vendor::factory()->create();

        $data = [
            'vendor_id' => $vendor->id,
            'name' => 'Test Active',
            'bandwidth_mbps' => 10,
            'status' => 'active',
            'subscription_id' => null,
        ];
        $this->actingAs($user)->postJson(route('internet-backups.store'), $data)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Backup aktif harus memiliki subscription.',
            ]);
    }

    public function test_user_with_update_permission_can_update(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.update']);
        $vendor = Vendor::factory()->create();
        $backup = InternetBackup::factory()->create(['vendor_id' => $vendor->id]);

        $data = [
            'vendor_id' => $vendor->id,
            'name' => 'Updated Name',
            'bandwidth_mbps' => 50,
            'status' => 'planned',
            'subscription_id' => null,
        ];
        $this->actingAs($user)->putJson(route('internet-backups.update', $backup), $data)
            ->assertOk()
            ->assertJson(['success' => true]);

        $backup->refresh();
        $this->assertSame('Updated Name', $backup->name);
    }

    public function test_user_with_delete_permission_can_delete(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.delete']);
        $vendor = Vendor::factory()->create();
        $backup = InternetBackup::factory()->create(['vendor_id' => $vendor->id]);

        $this->actingAs($user)->deleteJson(route('internet-backups.destroy', $backup))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('internet_backups', ['id' => $backup->id]);
    }

    public function test_soft_delete_hides_from_index(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.view']);
        $vendor = Vendor::factory()->create();
        InternetBackup::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Active Backup']);
        $deleted = InternetBackup::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Deleted Backup']);
        $deleted->delete();

        $response = $this->actingAs($user)->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
        ])->getJson(route('internet-backups.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [['data' => 'name', 'searchable' => true]],
            'search' => ['value' => ''],
        ]));
        $response->assertOk();
        $json = $response->json();
        $names = collect($json['data'])->pluck('name')->toArray();
        $this->assertContains('Active Backup', $names);
        $this->assertNotContains('Deleted Backup', $names);
    }

    public function test_client_name_displayed_through_subscription(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.view']);
        $vendor = Vendor::factory()->create();
        $subscription = Subscription::factory()->create();
        $backup = InternetBackup::factory()->create([
            'vendor_id' => $vendor->id,
            'subscription_id' => $subscription->id,
        ]);

        $this->actingAs($user)->getJson(route('internet-backups.show', $backup), [
            'headers' => ['Accept' => 'application/json'],
        ])->assertOk()->assertJsonFragment(['id' => $backup->id]);
    }

    public function test_activity_log_records_create(): void
    {
        $user = $this->createUserWithPermission(['internet_backups.create']);
        $vendor = Vendor::factory()->create();

        $data = [
            'vendor_id' => $vendor->id,
            'name' => 'Logged Backup',
            'bandwidth_mbps' => 10,
            'status' => 'planned',
        ];
        $this->actingAs($user)->postJson(route('internet-backups.store'), $data)->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => 'App\Models\InternetBackup',
            'event' => 'created',
        ]);
    }

    public function test_global_search_respects_permission(): void
    {
        $userWithout = $this->createUserWithPermission([]);
        $userWith = $this->createUserWithPermission(['internet_backups.view']);

        $vendor = Vendor::factory()->create();
        InternetBackup::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Backup Test']);

        $this->actingAs($userWithout)->getJson(route('search', ['q' => 'Backup']))
            ->assertOk()
            ->assertJsonMissing(['group' => 'Internet Backup']);

        $this->actingAs($userWith)->getJson(route('search', ['q' => 'Backup']))
            ->assertOk()
            ->assertJsonFragment(['group' => 'Internet Backup']);
    }
}
