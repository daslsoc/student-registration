<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class UpdateRegistrationFeatureTest
 *
 * Covers retrieving an existing registration link via email,
 * updating parent/child data, and re-processing payment.
 */
class UpdateRegistrationFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the retrieve details form loads.
     *
     * @return void
     */
    public function test_retrieve_details_form_loads()
    {
        $response = $this->get('/registration/retrieve');
        $response->assertStatus(200);
        $response->assertSee('Retrieve Registration Details');
    }

    /**
     * Test sending an update link to a known parent email.
     *
     * @return void
     */
    public function test_can_send_update_link()
    {
        $parent = ParentModel::factory()->create([
            'parent1_email' => 'existing@example.com',
        ]);

        $response = $this->post('/registration/retrieve', [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(302);
        $parent->refresh();
        $this->assertNotNull($parent->update_token);
        $this->assertNotNull($parent->token_expires_at);
    }

    /**
     * Test the update form loads if token is valid.
     *
     * @return void
     */
    public function test_update_form_loads_for_valid_token()
    {
        $parent = ParentModel::factory()->create([
            'update_token' => 'sometoken',
            'token_expires_at' => Carbon::now()->addHours(2),
        ]);

        $response = $this->get('/registration/update/'.$parent->update_token);

        $response->assertStatus(200);
        $response->assertSee('Update Registration Details');
    }

    /**
     * Test handleUpdate modifies parent/child data and re-initiates payment.
     *
     * @return void
     */
    public function test_can_update_existing_registration()
    {
        $parent = ParentModel::factory()->create([
            'update_token' => 'validtoken',
            'token_expires_at' => Carbon::now()->addHours(2),
        ]);
        $child = Child::factory()->create(['parent_id' => $parent->id]);

        $response = $this->post('/registration/update/'.$parent->update_token, [
            'parent1_first_name' => 'UpdatedFirst',
            'parent1_last_name' => 'UpdatedLast',
            'parent1_email' => $parent->parent1_email,
            'parent1_phone' => '9999999874',
            'parent2_first_name' => '',
            'parent2_last_name' => '',
            'parent2_email' => '',
            'parent2_phone' => '',
            'emergency_contact_name' => 'UpdatedContact',
            'emergency_contact_phone' => '1111222233',
            'relationship_to_family' => 'Cousin',
            'postcode' => '5678',
            'guidelines_accepted' => true,
            'children' => [
                [
                    'id' => $child->id,
                    'first_name' => 'KidUpdated',
                    'last_name' => 'SameLast',
                    'gender' => 'Male',
                    'date_of_birth' => '2010-05-05',
                    'residency_status' => 'Permanent Resident',
                    'day_school_name' => 'XYZ School',
                    'day_school_year' => 'Grade 2',
                    'allergies' => null,
                    'special_needs' => null,
                    'dhamma_class' => 'Class 1 (A)',
                    'sinhala_class' => 'Class 1 (A)',
                    'student_number' => '001',
                    'photography_allowed' => true,
                ],
                // Add a new child
                [
                    'first_name' => 'NewKid',
                    'last_name' => 'UpdatedLast',
                    'gender' => 'Female',
                    'date_of_birth' => '2012-01-01',
                    'residency_status' => 'Citizen',
                    'day_school_name' => 'XYZ School',
                    'day_school_year' => 'Grade 1',
                    'dhamma_class' => 'Class 1 (B)',
                    'sinhala_class' => 'Class 1 (B)',
                    'student_number' => '001',
                    'photography_allowed' => true,
                ],
            ],
        ]);

        // Expect 302 redirect to Stripe
        $response->assertStatus(302);

        // Check DB changes
        $parent->refresh();
        $this->assertEquals('UpdatedFirst', $parent->parent1_first_name);
        $this->assertEquals('UpdatedContact', $parent->emergency_contact_name);
        $this->assertCount(2, $parent->children); // old child + new one
        $this->assertDatabaseHas('children', [
            'first_name' => 'KidUpdated',
        ]);
        $this->assertDatabaseHas('children', [
            'first_name' => 'NewKid',
        ]);
    }
}
