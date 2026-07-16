<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN_PATH = '/login';

    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(self::LOGIN_PATH);
    }

    public function test_guest_cannot_access_home(): void
    {
        $this->get('/home')->assertRedirect(self::LOGIN_PATH);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(self::LOGIN_PATH);

        $this->assertGuest();
    }
}
