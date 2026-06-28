<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\StudentNumberTracker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class RegistrationFeatureTest
 *
 * Covers:
 *  - Viewing the registration form
 *  - Submitting new registrations (single child & multiple children)
 *  - Payment success handling
 *  - Basic validation checks
 */
class RegistrationFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the registration form loads successfully.
     *
     * @return void
     */
    public function test_registration_form_loads()
    {
        $response = $this->get('/registration');

        $response->assertStatus(200);
        $response->assertSee('School Registration'); // from form.blade.php h1
    }

    /**
     * Test submitting a new registration with a single child.
     *
     * @return void
     */
    public function test_can_submit_single_child_registration()
    {
        // Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/registration', [
            'parent1_first_name' => 'Jane',
            'parent1_last_name' => 'Doe',
            'parent1_email' => 'jane@example.com',
            'parent1_phone' => '5551234687',
            'emergency_contact_name' => 'Alice',
            'emergency_contact_phone' => '5555678541',
            'relationship_to_family' => 'Aunt',
            'postcode' => '5678',
            'guidelines_accepted' => true,
            'children' => [
                [
                    'first_name' => 'ChildOne',
                    'last_name' => 'Doe',
                    'gender' => 'Female',
                    'date_of_birth' => '2010-01-01',
                    'residency_status' => 'Citizen',
                    'day_school_name' => 'ABC School',
                    'day_school_year' => 'Grade 5',
                    'student_number' => '001',
                    'photography_allowed' => true,
                ],
            ],
        ]);

        // Registration typically redirects to Stripe's URL => 302
        $response->assertStatus(302);

        // Check DB
        $this->assertDatabaseHas('parents', ['parent1_email' => 'jane@example.com']);
        $this->assertDatabaseHas('children', ['first_name' => 'ChildOne']);

        // StudentNumberTracker should have incremented
        $tracker = StudentNumberTracker::find(1);
        $this->assertNotNull($tracker);
        $this->assertEquals(1, $tracker->current_number);
    }

    /**
     * Test registration with multiple children.
     *
     * @return void
     */
    public function test_can_submit_multiple_children_registration()
    {
        // Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/registration', [
            'parent1_first_name' => 'John',
            'parent1_last_name' => 'Smith',
            'parent1_email' => 'john.smith@example.com',
            'parent1_phone' => '1234567890',
            'emergency_contact_name' => 'Bob',
            'emergency_contact_phone' => '9999999998',
            'relationship_to_family' => 'Uncle',
            'postcode' => '5678',
            'guidelines_accepted' => true,
            'children' => [
                [
                    'first_name' => 'KidOne',
                    'last_name' => 'Smith',
                    'gender' => 'Male',
                    'date_of_birth' => '2012-02-02',
                    'residency_status' => 'Citizen',
                    'day_school_name' => 'XYZ School',
                    'day_school_year' => 'Grade 3',
                    'student_number' => '001',
                    'photography_allowed' => true,
                ],
                [
                    'first_name' => 'KidTwo',
                    'last_name' => 'Smith',
                    'gender' => 'Female',
                    'date_of_birth' => '2014-03-03',
                    'residency_status' => 'Permanent Resident',
                    'day_school_name' => 'XYZ School',
                    'day_school_year' => 'Grade 1',
                    'student_number' => '002',
                    'photography_allowed' => true,
                ],
            ],
        ]);

        $response->assertStatus(302);  // Redirect to Stripe

        $this->assertDatabaseHas('parents', ['parent1_email' => 'john.smith@example.com']);
        $this->assertDatabaseCount('children', 2);
    }

    /**
     * Test payment success route.
     * Typically you'd have a "success" route where we record payment details.
     *
     * @return void
     */
    public function test_handle_success_records_payment()
    {
        // Create a parent
        $parent = ParentModel::factory()->create();

        // provide a random string to check later
        $paymentToken = Str::random(32);
        $parent->update(['payment_token' => $paymentToken]);

        // Simulate Stripe success callback with ?amount=100
        $response = $this->get('/registration/success/'.$parent->id.'?amount=100.00'.'&token='.$paymentToken);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'parent_id' => $parent->id,
            'amount_paid' => 100.00,
        ]);
    }

    /**
     * The recorded payment amount must be computed server-side from the child
     * count + pricing config, NOT read from the ?amount= query string (which a
     * user can forge). A parent with two children must be billed the
     * multiple-children price even if the URL claims a lower amount.
     *
     * @return void
     */
    public function test_success_amount_is_server_computed_and_ignores_query_string()
    {
        $parent = ParentModel::factory()->create();
        Child::factory()->count(2)->create(['parent_id' => $parent->id]);

        $paymentToken = Str::random(32);
        $parent->update(['payment_token' => $paymentToken]);

        // Forge a tiny amount in the URL; it must be ignored.
        $response = $this->get('/registration/success/'.$parent->id.'?amount=1&token='.$paymentToken);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'parent_id' => $parent->id,
            'amount_paid' => config('custom.pricing.multiple_children'),
        ]);
        $this->assertDatabaseMissing('payments', [
            'parent_id' => $parent->id,
            'amount_paid' => 1,
        ]);
    }

    /**
     * Re-registering with an email already on file should not create a second
     * family. Instead the user is redirected to the retrieve flow to get a
     * secure update link.
     *
     * @return void
     */
    public function test_duplicate_email_registration_redirects_to_retrieve()
    {
        ParentModel::factory()->create(['parent1_email' => 'existing@example.com']);

        $response = $this->post('/registration', [
            'parent1_first_name' => 'Jane',
            'parent1_last_name' => 'Doe',
            'parent1_email' => 'existing@example.com',
            'parent1_phone' => '5551234687',
            'emergency_contact_name' => 'Alice',
            'emergency_contact_phone' => '5555678541',
            'relationship_to_family' => 'Aunt',
            'postcode' => '5678',
            'guidelines_accepted' => true,
            'children' => [
                [
                    'first_name' => 'ChildOne',
                    'last_name' => 'Doe',
                    'gender' => 'Female',
                    'date_of_birth' => '2010-01-01',
                    'residency_status' => 'Citizen',
                    'day_school_name' => 'ABC School',
                    'day_school_year' => 'Grade 5',
                    'student_number' => '001',
                    'photography_allowed' => true,
                ],
            ],
        ]);

        $response->assertRedirect(route('registration.retrieve'));
        $response->assertSessionHas('status');

        // No duplicate parent and no children were created.
        $this->assertDatabaseCount('parents', 1);
        $this->assertDatabaseCount('children', 0);
    }

    /**
     * Test validation fails if required fields are missing.
     *
     * @return void
     */
    public function test_validation_fails_for_missing_fields()
    {
        $response = $this->post('/registration', [
            // no parent data
            'children' => [
                [
                    'first_name' => 'IncompleteChild',
                    'last_name' => 'NoParentData',
                    'gender' => 'Male',
                    'date_of_birth' => '2010-01-01',
                    'residency_status' => 'Citizen',
                    'day_school_name' => 'Test School',
                    'day_school_year' => 3,
                ],
            ],
        ]);

        // Should fail validation => 302 redirect or 422 if expecting JSON
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'parent1_first_name',
            'parent1_last_name',
            'parent1_email',
            'parent1_phone',
            'emergency_contact_name',
            'emergency_contact_phone',
            'relationship_to_family',
        ]);
    }
}
