<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
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
        $this->get('/customer-history')->assertRedirect(self::LOGIN_PATH);
    }

    public function test_expired_ajax_session_returns_localized_json_response(): void
    {
        $this->withSession(['locale' => 'th'])
            ->withHeaders([
                'Accept' => 'text/html',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get('/customer-history?partial=1')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
                'login_url' => url(self::LOGIN_PATH),
            ]);
    }

    public function test_expired_csrf_token_returns_session_expired_json(): void
    {
        app()->setLocale('th');
        $request = Request::create('/customer-history', 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame([
            'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
            'login_url' => url(self::LOGIN_PATH),
        ], $response->getData(true));
    }

    public function test_expired_csrf_token_on_standard_form_redirects_to_login_with_return_path(): void
    {
        app()->setLocale('th');
        $request = Request::create('/settings/users', 'POST', server: [
            'HTTP_REFERER' => 'http://localhost:8080/settings/users?per_page=25',
        ]);
        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(
            'http://localhost:8080/login?redirect=%2Fsettings%2Fusers%3Fper_page%3D25',
            $response->headers->get('Location')
        );
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
        ])->assertRedirect('/customer-history');

        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(self::LOGIN_PATH);

        $this->assertGuest();

        $this->get(self::LOGIN_PATH)
            ->assertOk()
            ->assertSee('id="appToast"', false)
            ->assertSee(__('auth::messages.logged_out'));
    }

    public function test_user_returns_to_requested_page_after_signing_in_again(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::ADMIN_SLUG)->valueOrFail('id'),
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->get('/login?redirect=/settings/users')->assertOk();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/settings/users');
    }
}
