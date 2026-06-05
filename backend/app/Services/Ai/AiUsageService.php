<?php

namespace App\Services\Ai;

use App\Models\AiUsageLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AiUsageService
{
    public function isAllowedUser(User $user): bool
    {
        $ids = config('ai.beta_user_ids', []);
        $emails = config('ai.beta_user_emails', []);

        if (empty($ids) && empty($emails)) {
            return true;
        }

        return in_array((string) $user->id, array_map('strval', $ids), true)
            || in_array(strtolower($user->email), $emails, true);
    }

    public function hasReachedMonthlyLimit(User $user): bool
    {
        $limit = (int) config('ai.monthly_limit_per_user', 100);

        if ($limit <= 0) {
            return false;
        }

        return AiUsageLog::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count() >= $limit;
    }

    public function log(User $user, string $status): void
    {
        try {
            AiUsageLog::create([
                'user_id' => $user->id,
                'provider' => (string) config('ai.provider', 'gemini'),
                'model' => $this->configuredModel(),
                'status' => $status,
                'created_at' => now(),
            ]);
        } catch (\Throwable $error) {
            Log::error('Failed to write AI usage log.', [
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'user_id' => $user->id,
                'status' => $status,
            ]);
        }
    }

    public function shouldRefuseMessage(string $message): bool
    {
        $normalized = strtolower($message);

        $blockedPatterns = [
            'ignore previous',
            'ignore all previous',
            'ignore your instructions',
            'bypass security',
            'reveal system prompt',
            'show system prompt',
            'hidden rules',
            'api key',
            'api keys',
            'token',
            'tokens',
            'otp',
            'password',
            'database dump',
            'dump database',
            'all database records',
            'other users',
            'another user',
            'raw logs',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function configuredModel(): ?string
    {
        $provider = (string) config('ai.provider', 'gemini');

        return config("ai.{$provider}.model");
    }
}
