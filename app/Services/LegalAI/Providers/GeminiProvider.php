<?php

declare(strict_types=1);

namespace App\Services\LegalAI\Providers;

use App\Contracts\LegalAI\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProvider
{
    private string $apiKey;
    private string $defaultModel;
    private string $apiUrl;


    /*
    |--------------------------------------------------------------------------
    | Constract
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->apiKey = config('legal_ai.providers.gemini.api_key');
        $this->defaultModel = config('legal_ai.providers.gemini.models.chat', 'gemini-1.5-flash-latest');

        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API Key is not set in your .env file.');
        }

        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->defaultModel}";
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Response
    |--------------------------------------------------------------------------
    */
    public function generateResponse(array $messages, array $options = []): string
    {
        $payload = $this->preparePayload($messages);

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(180)
            ->post("{$this->apiUrl}:generateContent?key={$this->apiKey}", $payload);

        if ($response->successful()) {
            $responseText = '';
            $candidates = $response->json()['candidates'] ?? [];
            foreach ($candidates as $candidate) {
                foreach ($candidate['content']['parts'] as $part) {
                    $responseText .= $part['text'];
                }
            }
            return $responseText ?: 'لم يتمكن الذكاء الاصطناعي من توليد رد.';
        }

        Log::error('Gemini API Error: ' . $response->body());
        return 'عذرًا، حدث خطأ أثناء الاتصال بخدمة Gemini.';
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Stream Request
    |--------------------------------------------------------------------------
    */
    public function prepareStreamRequest(array $messages): array
    {
        $payload = $this->preparePayload($messages);

        return [
            'url'     => "{$this->apiUrl}:streamGenerateContent?alt=sse&key={$this->apiKey}",
            'headers' => [
                'Content-Type: application/json',
                'Accept: text/event-stream',
            ],
            'payload' => json_encode($payload),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | يجهز الـ payload بما يتوافق مع متطلبات Gemini API.
    |--------------------------------------------------------------------------
    */
    private function preparePayload(array $messages): array
    {
        $systemPrompt = '';
        foreach ($messages as $key => $message) {
            if ($message['role'] === 'system') {
                $systemPrompt = $message['content'];
                unset($messages[$key]);
                break;
            }
        }

        $contents = [];
        foreach ($messages as $message) {
            $role = ($message['role'] === 'assistant') ? 'model' : $message['role'];

            $contents[] = [
                'role'  => $role,
                'parts' => [
                    ['text' => $message['content']]
                ]
            ];
        }

        $payload = ['contents' => $contents];

        if (!empty($systemPrompt)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ];
        }
        return $payload;
    }
}
