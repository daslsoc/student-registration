<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Models\ParentModel;
use App\Models\Child;

/**
 * Class CSVImportFeatureTest
 *
 * Covers CSV import form and file processing.
 */
class CSVImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test CSV import form loads.
     *
     * @return void
     */
    public function test_csv_import_form_loads()
    {
        $response = $this->get('/admin/import-csv');
        $response->assertStatus(200);
        $response->assertSee('CSV Import');
    }

    /**
     * Test successfully importing a CSV file with one parent and one child.
     *
     * @return void
     */
    public function test_can_import_csv_single_child()
    {
        $csvData = "Parent1FirstName,Parent1LastName,Parent1Email,Parent1Phone,Parent2FirstName,Parent2LastName,Parent2Email,Parent2Phone,EmergencyContactName,EmergencyContactPhone,RelationshipToFamily,Child1FirstName,Child1LastName,Child1Gender,Child1DateOfBirth,Child1ResidencyStatus,Child1DaySchoolName,Child1DaySchoolYear,Child1Allergies,Child1SpecialNeeds,Child1DhammaClass,Child1SinhalaClass\n"
                 . "John,Doe,john@example.com,123456,,,,'',Jane,789,Aunt,Kiddo,Doe,Male,2010-01-01,Citizen,TestSchool,5,,,G1,LevelA\n";

        $file = UploadedFile::fake()->createWithContent('parents.csv', $csvData);

        $response = $this->post('/admin/import-csv', [
            'csv_file' => $file,
            'default_registration_year' => 2023,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status', 'CSV data has been successfully imported!');

        $this->assertDatabaseHas('parents', [
            'parent1_email' => 'john@example.com',
        ]);
        $this->assertDatabaseHas('children', [
            'first_name' => 'Kiddo',
            'last_name' => 'Doe',
        ]);
    }

    /**
     * Test importing CSV with multiple children columns (Child2, Child3, etc.).
     *
     * @return void
     */
    public function test_can_import_csv_multiple_children()
    {
        $csvData = "Parent1FirstName,Parent1LastName,Parent1Email,Parent1Phone,Parent2FirstName,Parent2LastName,Parent2Email,Parent2Phone,EmergencyContactName,EmergencyContactPhone,RelationshipToFamily,Child1FirstName,Child1LastName,Child1Gender,Child1DateOfBirth,Child1ResidencyStatus,Child1DaySchoolName,Child1DaySchoolYear,Child1Allergies,Child1SpecialNeeds,Child1DhammaClass,Child1SinhalaClass,Child2FirstName,Child2LastName,Child2Gender,Child2DateOfBirth,Child2ResidencyStatus,Child2DaySchoolName,Child2DaySchoolYear,Child2Allergies,Child2SpecialNeeds,Child2DhammaClass,Child2SinhalaClass\n"
                 . "Mary,Smith,mary@example.com,987654,Tom,Smith,tom@example.com,333333,Jane,999,Aunt,KidA,Smith,Female,2012-01-01,Citizen,SchoolA,4,,,G2,LevelB,ChildB,Smith,Male,2015-02-02,Citizen,SchoolA,1,,,G1,LevelA\n";

        $file = UploadedFile::fake()->createWithContent('parents.csv', $csvData);

        $response = $this->post('/admin/import-csv', [
            'csv_file' => $file,
            'default_registration_year' => 2023,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status', 'CSV data has been successfully imported!');

        $this->assertDatabaseHas('parents', ['parent1_email' => 'mary@example.com']);
        $this->assertDatabaseCount('children', 2);

        $this->assertDatabaseHas('children', ['first_name' => 'KidA']);
        $this->assertDatabaseHas('children', ['first_name' => 'ChildB']);
    }
}
