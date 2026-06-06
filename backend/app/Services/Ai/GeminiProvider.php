<?php

namespace App\Services\Ai;

use App\Services\Ai\Concerns\BuildsAiPrompts;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                        'temperature' => 0.4,
                        'maxOutputTokens' => 800,
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Gemini request timed out or could not connect.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'model' => $this->model,
            ]);

            throw new AiProviderException('AI provider request timed out.');
        }

        if ($response->status() === 429) {
            Log::error('Gemini API rate limit response.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'model' => $this->model,
            ]);

            throw new AiProviderException('AI provider rate limit reached.');
        }

        if (! $response->successful()) {
            Log::error('Gemini API returned non-2xx response.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'model' => $this->model,
            ]);

            throw new AiProviderException('AI provider request failed.');
        }

        $parts = $response->json('candidates.0.content.parts');

        if (! is_array($parts)) {
            Log::error('Gemini API response did not include expected text parts.', [
                'body' => $response->body(),
                'model' => $this->model,
            ]);

            throw new AiProviderException('AI provider returned an empty response.');
        }

        $reply = collect($parts)
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if (blank($reply)) {
            Log::error('Gemini API response text was empty.', [
                'body' => $response->body(),
                'model' => $this->model,
            ]);

            throw new AiProviderException('AI provider returned an empty response.');
        }

        return trim($reply);
    }
}
