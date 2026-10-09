<?php

namespace Tests\Feature\Auth;

use App\Modules\Settings\Models\Role;
use App\Modules\Settings\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN_PATH = '/login';

    private const CUSTOMER_HISTORY_PATH = '/customer-history';

    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(self::LOGIN_PATH);
    }

    public function test_guest_cannot_access_home(): void
    {
        $this->get(self::CUSTOMER_HISTORY_PATH)->assertRedirect(self::LOGIN_PATH);
    }

    public function test_expired_ajax_session_returns_localized_json_response(): void
    {
        $this->withSession(['locale' => 'th'])
            ->withHeaders([
                'Accept' => 'text/html',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get(self::CUSTOMER_HISTORY_PATH.'?partial=1')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
                'login_url' => url(self::LOGIN_PATH),
            ]);
    }

    public function test_expired_csrf_token_returns_session_expired_json(): void
    {
        app()->setLocale('th');
        $request = Request::create(self::CUSTOMER_HISTORY_PATH, 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame([
            'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
            'login_url' => url(self::LOGIN_PATH),
        ], json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR));
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
            'http://localhost:8080'.self::LOGIN_PATH.'?redirect=%2Fsettings%2Fusers%3Fper_page%3D25',
            $response->headers->get('Location')
        );
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(self::CUSTOMER_HISTORY_PATH);

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

    public function test_disabled_authenticated_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $this->get(self::CUSTOMER_HISTORY_PATH)
            ->assertRedirect(self::LOGIN_PATH)
            ->assertSessionHas('status', __('auth::messages.account_disabled'));

        $this->assertGuest();
    }

    public function test_disabled_authenticated_user_receives_unauthorized_ajax_response(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get(self::CUSTOMER_HISTORY_PATH.'?partial=1')
            ->assertUnauthorized()
            ->assertJson([
                'message' => __('auth::messages.account_disabled'),
                'login_url' => url(self::LOGIN_PATH),
            ]);

        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rendered_as_error_toast(): void
    {
        $this->from(self::LOGIN_PATH)->post(self::LOGIN_PATH, [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect(self::LOGIN_PATH);

        $this->get(self::LOGIN_PATH)
            ->assertOk()
            ->assertSee(__('auth::messages.invalid_credentials'))
            ->assertSee('window.showToast(', false)
            ->assertSee('"error"', false)
            ->assertDontSee('auth-alert-error', false);
    }

    public function test_user_returns_to_requested_page_after_signing_in_again(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::ADMIN_SLUG)->valueOrFail('id'),
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->get(self::LOGIN_PATH.'?redirect=/settings/users')->assertOk();

        $this->post(self::LOGIN_PATH, [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/settings/users');
    }
}
