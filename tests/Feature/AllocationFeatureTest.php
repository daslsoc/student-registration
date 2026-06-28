<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AllocationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_allocates_each_child_to_a_class_for_both_subjects(): void
    {
        Mail::fake();

        $parent = ParentModel::factory()->create();
        $token = Str::random(32);
        $parent->update(['payment_token' => $token]);

        $child = Child::factory()->create([
            'parent_id' => $parent->id,
            'day_school_year' => 'Grade 3',          // → Class C
            'allocated_dhamma_class' => null,
            'allocated_sinhala_class' => null,
        ]);

        $this->get('/registration/success/'.$parent->id.'?token='.$token)->assertStatus(200);

        $child->refresh();
        $this->assertSame('Class C', $child->allocated_dhamma_class);
        $this->assertSame('Class C', $child->allocated_sinhala_class);
    }

    public function test_an_unmapped_grade_is_left_unallocated(): void
    {
        Mail::fake();

        $parent = ParentModel::factory()->create();
        $token = Str::random(32);
        $parent->update(['payment_token' => $token]);

        $child = Child::factory()->create([
            'parent_id' => $parent->id,
            'day_school_year' => 'Grade 99',         // not in the rule
            'allocated_dhamma_class' => null,
        ]);

        $this->get('/registration/success/'.$parent->id.'?token='.$token)->assertStatus(200);

        $child->refresh();
        $this->assertNull($child->allocated_dhamma_class);
        $this->assertNull($child->allocated_sinhala_class);
    }
}
