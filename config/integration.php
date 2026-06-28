<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integration API token
    |--------------------------------------------------------------------------
    |
    | A long random shared secret. The sibling student-attendance app sends it
    | as a Bearer token to read the integration API. If this is empty the API
    | denies every request (fail closed), so set it in production.
    |
    | Generate one with: openssl rand -hex 32   (or any 32+ byte random string)
    |
    */

    'api_token' => env('INTEGRATION_API_TOKEN'),

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
