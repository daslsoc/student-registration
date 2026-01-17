<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ParentModel;
use App\Models\Child;
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

        // Check that the CSV content includes key data
        $csvContent = $response->getContent();

        var_dump($csvContent);

        // We expect at least the parent's name & child's name to appear
        $this->assertStringContainsString('John', $csvContent);
        $this->assertStringContainsString('Doe', $csvContent);
        $this->assertStringContainsString('Kiddo', $csvContent);
        $this->assertStringContainsString('TestSchool', $csvContent);
    }
}
