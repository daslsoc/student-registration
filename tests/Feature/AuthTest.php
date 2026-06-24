<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AuthTest
 *
 * Tests the login/logout flow and admin route protection.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_routes()
    {
        // Attempt to visit an admin route
        $response = $this->get('/admin/parents-students');
        // Should be redirected to login
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_and_access_admin()
    {
        // 1) Create a user
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ]);

        // 2) Attempt to login
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);
        // Should redirect to the intended admin route (or a default)
        $response->assertRedirect('/admin/parents-students');

        // 3) Confirm the user is authenticated
        $this->assertAuthenticatedAs($user);

        // 4) Now that we're logged in, we can access admin route
        $adminResponse = $this->get('/admin/parents-students');
        $adminResponse->assertStatus(200);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ]);

        // Attempt login with wrong password
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpass',
        ]);

        // Should redirect back to /login with an error
        $response->assertStatus(302);
        $this->assertGuest(); // user not authenticated
    }

    public function test_user_can_logout()
    {
        // Create & login a user
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        // Confirm we are logged in
        $this->assertAuthenticatedAs($user);

        // Perform logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();

        // Try admin page again
        $res = $this->get('/admin/parents-students');
        $res->assertRedirect('/login');
    }
}
