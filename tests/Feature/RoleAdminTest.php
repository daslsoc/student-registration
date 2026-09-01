<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role administration: the checkbox grid, what it may write, and the guard that
 * stops an edit locking every administrator out.
 */
class RoleAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_roles_is_required_to_reach_the_role_editor(): void
    {
        $user = User::factory()->withAtoms(['manage_users'])->create();

        $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.roles.create'))->assertForbidden();
    }

    public function test_the_seeded_roles_carry_the_expected_permissions(): void
    {
        $admin = Role::where('name', 'Administrator')->firstOrFail();
        $registrar = Role::where('name', 'Registrar')->firstOrFail();
        $readOnly = Role::where('name', 'Read-only')->firstOrFail();

        $this->assertContains('manage_users', $admin->atoms());
        $this->assertContains('manage_roles', $admin->atoms());

        // The whole point of the Registrar role: does the work, doesn't hand
        // out permissions.
        $this->assertContains('manage_allocations', $registrar->atoms());
        $this->assertNotContains('manage_users', $registrar->atoms());
        $this->assertNotContains('manage_roles', $registrar->atoms());

        $this->assertContains('view_registrations', $readOnly->atoms());
        $this->assertNotContains('manage_allocations', $readOnly->atoms());
    }

    public function test_a_role_can_be_created_with_a_chosen_set_of_permissions(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Allergy Nurse',
            'description' => 'Medical details only.',
            'atoms' => ['view_allergies', 'view_registrations'],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'Allergy Nurse')->firstOrFail();
        $this->assertEqualsCanonicalizing(['view_allergies', 'view_registrations'], $role->atoms());
        $this->assertDatabaseHas('activity_log_entries', ['action' => 'role.created']);
    }

    public function test_permissions_not_in_the_registry_are_discarded(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Invented',
            'atoms' => ['view_allergies', 'become_superuser'],
        ])->assertSessionHasErrors('atoms.1');

        $this->assertDatabaseMissing('roles', ['name' => 'Invented']);
    }

    public function test_editing_a_role_changes_what_its_members_can_do_immediately(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->withAtoms(['view_registrations'])->create(['name' => 'Helper']);
        $member = User::factory()->inRole($role)->create();

        $this->actingAs($member)->get(route('admin.parent_student_list'))->assertOk();

        $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => 'Helper',
            'atoms' => ['view_allergies'],
        ])->assertRedirect(route('admin.roles.index'));

        // Same session, next request: the page they could see a moment ago is
        // now closed to them, and the one they were granted is open.
        $this->actingAs($member->fresh())->get(route('admin.parent_student_list'))->assertForbidden();
        $this->actingAs($member->fresh())->get(route('admin.allergies'))->assertOk();

        $this->assertDatabaseHas('activity_log_entries', ['action' => 'role.updated']);
    }

    public function test_a_role_edit_cannot_leave_nobody_able_to_manage_users(): void
    {
        $adminRole = Role::where('name', 'Administrator')->firstOrFail();
        $onlyAdmin = User::factory()->inRole($adminRole)->create();
        User::factory()->inRole(Role::where('name', 'Registrar')->firstOrFail())->create();

        $atomsWithoutUserManagement = array_values(array_diff($adminRole->atoms(), ['manage_users']));

        $this->actingAs($onlyAdmin)->put(route('admin.roles.update', $adminRole), [
            'name' => $adminRole->name,
            'atoms' => $atomsWithoutUserManagement,
        ])->assertSessionHasErrors('atoms');

        $this->assertContains('manage_users', $adminRole->fresh()->atoms());
    }

    public function test_the_same_edit_is_allowed_once_another_role_can_manage_users(): void
    {
        $adminRole = Role::where('name', 'Administrator')->firstOrFail();
        $onlyAdmin = User::factory()->inRole($adminRole)->create();

        // A second person who can manage users, in a different role.
        $backupRole = Role::factory()->withAtoms(['manage_users', 'manage_roles'])->create(['name' => 'Backup']);
        User::factory()->inRole($backupRole)->create();

        $atomsWithoutUserManagement = array_values(array_diff($adminRole->atoms(), ['manage_users']));

        $this->actingAs($onlyAdmin)->put(route('admin.roles.update', $adminRole), [
            'name' => $adminRole->name,
            'atoms' => $atomsWithoutUserManagement,
        ])->assertSessionHasNoErrors();

        $this->assertNotContains('manage_users', $adminRole->fresh()->atoms());
    }

    public function test_a_role_with_members_cannot_be_deleted(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->withAtoms(['view_registrations'])->create(['name' => 'Occupied']);
        User::factory()->inRole($role)->create();

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_an_empty_role_can_be_deleted(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Unused']);

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('activity_log_entries', ['action' => 'role.deleted']);
    }

    public function test_permission_matching_is_not_fooled_by_a_prefix(): void
    {
        // ",view_registrations," must not satisfy a check for "view_reg".
        $role = Role::factory()->withAtoms(['view_registrations'])->create();
        $user = User::factory()->inRole($role)->create();

        $this->assertTrue($user->hasPermission('view_registrations'));
        $this->assertFalse($user->hasPermission('view_reg'));
    }

    public function test_a_user_with_no_role_can_do_nothing(): void
    {
        $user = User::factory()->roleless()->create();

        $this->assertFalse($user->hasPermission('view_registrations'));
        $this->actingAs($user)->get(route('admin.parent_student_list'))->assertForbidden();
    }
}
