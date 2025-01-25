<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
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

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null,
            'Test Personal Access Client',
            config('app.url') // Base URL for your test environment
        );

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $client->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    // -----------------------------------------
    // Login Tests
    // -----------------------------------------

    public function test_it_requires_email_and_password_for_login()
    {
        $response = $this->postJson(route('auth.login'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_it_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Invalid credentials']);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Send login request
        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'user']);

        $this->assertAuthenticatedAs($user);
    }
}
