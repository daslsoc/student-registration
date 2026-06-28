<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AdminFeatureTest
 *
 * Tests admin-specific routes, e.g. viewing the parent/child list.
 */
class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test viewing the parent/child list page.
     *
     * @return void
     */
    public function test_can_view_parent_child_list()
    {
        // Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create 1 parent with 2 children
        $parent = ParentModel::factory()->create([
            'parent1_first_name' => 'AdminTest',
            'parent1_last_name' => 'Parent',
        ]);

        Child::factory()->count(2)->create(['parent_id' => $parent->id]);

        $response = $this->get('/admin/parents-students');

        $response->assertStatus(200);
        $response->assertSee('Parent & Child List'); // from parent_child_list.blade.php h1
        $response->assertSee('AdminTest Parent');
    }

    public function test_the_status_filter_narrows_the_list(): void
    {
        $this->actingAs(User::factory()->create());

        $completed = ParentModel::factory()->create(['registration_status' => ParentModel::STATUS_COMPLETED]);
        Child::factory()->create(['parent_id' => $completed->id, 'first_name' => 'Completed', 'last_name' => 'Kid']);

        $pending = ParentModel::factory()->create(['registration_status' => ParentModel::STATUS_PENDING]);
        Child::factory()->create(['parent_id' => $pending->id, 'first_name' => 'Pending', 'last_name' => 'Kid']);

        // Both by default.
        $this->get('/admin/parents-students')
            ->assertSee('Completed Kid')->assertSee('Pending Kid');

        // Completed only.
        $this->get('/admin/parents-students?status=completed')
            ->assertSee('Completed Kid')->assertDontSee('Pending Kid');

        // Pending only.
        $this->get('/admin/parents-students?status=pending')
            ->assertSee('Pending Kid')->assertDontSee('Completed Kid');
    }
}
