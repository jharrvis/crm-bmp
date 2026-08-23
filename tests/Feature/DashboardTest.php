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

        $branch = \App\Models\Branch::create(['name' => 'Test Branch', 'code' => 'TST']);
        $client = \App\Models\Client::create(['branch_id' => $branch->id, 'client_code' => 'TST-001', 'name' => 'Test Client', 'status' => 'active']);
        $invoice = \App\Models\Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 300000,
            'status' => 'unpaid',
        ]);

        // Payment A: hari ini — masuk 1M dan 30d
        \App\Models\Payment::create(['invoice_id' => $invoice->id, 'amount' => 100000, 'payment_method' => 'transfer', 'payment_date' => now()->toDateString(), 'status' => 'verified', 'verified_at' => now()]);
        // Payment B: 25 hari lalu — masuk 30d, tapi di luar bulan berjalan jika hari ini > 25 (misal 23 Aug → 29 Jul bukan Agustus)
        // Untuk deterministik, pakai 25 hari lalu yang pasti di luar startOfMonth Agustus (01 Aug) bila hari ini 23 Aug
        $dateB = now()->subDays(25)->toDateString(); // 29 Jul
        $inAugust = \Carbon\Carbon::parse($dateB)->isSameMonth(now());
        $amountB = 60000;
        \App\Models\Payment::create(['invoice_id' => $invoice->id, 'amount' => $amountB, 'payment_method' => 'transfer', 'payment_date' => $dateB, 'status' => 'verified', 'verified_at' => now()]);
        // Payment C: 40 hari lalu — di luar 30d dan di luar bulan ini
        \App\Models\Payment::create(['invoice_id' => $invoice->id, 'amount' => 50000, 'payment_method' => 'transfer', 'payment_date' => now()->subDays(40)->toDateString(), 'status' => 'verified', 'verified_at' => now()]);
        // Payment D: 400 hari lalu — untuk uji 1y (hanya masuk 1y, tidak masuk 30d/1M)
        \App\Models\Payment::create(['invoice_id' => $invoice->id, 'amount' => 70000, 'payment_method' => 'transfer', 'payment_date' => now()->subDays(200)->toDateString(), 'status' => 'verified', 'verified_at' => now()]);

        $svc = app(\App\Services\DashboardStatsService::class);
        // Clear cache agar hitungan fresh
        \Illuminate\Support\Facades\Cache::flush();

        $r1m = $svc->revenue($user, '1M');
        $r30d = $svc->revenue($user, '30d');
        $r1y = $svc->revenue($user, '1y');

        // 1M hanya hitung Agustus (100k), 30d hitung 30 hari terakhir (100k + 60k jika B dalam 30d)
        $expected30d = $inAugust ? 100000 : 160000; // jika 29 Jul di Agustus? Juni-Juli edge — tetapi 29 Jul tidak di Agustus
        // Untuk 23 Aug, 30d = 24 Jul–23 Aug → mencakup 29 Jul (B) dan hari ini
        $this->assertEquals($expected30d, (int) $r30d['current']);
        // 1M dan 30d harus berbeda jika B di luar bulan
        if (! $inAugust) {
            $this->assertNotEquals((int) $r1m['current'], (int) $r30d['current']);
        }
        $this->assertArrayHasKey('period', $r30d);
        $this->assertEquals('30d', $r30d['period']);
        $this->assertEquals('1M', $r1m['period']);
        $this->assertEquals('1y', $r1y['period']);
        // 1y harus >= 30d karena mencakup payment 200 hari lalu
        $this->assertGreaterThanOrEqual((int) $r30d['current'], (int) $r1y['current']);
        // previous period untuk 1y harus ada
        $this->assertArrayHasKey('prev', $r1y);
    }

    public function test_widget_w_preset_validation_and_clamp(): void
    {
        $user = $this->userWithRole('Owner');
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user);

        // w tidak dalam preset → 422
        $this->putJson(route('dashboard.preferences'), [
            'layout' => [['id' => 'clients_count', 'visible' => true, 'w' => 5]],
        ])->assertStatus(422);

        // w melebihi max (clients_count max 4) — harus di-clamp jadi 4, bukan error
        $this->putJson(route('dashboard.preferences'), [
            'layout' => [['id' => 'clients_count', 'visible' => true, 'w' => 12]],
        ])->assertOk();
        $this->assertEquals(4, $user->fresh()->dashboard_preferences['layout'][0]['w']);

        // w valid dalam rentang
        $this->putJson(route('dashboard.preferences'), [
            'layout' => [['id' => 'growth', 'visible' => true, 'w' => 8]],
        ])->assertOk();
        $this->assertEquals(8, $user->fresh()->dashboard_preferences['layout'][0]['w']);

        // w di bawah min → clamp ke min
        $this->putJson(route('dashboard.preferences'), [
            'layout' => [['id' => 'growth', 'visible' => true, 'w' => 3]],
        ])->assertOk();
        $this->assertEquals(6, $user->fresh()->dashboard_preferences['layout'][0]['w']); // growth min 6
    }

    public function test_widget_position_is_order(): void
    {
        $user = $this->userWithRole('Owner');
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user);
        $layout = [
            ['id' => 'revenue', 'visible' => true, 'w' => 3],
            ['id' => 'clients_count', 'visible' => true, 'w' => 3],
            ['id' => 'tickets_open', 'visible' => true, 'w' => 3],
        ];
        $this->putJson(route('dashboard.preferences'), ['layout' => $layout])->assertOk();
        $saved = $user->fresh()->dashboard_preferences['layout'];
        $this->assertEquals(['revenue', 'clients_count', 'tickets_open'], array_column($saved, 'id'));
        $this->assertEquals(3, $saved[0]['w']);
    }

    public function test_clamp_w_and_col_class(): void
    {
        $this->assertEquals(4, \App\Services\DashboardWidgetRegistry::clampW('clients_count', 12)); // max 4
        $this->assertEquals(6, \App\Services\DashboardWidgetRegistry::clampW('growth', 3)); // min 6
        $this->assertEquals(3, \App\Services\DashboardWidgetRegistry::clampW('clients_count', 3));
        $this->assertEquals(12, \App\Services\DashboardWidgetRegistry::clampW('growth', 12));
        $this->assertEquals('col-span-12 md:col-span-6 lg:col-span-3', \App\Services\DashboardWidgetRegistry::colClass(3));
        $this->assertEquals('col-span-12 lg:col-span-6', \App\Services\DashboardWidgetRegistry::colClass(6));
        $this->assertEquals('col-span-12', \App\Services\DashboardWidgetRegistry::colClass(12));
    }
}