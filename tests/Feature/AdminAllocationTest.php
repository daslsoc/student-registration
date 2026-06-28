<?php

namespace Tests\Feature;

use App\Mail\ClassAllocationChanged;
use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminAllocationTest extends TestCase
{
    use RefreshDatabase;

    private function paidChild(array $attributes = []): Child
    {
        $parent = ParentModel::factory()->create();
        Payment::create(['parent_id' => $parent->id, 'amount_paid' => 50, 'paid_date' => now()]);

        return Child::factory()->create(array_merge(['parent_id' => $parent->id], $attributes));
    }

    public function test_allocations_page_requires_login(): void
    {
        $this->paidChild();

        $this->get(route('admin.unallocated'))->assertRedirect(route('login'));
    }

    public function test_admin_sees_paid_children(): void
    {
        $this->actingAs(User::factory()->create());
        $this->paidChild(['first_name' => 'Amara', 'last_name' => 'Perera', 'student_number' => 4321]);

        $this->get(route('admin.unallocated'))
            ->assertStatus(200)
            ->assertSee('Amara');
    }

    public function test_admin_can_override_both_subject_allocations(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->paidChild([
            'student_number' => 4321,
            'allocated_dhamma_class' => 'Class A',
            'allocated_sinhala_class' => 'Class A',
        ]);

        $this->post(route('admin.allocations.update'), [
            'allocations' => ['4321' => ['dhamma' => 'Class C', 'sinhala' => 'Class D']],
        ])->assertRedirect(route('admin.unallocated'));

        $child->refresh();
        $this->assertSame('Class C', $child->allocated_dhamma_class);
        $this->assertSame('Class D', $child->allocated_sinhala_class);
    }

    public function test_an_invalid_class_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->paidChild(['student_number' => 4321, 'allocated_dhamma_class' => 'Class A']);

        $this->post(route('admin.allocations.update'), [
            'allocations' => ['4321' => ['dhamma' => 'Class Z']],
        ])->assertSessionHasErrors();

        $child->refresh();
        $this->assertSame('Class A', $child->allocated_dhamma_class);
    }

    public function test_changing_a_class_emails_both_parents(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());

        $parent = ParentModel::factory()->create([
            'parent1_email' => 'p1@example.com',
            'parent2_email' => 'p2@example.com',
        ]);
        Payment::create(['parent_id' => $parent->id, 'amount_paid' => 50, 'paid_date' => now()]);
        Child::factory()->create([
            'parent_id' => $parent->id,
            'student_number' => 4321,
            'allocated_dhamma_class' => 'Class A',
            'allocated_sinhala_class' => 'Class A',
        ]);

        // Only Dhamma changes; Sinhala stays the same.
        $this->post(route('admin.allocations.update'), [
            'allocations' => ['4321' => ['dhamma' => 'Class C', 'sinhala' => 'Class A']],
        ])->assertRedirect(route('admin.unallocated'));

        // ClassAllocationChanged implements ShouldQueue, so the fake records it
        // as queued rather than sent — one mail per parent address.
        Mail::assertQueued(ClassAllocationChanged::class, 2);
        Mail::assertQueued(ClassAllocationChanged::class, fn ($mail) => $mail->hasTo('p1@example.com'));
        Mail::assertQueued(ClassAllocationChanged::class, fn ($mail) => $mail->hasTo('p2@example.com'));
    }

    public function test_no_email_when_allocation_is_unchanged(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());
        $this->paidChild([
            'student_number' => 4321,
            'allocated_dhamma_class' => 'Class A',
            'allocated_sinhala_class' => 'Class B',
        ]);

        // Resubmit the same values — nothing changed.
        $this->post(route('admin.allocations.update'), [
            'allocations' => ['4321' => ['dhamma' => 'Class A', 'sinhala' => 'Class B']],
        ])->assertRedirect(route('admin.unallocated'));

        Mail::assertNotQueued(ClassAllocationChanged::class);
    }

    /**
     * The worklist is for PAID students only. An unpaid/imported student with a
     * missing allocation must NOT appear here — they're handled via the Class
     * Relocation search instead.
     */
    public function test_unallocated_page_excludes_students_without_a_payment(): void
    {
        $this->actingAs(User::factory()->create());
        $parent = ParentModel::factory()->create();
        Child::factory()->create([
            'parent_id' => $parent->id,
            'first_name' => 'Imported',
            'last_name' => 'Student',
            'student_number' => 7001,
            'allocated_dhamma_class' => null,
            'allocated_sinhala_class' => null,
        ]);

        $this->get(route('admin.unallocated'))
            ->assertStatus(200)
            ->assertDontSee('Imported');
    }

    public function test_unallocated_page_shows_paid_students_missing_a_class(): void
    {
        $this->actingAs(User::factory()->create());
        $this->paidChild([
            'first_name' => 'Paidbut',
            'last_name' => 'Unclassed',
            'student_number' => 7005,
            'allocated_dhamma_class' => null,
            'allocated_sinhala_class' => null,
        ]);

        $this->get(route('admin.unallocated'))
            ->assertStatus(200)
            ->assertSee('Paidbut');
    }

    public function test_unallocated_page_hides_fully_allocated_students(): void
    {
        $this->actingAs(User::factory()->create());
        $this->paidChild([
            'first_name' => 'AllSet',
            'student_number' => 7002,
            'allocated_dhamma_class' => 'Class A',
            'allocated_sinhala_class' => 'Class A',
        ]);

        $this->get(route('admin.unallocated'))
            ->assertStatus(200)
            ->assertDontSee('AllSet');
    }

    public function test_search_page_loads_empty_without_a_query(): void
    {
        $this->actingAs(User::factory()->create());
        $this->paidChild(['first_name' => 'Hidden', 'student_number' => 7003]);

        $this->get(route('admin.class_relocation'))
            ->assertStatus(200)
            ->assertSee('Search')
            ->assertDontSee('Hidden');
    }

    /**
     * The core fix: an already-allocated existing student (no payment) can be
     * found via search and moved to a different class, emailing the parents.
     */
    public function test_search_finds_and_moves_an_existing_student(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());

        $parent = ParentModel::factory()->create(['parent1_email' => 'mum@example.com']);
        $child = Child::factory()->create([
            'parent_id' => $parent->id,
            'first_name' => 'Nimal',
            'last_name' => 'Silva',
            'student_number' => 7004,
            'allocated_dhamma_class' => 'Class A',
            'allocated_sinhala_class' => 'Class A',
        ]);

        // The student is findable by name even with no payment on file.
        $this->get(route('admin.class_relocation', ['q' => 'Nimal']))
            ->assertStatus(200)
            ->assertSee('Nimal');

        // Moving them returns to the search results and emails the parent.
        $returnTo = '/admin/class-relocation?q=Nimal';
        $this->post(route('admin.allocations.update'), [
            'redirect_to' => $returnTo,
            'allocations' => ['7004' => ['dhamma' => 'Class D', 'sinhala' => 'Class A']],
        ])->assertRedirect($returnTo);

        $child->refresh();
        $this->assertSame('Class D', $child->allocated_dhamma_class);
        Mail::assertQueued(ClassAllocationChanged::class, fn ($mail) => $mail->hasTo('mum@example.com'));
    }
}
