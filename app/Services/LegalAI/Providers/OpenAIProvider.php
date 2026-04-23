<?php

namespace App\Services\LegalAI\Providers;

use App\Contracts\LegalAI\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProvider
{
    protected string $apiKey;
    protected string $defaultModel;


    /*
    |--------------------------------------------------------------------------
    | OpenAIProvider Constructor
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->apiKey = config('legal_ai.providers.openai.api_key');
        $this->defaultModel = config('legal_ai.providers.openai.models.chat', 'gpt-4o');

        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API Key is not set in your .env file.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Send a message to OpenAI and get a response
    |--------------------------------------------------------------------------
    */
    public function generateResponse(array $messages, array $options = []): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(180)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $options['model'] ?? $this->defaultModel,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.5,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'] ?? 'لم يتمكن الذكاء الاصطناعي من توليد رد.';
        }

        return 'عذرًا، حدث خطأ أثناء الاتصال بخدمة الذكاء الاصطناعي. يرجى المحاولة لاحقًا.';
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Stream Request
    |--------------------------------------------------------------------------
    */
    public function prepareStreamRequest(array $messages): array
    {
        $payload = [
            'model'    => $this->defaultModel,
            'messages' => $messages,
            'stream'   => true,
        ];

        return [
            'url'     => 'https://api.openai.com/v1/chat/completions',
            'headers' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: text/event-stream',
            ],
            'payload' => json_encode($payload),
        ];
    }
}
