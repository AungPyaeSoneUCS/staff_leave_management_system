<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Super Admin Registration
    |--------------------------------------------------------------------------
    |
    | Hidden feature: the super admin account can only be created by visiting
    | the special registration URL with the matching secret key. Set the value
    | via the SUPER_ADMIN_REGISTER_SECRET environment variable.
    |
    */

    'register_secret' => env('SUPER_ADMIN_REGISTER_SECRET', ''),
];
