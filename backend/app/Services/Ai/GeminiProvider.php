<?php

namespace App\Services\Ai;

use App\Services\Ai\Concerns\BuildsAiPrompts;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AiProvider
{
    use BuildsAiPrompts;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly int $timeout,
    ) {
    }

    public function reply(string $message, array $financeContext): string
    {
        if (blank($this->apiKey)) {
            throw new AiProviderException('AI provider API key is missing.');
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                ])
                ->post(rtrim($this->baseUrl, '/').'/models/'.$this->model.':generateContent', [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $this->systemPrompt()],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $this->userPrompt($message, $financeContext)],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 500,
                    ],
                ]);
        } catch (ConnectionException) {
            throw new AiProviderException('AI provider request timed out.');
        }

        if ($response->status() === 429) {
            throw new AiProviderException('AI provider rate limit reached.');
        }

        if (! $response->successful()) {
            throw new AiProviderException('AI provider request failed.');
        }

        $reply = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if (blank($reply)) {
            throw new AiProviderException('AI provider returned an empty response.');
        }

        return trim($reply);
    }
}
