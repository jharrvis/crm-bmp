<?php

namespace Tests\Unit;

use App\Models\HostingServer;
use App\Models\Mailbox;
use App\Models\SubscriptionMailHosting;
use App\Services\MailServerResolver;
use DomainException;
use PHPUnit\Framework\TestCase;

class MailHostingSecurityTest extends TestCase
{
    public function test_sensitive_mail_hosting_attributes_are_hidden_from_serialization(): void
    {
        $this->assertContains('secret_key', (new HostingServer)->getHidden());
        $this->assertContains('admin_password_encrypted', (new SubscriptionMailHosting)->getHidden());
        $this->assertContains('password_encrypted', (new Mailbox)->getHidden());
    }

    public function test_inactive_or_unsupported_mail_server_cannot_be_resolved(): void
    {
        $resolver = new MailServerResolver;

        $this->expectException(DomainException::class);
        $resolver->resolve(new HostingServer(['type' => 'zimbra', 'is_active' => false]));
    }
}
