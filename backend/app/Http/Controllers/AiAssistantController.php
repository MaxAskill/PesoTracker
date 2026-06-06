<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiFinanceContextService;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\AiUsageService;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Throwable;

class AiAssistantController extends Controller
{
    public function __invoke(
        Request $request,
        AiFinanceContextService $context,
        AiProviderFactory $providers,
        AiUsageService $usage,
    ) {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The message field is required and must be a string no longer than 1000 characters.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

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

            if (strtolower((string) config('ai.provider')) === 'gemini' && blank(config('ai.gemini.key'))) {
                Log::error('Gemini API key is missing for AI Assistant.', [
                    'provider' => config('ai.provider'),
                    'model' => config('ai.gemini.model'),
                ]);

                $usage->log($user, 'missing_api_key');

                return response()->json([
                    'message' => 'AI Assistant is not configured correctly.',
                ], 500);
            }

            // Send only summarized, least-privilege finance context like totals,
            // category summaries, budget status, and goal progress for this user.
            // Never send raw tokens, OTPs, passwords, logs, auth headers, secrets,
            // full database records, API keys, or unrelated user data to any provider.
            $financeContext = $context->forUser($user->id);

            // The AI Assistant is read-only. It must never create, update, or delete
            // transactions, budgets, savings goals, users, tokens, or settings.
            $reply = $providers->make()->reply($validated['message'], $financeContext);
        } catch (InvalidArgumentException $exception) {
            if (isset($user) && $user) {
                $usage->log($user, 'invalid_provider');
            }

            Log::warning('Invalid AI provider configured.', [
                'provider' => config('ai.provider'),
            ]);

            return response()->json([
                'message' => 'AI Assistant is not configured correctly.',
            ], 503);
        } catch (AiProviderException $exception) {
            if (isset($user) && $user) {
                $usage->log($user, $this->statusForProviderError($exception->getMessage()));
            }

            Log::error('AI provider request failed.', [
                'provider' => config('ai.provider'),
                'reason' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'message' => $this->safeProviderMessage($exception->getMessage()),
            ], $this->statusCodeForProviderError($exception->getMessage()));
        } catch (Throwable $exception) {
            Log::error('Unexpected AI Assistant backend error.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'provider' => config('ai.provider'),
                'user_id' => isset($user) && $user ? $user->id : null,
            ]);

            if (isset($user) && $user) {
                $usage->log($user, 'server_error');
            }

            return response()->json([
                'message' => 'AI Assistant is temporarily unavailable. Please try again later.',
            ], 500);
        }

        $usage->log($user, 'success');

        return response()->json([
            'message' => $reply,
            'reply' => $reply,
            'answer' => $reply,
        ]);
    }

    private function safeProviderMessage(string $message): string
    {
        return match ($message) {
            'AI provider request timed out.' => 'AI Assistant timed out. Please try again later.',
            'AI provider rate limit reached.' => 'AI Assistant provider is rate limited. Please try again later.',
            'AI provider request failed.' => 'AI provider error. Please try again later.',
            'AI provider returned an empty response.' => 'AI provider returned an invalid response. Please try again later.',
            default => 'AI Assistant is temporarily unavailable. Please try again later.',
        };
    }

    private function statusCodeForProviderError(string $message): int
    {
        return match ($message) {
            'AI provider request timed out.' => 503,
            'AI provider request failed.',
            'AI provider returned an empty response.' => 502,
            'AI provider rate limit reached.' => 429,
            default => 500,
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
