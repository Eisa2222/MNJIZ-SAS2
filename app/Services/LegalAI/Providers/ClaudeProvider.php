<?php

declare(strict_types=1);

namespace App\Services\LegalAI\Providers;

use App\Contracts\LegalAI\AIProvider;
use Illuminate\Support\Facades\Http;

class ClaudeProvider implements AIProvider
{
    private string $apiKey;
    private string $apiVersion;
    private string $defaultModel;

    /*
    |--------------------------------------------------------------------------
    | Constract
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->apiKey = config('legal_ai.providers.claude.api_key');
        $this->apiVersion = config('legal_ai.providers.claude.version');
        $this->defaultModel = config('legal_ai.providers.claude.models.chat', 'claude-3-opus-20240229');

        if (empty($this->apiKey)) {
            throw new \Exception('Claude API Key is not set in your .env file.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Response
    |--------------------------------------------------------------------------
    */
    public function generateResponse(array $messages, array $options = []): string
    {
        $payload = $this->preparePayload($messages, $options, false);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
        ])->timeout(180)->post('https://api.anthropic.com/v1/messages', $payload);

        if ($response->successful()) {
            return $response->json()['content'][0]['text'] ?? 'لم يتمكن الذكاء الاصطناعي من توليد رد.';
        }
        
        return 'عذرًا، حدث خطأ أثناء الاتصال بخدمة الذكاء الاصطناعي.';
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Streamed Response
    |--------------------------------------------------------------------------
    */
    public function generateStreamedResponse(array $messages)
    {
        $payload = $this->preparePayload($messages, [], true);

        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ])
            ->timeout(180)
            ->post('https://api.anthropic.com/v1/messages', $payload);
    }



    /*
    |--------------------------------------------------------------------------
    | Prepare Payload
    |--------------------------------------------------------------------------
    */
    private function preparePayload(array $messages, array $options, bool $streaming = false): array
    {
        $systemPrompt = '';
        $processedMessages = [];
        $lastRole = null;

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemPrompt = $message['content'];
                continue;
            }

            if ($message['role'] === $lastRole) {
                array_pop($processedMessages);
            }

            if (in_array($message['role'], ['user', 'assistant'])) {
                $processedMessages[] = $message;
                $lastRole = $message['role'];
            }
        }

        $payload = [
            'model'       => $options['model'] ?? $this->defaultModel,
            'max_tokens'  => $options['max_tokens'] ?? 4096,
            'messages'    => $processedMessages,
            'temperature' => $options['temperature'] ?? 0.3,
        ];

        if (!empty($systemPrompt)) {
            $payload['system'] = $systemPrompt;
        }

        if ($streaming) {
            $payload['stream'] = true;
        }

        return $payload;
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Stream Request
    |--------------------------------------------------------------------------
    */
    public function prepareStreamRequest(array $messages): array
    {
        $payload = $this->preparePayload($messages, [], true);

        return [
            'url'     => 'https://api.anthropic.com/v1/messages',
            'headers' => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->apiVersion,
                'Accept: text/event-stream',
            ],
            'payload' => json_encode($payload),
        ];
    }
}
