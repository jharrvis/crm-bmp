<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_disabled_when_config_off(): void
    {
        if (config('auth.registration_enabled')) {
            $this->markTestSkipped('Registrasi diaktifkan di environment ini.');
        }

        // Route /register hanya diregistrasikan jika AUTH_REGISTRATION_ENABLED=true
        $this->get('/register')->assertNotFound();
    }

    public function test_new_users_cannot_register_when_disabled(): void
    {
        if (config('auth.registration_enabled')) {
            $this->markTestSkipped('Registrasi diaktifkan di environment ini.');
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertNotFound();
        $this->assertGuest();
    }
}