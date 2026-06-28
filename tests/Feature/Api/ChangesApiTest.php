<?php

namespace Tests\Feature\Api;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangesApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-integration-token';

    private string $endpoint = '/api/integration/changes';

    protected function setUp(): void
    {
        parent::setUp();
        config(['integration.api_token' => $this->token]);
    }

    private function auth(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    private function paidChild(array $childAttributes = []): Child
    {
        $parent = ParentModel::factory()->create();
        Payment::create([
            'parent_id' => $parent->id,
            'amount_paid' => 50,
            'paid_date' => now(),
        ]);

        return Child::factory()->create(array_merge([
            'parent_id' => $parent->id,
            'allocated_dhamma_class' => 'Class C',
            'allocated_sinhala_class' => 'Class C',
        ], $childAttributes));
    }

    private function unpaidChild(array $childAttributes = []): Child
    {
        $parent = ParentModel::factory()->create();

        return Child::factory()->create(array_merge(['parent_id' => $parent->id], $childAttributes));
    }

    public function test_it_rejects_without_a_token(): void
    {
        $this->paidChild();

        $this->getJson($this->endpoint)->assertStatus(401);
    }

    public function test_it_rejects_a_wrong_token(): void
    {
        $this->paidChild();

        $this->getJson($this->endpoint, ['Authorization' => 'Bearer nope'])->assertStatus(401);
    }

    public function test_it_returns_paid_children_with_allocations_and_metadata(): void
    {
        $this->paidChild([
            'first_name' => 'Amara',
            'last_name' => 'Perera',
            'student_number' => 4321,
            'allocated_dhamma_class' => 'Class C',
            'allocated_sinhala_class' => 'Class D',
        ]);
        $this->unpaidChild(['first_name' => 'Unpaid', 'student_number' => 9999]);

        $response = $this->getJson($this->endpoint, $this->auth());

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'students');
        $response->assertJsonPath('count', 1);
        $response->assertJsonFragment([
            'student_number' => '4321',
            'first_name' => 'Amara',
            'last_name' => 'Perera',
            'allocated_dhamma_class' => 'Class C',
            'allocated_sinhala_class' => 'Class D',
        ]);
        $response->assertJsonMissing(['first_name' => 'Unpaid']);
        $this->assertNotNull($response->json('last_changed_at'));
    }

    public function test_it_does_not_leak_parent_or_contact_fields(): void
    {
        $this->paidChild();

        $row = $this->getJson($this->endpoint, $this->auth())->json('students.0');

        $this->assertSame(
            ['student_number', 'first_name', 'last_name', 'allocated_dhamma_class', 'allocated_sinhala_class'],
            array_keys($row),
        );
    }

    public function test_since_returns_only_children_changed_after_the_timestamp(): void
    {
        // An old paid child, then a cutoff, then a newer one.
        $old = $this->paidChild(['student_number' => 1, 'first_name' => 'Old']);
        // Query-builder update sets updated_at exactly (no auto-timestamp).
        Child::where('id', $old->id)->update(['updated_at' => now()->subDays(2)]);

        $cutoff = now()->subDay()->toDateTimeString();

        $this->paidChild(['student_number' => 2, 'first_name' => 'New']);

        $response = $this->getJson($this->endpoint.'?since='.urlencode($cutoff), $this->auth());

        $response->assertStatus(200);
        // count stays the total (2); students is just the delta (1).
        $response->assertJsonPath('count', 2);
        $response->assertJsonCount(1, 'students');
        $response->assertJsonFragment(['first_name' => 'New']);
        $response->assertJsonMissing(['first_name' => 'Old']);
    }
}
