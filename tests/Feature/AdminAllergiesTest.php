<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAllergiesTest extends TestCase
{
    use RefreshDatabase;

    private function childWithAllergy(?string $allergy, array $attributes = []): Child
    {
        $parent = ParentModel::factory()->create();

        return Child::factory()->create(array_merge([
            'parent_id' => $parent->id,
            'allergies' => $allergy,
            // Pin special needs so allergy-focused assertions stay deterministic
            // (the factory otherwise randomises it, which would list the child).
            'special_needs' => 'None',
        ], $attributes));
    }

    public function test_allergies_page_requires_login(): void
    {
        $this->get(route('admin.allergies'))->assertRedirect(route('login'));
    }

    public function test_lists_students_with_a_real_allergy(): void
    {
        $this->actingAs(User::factory()->create());
        $this->childWithAllergy('Peanuts', ['first_name' => 'Sahan', 'student_number' => 8001]);

        $this->get(route('admin.allergies'))
            ->assertStatus(200)
            ->assertSee('Sahan')
            ->assertSee('Peanuts');
    }

    public function test_excludes_none_blank_and_null_allergies(): void
    {
        $this->actingAs(User::factory()->create());
        $this->childWithAllergy('None', ['first_name' => 'NoneKid', 'student_number' => 8002]);
        $this->childWithAllergy('none', ['first_name' => 'LowerNoneKid', 'student_number' => 8003]);
        $this->childWithAllergy('', ['first_name' => 'BlankKid', 'student_number' => 8004]);
        $this->childWithAllergy(null, ['first_name' => 'NullKid', 'student_number' => 8005]);

        $response = $this->get(route('admin.allergies'))->assertStatus(200);
        $response->assertDontSee('NoneKid');
        $response->assertDontSee('LowerNoneKid');
        $response->assertDontSee('BlankKid');
        $response->assertDontSee('NullKid');
        $response->assertSee('No students currently have an allergy or special need recorded.');
    }

    public function test_includes_students_with_a_special_need_but_no_allergy(): void
    {
        $this->actingAs(User::factory()->create());
        $parent = ParentModel::factory()->create();
        Child::factory()->create([
            'parent_id' => $parent->id,
            'first_name' => 'Tharushi',
            'student_number' => 8006,
            'allergies' => 'None',
            'special_needs' => 'Requires wheelchair access',
        ]);

        $this->get(route('admin.allergies'))
            ->assertStatus(200)
            ->assertSee('Tharushi')
            ->assertSee('Requires wheelchair access');
    }
}
