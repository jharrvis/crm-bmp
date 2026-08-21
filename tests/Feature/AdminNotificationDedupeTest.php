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
}
