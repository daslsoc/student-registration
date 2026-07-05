<?php

namespace Tests\Browser;

use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use App\Models\PaymentOverride;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Captures the screenshots used by the user guides in docs/guides. This is a
 * documentation tool, NOT a regression test.
 *
 * It is guarded by an env flag so it never runs (and never overwrites the
 * committed screenshots) during a normal `make test-dusk` / CI run. To refresh
 * the images, bring up the Dusk stack and run it explicitly:
 *
 *   make dusk-up
 *   DOCS_SCREENSHOTS=1 make dusk ARGS="tests/Browser/DocsScreenshotsCapture.php"
 *   make dusk-down
 *
 * Each shot is written straight into docs/guides/images/<audience>/<name>.png,
 * exactly where the markdown references it — no copy step. Filenames match the
 * ![alt](images/...) links in parent-guide.md / admin-guide.md; keep them in
 * sync if you rename a shot.
 */
class DocsScreenshotsCapture extends DuskTestCase
{
    use DatabaseMigrations;

    /** A known, unexpired update-link token so the update page renders. */
    private const UPDATE_TOKEN = 'docs-preview-update-token';

    protected function setUp(): void
    {
        parent::setUp();

        if (! env('DOCS_SCREENSHOTS')) {
            $this->markTestSkipped(
                'Documentation capture. Run with DOCS_SCREENSHOTS=1 to (re)generate '.
                'the guide screenshots — see the class docblock.'
            );
        }
    }

    public function test_capture_parent_pages(): void
    {
        $this->seedDemoFamilies();

        $this->browse(function (Browser $browser) {
            $browser->resize(1366, 900);

            $browser->visit('/')
                ->waitForText('Online Registration')
                ->pause(400);
            $this->capture($browser, 'parent/landing.png');

            $browser->visit('/guidelines')
                ->pause(500);
            $this->capture($browser, 'parent/guidelines.png');

            $browser->visit('/registration')
                ->waitForText('School Registration')
                ->pause(500);
            $this->capture($browser, 'parent/register.png');

            // A focused shot of a child block — shows the per-child fields and
            // the Allergies / Special needs boxes pre-filled with "None".
            $this->captureElement($browser, '.child-block', 'parent/register-child.png');

            $browser->visit('/registration/retrieve')
                ->waitForText('Retrieve')
                ->pause(300);
            $this->capture($browser, 'parent/retrieve.png');

            // The pre-filled form (top of page)…
            $browser->visit('/registration/update/'.self::UPDATE_TOKEN)
                ->waitFor('.allocated-info')
                ->pause(400);
            $this->capture($browser, 'parent/update.png');

            // …and a focused shot of the read-only allocated-class notice, the
            // headline feature of this page (it lives further down the child
            // block, so grab just that element).
            $this->captureElement($browser, '.allocated-info', 'parent/update-allocated-class.png');
        });
    }

    public function test_capture_admin_pages(): void
    {
        $admin = $this->seedDemoFamilies();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->resize(1366, 900);

            // The login screen (as a signed-out visitor sees it).
            $browser->visit('/login')
                ->waitForText('administrators only')
                ->pause(300);
            $this->capture($browser, 'admin/login.png');

            // Everything else needs an authenticated session.
            $browser->loginAs($admin);

            $browser->visit('/admin/parents-students')
                ->waitFor('#parent-child-table')
                ->pause(800);
            $this->capture($browser, 'admin/parents-students.png');

            $browser->visit('/admin/allergies')
                ->waitFor('#allergies-table')
                ->pause(800);
            $this->capture($browser, 'admin/allergies.png');

            $browser->visit('/admin/unallocated')
                ->waitForText('Buddhism')
                ->pause(500);
            $this->capture($browser, 'admin/unallocated.png');

            $browser->visit('/admin/class-relocation?q=Perera')
                ->waitForText('Buddhism')
                ->pause(500);
            $this->capture($browser, 'admin/class-relocation.png');

            $browser->visit('/admin/payment-override')
                ->waitFor('#families-table')
                ->pause(800);
            $this->capture($browser, 'admin/payment-override.png');

            $browser->visit('/admin/import-csv')
                ->waitForText('Import CSV')
                ->pause(300);
            $this->capture($browser, 'admin/import-csv.png');
        });
    }

    /**
     * Write the current viewport to docs/guides/images/<relativePath>.
     */
    private function capture(Browser $browser, string $relativePath): void
    {
        $absolute = base_path('docs/guides/images/'.$relativePath);
        File::ensureDirectoryExists(dirname($absolute));

        file_put_contents($absolute, $browser->driver->takeScreenshot());
    }

    /**
     * Write a tight, cropped screenshot of a single element (used for the small
     * "notice" figures where a full-page shot would bury the point).
     */
    private function captureElement(Browser $browser, string $selector, string $relativePath): void
    {
        $absolute = base_path('docs/guides/images/'.$relativePath);
        File::ensureDirectoryExists(dirname($absolute));

        $browser->scrollIntoView($selector)->pause(300);
        $browser->element($selector)->takeElementScreenshot($absolute);
    }

    /**
     * Create a small, realistic, deterministic roster so the tables and forms
     * have something to show. Returns the admin user for loginAs().
     */
    private function seedDemoFamilies(): User
    {
        $admin = User::factory()->create([
            'name' => 'Site Admin',
            'email' => 'admin@example.test',
        ]);

        // Completed + allocated. Carries the update-link token so the parent
        // "update registration" page renders with the read-only class notice.
        $perera = $this->family(
            [
                'parent1_first_name' => 'Nimal', 'parent1_last_name' => 'Perera',
                'parent1_email' => 'nimal.perera@example.test', 'parent1_phone' => '0412345678',
                'parent2_first_name' => 'Anoma', 'parent2_last_name' => 'Perera',
                'parent2_email' => 'anoma.perera@example.test', 'parent2_phone' => '0412345679',
                'emergency_contact_name' => 'Sunethra Perera', 'emergency_contact_phone' => '0498765432',
                'relationship_to_family' => 'Grandparent',
                'registration_status' => ParentModel::STATUS_COMPLETED,
                'update_token' => self::UPDATE_TOKEN,
                'token_expires_at' => now()->addHours(4),
            ],
            [[
                'first_name' => 'Amara', 'last_name' => 'Perera', 'gender' => 'Female',
                'day_school_year' => 'Grade 3', 'day_school_name' => 'Canberra Primary',
                'allergies' => 'Peanuts', 'special_needs' => 'None',
                'allocated_dhamma_class' => 'Class C', 'allocated_sinhala_class' => 'Class C',
                'student_number' => 4321,
            ]],
            paid: true,
        );

        // Completed + allocated, with a genuine special need (for the Allergies
        // & Medical page).
        $this->family(
            [
                'parent1_first_name' => 'Ruwan', 'parent1_last_name' => 'Silva',
                'parent1_email' => 'ruwan.silva@example.test', 'parent1_phone' => '0421110022',
                'emergency_contact_name' => 'Kamala Silva', 'emergency_contact_phone' => '0421110099',
                'relationship_to_family' => 'Aunt',
                'registration_status' => ParentModel::STATUS_COMPLETED,
            ],
            [[
                'first_name' => 'Kavindi', 'last_name' => 'Silva', 'gender' => 'Female',
                'day_school_year' => 'Grade 1', 'day_school_name' => 'Lyneham Primary',
                'allergies' => 'None', 'special_needs' => 'Requires wheelchair access',
                'allocated_dhamma_class' => 'Class A', 'allocated_sinhala_class' => 'Class A',
                'student_number' => 5102,
            ]],
            paid: true,
        );

        // Completed + allocated, nothing medical.
        $this->family(
            [
                'parent1_first_name' => 'Sunil', 'parent1_last_name' => 'Fernando',
                'parent1_email' => 'sunil.fernando@example.test', 'parent1_phone' => '0430220011',
                'emergency_contact_name' => 'Nayana Fernando', 'emergency_contact_phone' => '0430220044',
                'relationship_to_family' => 'Uncle',
                'registration_status' => ParentModel::STATUS_COMPLETED,
            ],
            [[
                'first_name' => 'Sahan', 'last_name' => 'Fernando', 'gender' => 'Male',
                'day_school_year' => 'Grade 7', 'day_school_name' => 'Dickson College',
                'allergies' => 'None', 'special_needs' => 'None',
                'allocated_dhamma_class' => 'Class E', 'allocated_sinhala_class' => 'Class E',
                'student_number' => 6210,
            ]],
            paid: true,
        );

        // PAID but NOT yet allocated — this is the one the Unallocated Students
        // worklist exists to surface.
        $this->family(
            [
                'parent1_first_name' => 'Dilani', 'parent1_last_name' => 'Jayasuriya',
                'parent1_email' => 'dilani.j@example.test', 'parent1_phone' => '0455330011',
                'emergency_contact_name' => 'Priya Jayasuriya', 'emergency_contact_phone' => '0455330077',
                'relationship_to_family' => 'Family Friend',
                'registration_status' => ParentModel::STATUS_COMPLETED,
            ],
            [[
                'first_name' => 'Dinuka', 'last_name' => 'Jayasuriya', 'gender' => 'Male',
                'day_school_year' => 'Grade 2', 'day_school_name' => 'Majura Primary',
                'allergies' => 'None', 'special_needs' => 'None',
                'allocated_dhamma_class' => null, 'allocated_sinhala_class' => null,
                'student_number' => 7001,
            ]],
            paid: true,
        );

        // Pending (never paid) — shows the "Pending" status badge/filter and is
        // a candidate for the Payment Override screen.
        $this->family(
            [
                'parent1_first_name' => 'Manel', 'parent1_last_name' => 'Wickrama',
                'parent1_email' => 'manel.wickrama@example.test', 'parent1_phone' => '0466778899',
                'emergency_contact_name' => 'Ajith Wickrama', 'emergency_contact_phone' => '0466778800',
                'relationship_to_family' => 'Aunt',
                'registration_status' => ParentModel::STATUS_PENDING,
            ],
            [[
                'first_name' => 'Tharindu', 'last_name' => 'Wickrama', 'gender' => 'Male',
                'day_school_year' => 'Grade 5', 'day_school_name' => 'Turner School',
                'allergies' => 'None', 'special_needs' => 'None',
                'allocated_dhamma_class' => null, 'allocated_sinhala_class' => null,
                'student_number' => 8003,
            ]],
            paid: false,
        );

        // One audit row so the Payment Override log isn't empty.
        PaymentOverride::create([
            'parent_id' => $perera->id,
            'user_id' => $admin->id,
            'performed_by' => $admin->name,
            'action' => PaymentOverride::ACTION_MARKED_PAID,
            'method' => PaymentOverride::METHOD_CASH,
            'amount' => 150,
            'previous_status' => ParentModel::STATUS_PENDING,
            'new_status' => ParentModel::STATUS_COMPLETED,
            'note' => 'Paid in cash at orientation.',
        ]);

        return $admin;
    }

    /**
     * Create a parent with children (and, when paid, a recorded payment).
     */
    private function family(array $parentAttributes, array $children, bool $paid): ParentModel
    {
        $parent = ParentModel::factory()->create(array_merge([
            'guidelines_accepted' => true,
        ], $parentAttributes));

        foreach ($children as $child) {
            Child::factory()->for($parent, 'parent')->create($child);
        }

        if ($paid) {
            Payment::create([
                'parent_id' => $parent->id,
                'amount_paid' => count($children) > 1 ? 150 : 100,
                'paid_date' => now(),
                'method' => 'online',
            ]);
        }

        return $parent;
    }
}
