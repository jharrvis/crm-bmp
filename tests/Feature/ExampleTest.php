<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        // / mengarahkan ke dashboard (auth redirect untuk guest saat sampai di dashboard)
        $response->assertRedirect(route('dashboard'));
    }
}