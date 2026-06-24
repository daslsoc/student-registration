<?php

namespace Tests\Unit;

use App\Models\Child;
use App\Models\ParentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ParentModel.
 */
class ParentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a parent and linking children.
     *
     * @return void
     */
    public function test_parent_can_have_children()
    {
        $parent = ParentModel::factory()->create();
        $child = Child::factory()->create(['parent_id' => $parent->id]);

        $this->assertCount(1, $parent->children);
        $this->assertEquals($child->id, $parent->children->first()->id);
    }
}
