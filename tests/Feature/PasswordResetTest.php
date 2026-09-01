<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * The "forgot my password" flow: link out, password in, and the cases where a
 * link must not work.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_offers_a_password_reset_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'));
    }

    public function test_requesting_a_link_emails_one(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'admin@example.test']);

        $this->post(route('password.email'), ['email' => 'admin@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_an_unknown_address_gets_the_same_answer_as_a_known_one(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'admin@example.test']);

        $known = $this->post(route('password.email'), ['email' => 'admin@example.test']);
        $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.test']);

        // Identical response, so the form can't be used to find out which
        // addresses have accounts.
        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
        Notification::assertSentTimes(ResetPassword::class, 1);
    }

    public function test_a_deactivated_account_is_sent_nothing(): void
    {
        Notification::fake();
        User::factory()->deactivated()->create(['email' => 'gone@example.test']);

        $this->post(route('password.email'), ['email' => 'gone@example.test'])
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_a_valid_token_sets_a_new_password(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test']);
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token]))->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'admin@example.test',
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-pass-1', $user->fresh()->password));

        $this->post(route('login.submit'), [
            'email' => 'admin@example.test',
            'password' => 'brand-new-pass-1',
        ]);
        $this->assertAuthenticated();
    }

    public function test_a_token_only_works_once(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'admin@example.test',
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ];

        $this->post(route('password.update'), $payload)->assertRedirect(route('login'));
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_a_weak_password_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'admin@example.test',
            'password' => 'abcdefgh',   // letters only, no number
            'password_confirmation' => 'abcdefgh',
        ])->assertSessionHasErrors('password');
    }

    public function test_a_mismatched_confirmation_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'admin@example.test',
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'something-else-2',
        ])->assertSessionHasErrors('password');
    }

    public function test_a_deactivated_user_cannot_spend_a_token_issued_before_removal(): void
    {
        $user = User::factory()->create(['email' => 'gone@example.test']);
        $token = Password::createToken($user);

        // Guarded against mass assignment, so set it explicitly.
        $user->deactivated_at = now();
        $user->save();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'gone@example.test',
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('brand-new-pass-1', $user->fresh()->password));
    }
}
