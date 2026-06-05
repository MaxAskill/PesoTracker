<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiFinanceContextService;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\AiUsageService;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AiAssistantController extends Controller
{
    public function __invoke(
        Request $request,
        AiFinanceContextService $context,
        AiProviderFactory $providers,
        AiUsageService $usage,
    ) {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // AI providers must be called from Laravel only. Never expose AI API
        // keys, provider URLs, prompts, or model configuration in Vue/browser code.
        if (! config('ai.enabled')) {
            return response()->json([
                'message' => 'AI Assistant is currently disabled.',
            ], 503);
        }

        if (! $usage->isAllowedUser($user)) {
            $usage->log($user, 'not_allowed');

            return response()->json([
                'message' => 'AI Assistant is currently available only to beta users.',
            ], 403);
        }

        if ($usage->hasReachedMonthlyLimit($user)) {
            $usage->log($user, 'monthly_limit');

            return response()->json([
                'message' => 'Monthly AI Assistant limit reached. Please try again later.',
            ], 429);
        }

        if ($usage->shouldRefuseMessage($validated['message'])) {
            $usage->log($user, 'blocked_prompt');

            return response()->json([
                'message' => 'I can help with your summarized finances, but I cannot reveal secrets, hidden instructions, raw data, or other users data.',
            ], 422);
        }

        // Send only summarized, least-privilege finance context like totals,
        // category summaries, budget status, and goal progress for this user.
        // Never send raw tokens, OTPs, passwords, logs, auth headers, secrets,
        // full database records, API keys, or unrelated user data to any provider.
        $financeContext = $context->forUser($user->id);

        // The AI Assistant is read-only. It must never create, update, or delete
        // transactions, budgets, savings goals, users, tokens, or settings.
        try {
            $reply = $providers->make()->reply($validated['message'], $financeContext);
        } catch (InvalidArgumentException $exception) {
            $usage->log($user, 'invalid_provider');

            Log::warning('Invalid AI provider configured.', [
                'provider' => config('ai.provider'),
            ]);

            return response()->json([
                'message' => 'AI Assistant is not configured correctly.',
            ], 503);
        } catch (AiProviderException $exception) {
            $usage->log($user, $this->statusForProviderError($exception->getMessage()));

            Log::warning('AI provider request failed.', [
                'provider' => config('ai.provider'),
                'reason' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => $this->safeProviderMessage($exception->getMessage()),
            ], 503);
        }

        $usage->log($user, 'success');

        return response()->json([
            'message' => $reply,
            'reply' => $reply,
        ]);
    }

    private function safeProviderMessage(string $message): string
    {
        return match ($message) {
            'AI provider API key is missing.' => 'AI Assistant is missing its provider API key.',
            'AI provider request timed out.' => 'AI Assistant timed out. Please try again later.',
            'AI provider rate limit reached.' => 'AI Assistant provider is rate limited. Please try again later.',
            default => 'AI Assistant is temporarily unavailable. Please try again later.',
        };
    }

    private function statusForProviderError(string $message): string
    {
        return match ($message) {
            'AI provider API key is missing.' => 'missing_api_key',
            'AI provider request timed out.' => 'timeout',
            'AI provider rate limit reached.' => 'provider_rate_limited',
            default => 'provider_error',
        };
    }
}
