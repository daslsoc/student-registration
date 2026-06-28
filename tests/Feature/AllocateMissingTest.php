<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllocateMissingTest extends TestCase
{
    use RefreshDatabase;

    private function paidChild(array $attributes = []): Child
    {
        $parent = ParentModel::factory()->create();
        Payment::create(['parent_id' => $parent->id, 'amount_paid' => 50, 'paid_date' => now()]);

        return Child::factory()->create(array_merge([
            'parent_id' => $parent->id,
            'student_number' => '1000',
            'allocated_dhamma_class' => null,
            'allocated_sinhala_class' => null,
        ], $attributes));
    }

    public function test_it_allocates_unallocated_paid_children_from_the_rule(): void
    {
        $child = $this->paidChild(['day_school_year' => 'Grade 3']); // → Class C

        $this->artisan('integration:allocate-missing')->assertExitCode(0);

        $child->refresh();
        $this->assertSame('Class C', $child->allocated_dhamma_class);
        $this->assertSame('Class C', $child->allocated_sinhala_class);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $child = $this->paidChild(['day_school_year' => 'Grade 3']);

        $this->artisan('integration:allocate-missing --dry-run')
            ->expectsOutputToContain('would allocate')
            ->assertExitCode(0);

        $child->refresh();
        $this->assertNull($child->allocated_dhamma_class);
    }

    public function test_it_never_overrides_an_existing_allocation(): void
    {
        // An admin has placed this child in Class A, overriding the Grade-3 rule.
        $child = $this->paidChild([
            'day_school_year' => 'Grade 3',
            'allocated_dhamma_class' => 'Class A',
            'allocated_sinhala_class' => 'Class A',
        ]);

        $this->artisan('integration:allocate-missing')->assertExitCode(0);

        $child->refresh();
        $this->assertSame('Class A', $child->allocated_dhamma_class);
    }

    public function test_it_skips_unpaid_children(): void
    {
        $parent = ParentModel::factory()->create(); // no payment
        $child = Child::factory()->create([
            'parent_id' => $parent->id,
            'student_number' => '2000',
            'day_school_year' => 'Grade 3',
            'allocated_dhamma_class' => null,
        ]);

        $this->artisan('integration:allocate-missing')->assertExitCode(0);

        $child->refresh();
        $this->assertNull($child->allocated_dhamma_class);
    }

    public function test_it_warns_about_an_unmapped_year(): void
    {
        $this->paidChild(['day_school_year' => 'Grade 99']); // not in the rule

        $this->artisan('integration:allocate-missing')
            ->expectsOutputToContain('No rule for')
            ->assertExitCode(0);
    }
}
