<?php

namespace Tests\Browser;

use App\Models\ParentModel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The two token-gated GET pages: the Stripe success callback and the
 * email-link update form. Both need a parent row with the right token, which
 * is committed (DatabaseMigrations, not a transaction) so the served app sees it.
 */
class RegistrationTokenPagesTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_payment_success_page_renders_with_a_valid_token(): void
    {
        $token = Str::random(32);
        $parent = ParentModel::factory()->create(['payment_token' => $token]);

        $this->browse(function (Browser $browser) use ($parent, $token) {
            $browser->visit("/registration/success/{$parent->id}?token={$token}&amount=100")
                ->assertSee('Registration Complete!');
        });
    }

    public function test_update_form_renders_with_a_valid_update_token(): void
    {
        $token = Str::random(64);
        ParentModel::factory()->create([
            'update_token' => $token,
            'token_expires_at' => now()->addHour(),
        ]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/registration/update/{$token}")
                ->assertSee('Update Registration Details');
        });
    }
}
