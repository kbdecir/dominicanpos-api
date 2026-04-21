<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@test.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'company_name' => 'Empresa Demo',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@test.com',
        ]);
    }

    public function test_user_can_login_successfully(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@test.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'company_name' => 'Empresa Demo',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => '12345678',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'fake@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ]);
    }
}
