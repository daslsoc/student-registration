<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Verifies the Vite-bundled registration form behaviour (initRegistrationForm)
 * actually loads and runs in a real browser — i.e. @vite wiring works.
 */
class RegistrationFormJsTest extends DuskTestCase
{
    public function test_add_and_remove_child_buttons_work(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/registration')
                ->assertSee('School Registration')
                ->assertMissing('input[name="children[1][first_name]"]')
                // Add a second child block (re-indexed by the bundled JS).
                // Dispatch the click via JS so the assertion isn't flaky on
                // overlapping/off-screen elements — it still fires the handler
                // that initRegistrationForm() attached, which is what we verify.
                ->script("document.getElementById('addChildBtn').click()");

            $browser->waitFor('input[name="children[1][first_name]"]')
                ->assertPresent('input[name="children[1][first_name]"]')
                // Remove it again.
                ->script("document.getElementById('removeChildBtn').click()");

            $browser->waitUntilMissing('input[name="children[1][first_name]"]')
                ->assertMissing('input[name="children[1][first_name]"]');
        });
    }
}
