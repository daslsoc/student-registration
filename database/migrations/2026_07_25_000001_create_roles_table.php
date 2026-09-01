<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles: a named bundle of permission atoms. Each user points at exactly one
 * row here (users.role_id, added in the next migration).
 *
 * `permission_list` is a comma-BOUNDED CSV (",a,b,c,"). The leading and
 * trailing commas are what let User::hasPermission do a substring check for
 * ",{$atom}," without "view_ref" matching "view_reference".
 *
 * Three roles are seeded so the app is usable the moment this runs; the atoms
 * on each are editable afterwards in Admin -> Roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            // Comma-bounded CSV of atoms from config/permissions.php.
            $table->text('permission_list');
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert([
            [
                'name' => 'Administrator',
                'description' => 'Full access, including user and role management.',
                'permission_list' => $this->csv([
                    'view_registrations', 'export_registrations', 'import_registrations',
                    'view_allergies', 'view_unallocated', 'manage_allocations',
                    'manage_payment_overrides', 'manage_users', 'manage_roles',
                    'view_audit_log',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Registrar',
                'description' => 'Day-to-day registration work. Cannot manage users or roles.',
                'permission_list' => $this->csv([
                    'view_registrations', 'export_registrations', 'import_registrations',
                    'view_allergies', 'view_unallocated', 'manage_allocations',
                    'manage_payment_overrides',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Read-only',
                'description' => 'Can look at registrations and export them, but change nothing.',
                'permission_list' => $this->csv([
                    'view_registrations', 'export_registrations',
                    'view_allergies', 'view_unallocated',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }

    /**
     * @param  list<string>  $atoms
     */
    private function csv(array $atoms): string
    {
        return ','.implode(',', $atoms).',';
    }
};
