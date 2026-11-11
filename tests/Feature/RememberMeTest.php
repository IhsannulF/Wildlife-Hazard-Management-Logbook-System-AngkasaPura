<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    private function createTemporaryPegawai(bool $aktif = true): User
    {
        $suffix = str_replace('.', '', (string) microtime(true)).'_'.random_int(100, 999);

        return User::create([
            'username' => 'remember_test_'.$suffix,
            'password' => Hash::make('secret123'),
            'namalengkap' => 'Petugas Remember Test',
            'jabatan' => 'AMC Officer',
            'role' => 'pegawai',
            'nama' => 'Petugas Remember Test',
            'aktif' => $aktif,
        ]);
    }

    public function test_login_page_displays_remember_me_checkbox(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('name="remember"', false);
        $response->assertSee('Ingat saya');
    }

    public function test_user_can_login_with_remember_me_and_receive_recaller_cookie(): void
    {
        $user = $this->createTemporaryPegawai();

        try {
            $response = $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'secret123',
                'remember' => '1',
            ]);

            $response->assertRedirect(route('user.dashboard'));
            $this->assertAuthenticatedAs($user);
            $response->assertCookie(Auth::guard('web')->getRecallerName());
            $this->assertNotNull($user->fresh()->remember_token);
        } finally {
            $user->delete();
        }
    }

    public function test_user_is_reauthenticated_via_remember_cookie_when_session_is_cleared(): void
    {
        $user = $this->createTemporaryPegawai();

        try {
            $loginResponse = $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'secret123',
                'remember' => '1',
            ]);

            $recallerName = Auth::guard('web')->getRecallerName();
            $recallerCookie = $loginResponse->getCookie($recallerName, false);
            $this->assertNotNull($recallerCookie);

            // Simulate a fresh browser visit after session expiration (only recaller cookie remains)
            $this->flushSession();
            Auth::forgetGuards();

            $dashboardResponse = $this->withUnencryptedCookie($recallerName, $recallerCookie->getValue())
                ->get(route('user.dashboard'));

            $dashboardResponse->assertStatus(200);
            $this->assertAuthenticatedAs($user);
            $this->assertTrue(Auth::viaRemember());
        } finally {
            $user->delete();
        }
    }

    public function test_user_without_remember_me_does_not_receive_recaller_cookie(): void
    {
        $user = $this->createTemporaryPegawai();

        try {
            $response = $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'secret123',
            ]);

            $response->assertRedirect(route('user.dashboard'));
            $this->assertAuthenticatedAs($user);
            $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
        } finally {
            $user->delete();
        }
    }

    public function test_logout_clears_remember_cookie_and_rotates_token(): void
    {
        $user = $this->createTemporaryPegawai();

        try {
            $loginResponse = $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'secret123',
                'remember' => '1',
            ]);

            $recallerName = Auth::guard('web')->getRecallerName();
            $recallerCookie = $loginResponse->getCookie($recallerName, false);
            $this->assertNotNull($recallerCookie);

            $oldToken = $user->fresh()->remember_token;
            $this->assertNotNull($oldToken);

            $logoutResponse = $this->withUnencryptedCookie($recallerName, $recallerCookie->getValue())
                ->post(route('logout'));

            $logoutResponse->assertRedirect(route('login'));
            $this->assertGuest();
            $logoutResponse->assertCookieExpired($recallerName);
            $this->assertNotSame($oldToken, $user->fresh()->remember_token);
        } finally {
            $user->delete();
        }
    }

    public function test_failed_login_preserves_remember_checkbox_input(): void
    {
        $user = $this->createTemporaryPegawai();

        try {
            $response = $this->from(route('login'))->post(route('login'), [
                'username' => $user->username,
                'password' => 'wrong-password',
                'remember' => '1',
            ]);

            $response->assertRedirect(route('login'));
            $response->assertSessionHasErrors('username');
            $this->assertSame('1', old('remember'));
        } finally {
            $user->delete();
        }
    }

    public function test_inactive_user_cannot_stay_logged_in_via_remember_cookie(): void
    {
        $user = $this->createTemporaryPegawai();

        try {
            $loginResponse = $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'secret123',
                'remember' => '1',
            ]);

            $recallerName = Auth::guard('web')->getRecallerName();
            $recallerCookie = $loginResponse->getCookie($recallerName, false);
            $this->assertNotNull($recallerCookie);

            $user->update(['aktif' => false]);

            $this->flushSession();
            Auth::forgetGuards();

            $response = $this->withUnencryptedCookie($recallerName, $recallerCookie->getValue())
                ->get(route('user.dashboard'));

            $response->assertRedirect(route('login'));
            $this->assertGuest();
        } finally {
            $user->delete();
        }
    }
}
