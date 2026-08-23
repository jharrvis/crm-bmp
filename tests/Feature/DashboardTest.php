<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DashboardWidgetRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['Owner', 'Admin', 'Billing', 'NOC', 'CS', 'Sales', 'Finance', 'Employee'] as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }
        foreach (['clients.view', 'subscriptions.view', 'invoices.view', 'financial_reports.view', 'payments.verify', 'tickets.view', 'logs.view', 'routers.view', 'servers.view', 'zabbix_monitors.view', 'domains.view', 'registrar_accounts.view', 'notifications.view', 'packages.view', 'maps.view'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
    }

    private function userWithRole(string $role, array $perms = []): User
    {
        $u = User::factory()->create();
        $u->assignRole($role);
        if ($perms) {
            $u->givePermissionTo($perms);
        }
        return $u;
    }

    public function test_default_layout_per_role(): void
    {
        $owner = $this->userWithRole('Owner');
        $owner->givePermissionTo(Permission::all());
        $layoutOwner = DashboardWidgetRegistry::defaultForRole($owner);
        $this->assertGreaterThan(10, count($layoutOwner));

        $sales = $this->userWithRole('Sales');
        $sales->givePermissionTo(['clients.view', 'packages.view']);
        $layoutSales = DashboardWidgetRegistry::defaultForRole($sales);
        $ids = array_column($layoutSales, 'id');
        $this->assertContains('clients_count', $ids);
        $this->assertNotContains('outstanding_invoice', $ids); // Sales tidak ada keuangan
    }

    public function test_visible_filters_permission(): void
    {
        $cs = $this->userWithRole('CS');
        $cs->givePermissionTo(['clients.view', 'subscriptions.view', 'tickets.view']);
        $prefs = ['layout' => [['id' => 'clients_count', 'visible' => true], ['id' => 'revenue', 'visible' => true], ['id' => 'tickets_open', 'visible' => true]]];
        $visible = DashboardWidgetRegistry::visibleForUser($cs, $prefs);
        $ids = array_column($visible, 'id');
        $this->assertContains('clients_count', $ids);
        $this->assertNotContains('revenue', $ids); // CS tanpa financial_reports.view
    }

    public function test_router_server_visible_if_either_permission(): void
    {
        $u1 = $this->userWithRole('NOC');
        $u1->givePermissionTo(['routers.view']);
        $prefs = ['layout' => [['id' => 'router_server', 'visible' => true]]];
        $this->assertCount(1, DashboardWidgetRegistry::visibleForUser($u1, $prefs));

        $u2 = $this->userWithRole('NOC');
        $u2->givePermissionTo(['servers.view']);
        $this->assertCount(1, DashboardWidgetRegistry::visibleForUser($u2, $prefs));

        $u3 = $this->userWithRole('CS');
        $u3->givePermissionTo(['clients.view']);
        $this->assertCount(0, DashboardWidgetRegistry::visibleForUser($u3, $prefs));
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_for_authenticated(): void
    {
        $user = $this->userWithRole('Owner');
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Halo');
    }

    public function test_update_preferences_validates_widget_and_period(): void
    {
        $user = $this->userWithRole('Owner');
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user);

        // invalid widget id
        $this->putJson(route('dashboard.preferences'), ['layout' => [['id' => 'unknown_widget', 'visible' => true]]])->assertStatus(422);

        // invalid period
        $this->putJson(route('dashboard.preferences'), ['layout' => [['id' => 'clients_count', 'visible' => true]], 'widget_periods' => ['revenue' => '99d']])->assertStatus(422);

        // valid
        $res = $this->putJson(route('dashboard.preferences'), ['layout' => [['id' => 'clients_count', 'visible' => true]], 'widget_periods' => ['revenue' => '30d']])->assertOk();
        $this->assertEquals('30d', $user->fresh()->dashboard_preferences['widget_periods']['revenue']);
    }

    public function test_stats_endpoint_permission_gate(): void
    {
        $cs = $this->userWithRole('CS');
        $cs->givePermissionTo(['clients.view']);
        $this->actingAs($cs)->getJson(route('dashboard.stats', ['widget' => 'revenue']))->assertStatus(403);
        $this->actingAs($cs)->getJson(route('dashboard.stats', ['widget' => 'clients_count']))->assertOk();

        // router_server: servers.view only should pass
        $noc2 = $this->userWithRole('NOC');
        $noc2->givePermissionTo(['servers.view']);
        $this->actingAs($noc2)->getJson(route('dashboard.stats', ['widget' => 'router_server']))->assertOk();
    }

    public function test_empty_state_when_no_data(): void
    {
        $user = $this->userWithRole('Owner');
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user)->get(route('dashboard'))->assertSee('Belum ada data');
    }

    public function test_revenue_period_affects_calculation(): void
    {
        $user = $this->userWithRole('Owner');
        $user->givePermissionTo(['financial_reports.view']);

        // Buat data minimal via DB (tanpa factory Client/Invoice)
        $branch = \App\Models\Branch::create(['name' => 'Test Branch', 'code' => 'TST']);
        $client = \App\Models\Client::create(['branch_id' => $branch->id, 'client_code' => 'TST-001', 'name' => 'Test Client', 'status' => 'active']);
        $invoice = \App\Models\Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 150000,
            'status' => 'unpaid',
        ]);
        \App\Models\Payment::create(['invoice_id' => $invoice->id, 'amount' => 100000, 'payment_method' => 'transfer', 'payment_date' => now()->toDateString(), 'status' => 'verified', 'verified_at' => now()]);
        \App\Models\Payment::create(['invoice_id' => $invoice->id, 'amount' => 50000, 'payment_method' => 'transfer', 'payment_date' => now()->subDays(40)->toDateString(), 'status' => 'verified', 'verified_at' => now()]);

        $svc = app(\App\Services\DashboardStatsService::class);
        $r1m = $svc->revenue($user, '1M');
        $r30d = $svc->revenue($user, '30d');
        $this->assertEquals(100000, (int) $r1m['current']);
        $this->assertEquals(100000, (int) $r30d['current']);
        $this->assertArrayHasKey('period', $r30d);
        $this->assertEquals('30d', $r30d['period']);
        $this->assertEquals('1M', $r1m['period']);
    }
}