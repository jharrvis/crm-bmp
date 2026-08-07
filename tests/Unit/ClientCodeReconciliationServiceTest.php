<?php

namespace Tests\Unit;

use App\Services\ClientCodeReconciliationService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ClientCodeReconciliationServiceTest extends TestCase
{
    public function test_it_reconciles_the_branch_prefix_without_changing_sequence(): void
    {
        $plan = (new ClientCodeReconciliationService)->plan(new Collection([
            (object) ['id' => 10, 'branch_id' => 3, 'client_code' => '126001'],
        ]));

        $this->assertSame([[
            'id' => 10,
            'branch_id' => 3,
            'old_code' => '126001',
            'new_code' => '326001',
        ]], $plan['changes']);
        $this->assertSame([], $plan['skipped']);
        $this->assertSame([], $plan['conflicts']);
    }

    public function test_it_skips_non_legacy_codes_and_detects_existing_target_conflicts(): void
    {
        $plan = (new ClientCodeReconciliationService)->plan(new Collection([
            (object) ['id' => 10, 'branch_id' => 3, 'client_code' => '126001'],
            (object) ['id' => 11, 'branch_id' => 3, 'client_code' => '326001'],
            (object) ['id' => 12, 'branch_id' => 3, 'client_code' => 'C-KUDUS-0001'],
        ]));

        $this->assertCount(1, $plan['skipped']);
        $this->assertCount(1, $plan['conflicts']);
        $this->assertSame('326001', $plan['conflicts'][0]['new_code']);
    }
}
