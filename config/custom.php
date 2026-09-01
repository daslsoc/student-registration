<?php

return [
    'school' => [
        'name' => env('SCHOOL_NAME', 'School Name'),
        'email' => env('SCHOOL_EMAIL'),
        'orientation_day' => env('ORIENTATION_DAY'),
        'minimum_child_age' => env('MINIMUM_CHILD_AGE'),
    ],
    'pricing' => [
        'single_child' => env('SINGLE_CHILD_COST'),
        'multiple_children' => env('MULTIPLE_CHILDREN_COST'),
    ],
    'contacts' => [
        // Who families are pointed at for tea-roster swaps and queries.
        // Deployment config, not code: set these in .env. Left unset, the
        // guidelines name nobody and refer to "the tea roster coordinator"
        // instead, so a fresh clone carries no one's personal details.
        'tea_roster_name' => env('TEA_ROSTER_CONTACT_NAME'),
        'tea_roster_phone' => env('TEA_ROSTER_CONTACT_PHONE'),
    ],
    'socials' => [
        'whatsapp_join_url' => env('WHATSAPP_JOIN_URL'),
    ],
    'tracking' => [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],
];
