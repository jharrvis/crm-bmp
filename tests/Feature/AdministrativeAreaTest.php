<?php

namespace Tests\Feature;

use App\Models\AdministrativeArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdministrativeAreaTest extends TestCase
{
    use RefreshDatabase;

    private function authorizedUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'CS', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'clients.view', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $user->givePermissionTo('clients.view');

        return $user;
    }

    public function test_administrative_area_hierarchy_can_load_subang(): void
    {
        AdministrativeArea::insert([
            ['code' => '32', 'parent_code' => null, 'level' => 'province', 'name' => 'Jawa Barat', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '32.13', 'parent_code' => '32', 'level' => 'regency', 'name' => 'Kabupaten Subang', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '32.13.01', 'parent_code' => '32.13', 'level' => 'district', 'name' => 'Subang', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '32.13.01.2001', 'parent_code' => '32.13.01', 'level' => 'village', 'name' => 'Contoh Desa', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $user = $this->authorizedUser();

        $this->actingAs($user)
            ->getJson(route('administrative-areas.index', ['level' => 'regency', 'parent_code' => '32']))
            ->assertOk()
            ->assertJsonPath('data.0.code', '32.13')
            ->assertJsonPath('data.0.name', 'Kabupaten Subang');

        $this->actingAs($user)
            ->getJson(route('administrative-areas.index', ['level' => 'village', 'parent_code' => '32.13.01']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Contoh Desa');
    }

    public function test_administrative_area_endpoint_requires_clients_view(): void
    {
        $role = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->getJson(route('administrative-areas.index', ['level' => 'province']))
            ->assertForbidden();
    }
}
