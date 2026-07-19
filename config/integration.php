<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integration API tokens (one per consuming app)
    |--------------------------------------------------------------------------
    |
    | Long random shared secrets. Each sibling app that reads this API gets its
    | OWN token, so one can be rotated or revoked without breaking the others,
    | and so a log line can name which app called.
    |
    |   attendance — student-attendance, reads paid students (/integration/changes)
    |   tea-roster — tea-roster, reads parent contacts  (/integration/parents)
    |
    | An empty/unset token never matches, so an app you haven't set up yet is
    | denied rather than accidentally allowed. If none are set the API denies
    | every request (fail closed).
    |
    | Generate one with: openssl rand -hex 32   (or any 32+ byte random string)
    |
    */

    'api_tokens' => [
        'attendance' => env('INTEGRATION_API_TOKEN'),
        'tea-roster' => env('INTEGRATION_API_TOKEN_TEA_ROSTER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Class allocation rule
    |--------------------------------------------------------------------------
    |
    | Maps a child's day-school year (the registration form's controlled list)
    | to the school class they're allocated to for BOTH subjects. Applied
    | automatically when a parent completes payment, and editable afterwards by
    | an admin. The value is the attendance app's class name, so it can be
    | matched there directly.
    |
    | Change the bands here without touching code. A day_school_year not listed
    | yields no allocation (left for an admin to set manually).
    |
    */

    'allocation' => [
        'Pre School' => 'Class A',
        'Kindergarten' => 'Class A',
        'Grade 1' => 'Class A',
        'Grade 2' => 'Class B',
        'Grade 3' => 'Class C',
        'Grade 4' => 'Class C',
        'Grade 5' => 'Class D',
        'Grade 6' => 'Class D',
        'Grade 7' => 'Class E',
        'Grade 8' => 'Class E',
        'Grade 9' => 'Class E',
        'Grade 10' => 'Class E',
        'Grade 11' => 'Class E',
        'Grade 12' => 'Class E',
    ],

];
