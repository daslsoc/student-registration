<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integration API token
    |--------------------------------------------------------------------------
    |
    | A long random shared secret. The sibling student-attendance app sends it
    | as a Bearer token to read the paid-students endpoint. If this is empty the
    | API denies every request (fail closed), so set it in production.
    |
    | Generate one with: openssl rand -hex 32   (or any 32+ byte random string)
    |
    */

    'api_token' => env('INTEGRATION_API_TOKEN'),

];
