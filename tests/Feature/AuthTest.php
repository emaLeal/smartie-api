<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test for creating an user
     **/
    public function test_user_can_login_with_correct_credentials(): void {
        $user = $this->getFactoryUser();
        $response = $this->postJson('/api/auth/login', [
            'name' => 'pepe',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('user.name', 'pepe');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Try Authenticating with incorrect credentials
     **/
    public function test_cannot_login_with_incorrect_credentials(): void {
        $user = $this->getFactoryUser();

        $response = $this->postJson('/api/auth/login', [
            'name' => 'pepe',
            'password' => 'clave-equivocada',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test to check if the email can be repeated
     **/
    public function test_register_fails_if_email_is_already_taken() {
        User::factory()->create(['email' => 'test@ejemplo.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Nuevo Usuario',
            'email' => 'test@ejemplo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test to see the user is able to work
     **/
    public function test_user_can_register_successfully() {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Perez',
            'email' => 'juan@ejemplo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@ejemplo.com'
        ]);
    }

    /**
     * Test to see if the view is protected
     **/
    public function test_user_can_view_their_profile() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->getJson('/api/auth/me');

        $response->assertStatus(200)
                 ->assertJsonPath('user.id', $user->id);
    }


    /**
     * Return a generic user for handling the tests
     **/
    private function getFactoryUser(): User {
        return User::factory()->create([
            'name' => 'pepe',
            'password' => bcrypt('password123')
        ]);
    }
}
