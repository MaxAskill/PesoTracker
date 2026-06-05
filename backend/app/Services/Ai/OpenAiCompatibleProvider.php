<?php

namespace App\Services\Ai;

use App\Services\Ai\Concerns\BuildsAiPrompts;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAiCompatibleProvider implements AiProvider
{
    use BuildsAiPrompts;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly int $timeout,
        private readonly bool $requiresApiKey = true,
    ) {
    }

    public function reply(string $message, array $financeContext): string
    {
        if ($this->requiresApiKey && blank($this->apiKey)) {
            throw new AiProviderException('AI provider API key is missing.');
        }

        try {
            $request = Http::timeout($this->timeout)
                ->acceptJson();

            if (filled($this->apiKey)) {
                $request = $request->withToken($this->apiKey);
            }

            $response = $request->post(rtrim($this->baseUrl, '/').'/chat/completions', [
                'model' => $this->model,
                'messages' => $this->messages($message, $financeContext),
                'temperature' => 0.2,
                'max_tokens' => 500,
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

        $reply = $response->json('choices.0.message.content');

        if (blank($reply)) {
            throw new AiProviderException('AI provider returned an empty response.');
        }

        return trim($reply);
    }
}
