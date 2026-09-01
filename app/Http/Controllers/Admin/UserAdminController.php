<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\AdminSafety;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * User accounts admin. Gated by `can:manage_users` on the routes.
 *
 * Accounts are added and edited here, and "removed" by deactivating them —
 * nothing hard-deletes a user, so the audit trail keeps naming a real person
 * and a mistake is one click to undo.
 *
 * Two things are deliberately impossible, both enforced by AdminSafety:
 * deactivating yourself, and taking the last `manage_users` permission out of
 * the system.
 */
class UserAdminController extends Controller
{
    public function index(Request $request): View
    {
        $roleFilter = $request->query('role');

        $users = User::query()
            ->with('role')
            ->when($roleFilter, fn ($query) => $query->where('role_id', $roleFilter))
            ->orderBy('name')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'roleFilter' => $roleFilter,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
        ]);

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        // Cast to 'hashed' on the model takes care of hashing.
        $user->password = $data['password'];
        // role_id is guarded against mass assignment, so it's set explicitly.
        $user->role_id = $data['role_id'];
        $user->save();

        ActivityLogger::adminAction(
            'user.created',
            "Added user {$user->name}",
            $user,
            ['email' => $user->email, 'role' => $user->role?->name],
        );

        return redirect()->route('admin.users.index')
            ->with('status', "User {$user->name} added.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    /**
     * Profile fields and role live on one form, but they are logged as two
     * different things — a rename and a privilege change are not the same
     * event, and only the second one matters when reading the audit trail.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
        ]);

        $newRoleId = (int) $data['role_id'];
        $roleChanged = $newRoleId !== (int) $user->role_id;

        // Moving yourself (or the only other admin) out of an admin role is the
        // same lock-out as deactivating them.
        if ($roleChanged && AdminSafety::isLastAdministrator($user)) {
            $newRole = Role::find($newRoleId);

            if ($newRole === null || ! in_array(AdminSafety::ATOM, $newRole->atoms(), true)) {
                return back()->withErrors([
                    'role_id' => 'This is the only account that can manage users. Give someone else that permission first.',
                ])->withInput();
            }
        }

        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
        ];

        DB::transaction(function () use ($user, $data, $newRoleId) {
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role_id = $newRoleId;
            $user->save();
        });

        $user->refresh()->load('role');

        if ($before['name'] !== $user->name || $before['email'] !== $user->email) {
            ActivityLogger::adminAction(
                'user.updated',
                "Updated user {$user->name}",
                $user,
                ['before' => $before, 'after' => ['name' => $user->name, 'email' => $user->email]],
            );
        }

        if ($roleChanged) {
            ActivityLogger::adminAction(
                'user.role_changed',
                "Moved {$user->name} from {$before['role']} to {$user->role?->name}",
                $user,
                ['before' => $before['role'], 'after' => $user->role?->name],
            );
        }

        return redirect()->route('admin.users.index')
            ->with('status', "User {$user->name} updated.");
    }

    /**
     * "Remove" a user: they can no longer log in or reset their password, and
     * any live session of theirs is dropped on its next request
     * (EnsureUserIsActive). The row stays.
     */
    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        if (! $user->isActive()) {
            return back()->with('status', "{$user->name} is already deactivated.");
        }

        if (AdminSafety::isLastAdministrator($user)) {
            return back()->withErrors([
                'user' => 'This is the only account that can manage users. Give someone else that permission first.',
            ]);
        }

        $user->deactivated_at = now();
        $user->save();

        ActivityLogger::adminAction(
            'user.deactivated',
            "Deactivated user {$user->name}",
            $user,
            ['email' => $user->email],
        );

        return back()->with('status', "{$user->name} has been deactivated.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        if ($user->isActive()) {
            return back()->with('status', "{$user->name} is already active.");
        }

        $user->deactivated_at = null;
        $user->save();

        ActivityLogger::adminAction(
            'user.reactivated',
            "Reactivated user {$user->name}",
            $user,
            ['email' => $user->email],
        );

        return back()->with('status', "{$user->name} has been reactivated.");
    }
}
