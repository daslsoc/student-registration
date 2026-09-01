<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Turns config/permissions.php into Gates.
 *
 * One Gate per atom, each delegating to User::hasPermission, so there is a
 * single place where "can this person do X?" is answered no matter whether the
 * question was asked by a route (`can:manage_users`), a Blade template
 * (`@can`), or code (`Gate::allows`).
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // One policy for every password the app accepts — the reset form and
        // the admin "add user" form both defer to this, so they can't drift.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        foreach (config('permissions', []) as $module) {
            foreach (array_keys($module['atoms'] ?? []) as $atom) {
                Gate::define($atom, fn (User $user) => $user->hasPermission($atom));
            }
        }
    }
}
