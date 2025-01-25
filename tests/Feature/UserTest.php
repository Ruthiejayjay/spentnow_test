<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;


    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for testing
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('adminpassword'),
        ]);

        // Create a regular user for testing
        $this->user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('userpassword'),
        ]);

        Passport::actingAs($this->admin, ['*']);
    }

    public function test_admin_can_view_all_users()
    {
        $response = $this->actingAs($this->admin, 'api')->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['email' => $this->admin->email])
            ->assertJsonFragment(['email' => $this->user->email]);
    }

    public function test_user_cannot_view_all_users()
    {
        Passport::actingAs($this->user, ['*']);
        $this->getJson(route('users.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        $data = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        Passport::actingAs($this->admin, ['*']);
        $response = $this->postJson(route('users.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'User created successfully.'])
            ->assertJsonPath('user.email', $data['email']);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    public function test_user_cannot_create_user()
    {
        Passport::actingAs($this->user, ['*']);
        $this->postJson(route('users.store'), [])
            ->assertStatus(403); // Forbidden
    }

    public function test_user_can_view_own_profile()
    {
        Passport::actingAs($this->user, ['*']);
        $this->getJson(route('users.show', $this->user->id))
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $this->user->email]);
    }

    public function test_user_cannot_view_other_user_profile()
    {
        Passport::actingAs($this->user, ['*']);
        $this->getJson(route('users.show', $this->admin->id))
            ->assertStatus(403); // Unauthorized
    }

    public function test_admin_can_view_other_user_profile()
    {
        Passport::actingAs($this->admin, ['*']);
        $this->getJson(route('users.show', $this->user->id))
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $this->user->email]);
    }

    public function test_user_cannot_update_other_user_profile()
    {
        $data = ['name' => 'Unauthorized Update'];

        Passport::actingAs($this->user, ['*']);
        $this->putJson(route('users.update', $this->admin->id), $data)
            ->assertStatus(403);
    }

    public function test_admin_can_update_user_profile()
    {
        $data = ['name' => 'Updated by Admin'];

        Passport::actingAs($this->admin, ['*']);
        $response = $this->putJson(route('users.update', $this->user->id), $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User updated successfully.'])
            ->assertJsonPath('user.name', $data['name']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $data['name'],
        ]);
    }

    public function test_admin_can_delete_user()
    {
        Passport::actingAs($this->admin, ['*']);
        $this->deleteJson(route('users.destroy', $this->user->id))
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'User deleted successfully.']);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_user_cannot_delete_user()
    {
        Passport::actingAs($this->user, ['*']);
        $this->deleteJson(route('users.destroy', $this->admin->id))
            ->assertStatus(403);
    }

    public function test_admin_can_update_user_role()
    {
        $data = ['role' => 'admin'];

        Passport::actingAs($this->admin, ['*']);
        $response = $this->patchJson(route('users.updateRole', $this->user->id), $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User role updated successfully.'])
            ->assertJsonPath('user.role', $data['role']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'role' => $data['role'],
        ]);
    }

    public function test_user_cannot_update_user_role()
    {
        $data = ['role' => 'admin'];

        Passport::actingAs($this->user, ['*']);
        $this->patchJson(route('users.updateRole', $this->admin->id), $data)
            ->assertStatus(403);
    }
}
