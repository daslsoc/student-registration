<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ParentModel;
use App\Models\Child;

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
}
