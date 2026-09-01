<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * `role_id` is deliberately absent: a user's role is only ever changed
     * through UserAdminController, which audit-logs the move. Keeping it out
     * means a stray User::create($request->all()) can't hand out permissions.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The single role this user holds. Every permission they have comes from
     * here — there are no per-user grants.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The bottleneck every permission check funnels through.
     *
     * AuthServiceProvider defines a Gate per atom in config/permissions.php
     * whose callback calls this method, so `@can('foo')`, `middleware('can:foo')`
     * and `Gate::allows('foo')` all end up here.
     */
    public function hasPermission(string $atom): bool
    {
        // Belt and braces: a deactivated account can't log in (EnsureUserIsActive
        // kicks it out), so it should never get this far — but if it does, it
        // can do nothing.
        if (! $this->isActive()) {
            return false;
        }

        if ($this->role === null) {
            return false;
        }

        // Comma-bounded on both sides so 'view_ref' can't match 'view_refund'.
        return str_contains((string) $this->role->permission_list, ",{$atom},");
    }

    /**
     * Accounts are "removed" by deactivating them, never by deleting the row —
     * the audit trail has to keep naming a real person.
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deactivated_at');
    }
}
