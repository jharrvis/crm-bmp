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

    public function test_it_allocates_the_next_sequence_when_the_requested_target_is_used(): void
    {
        $plan = (new ClientCodeReconciliationService)->plan(new Collection([
            (object) ['id' => 10, 'branch_id' => 3, 'client_code' => '126001'],
            (object) ['id' => 11, 'branch_id' => 3, 'client_code' => '326001'],
            (object) ['id' => 12, 'branch_id' => 3, 'client_code' => 'C-KUDUS-0001'],
        ]));

        $this->assertCount(1, $plan['skipped']);
        $this->assertSame([], $plan['conflicts']);
        $this->assertSame('326002', $plan['changes'][0]['new_code']);
        $this->assertSame('326001', $plan['resolutions'][0]['requested_code']);
    }

    public function test_it_considers_codes_from_other_branches_when_allocating_a_new_sequence(): void
    {
        $plan = (new ClientCodeReconciliationService)->plan(
            new Collection([
                (object) ['id' => 10, 'branch_id' => 3, 'client_code' => '126022'],
            ]),
            '26',
            new Collection([
                (object) ['id' => 10, 'branch_id' => 3, 'client_code' => '126022'],
                (object) ['id' => 99, 'branch_id' => 1, 'client_code' => '326022'],
                (object) ['id' => 100, 'branch_id' => 1, 'client_code' => '326023'],
            ])
        );

        $this->assertSame('326024', $plan['changes'][0]['new_code']);
        $this->assertCount(1, $plan['resolutions']);
    }
}
