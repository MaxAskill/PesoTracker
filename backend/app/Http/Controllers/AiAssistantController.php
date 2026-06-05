<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // Future AI providers must be called from Laravel only. Never expose AI API
        // keys, provider URLs, prompts, or model configuration in Vue/browser code.
        $financeContext = $this->summarizedFinanceContext($user->id);

        // Future implementation notes:
        // - Send only summarized, least-privilege finance context like totals,
        //   category summaries, budget status, and goal progress.
        // - Never send raw tokens, OTPs, passwords, logs, auth headers, secrets,
        //   full database records, or unrelated user data to any AI provider.
        // - Treat the user's message as untrusted input. Do not let prompt content
        //   override system safety rules or request hidden data.
        // - The AI Assistant must be read-only. It must never create, update, or
        //   delete transactions, budgets, savings goals, users, tokens, or settings.
        // - Keep any future tool/action layer allowlisted and server-side.
        unset($validated, $financeContext);

        return response()->json([
            'message' => 'AI Assistant is not available yet.',
        ]);
    }

    private function summarizedFinanceContext(int $userId): array
    {
        // Placeholder for future summary builders. Keep this aggregate-only and
        // scoped to the authenticated user; do not return raw records.
        return [
            'user_id' => $userId,
            'summary' => null,
        ];
    }
}
