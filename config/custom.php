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
    'socials' => [
        'whatsapp_join_url' => env('WHATSAPP_JOIN_URL'),
    ],
    'tracking' => [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],
];
