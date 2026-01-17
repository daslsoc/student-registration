<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
        $this->browse(function (Browser $browser) {
            $browser->visit('/registration')
                    ->assertSee('School Registration')
                    ->type('parent1_first_name', 'Jane')
                    ->type('parent1_last_name', 'Doe')
                    ->type('parent1_email', 'jane@example.com')
                    ->type('parent1_phone', '555-1234')
                    ->type('emergency_contact_name', 'Bob')
                    ->type('emergency_contact_phone', '555-5678')
                    ->type('relationship_to_family', 'Uncle')
                    ->type('children[0][first_name]', 'ChildA')
                    ->type('children[0][last_name]', 'Doe')
                    ->select('children[0][gender]', 'Female')
                    ->type('children[0][date_of_birth]', '2012-04-05')
                    ->select('children[0][residency_status]', 'Citizen')
                    ->type('children[0][day_school_name]', 'XYZ School')
                    ->type('children[0][day_school_year]', '4')
                    ->type('children[0][dhamma_class]', 'Grade 2')
                    ->type('children[0][sinhala_class]', 'Level B')
                    ->press('Register')
                    ->assertPathIsNot('/registration'); // Expect redirect
        });
    }
}
