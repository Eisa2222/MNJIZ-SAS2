<?php

declare(strict_types=1);

return [
    'meta' => [
        'title'       => 'Law office & HR management platform',
        'description' => 'Multi-tenant SaaS for law firms — HR, cases, hearings, contracts, billing & accounting in one place.',
    ],

    'nav' => [
        'features' => 'Features',
        'pricing'  => 'Pricing',
        'faq'      => 'FAQ',
        'cta'      => 'Start free',
    ],

    'hero' => [
        'default_title'    => 'One platform to run your entire law office',
        'default_subtitle' => 'HR, cases, hearings, contracts, billing, and AI — all in one secure multi-tenant system.',
        'default_cta_text' => 'Start your free trial',
        'see_pricing'      => 'See pricing',
    ],

    'features' => [
        'title' => 'Everything your firm needs in one place',
        'lead'  => 'From staff and contracts to hearings, collections, and accounting — without juggling five separate systems.',
        'fallback' => [
            'legal'   => ['title' => 'Legal case management', 'body' => 'Cases, hearings, opponents, powers of attorney, briefs, and documents.'],
            'hr'      => ['title' => 'Human resources',        'body' => 'Staff, attendance, leave, payroll (WPS), biometrics (BioStation).'],
            'billing' => ['title' => 'Billing & collections',   'body' => 'Contracts, quotes, payments, dunning, Qoyod accounting integration.'],
            'ai'      => ['title' => 'Legal AI',                'body' => 'Legal chat, brief drafting, document summarisation.'],
        ],
    ],

    'pricing' => [
        'title'             => 'Transparent pricing, no surprises',
        'lead'              => 'Every plan includes a free trial. Annual billing saves you two months.',
        'most_popular'      => 'Most popular',
        'per_month'         => '/ month',
        'yearly'            => 'Annual',
        'save_two_months'   => '(save 2 months)',
        'start_trial'       => 'Start :days-day free trial',
        'start_now'         => 'Get started',
    ],

    'faq' => [
        'title' => 'Frequently asked questions',
        'lead'  => 'Quick answers to the most common questions.',
        'fallback' => [
            'isolation' => ['q' => 'Is my data isolated from other customers?', 'a' => 'Yes. Every customer gets database-level isolation via tenant_id.'],
            'trial'     => ['q' => 'Is there a free trial?',                     'a' => 'Yes — full Pro features for the trial period. No credit card required to start.'],
            'upgrade'   => ['q' => 'Can I upgrade or downgrade anytime?',         'a' => 'Yes. You can change plans anytime from the billing page.'],
        ],
    ],

    'footer' => [
        'start_now' => 'Get started',
        'privacy'   => 'Privacy policy',
        'terms'     => 'Terms of service',
        'contact'   => 'Contact us',
    ],

    'admin' => [
        'features' => [
            'title'       => 'Landing Features',
            'subtitle'    => 'Edit the feature blocks shown on /, reorder, enable/disable individually.',
            'create'      => 'New block',
            'edit'        => 'Edit block',
            'empty'       => 'No feature blocks yet — add the first one.',
            'created'     => 'Feature created.',
            'updated'     => 'Feature updated.',
            'deleted'     => 'Feature deleted.',
            'reordered'   => 'Order saved.',
            'confirm_delete' => 'Delete this feature? This cannot be undone.',
            'fields' => [
                'title'       => 'Title',
                'description' => 'Description',
                'icon'        => 'Icon (emoji or CSS class)',
                'image'       => 'Image URL (optional)',
                'is_active'   => 'Active',
                'sort_order'  => 'Order',
            ],
        ],
        'faqs' => [
            'title'       => 'Landing FAQs',
            'subtitle'    => 'Edit the FAQ entries shown in the FAQ section on the landing page.',
            'create'      => 'New question',
            'edit'        => 'Edit question',
            'empty'       => 'No FAQ entries yet — add the first one.',
            'created'     => 'FAQ created.',
            'updated'     => 'FAQ updated.',
            'deleted'     => 'FAQ deleted.',
            'reordered'   => 'Order saved.',
            'confirm_delete' => 'Delete this FAQ? This cannot be undone.',
            'fields' => [
                'question'   => 'Question',
                'answer'     => 'Answer',
                'is_active'  => 'Active',
                'sort_order' => 'Order',
            ],
        ],
        'common' => [
            'save'    => 'Save',
            'cancel'  => 'Cancel',
            'back'    => 'Back',
            'delete'  => 'Delete',
            'edit'    => 'Edit',
            'active'  => 'Active',
            'inactive'=> 'Inactive',
            'drag_hint' => 'Drag rows to reorder — saved automatically.',
        ],
    ],
];
