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

        // Guest user hitting / gets redirected to login (auth middleware on group)
        $response->assertRedirect(route('login'));
    }
}