<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
     */

    // 'failed' => 'These credentials do not match our records.',
    // 'password' => 'The provided password is incorrect.',
    // 'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Phase F — secure password setup flow.
    'setup' => [
        'page_title'         => 'Set your password',
        'heading'            => 'Set your password',
        'lead'               => 'Welcome! Choose a strong password for the account :email to finish onboarding and log in for the first time.',
        'invalid_or_expired' => 'The link is invalid or has expired. Please request a new one.',
        'success'            => 'Your password has been set. You can sign in now.',
        'security_notice'    => 'We will never ask for your password via email or phone.',
        'password_hint'      => 'At least 8 characters.',
        'submit'             => 'Save password',
        'fields' => [
            'password'              => 'Password',
            'password_confirmation' => 'Confirm password',
        ],
    ],
];
