<?php

namespace App\Services\Ai;

use App\Services\Ai\Concerns\BuildsAiPrompts;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements AiProvider
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
        try {
            $request = Http::timeout($this->timeout)->acceptJson();

            if (filled($this->apiKey)) {
                $request = $request->withToken($this->apiKey);
            }

            $response = $request->post(rtrim($this->baseUrl, '/').'/api/chat', [
                'model' => $this->model,
                'messages' => $this->messages($message, $financeContext),
                'stream' => false,
                'options' => [
                    'temperature' => 0.2,
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

        $reply = $response->json('message.content');

        if (blank($reply)) {
            throw new AiProviderException('AI provider returned an empty response.');
        }

        return trim($reply);
    }
}
