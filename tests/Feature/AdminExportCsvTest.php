<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ParentModel;
use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Class AdminExportCsvTest
 *
 * Tests the /admin/export-csv endpoint which returns a CSV of parents & children.
 */
class AdminExportCsvTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test exporting CSV returns a proper response with CSV content.
     *
     * @return void
     */
    public function test_export_csv_endpoint_returns_csv()
    {
        // 1) Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1) Arrange: create parent + children
        $parent = ParentModel::factory()->create([
            'parent1_first_name' => 'John',
            'parent1_last_name' => 'Doe',
            'parent1_email' => 'john@example.com',
            'emergency_contact_name' => 'Jane',
            'emergency_contact_phone' => '123-456',
            'relationship_to_family' => 'Aunt',
        ]);

        // Create a child for the parent
        Child::factory()->create([
            'parent_id' => $parent->id,
            'first_name' => 'Kiddo',
            'last_name' => 'Doe',
            'day_school_name' => 'TestSchool',
            'dhamma_class' => 'Grade 1',
            'sinhala_class' => 'Level A',
        ]);

        // 2) Act: call the export-csv route
        $response = $this->get('/admin/export-csv');

        // 3) Assert: check headers and content
        // Expect a 200 status code
        $response->assertStatus(200);

        // Optionally check content-disposition (depending on how your controller sets it)
        // If using a streamed download with a known filename:
        $response->assertHeader('content-disposition', 'attachment; filename=parents_children.csv');

        // capture streamed response body
        ob_start();
        $response->baseResponse->send();
        $csvContent = ob_get_clean();

        // We expect at least the parent's name & child's name to appear
        $this->assertStringContainsString('John', $csvContent);
        $this->assertStringContainsString('Doe', $csvContent);
        $this->assertStringContainsString('Kiddo', $csvContent);
        $this->assertStringContainsString('TestSchool', $csvContent);
    }

    /**
     * Test exporting CSV with no parents or children returns empty CSV.
     *
     * @return void
     */
    public function test_export_csv_with_no_data()
    {
        // Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/export-csv');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=parents_children.csv');

        // capture streamed response body
        ob_start();
        $response->baseResponse->send();
        $csvContent = ob_get_clean();

        // Assuming CSV has headers even if empty
        $this->assertStringContainsString('Parent1FirstName', $csvContent);
        
        // No data rows
        $this->assertStringNotContainsString('John', $csvContent);
    }

    /**
     * Test exporting CSV with multiple parents and children.
     *
     * @return void
     */
    public function test_export_csv_with_multiple_entries()
    {
        // Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create multiple parents and children
        $parent1 = ParentModel::factory()->create([
            'parent1_first_name' => 'Alice',
            'parent1_last_name' => 'Smith',
        ]);
        Child::factory()->create(['parent_id' => $parent1->id, 'first_name' => 'Child1']);

        $parent2 = ParentModel::factory()->create([
            'parent1_first_name' => 'Bob',
            'parent1_last_name' => 'Johnson',
        ]);
        Child::factory()->create(['parent_id' => $parent2->id, 'first_name' => 'Child2']);
        Child::factory()->create(['parent_id' => $parent2->id, 'first_name' => 'Child3']);

        $response = $this->get('/admin/export-csv');

        $response->assertStatus(200);
        
        // capture streamed response body
        ob_start();
        $response->baseResponse->send();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('Alice', $csvContent);
        $this->assertStringContainsString('Smith', $csvContent);
        $this->assertStringContainsString('Bob', $csvContent);
        $this->assertStringContainsString('Johnson', $csvContent);
        $this->assertStringContainsString('Child1', $csvContent);
        $this->assertStringContainsString('Child2', $csvContent);
        $this->assertStringContainsString('Child3', $csvContent);
    }

    /**
     * Test exporting CSV without authentication redirects or fails.
     *
     * @return void
     */
    public function test_export_csv_unauthenticated()
    {
        $response = $this->get('/admin/export-csv');

        // Assuming middleware redirects to login or returns 401/403
        $response->assertRedirect('/login'); // Adjust based on your auth setup
    }

    /**
     * Test CSV headers are correct.
     *
     * @return void
     */
    public function test_export_csv_headers()
    {
        // Create and authenticate a user (bypasses the login form, satisfies auth middleware)
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/export-csv');

        $response->assertStatus(200);
        
        // capture streamed response body
        ob_start();
        $response->baseResponse->send();
        $csvContent = ob_get_clean();

        // Check for expected headers
        $this->assertStringContainsString('Parent1FirstName', $csvContent);
        $this->assertStringContainsString('Parent1LastName', $csvContent);
        $this->assertStringContainsString('ChildFirstName', $csvContent);
        $this->assertStringContainsString('SchoolName', $csvContent);
    }
}