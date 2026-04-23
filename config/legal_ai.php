<?php

use App\Services\LegalAI\Providers\ClaudeProvider;
use App\Services\LegalAI\Providers\GeminiProvider;
use App\Services\LegalAI\Providers\OpenAIProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | المزود الافتراضي للذكاء الاصطناعي
    |--------------------------------------------------------------------------
    |
    | هنا تحدد أي مزود خدمة سيتم استخدامه بشكل افتراضي.
    | يتم قراءة القيمة من ملف .env (المفتاح: AI_PROVIDER).
    | إذا لم يكن موجودًا، سيتم استخدام 'openai' كقيمة افتراضية.
    |
    */
    'default_provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | قائمة مزودي الخدمة المدعومين
    |--------------------------------------------------------------------------

    | كل عنصر في هذه المصفوفة يمثل مزود خدمة مع إعداداته الخاصة.
    |
    */

    'providers' => [

        'openai' => [
            'class'   => OpenAIProvider::class,
            'api_key' => env('OPENAI_API_KEY'),
            'models'  => [
                'chat'     => 'gpt-4o',
                'analysis' => 'gpt-4-turbo',
            ],
        ],

        'claude' => [
            'class'   => ClaudeProvider::class,
            'api_key' => env('CLAUDE_API_KEY'),
            'version' => '2023-06-01',
            'models'  => [

                'chat'     => 'claude-sonnet-4-20250514',
                'drafting' => 'claude-sonnet-4-20250514',
            ],
        ],

        'gemini' => [
            'class'   => GeminiProvider::class,
            'api_key' => env('GEMINI_API_KEY'),
            'models'  => [
                'chat'     => 'gemini-1.5-flash-latest',
                'analysis' => 'gemini-1.5-pro-latest',
            ],
        ],

    ],
];
