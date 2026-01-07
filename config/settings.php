<?php

use YellowParadox\LaravelSettings\Models\Setting;

return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | This file is for storing the default values of all the settings that are
    | changeable throughout the app. It is the centralized 'single source of
    | truth' to define settings by entity, and it will most likely grow with the
    | addition of new features and functionalities that require user inputted
    | settings.
    |
    | Every setting that we register here must have type and value. Type is
    | important as it will trigger automatic casts on the setting model level.
    | Please use YellowParadox\LaravelSettings\Models\Setting constants to define types, for
    | consistency.
    */

    /*
    |--------------------------------------------------------------------------
    | System Settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify system scoped settings that are intended for the
    | system admins to manipulate. No model is related to these settings.
    |
    */

    'system' => [
        // Add your system-wide settings here
    ],

    /*
    |--------------------------------------------------------------------------
    | User Settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify user scoped settings. These settings will be
    | attached to the user by relationship, via HasSettings trait.
    |
    */

    'user' => [
        // Add your user-specific settings here
    ],
];
