<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The auth-gated admin GET pages. loginAs() drives the same session the app's
 * auth middleware checks, so these screens render instead of redirecting to login.
 */
class AuthenticatedAdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_pages_render_for_an_authenticated_user(): void
    {
        $admin = User::factory()->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/parents-students')
                ->assertSee('Child List')
                ->visit('/admin/payment-override')
                ->assertSee('Payment Status Override')
                ->visit('/admin/import-csv')
                ->assertSee('Import CSV')
                // CSV export is a file download, not an HTML page — visiting it
                // exercises the route (auth + controller); its contents are
                // asserted in tests/Feature/AdminExportCsvTest.php.
                ->visit('/admin/export-csv');
        });
    }
}
