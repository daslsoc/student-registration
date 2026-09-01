<?php

/**
 * Permission atom registry.
 *
 * Every action the app gates on lives here. AuthServiceProvider walks this
 * file at boot and calls Gate::define for each atom, so every check —
 * `@can('view_registrations')` in Blade, `->middleware('can:manage_users')`
 * on a route, `Gate::allows(...)` in code — funnels through one bottleneck:
 * User::hasPermission, which reads the CSV on the user's role.
 *
 * Adding a permission:
 *   1. Add the atom to the right module group below with a short label.
 *   2. Tick it on whichever roles should have it (Admin -> Roles -> Edit).
 *   3. Apply it with `@can('foo')` (Blade) or `->middleware('can:foo')` (route).
 *
 * The `label` on each module and atom is what the role editor renders in its
 * checkbox grid, so keep them short and end-user-readable.
 */
return [
    'registrations' => [
        'label' => 'Registrations',
        'atoms' => [
            'view_registrations' => 'View parents & students',
            'export_registrations' => 'Export registrations to CSV',
            'import_registrations' => 'Import registrations from CSV',
            'view_allergies' => 'View allergies & medical details',
        ],
    ],

    'allocations' => [
        'label' => 'Class allocations',
        'atoms' => [
            'view_unallocated' => 'View unallocated students',
            'manage_allocations' => 'Allocate & relocate students',
        ],
    ],

    'payments' => [
        'label' => 'Payments',
        'atoms' => [
            'manage_payment_overrides' => 'Record payment overrides',
        ],
    ],

    'administration' => [
        'label' => 'Administration',
        'atoms' => [
            'manage_users' => 'Add, edit & deactivate users',
            'manage_roles' => 'Create roles & change their permissions',
            'view_audit_log' => 'View the audit log',
        ],
    ],
];
