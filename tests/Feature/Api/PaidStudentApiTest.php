<?php

namespace Tests\Feature\Api;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaidStudentApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-integration-token';

    private string $endpoint = '/api/integration/paid-students';

    protected function setUp(): void
    {
        parent::setUp();
        config(['integration.api_token' => $this->token]);
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
            'dhamma_class' => 'Class 1 (A)',
            'sinhala_class' => 'Class 2 (C)',
        ], $childAttributes));
    }

    private function unpaidChild(array $childAttributes = []): Child
    {
        $parent = ParentModel::factory()->create();

        return Child::factory()->create(array_merge([
            'parent_id' => $parent->id,
        ], $childAttributes));
    }

    public function test_it_rejects_a_request_with_no_token(): void
    {
        $this->paidChild();

        $this->getJson($this->endpoint)->assertStatus(401);
    }

    public function test_it_rejects_a_request_with_the_wrong_token(): void
    {
        $this->paidChild();

        $this->getJson($this->endpoint, ['Authorization' => 'Bearer not-the-token'])
            ->assertStatus(401);
    }

    public function test_it_fails_closed_when_no_token_is_configured(): void
    {
        config(['integration.api_token' => null]);
        $this->paidChild();

        // Even sending an empty bearer must not get through.
        $this->getJson($this->endpoint, ['Authorization' => 'Bearer '])
            ->assertStatus(401);
    }

    public function test_it_returns_only_paid_children_with_the_mapping_fields(): void
    {
        $paid = $this->paidChild([
            'first_name' => 'Amara',
            'last_name' => 'Perera',
            'student_number' => 4321,
        ]);
        $this->unpaidChild(['first_name' => 'Unpaid', 'student_number' => 9999]);

        $response = $this->getJson($this->endpoint, [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'student_number' => '4321',
            'first_name' => 'Amara',
            'last_name' => 'Perera',
            'dhamma_class' => 'Class 1 (A)',
            'sinhala_class' => 'Class 2 (C)',
        ]);
        $response->assertJsonMissing(['first_name' => 'Unpaid']);
    }

    public function test_it_does_not_leak_parent_or_contact_fields(): void
    {
        $this->paidChild();

        $response = $this->getJson($this->endpoint, [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $row = $response->json('data.0');
        $this->assertSame(
            [
                'student_number', 'first_name', 'last_name',
                'date_of_birth', 'day_school_name', 'day_school_year',
                'dhamma_class', 'sinhala_class',
            ],
            array_keys($row),
        );
    }
}
