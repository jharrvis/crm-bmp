<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationDedupeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles for testing
        foreach (['Owner', 'Admin'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_dedupe_per_user_not_global(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $owner->assignRole('Owner');
        $admin->assignRole('Admin');

        $service = app(AdminNotificationService::class);

        // First notification for owner — should create
        $payload = ['domain_name' => 'example.com', 'days_left' => 7];
        $n1 = $service->notify('domain_expiry_7', 'Domain example.com akan expired dalam 7 hari', 'msg', $payload, $owner->id);
        $this->assertNotNull($n1->id);

        // Same domain/days for same user same day — should dedupe (return existing, not create new)
        $n2 = $service->notify('domain_expiry_7', 'Domain example.com akan expired dalam 7 hari', 'msg', $payload, $owner->id);
        $this->assertSame($n1->id, $n2->id);
        $this->assertEquals(1, AdminNotification::where('user_id', $owner->id)->count());

        // Same domain/days for different user — should NOT dedupe (per-user)
        $n3 = $service->notify('domain_expiry_7', 'Domain example.com akan expired dalam 7 hari', 'msg', $payload, $admin->id);
        $this->assertNotSame($n1->id, $n3->id);
        $this->assertEquals(1, AdminNotification::where('user_id', $admin->id)->count());
    }

    public function test_broadcast_creates_per_user(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $owner->assignRole('Owner');
        $admin->assignRole('Admin');

        $service = app(AdminNotificationService::class);
        $service->notifyAdmins('domain_overdue', 'Domain overdue', 'msg', ['domain_name' => 'test.com', 'days_left' => -1]);

        $this->assertEquals(1, AdminNotification::where('user_id', $owner->id)->count());
        $this->assertEquals(1, AdminNotification::where('user_id', $admin->id)->count());

        // Mark one as read — other should remain unread
        $ownerNotif = AdminNotification::where('user_id', $owner->id)->first();
        $service->markRead($ownerNotif);
        $this->assertNotNull($ownerNotif->fresh()->read_at);
        $adminNotif = AdminNotification::where('user_id', $admin->id)->first();
        $this->assertNull($adminNotif->read_at);
    }

    public function test_for_user_scope_includes_per_user_and_broadcast(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        AdminNotification::create(['user_id' => $user->id, 'type' => 'test', 'title' => 't', 'message' => 'm', 'payload' => []]);
        AdminNotification::create(['user_id' => null, 'target_role' => 'Admin', 'type' => 'test', 'title' => 't2', 'message' => 'm2', 'payload' => []]);

        $count = AdminNotification::forUser($user)->count();
        $this->assertEquals(2, $count);
    }

    public function test_generic_daily_dedupe_per_source_and_state(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $service = app(AdminNotificationService::class);

        // daily type: invoice_overdue per invoice source
        $n1 = $service->notify('invoice_overdue', 'Tagihan overdue', 'msg', ['invoice_id' => 1], $user->id, null, null, 'App\\Models\\Invoice', 1, 'overdue');
        $n2 = $service->notify('invoice_overdue', 'Tagihan overdue', 'msg', ['invoice_id' => 1], $user->id, null, null, 'App\\Models\\Invoice', 1, 'overdue');
        $this->assertSame($n1->id, $n2->id);
        $this->assertEquals(1, AdminNotification::where('dedupe_key', $n1->dedupe_key)->count());
    }

    public function test_generic_two_sources_same_day_not_deduped(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $service = app(AdminNotificationService::class);

        $n1 = $service->notify('invoice_overdue', 'Tagihan overdue', 'msg', ['invoice_id' => 1], $user->id, null, null, 'App\\Models\\Invoice', 1, 'overdue');
        $n2 = $service->notify('invoice_overdue', 'Tagihan overdue', 'msg', ['invoice_id' => 2], $user->id, null, null, 'App\\Models\\Invoice', 2, 'overdue');
        $this->assertNotSame($n1->id, $n2->id);
        $this->assertEquals(2, AdminNotification::where('type', 'invoice_overdue')->where('user_id', $user->id)->count());
    }

    public function test_generic_incident_dedupe_and_resolved_recreates(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $service = app(AdminNotificationService::class);

        $n1 = $service->notify('domain_overdue', 'Overdue', 'msg', ['domain_name' => 'a.com'], $user->id, null, null, 'App\\Models\\SubscriptionDomain', 10, null);
        $n2 = $service->notify('domain_overdue', 'Overdue', 'msg', ['domain_name' => 'a.com'], $user->id, null, null, 'App\\Models\\SubscriptionDomain', 10, null);
        $this->assertSame($n1->id, $n2->id);

        $service->markResolved($n1, $user);
        $this->assertNotNull($n1->fresh()->resolved_at);

        $n3 = $service->notify('domain_overdue', 'Overdue', 'msg', ['domain_name' => 'a.com'], $user->id, null, null, 'App\\Models\\SubscriptionDomain', 10, null);
        $this->assertNotSame($n1->id, $n3->id);
        $this->assertEquals(2, AdminNotification::where('type', 'domain_overdue')->where('user_id', $user->id)->count());
    }

    public function test_generic_admins_wrapper_preserves_source_identity(): void
    {
        $owner = User::factory()->create();
        $a1 = User::factory()->create();
        $owner->assignRole('Owner');
        $a1->assignRole('Admin');
        $service = app(AdminNotificationService::class);

        // Dua domain berbeda via notifyAdmins harus jadi 2 notif per user, bukan dedupe jadi 1
        $service->notifyAdmins('domain_overdue', 'Overdue A', 'msg', ['source_type' => 'App\\Models\\SubscriptionDomain', 'source_id' => 1, 'domain_name' => 'a.com']);
        $service->notifyAdmins('domain_overdue', 'Overdue B', 'msg', ['source_type' => 'App\\Models\\SubscriptionDomain', 'source_id' => 2, 'domain_name' => 'b.com']);

        $this->assertEquals(2, AdminNotification::where('user_id', $owner->id)->count());
        $this->assertEquals(2, AdminNotification::where('user_id', $a1->id)->count());
        $keys = AdminNotification::where('user_id', $owner->id)->pluck('dedupe_key')->toArray();
        $this->assertNotSame($keys[0], $keys[1]);
    }

    public function test_payload_recursive_redaction(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $service = app(AdminNotificationService::class);

        $n = $service->notify('domain_expiry', 'Test', 'msg', [
            'domain_name' => 'x.com',
            'metadata' => ['nested' => ['password' => 'secret123', 'safe' => 'ok']],
            'context' => ['api_key' => 'key123'],
            'provider_metadata' => ['should' => 'gone'],
        ], $user->id, null, null, 'App\\Models\\SubscriptionDomain', 99, '7');

        $payload = $n->fresh()->payload;
        $this->assertArrayNotHasKey('provider_metadata', $payload);
        $this->assertEquals('ok', $payload['metadata']['nested']['safe']);
        $this->assertArrayNotHasKey('password', $payload['metadata']['nested']);
        $this->assertArrayNotHasKey('api_key', $payload['context']);
    }
}
