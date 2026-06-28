<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use App\Models\PaymentOverride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentOverrideTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    private function pendingFamily(string $year = 'Grade 3'): ParentModel
    {
        $parent = ParentModel::factory()->create(['registration_status' => ParentModel::STATUS_PENDING]);
        Child::factory()->create(['parent_id' => $parent->id, 'day_school_year' => $year]);

        return $parent;
    }

    public function test_guest_cannot_reach_the_override_page(): void
    {
        $this->get('/admin/payment-override')->assertRedirect(route('login'));
    }

    public function test_the_page_renders_for_an_admin(): void
    {
        $this->admin();

        $this->get('/admin/payment-override')
            ->assertStatus(200)
            ->assertSee('Payment Status Override');
    }

    public function test_marking_paid_by_cash_records_payment_completes_and_allocates_and_audits(): void
    {
        $admin = $this->admin();
        $parent = $this->pendingFamily('Grade 3');   // → Class C

        $this->post('/admin/payment-override', [
            'parent_id' => $parent->id,
            'action' => PaymentOverride::ACTION_MARKED_PAID,
            'method' => PaymentOverride::METHOD_CASH,
            'amount' => 50,
            'note' => 'paid at desk',
        ])->assertRedirect(route('admin.payment_override'))->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'parent_id' => $parent->id, 'method' => 'cash', 'amount_paid' => 50,
        ]);
        $this->assertSame(ParentModel::STATUS_COMPLETED, $parent->fresh()->registration_status);
        $this->assertSame('Class C', $parent->children()->first()->allocated_dhamma_class);

        $this->assertDatabaseHas('payment_overrides', [
            'parent_id' => $parent->id,
            'user_id' => $admin->id,
            'action' => PaymentOverride::ACTION_MARKED_PAID,
            'method' => 'cash',
            'previous_status' => 'pending',
            'new_status' => 'completed',
            'note' => 'paid at desk',
        ]);
    }

    public function test_waiving_completes_at_zero_and_records_the_method(): void
    {
        $this->admin();
        $parent = $this->pendingFamily();

        $this->post('/admin/payment-override', [
            'parent_id' => $parent->id,
            'action' => PaymentOverride::ACTION_MARKED_PAID,
            'method' => PaymentOverride::METHOD_WAIVED,
            'note' => 'recently migrated',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['parent_id' => $parent->id, 'method' => 'waived', 'amount_paid' => 0]);
        $this->assertSame(ParentModel::STATUS_COMPLETED, $parent->fresh()->registration_status);
        $this->assertDatabaseHas('payment_overrides', ['parent_id' => $parent->id, 'method' => 'waived', 'amount' => 0]);
    }

    public function test_reverting_voids_payments_and_returns_to_pending(): void
    {
        $this->admin();
        $parent = ParentModel::factory()->create(['registration_status' => ParentModel::STATUS_COMPLETED]);
        $child = Child::factory()->create([
            'parent_id' => $parent->id,
            'allocated_dhamma_class' => 'Class C',
            'allocated_sinhala_class' => 'Class C',
        ]);
        Payment::create(['parent_id' => $parent->id, 'amount_paid' => 50, 'paid_date' => now(), 'method' => 'cash']);

        $this->post('/admin/payment-override', [
            'parent_id' => $parent->id,
            'action' => PaymentOverride::ACTION_REVERTED,
            'note' => 'charged in error',
        ])->assertSessionHas('status');

        $this->assertSame(0, Payment::where('parent_id', $parent->id)->count());
        $this->assertSame(ParentModel::STATUS_PENDING, $parent->fresh()->registration_status);

        // Allocations cleared so the child drops off the attendance sync.
        $child->refresh();
        $this->assertNull($child->allocated_dhamma_class);
        $this->assertNull($child->allocated_sinhala_class);
        $this->assertDatabaseHas('payment_overrides', [
            'parent_id' => $parent->id,
            'action' => PaymentOverride::ACTION_REVERTED,
            'previous_status' => 'completed',
            'new_status' => 'pending',
        ]);
    }

    public function test_marking_paid_requires_a_method(): void
    {
        $this->admin();
        $parent = $this->pendingFamily();

        $this->post('/admin/payment-override', [
            'parent_id' => $parent->id,
            'action' => PaymentOverride::ACTION_MARKED_PAID,
        ])->assertSessionHasErrors('method');

        $this->assertDatabaseMissing('payments', ['parent_id' => $parent->id]);
        $this->assertSame(ParentModel::STATUS_PENDING, $parent->fresh()->registration_status);
    }

    public function test_an_invalid_method_is_rejected(): void
    {
        $this->admin();
        $parent = $this->pendingFamily();

        $this->post('/admin/payment-override', [
            'parent_id' => $parent->id,
            'action' => PaymentOverride::ACTION_MARKED_PAID,
            'method' => 'bitcoin',
        ])->assertSessionHasErrors('method');

        $this->assertDatabaseMissing('payments', ['parent_id' => $parent->id]);
    }
}
