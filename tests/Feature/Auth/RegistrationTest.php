<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_doctor_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Dr. Test Physician',
            'email' => 'doctor_test@example.com',
            'specialty' => 'Cardiologist',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'doctor_test@example.com',
            'role' => 'doctor',
        ]);

        $this->assertDatabaseHas('doctor_profiles', [
            'specialty' => 'Cardiologist',
        ]);
    }
}
