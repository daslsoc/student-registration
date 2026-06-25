<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

/**
 * Tests for the frontend registration flow using Dusk.
 */
class RegistrationBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test visiting the registration form and submitting data.
     *
     * @return void
     */
    public function test_user_can_submit_registration_form()
    {
        $this->markTestSkipped(
            'Superseded and not runnable as written. The full submit path calls '.
            'the live Stripe API (no key in the dusk env), and the selectors here '.
            'are invalid (bracketed names as CSS, typing into <select>s, wrong '.
            'option values, a too-short phone). The form behaviour is covered by '.
            'RegistrationFormJsTest (browser) and the actual submission + validation '.
            '+ Stripe redirect by tests/Feature/RegistrationFeatureTest. To restore '.
            'an end-to-end browser payment test, add a Stripe test key to .env.dusk '.
            'and fix the field selectors to input[name="children[0][...]"].'
        );
    }
}
