<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Public GET pages that render without any data or auth.
 */
class PublicPagesTest extends DuskTestCase
{
    public function test_public_pages_render(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/guidelines')
                ->assertSee('Rules, Guidelines and Responsibilities');

            $browser->visit('/login')
                ->assertSee('Login');

            $browser->visit('/registration/retrieve')
                ->assertSee('Retrieve Registration Details');
        });
    }
}
