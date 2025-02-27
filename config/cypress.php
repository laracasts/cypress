<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Excluded Environments
    |--------------------------------------------------------------------------
    |
    | This value determines the environment(s) your application should not configure
    | the cypress routes for. You may list additional environments to prevent
    | from getting included with your application.
    |
    | Default: 'production'
    | Example: 'production','staging'
    */

    'exclude' => env('CYPRESS_EXCLUDED_ENV', 'production'),

];
