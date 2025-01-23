<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private array $validUserData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validUserData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    // -----------------------------------------
    // Registration Tests
    // -----------------------------------------

    public function test_it_validates_required_fields()
    {
        $response = $this->postJson(route('auth.register'), []);
        $response->assertStatus(402)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_it_registers_a_user_successfully()
    {
        $data = array_merge($this->validUserData, ['role' => 'admin']);

        $response = $this->postJson(route('auth.register'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'User registered successfully.'])
            ->assertJsonPath('user.name', $data['name'])
            ->assertJsonPath('user.email', $data['email'])
            ->assertJsonPath('user.role', $data['role']);

        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
        ]);
    }

    public function test_it_does_not_allow_duplicate_emails()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $data = array_merge($this->validUserData, ['email' => 'existing@example.com']);

        $response = $this->postJson(route('auth.register'), $data);

        $response->assertStatus(402)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_assigns_default_role_when_role_is_not_provided()
    {
        $response = $this->postJson(route('auth.register'), $this->validUserData);

        $response->assertStatus(201)
            ->assertJsonPath('user.role', 'user');

        $this->assertDatabaseHas('users', [
            'email' => $this->validUserData['email'],
            'role' => 'user',
        ]);
    }

    public function test_it_requires_password_confirmation()
    {
        $data = $this->validUserData;
        unset($data['password_confirmation']);

        $response = $this->postJson(route('auth.register'), $data);

        $response->assertStatus(402)
            ->assertJsonValidationErrors(['password']);
    }
}
