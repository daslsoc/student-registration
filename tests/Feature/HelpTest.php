<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_parent_help_page_is_public(): void
    {
        $response = $this->get(route('help'));

        $response->assertStatus(200);
        $response->assertSee('Help &amp; Guide', false);
        $response->assertSee('Register a new family');
        // The screenshots the guide leans on are wired up.
        $response->assertSee('/images/help/parent/register.png', false);
    }

    public function test_admin_help_is_hidden_from_guests(): void
    {
        $this->get(route('admin.help'))->assertRedirect(route('login'));
    }

    public function test_admin_help_renders_for_a_signed_in_user(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('admin.help'));

        $response->assertStatus(200);
        $response->assertSee('Admin Help &amp; Guide', false);
        $response->assertSee('Payment Override');
        $response->assertSee('/images/help/admin/payment-override.png', false);
    }
}
