<?php

namespace Tests\Unit;

use App\Models\Package;
use App\Models\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionPriceTest extends TestCase
{
    public function test_base_price_uses_the_locked_package_price(): void
    {
        $subscription = new Subscription([
            'price_at_subscription' => 150000,
            'billing_period_months' => 2,
        ]);
        $subscription->setRelation('package', new Package(['price' => 100000]));

        $this->assertSame(300000.0, $subscription->base_price);
    }

    public function test_custom_price_overrides_the_locked_package_price(): void
    {
        $subscription = new Subscription([
            'price_at_subscription' => 150000,
            'custom_price' => 275000,
            'billing_period_months' => 2,
        ]);

        $this->assertSame(275000.0, $subscription->base_price);
    }
}
