<?php

namespace Tests\Feature;

use App\Models\ActivityLogEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * User administration: who can reach it, what it writes, and the two things it
 * must refuse to do (deactivate yourself, or remove the last administrator).
 */
class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::factory()->create();
    }

    public function test_user_without_manage_users_cannot_see_the_user_list(): void
    {
        $user = User::factory()->withAtoms(['view_registrations'])->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_administrator_can_see_the_user_list(): void
    {
        $admin = $this->administrator();
        User::factory()->create(['name' => 'Nimal Perera']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Nimal Perera');
    }

    public function test_administrator_can_add_a_user_and_it_is_audit_logged(): void
    {
        $admin = $this->administrator();
        $role = Role::where('name', 'Registrar')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Sunil Silva',
            'email' => 'sunil@example.test',
            'password' => 'correct-horse-9',
            'role_id' => $role->id,
        ])->assertRedirect(route('admin.users.index'));

        $created = User::where('email', 'sunil@example.test')->first();
        $this->assertNotNull($created);
        $this->assertSame($role->id, $created->role_id);
        $this->assertTrue($created->isActive());

        $this->assertDatabaseHas('activity_log_entries', [
            'action' => 'user.created',
            'user_id' => $admin->id,
            'subject_id' => $created->id,
        ]);
    }

    public function test_added_user_can_log_in_with_the_initial_password(): void
    {
        $admin = $this->administrator();
        $role = Role::where('name', 'Registrar')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Sunil Silva',
            'email' => 'sunil@example.test',
            'password' => 'correct-horse-9',
            'role_id' => $role->id,
        ]);

        // Password is stored hashed by the model cast, not in the clear.
        $this->assertNotSame('correct-horse-9', User::where('email', 'sunil@example.test')->value('password'));

        $this->post(route('login.submit'), [
            'email' => 'sunil@example.test',
            'password' => 'correct-horse-9',
        ])->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_weak_passwords_are_rejected_when_adding_a_user(): void
    {
        $admin = $this->administrator();
        $role = Role::where('name', 'Registrar')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Sunil Silva',
            'email' => 'sunil@example.test',
            'password' => 'short',
            'role_id' => $role->id,
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'sunil@example.test']);
    }

    public function test_deactivating_a_user_blocks_login_and_keeps_the_row(): void
    {
        $admin = $this->administrator();
        $victim = User::factory()->withAtoms(['view_registrations'])->create([
            'email' => 'gone@example.test',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $victim))
            ->assertRedirect();

        // The row survives so the audit trail still names a real person.
        $this->assertDatabaseHas('users', ['id' => $victim->id]);
        $this->assertNotNull($victim->fresh()->deactivated_at);

        $this->post(route('login.submit'), [
            'email' => 'gone@example.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_live_session_is_dropped_as_soon_as_the_account_is_deactivated(): void
    {
        $victim = User::factory()->create();

        $this->actingAs($victim)->get(route('admin.parent_student_list'))->assertOk();

        // Set explicitly: deactivated_at is guarded against mass assignment,
        // like role_id, so ->update([...]) would silently do nothing.
        $victim->deactivated_at = now();
        $victim->save();

        $this->actingAs($victim)
            ->get(route('admin.parent_student_list'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_user_cannot_deactivate_themselves(): void
    {
        $admin = $this->administrator();

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $admin))
            ->assertSessionHasErrors('user');

        $this->assertNull($admin->fresh()->deactivated_at);
    }

    public function test_one_administrator_can_deactivate_another(): void
    {
        $admin = $this->administrator();
        $second = $this->administrator();

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $second))
            ->assertSessionHasNoErrors();

        $this->assertNotNull($second->fresh()->deactivated_at);
        // The one doing the removing is still standing, which is the invariant
        // AdminSafety exists to protect.
        $this->assertTrue($admin->fresh()->hasPermission('manage_users'));
    }

    public function test_a_deactivated_user_can_be_reactivated(): void
    {
        $admin = $this->administrator();
        $user = User::factory()->deactivated()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.reactivate', $user))
            ->assertRedirect();

        $this->assertNull($user->fresh()->deactivated_at);
        $this->assertDatabaseHas('activity_log_entries', ['action' => 'user.reactivated']);
    }

    public function test_changing_a_users_role_is_logged_separately_from_a_rename(): void
    {
        $admin = $this->administrator();
        $readOnly = Role::where('name', 'Read-only')->firstOrFail();
        $user = User::factory()->inRole(Role::where('name', 'Registrar')->firstOrFail())->create();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Renamed Person',
            'email' => $user->email,
            'role_id' => $readOnly->id,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame($readOnly->id, $user->fresh()->role_id);
        $this->assertDatabaseHas('activity_log_entries', ['action' => 'user.updated']);
        $this->assertDatabaseHas('activity_log_entries', ['action' => 'user.role_changed']);
    }

    public function test_the_last_administrator_cannot_be_moved_out_of_their_role(): void
    {
        $admin = $this->administrator();
        $readOnly = Role::where('name', 'Read-only')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $readOnly->id,
        ])->assertSessionHasErrors('role_id');

        $this->assertNotSame($readOnly->id, $admin->fresh()->role_id);
    }

    public function test_role_id_cannot_be_smuggled_in_through_mass_assignment(): void
    {
        $registrar = Role::where('name', 'Registrar')->firstOrFail();

        $user = User::create([
            'name' => 'Sneaky',
            'email' => 'sneaky@example.test',
            'password' => 'irrelevant-1234',
            'role_id' => Role::where('name', 'Administrator')->value('id'),
        ]);

        $this->assertNull($user->role_id);
        $this->assertFalse($user->hasPermission('manage_users'));

        // And the honest path still works.
        $user->role_id = $registrar->id;
        $user->save();
        $this->assertTrue($user->fresh()->hasPermission('view_registrations'));
    }

    public function test_audit_log_page_needs_its_own_permission(): void
    {
        $withoutIt = User::factory()->withAtoms(['manage_users'])->create();
        $withIt = User::factory()->withAtoms(['view_audit_log'])->create();

        ActivityLogEntry::create([
            'type' => ActivityLogEntry::TYPE_ADMIN_ACTION,
            'user_name' => 'Someone',
            'action' => 'user.created',
            'description' => 'Added user Test Person',
        ]);

        $this->actingAs($withoutIt)->get(route('admin.audit'))->assertForbidden();
        $this->actingAs($withIt)->get(route('admin.audit'))->assertOk()->assertSee('Added user Test Person');
    }
}
