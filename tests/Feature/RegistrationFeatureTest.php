<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Models\ParentModel;
use App\Models\Child;
use App\Models\Payment;
use App\Models\StudentNumberTracker;
use App\Models\User;

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
                    'dhamma_class' => 'Class 2 (C)',
                    'sinhala_class' => 'Class 3 (D)',
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
                    'dhamma_class' => 'Class 1 (A)',
                    'sinhala_class' => 'Class 1 (B)',
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
                    'dhamma_class' => 'Class 2 (C)',
                    'sinhala_class' => 'Class 3 (D)',
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
        $response = $this->get('/registration/success/' . $parent->id . '?amount=100.00' . '&token='. $paymentToken);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'parent_id' => $parent->id,
            'amount_paid' => 100.00,
        ]);
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
                    'dhamma_class' => 'G1',
                    'sinhala_class' => 'Class 1 (A)',
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
