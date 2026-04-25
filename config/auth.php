<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        /*
        |----------------------------------------------------------------------
        | Admin Guard (Phase 3 — SaaS Super Admin)
        |----------------------------------------------------------------------
        | Platform operators sign in here via /admin/login. Session cookie
        | is separate from the tenant 'web' guard — an admin and a tenant user
        | can be logged in simultaneously (required for impersonation).
        */
        'admin' => [
            'driver'   => 'session',
            'provider' => 'admins',
        ],

        /*
        |----------------------------------------------------------------------
        | Super Admin Guard (Phase B — Path C compatibility layer)
        |----------------------------------------------------------------------
        | Spec-compliant alias of the `admin` guard above.
        |
        |   - Same `admins` table (no rename)
        |   - Distinct provider → distinct App\Models\SuperAdmin marker class
        |   - Distinct session → cannot accidentally elevate /admin into
        |     /super-admin (or vice versa) without re-authentication
        |
        | Both guards continue to work side-by-side until the legacy /admin
        | surface is retired. Optionally enable a 301 from /admin/* to
        | /super-admin/* via `ADMIN_LEGACY_REDIRECT=true`.
        */
        'super_admin' => [
            'driver'   => 'session',
            'provider' => 'super_admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Admin::class,
        ],

        // Phase B — Super Admin provider. Same `admins` table is reached via
        // App\Models\SuperAdmin (extends Admin); a distinct provider is what
        // gives Auth::guard('super_admin') its own session bucket.
        'super_admins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\SuperAdmin::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 5,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table'    => 'admin_password_reset_tokens',
            'expire'   => 15,
            'throttle' => 60,
        ],

        // Phase B — same reset table, different provider. Lets a super-admin
        // use Password::broker('super_admins')->sendResetLink() / reset()
        // without colliding with the legacy `admins` broker.
        'super_admins' => [
            'provider' => 'super_admins',
            'table'    => 'admin_password_reset_tokens',
            'expire'   => 15,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

];
