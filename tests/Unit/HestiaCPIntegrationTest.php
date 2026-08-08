<?php

namespace Tests\Unit;

use App\Models\HostingServer;
use App\Models\SubscriptionHosting;
use App\Services\HestiaCPService;
use App\Services\WebHostResolver;
use DomainException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HestiaCPIntegrationTest extends TestCase
{
    public function test_sensitive_hosting_attributes_are_hidden_from_serialization(): void
    {
        $this->assertContains('password_encrypted', (new SubscriptionHosting)->getHidden());
        $this->assertContains('api_key', (new HostingServer)->getHidden());
        $this->assertContains('secret_key', (new HostingServer)->getHidden());

        $excluded = (new ReflectionClass(SubscriptionHosting::class))
            ->getProperty('activitylogExcludeAttributes')
            ->getValue(new SubscriptionHosting);
        $this->assertContains('password_encrypted', $excluded);
    }

    public function test_inactive_or_unsupported_web_server_cannot_be_resolved(): void
    {
        $resolver = new WebHostResolver;

        $this->expectException(DomainException::class);
        $resolver->resolve(new HostingServer(['type' => 'hestiacp', 'is_active' => false]));
    }

    public function test_non_hestia_type_is_rejected_by_resolver(): void
    {
        $resolver = new WebHostResolver;

        $this->expectException(DomainException::class);
        $resolver->resolve(new HostingServer(['type' => 'zimbra', 'is_active' => true]));
    }

    public function test_normalise_user_sanitises_suspended_flag(): void
    {
        $service = new HestiaCPService(new HostingServer(['host' => 'h', 'port' => 8083]));
        $method = (new ReflectionClass($service))->getMethod('normaliseUser');
        $method->setAccessible(true);

        $user = $method->invoke($service, 'client01', ['USER' => 'client01', 'SUSPENDED' => 'yes', 'PACKAGE' => 'basic']);
        $this->assertTrue($user['suspended']);
        $this->assertSame('client01', $user['username']);

        $other = $method->invoke($service, 'client02', ['USER' => 'client02', 'SUSPENDED' => 'no']);
        $this->assertFalse($other['suspended']);
    }

    public function test_error_response_discards_remote_detail_and_uses_safe_message(): void
    {
        $service = new HestiaCPService(new HostingServer(['host' => 'h', 'port' => 8083]));
        $method = (new ReflectionClass($service))->getMethod('error');
        $method->setAccessible(true);

        $result = $method->invoke($service, 'Connection failed with HTTP 500');

        $this->assertFalse($result['success']);
        $this->assertNull($result['data']);
        $this->assertSame('Connection failed with HTTP 500', $result['message']);
    }
}