<?php

namespace App\Services\Ai;

use InvalidArgumentException;

class AiProviderFactory
{
    public function make(): AiProvider
    {
        $provider = strtolower((string) config('ai.provider', 'gemini'));
        $timeout = (int) config('ai.timeout', 20);

        return match ($provider) {
            'gemini' => new GeminiProvider(
                config('ai.gemini.base_url'),
                config('ai.gemini.key'),
                config('ai.gemini.model'),
                $timeout,
            ),
            'groq' => new OpenAiCompatibleProvider(
                config('ai.groq.base_url'),
                config('ai.groq.key'),
                config('ai.groq.model'),
                $timeout,
            ),
            'huggingface' => new OpenAiCompatibleProvider(
                config('ai.huggingface.base_url'),
                config('ai.huggingface.key'),
                config('ai.huggingface.model'),
                $timeout,
            ),
            'ollama' => new OllamaProvider(
                config('ai.ollama.base_url'),
                config('ai.ollama.key'),
                config('ai.ollama.model'),
                $timeout,
            ),
            default => throw new InvalidArgumentException("Unsupported AI provider [{$provider}]."),
        };
    }
}
