<?php

namespace Tests\Feature\Api;

use App\Models\Child;
use App\Models\ParentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The parent-contact delta endpoint consumed by tea-roster.
 *
 * Mirrors ChangesApiTest in shape; the important extra ground covered here is
 * the field allowlist (this endpoint deliberately exposes contact details, so
 * it must expose ONLY those) and per-consumer token isolation.
 */
class ParentsApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-tea-roster-token';

    private string $attendanceToken = 'test-attendance-token';

    private string $endpoint = '/api/integration/parents';

    protected function setUp(): void
    {
        parent::setUp();

        config(['integration.api_tokens' => [
            'attendance' => $this->attendanceToken,
            'tea-roster' => $this->token,
        ]]);
    }

    private function auth(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    private function registeredHousehold(array $attributes = []): ParentModel
    {
        return ParentModel::factory()->create(array_merge([
            'registration_status' => ParentModel::STATUS_COMPLETED,
        ], $attributes));
    }

    public function test_it_rejects_without_a_token(): void
    {
        $this->getJson($this->endpoint)->assertStatus(401);
    }

    public function test_it_rejects_a_wrong_token(): void
    {
        $this->getJson($this->endpoint, ['Authorization' => 'Bearer nope'])
            ->assertStatus(401);
    }

    public function test_it_rejects_when_no_tokens_are_configured(): void
    {
        // Fail closed: an unconfigured API must deny, never allow.
        config(['integration.api_tokens' => []]);

        $this->getJson($this->endpoint, $this->auth())->assertStatus(401);
    }

    public function test_an_empty_configured_token_never_matches(): void
    {
        // A blank env var must not turn into "any empty bearer works".
        config(['integration.api_tokens' => ['tea-roster' => '']]);

        $this->getJson($this->endpoint, ['Authorization' => 'Bearer '])
            ->assertStatus(401);
    }

    public function test_the_attendance_token_also_works(): void
    {
        // Both consumers authenticate against the same middleware; the tokens
        // are separate so either can be revoked, not so routes are partitioned.
        $this->getJson($this->endpoint, ['Authorization' => 'Bearer '.$this->attendanceToken])
            ->assertOk();
    }

    public function test_it_returns_registered_households_with_both_guardians(): void
    {
        $this->registeredHousehold([
            'parent1_first_name' => 'Anu', 'parent1_last_name' => 'Jayamanne',
            'parent1_email' => 'anu@example.org', 'parent1_phone' => '0412 345 678',
            'parent2_first_name' => 'Dinuk', 'parent2_last_name' => 'Jayamanne',
            'parent2_email' => 'dinuk@example.org', 'parent2_phone' => '0412 999 888',
        ]);

        $response = $this->getJson($this->endpoint, $this->auth())->assertOk();

        $response->assertJsonPath('count', 1);
        $response->assertJsonPath('parents.0.guardians.0.first_name', 'Anu');
        $response->assertJsonPath('parents.0.guardians.0.email', 'anu@example.org');
        $response->assertJsonPath('parents.0.guardians.1.first_name', 'Dinuk');
        $response->assertJsonPath('parents.0.guardians.1.phone', '0412 999 888');
        $this->assertNotNull($response->json('last_changed_at'));
    }

    public function test_a_household_with_one_guardian_returns_one(): void
    {
        $this->registeredHousehold([
            'parent1_first_name' => 'Amanthi', 'parent1_last_name' => 'Haslum',
            'parent2_first_name' => null, 'parent2_last_name' => null,
            'parent2_email' => null, 'parent2_phone' => null,
        ]);

        $this->getJson($this->endpoint, $this->auth())
            ->assertOk()
            ->assertJsonCount(1, 'parents.0.guardians')
            ->assertJsonPath('parents.0.guardians.0.first_name', 'Amanthi');
    }

    public function test_a_guardian_with_no_email_is_returned_with_a_null_email(): void
    {
        // tea-roster can still roster a family it can't email; it must be able
        // to tell "no address on file" from an empty string.
        $this->registeredHousehold([
            'parent2_first_name' => 'Sam', 'parent2_last_name' => 'Perera',
            'parent2_email' => '', 'parent2_phone' => '   ',
        ]);

        $this->getJson($this->endpoint, $this->auth())
            ->assertOk()
            ->assertJsonPath('parents.0.guardians.1.email', null)
            ->assertJsonPath('parents.0.guardians.1.phone', null);
    }

    public function test_unregistered_households_are_listed_in_removed(): void
    {
        $departed = ParentModel::factory()->create([
            'registration_status' => ParentModel::STATUS_PENDING,
        ]);
        $this->registeredHousehold();

        $response = $this->getJson($this->endpoint, $this->auth())->assertOk();

        $response->assertJsonPath('count', 1);
        $this->assertSame([(string) $departed->id], $response->json('removed'));
    }

    public function test_since_returns_only_households_changed_after_the_timestamp(): void
    {
        $old = $this->registeredHousehold();
        $old->forceFill(['updated_at' => now()->subDays(10)])->saveQuietly();

        $recent = $this->registeredHousehold();
        $recent->forceFill(['updated_at' => now()->subDay()])->saveQuietly();

        $response = $this->getJson(
            $this->endpoint.'?since='.now()->subDays(3)->toDateTimeString(),
            $this->auth(),
        )->assertOk();

        $ids = array_column($response->json('parents'), 'registration_parent_id');
        $this->assertSame([(string) $recent->id], $ids);

        // count is the full registered total, not the delta size — it's a
        // headline figure for the consumer's status page.
        $response->assertJsonPath('count', 2);
    }

    public function test_it_exposes_only_the_allowlisted_contact_fields(): void
    {
        // This endpoint exists to share contact details, so the guard that
        // matters is that it shares NOTHING ELSE — no emergency contacts, no
        // addresses, no tokens, no children.
        $household = $this->registeredHousehold([
            'emergency_contact_name' => 'Should Not Appear',
            'emergency_contact_phone' => '0400 000 000',
            'postcode' => '2600',
            'payment_token' => 'secret-payment-token',
            'update_token' => 'secret-update-token',
        ]);
        Child::factory()->create(['parent_id' => $household->id]);

        $response = $this->getJson($this->endpoint, $this->auth())->assertOk();

        $this->assertSame(
            ['registration_parent_id', 'guardians'],
            array_keys($response->json('parents.0')),
        );
        $this->assertSame(
            ['first_name', 'last_name', 'email', 'phone'],
            array_keys($response->json('parents.0.guardians.0')),
        );

        // Belt and braces: none of the sensitive values leak anywhere in the body.
        $body = $response->getContent();
        foreach (['Should Not Appear', '0400 000 000', '2600', 'secret-payment-token', 'secret-update-token'] as $secret) {
            $this->assertStringNotContainsString($secret, $body);
        }
    }
}
