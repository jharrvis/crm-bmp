<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Service;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationalMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['Owner', 'Admin', 'NOC', 'Sales', 'CS'] as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }
        Permission::firstOrCreate(['name' => 'maps.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'clients.view', 'guard_name' => 'web']);
    }

    private function userWith(string $role, array $perms = []): User
    {
        $u = User::factory()->create();
        $u->assignRole($role);
        if ($perms) $u->givePermissionTo($perms);
        return $u;
    }

    public function test_maps_view_required(): void
    {
        $u = $this->userWith('CS', ['clients.view']);
        $this->actingAs($u)->get(route('operational-map.index'))->assertStatus(403);
        $this->actingAs($u)->getJson(route('operational-map.locations'))->assertStatus(403);
        $this->actingAs($u)->getJson(route('operational-map.summary'))->assertStatus(403);
    }

    public function test_clients_view_required_even_with_maps(): void
    {
        $u = $this->userWith('NOC', ['maps.view']); // tanpa clients.view
        $this->actingAs($u)->get(route('operational-map.index'))->assertStatus(403);
        $this->actingAs($u)->getJson(route('operational-map.locations'))->assertStatus(403);
        $this->actingAs($u)->getJson(route('operational-map.summary'))->assertStatus(403);
    }

    public function test_locations_only_mapped_by_default(): void
    {
        $branch = Branch::create(['name' => 'Branch A', 'code' => 'BRA', 'default_latitude' => -6.9, 'default_longitude' => 110.0]);
        $u = $this->userWith('NOC', ['maps.view', 'clients.view']);
        $c1 = Client::create(['branch_id' => $branch->id, 'client_code' => 'C001', 'name' => 'Mapped Client', 'status' => 'active', 'latitude' => -6.984, 'longitude' => 110.42]);
        $c2 = Client::create(['branch_id' => $branch->id, 'client_code' => 'C002', 'name' => 'Unmapped Client', 'status' => 'active']);

        $res = $this->actingAs($u)->getJson(route('operational-map.locations'))->assertOk()->json();
        $ids = array_column($res['data'], 'id');
        $this->assertContains($c1->id, $ids);
        $this->assertNotContains($c2->id, $ids);
        // Branch marker tetap ada
        $types = array_column($res['data'], 'type');
        $this->assertContains('branch', $types);
    }

    public function test_filter_branch_limits_results(): void
    {
        $b1 = Branch::create(['name' => 'B1', 'code' => 'B1']);
        $b2 = Branch::create(['name' => 'B2', 'code' => 'B2']);
        $u = $this->userWith('NOC', ['maps.view', 'clients.view']);
        $c1 = Client::create(['branch_id' => $b1->id, 'client_code' => 'C1', 'name' => 'Client B1', 'status' => 'active', 'latitude' => -6.9, 'longitude' => 110.0]);
        $c2 = Client::create(['branch_id' => $b2->id, 'client_code' => 'C2', 'name' => 'Client B2', 'status' => 'active', 'latitude' => -7.0, 'longitude' => 110.1]);

        $res = $this->actingAs($u)->getJson(route('operational-map.locations', ['branch_id' => $b1->id]))->assertOk()->json();
        $ids = array_column(array_filter($res['data'], fn($d)=>$d['type']==='client'), 'id');
        $this->assertContains($c1->id, $ids);
        $this->assertNotContains($c2->id, $ids);
    }

    public function test_response_does_not_contain_sensitive(): void
    {
        $branch = Branch::create(['name' => 'B', 'code' => 'B']);
        $u = $this->userWith('NOC', ['maps.view', 'clients.view']);
        $c = Client::create(['branch_id' => $branch->id, 'client_code' => 'C1', 'name' => 'Client', 'status' => 'active', 'latitude' => -6.9, 'longitude' => 110.0, 'identity_number' => '1234567890']);
        $res = $this->actingAs($u)->getJson(route('operational-map.locations'))->assertOk()->json();
        $json = json_encode($res);
        $this->assertStringNotContainsString('identity_number', $json);
        $this->assertStringNotContainsString('1234567890', $json);
        $this->assertStringNotContainsString('auth_code', $json);
    }

    public function test_summary_mapped_unmapped_consistent(): void
    {
        $b = Branch::create(['name' => 'B', 'code' => 'B']);
        $u = $this->userWith('NOC', ['maps.view', 'clients.view']);
        Client::create(['branch_id' => $b->id, 'client_code' => 'C1', 'name' => 'Mapped', 'status' => 'active', 'latitude' => -6.9, 'longitude' => 110.0]);
        Client::create(['branch_id' => $b->id, 'client_code' => 'C2', 'name' => 'Unmapped', 'status' => 'active']);
        Client::create(['branch_id' => $b->id, 'client_code' => 'C3', 'name' => 'Mapped2', 'status' => 'active', 'latitude' => -7.0, 'longitude' => 110.1]);

        $summary = $this->actingAs($u)->getJson(route('operational-map.summary'))->assertOk()->json();
        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(2, $summary['mapped']);
        $this->assertEquals(1, $summary['unmapped']);

        $loc = $this->actingAs($u)->getJson(route('operational-map.locations'))->assertOk()->json();
        $this->assertEquals(2, $loc['meta']['count']); // hanya mapped
    }

    public function test_unmapped_filter_returns_only_unmapped(): void
    {
        $b = Branch::create(['name' => 'B', 'code' => 'B']);
        $u = $this->userWith('NOC', ['maps.view', 'clients.view']);
        Client::create(['branch_id' => $b->id, 'client_code' => 'C1', 'name' => 'Mapped', 'status' => 'active', 'latitude' => -6.9, 'longitude' => 110.0]);
        $unmapped = Client::create(['branch_id' => $b->id, 'client_code' => 'C2', 'name' => 'Unmapped', 'status' => 'active']);

        $res = $this->actingAs($u)->getJson(route('operational-map.locations', ['mapped' => 'unmapped']))->assertOk()->json();
        $ids = array_column(array_filter($res['data'], fn($d)=>$d['type']==='client'), 'id');
        $this->assertContains($unmapped->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_summary_consistent_with_locations_filters(): void
    {
        $b = Branch::create(['name' => 'B', 'code' => 'B']);
        $u = $this->userWith('NOC', ['maps.view', 'clients.view']);
        $c1 = Client::create(['branch_id' => $b->id, 'client_code' => 'C1', 'name' => 'Alpha Client', 'status' => 'active', 'latitude' => -6.9, 'longitude' => 110.0]);
        $c2 = Client::create(['branch_id' => $b->id, 'client_code' => 'C2', 'name' => 'Beta Client', 'status' => 'inactive', 'latitude' => -7.0, 'longitude' => 110.1]);

        // Filter q=Alpha harus konsisten antara locations dan summary
        $loc = $this->actingAs($u)->getJson(route('operational-map.locations', ['q' => 'Alpha']))->assertOk()->json();
        $sum = $this->actingAs($u)->getJson(route('operational-map.summary', ['q' => 'Alpha']))->assertOk()->json();
        $this->assertEquals(1, $loc['meta']['count']);
        $this->assertEquals(1, $sum['total']);
        $this->assertEquals(1, $sum['mapped']);

        // Filter branch + mapped
        $sum2 = $this->actingAs($u)->getJson(route('operational-map.summary', ['mapped' => 'unmapped']))->assertOk()->json();
        $this->assertEquals(0, $sum2['mapped']);
    }

    public function test_dashboard_widget_visible_only_with_maps_view(): void
    {
        $uNoMap = $this->userWith('CS', ['clients.view']);
        // CS with maps.view now has it via seeder, but test without
        $uNoMap->revokePermissionTo('maps.view'); // ensure no

        $prefs = ['layout' => [['id' => 'operational_map', 'visible' => true, 'w' => 6]]];
        $visible = \App\Services\DashboardWidgetRegistry::visibleForUser($uNoMap, $prefs);
        $this->assertCount(0, $visible);

        $uWith = $this->userWith('NOC', ['maps.view']);
        $visible2 = \App\Services\DashboardWidgetRegistry::visibleForUser($uWith, $prefs);
        $this->assertCount(1, $visible2);
    }
}