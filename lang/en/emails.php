<?php

declare(strict_types=1);

return [
    'tenant_welcome' => [
        'subject'             => 'Welcome to :app — finish setting up your account',
        'heading'             => 'Welcome to :app',
        'greeting'            => 'Hi :name,',
        'intro'               => 'Your :firm account on :app has been created. To finish onboarding, set your password using the link below.',
        'summary_title'       => 'Account summary',
        'fields' => [
            'firm'         => 'Firm',
            'email'        => 'Email',
            'plan'         => 'Plan',
            'cycle'        => 'Billing cycle',
            'trial_ends'   => 'Trial ends',
            'period_ends'  => 'Current period ends',
        ],
        'setup_title'         => 'Set your password',
        'setup_intro'         => 'This link is valid for :hours hours and can be used once. We will never email you a temporary password.',
        'setup_cta'           => 'Set password now',
        'fallback_url_hint'   => 'If the button doesn\'t work, copy this URL into your browser:',
        'login_hint'          => 'Once set up, you can sign in at:',
        'support_hint'        => 'Questions? Reach us at',
        'security_notice'     => 'We will never ask for your password or a verification code via email or phone.',
    ],

    // Phase G — trial lifecycle.
    'trial_warning' => [
        'subject'          => '[:app] Your trial ends in :days days',
        'heading'          => 'Your trial ends soon',
        'greeting'         => 'Hi :name,',
        'body'             => 'Your free trial of the :plan plan ends in :days days — on :date. Subscribe now to keep working without interruption.',
        'cta'              => 'Subscribe now',
        'support_hint'     => 'Questions? Reach us at',
    ],
    'trial_expired' => [
        'subject'          => '[:app] Your trial has ended',
        'heading'          => 'Your trial has ended',
        'greeting'         => 'Hi :name,',
        'body_suspended'   => 'Your :firm trial has ended and the account has been suspended. Subscribe to a paid plan to restore full access.',
        'body_active'      => 'Your :firm trial has ended. The account is still reachable but some features may be limited. Subscribe to restore full access.',
        'cta'              => 'Subscribe now',
        'support_hint'     => 'Questions? Reach us at',
    ],

    'new_subscription' => [
        'subject'        => '[:app] New subscription',
        'greeting'       => 'Operator alert',
        'intro'          => 'A new subscription has been recorded for: :firm.',
        'source_paid'    => 'Paid checkout',
        'source_trial'   => 'Trial signup',
        'fields' => [
            'source'  => 'Source: :source',
            'owner'   => 'Owner: :name (:email)',
            'plan'    => 'Plan: :plan',
            'cycle'   => 'Cycle: :cycle',
        ],
    ],
];
