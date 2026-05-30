<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AssistantInsightService
{
    public function insights(int $userId): array
    {
        $context = $this->financialContext($userId);

        return [
            'summary' => $context['summary'],
            'insights' => $this->buildInsights($context),
            'suggested_questions' => $this->suggestedQuestions(),
        ];
    }

    public function ask(int $userId, string $message): array
    {
        $context = $this->financialContext($userId);
        $intent = $this->matchIntent($message);

        return [
            'reply' => $this->ruleBasedReply($intent, $context),
            'matched_intent' => $intent,
        ];
    }

    private function financialContext(int $userId): array
    {
        $now = Carbon::now();
        $month = $now->format('Y-m');
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthlyTransactions = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->get();

        $totalIncome = (float) $monthlyTransactions
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = (float) $monthlyTransactions
            ->where('type', 'expense')
            ->sum('amount');

        $categorySpending = $monthlyTransactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn (Collection $transactions) => (float) $transactions->sum('amount'))
            ->sortDesc();

        $budgets = Budget::where('user_id', $userId)
            ->where('month', $month)
            ->get()
            ->map(function (Budget $budget) use ($categorySpending) {
                $spent = (float) ($categorySpending[$budget->category] ?? 0);
                $amount = (float) $budget->amount;

                return [
                    'category' => $budget->category,
                    'amount' => $amount,
                    'spent' => $spent,
                    'remaining' => $amount - $spent,
                    'is_over' => $spent > $amount,
                    'over_by' => max($spent - $amount, 0),
                ];
            })
            ->values();

        $goals = SavingsGoal::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function (SavingsGoal $goal) {
                $target = (float) $goal->target_amount;
                $saved = (float) $goal->saved_amount;

                return [
                    'title' => $goal->title,
                    'target_amount' => $target,
                    'saved_amount' => $saved,
                    'progress' => $target > 0 ? min(round(($saved / $target) * 100), 100) : 0,
                ];
            })
            ->values();

        return [
            'summary' => [
                'month' => $now->format('F Y'),
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'balance' => $totalIncome - $totalExpenses,
            ],
            'biggest_category' => $categorySpending->isNotEmpty()
                ? [
                    'category' => $categorySpending->keys()->first(),
                    'amount' => (float) $categorySpending->first(),
                ]
                : null,
            'budgets' => $budgets,
            'overspent_budgets' => $budgets
                ->filter(fn (array $budget) => $budget['is_over'])
                ->sortByDesc('over_by')
                ->values(),
            'goals' => $goals,
            'top_goal' => $goals->sortByDesc('progress')->first(),
            'has_data' => $monthlyTransactions->isNotEmpty() || $budgets->isNotEmpty() || $goals->isNotEmpty(),
        ];
    }

    private function buildInsights(array $context): array
    {
        $insights = [];

        foreach ($context['overspent_budgets'] as $budget) {
            $insights[] = [
                'type' => 'warning',
                'title' => "{$budget['category']} budget exceeded",
                'message' => "You spent {$this->money($budget['spent'])} on {$budget['category']}, which is {$this->money($budget['over_by'])} over your {$this->money($budget['amount'])} budget.",
            ];
        }

        foreach ($context['budgets']->where('is_over', false)->take(3) as $budget) {
            $insights[] = [
                'type' => 'info',
                'title' => "{$budget['category']} budget remaining",
                'message' => "You still have {$this->money(max($budget['remaining'], 0))} left from your {$this->money($budget['amount'])} {$budget['category']} budget this month.",
            ];
        }

        if ($context['biggest_category']) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Biggest expense',
                'message' => "Your biggest spending category this month is {$context['biggest_category']['category']} at {$this->money($context['biggest_category']['amount'])}.",
            ];
        }

        if ($context['top_goal']) {
            $goal = $context['top_goal'];
            $insights[] = [
                'type' => $goal['progress'] >= 100 ? 'success' : 'info',
                'title' => 'Savings progress',
                'message' => "You have saved {$goal['progress']}% toward your {$goal['title']} goal.",
            ];
        }

        $insights[] = $this->financialTip($context);

        if (!$context['has_data']) {
            return [[
                'type' => 'info',
                'title' => 'Ready when you are',
                'message' => 'Add transactions, budgets, or savings goals and I will start finding useful patterns for you.',
            ]];
        }

        return array_slice($insights, 0, 8);
    }

    private function matchIntent(string $message): string
    {
        $message = strtolower($message);

        if ($this->hasAny($message, ['tip', 'advice', 'recommend'])) {
            return 'financial_tip';
        }

        if ($this->hasAny($message, ['overspending', 'over budget', 'exceed', 'exceeded'])) {
            return 'budget_overspending';
        }

        if ($this->hasAny($message, ['budget'])) {
            return 'budget_status';
        }

        if ($this->hasAny($message, ['savings', 'saving', 'goal', 'progress'])) {
            return 'savings_progress';
        }

        if ($this->hasAny($message, ['income', 'earn', 'earned'])) {
            return 'income';
        }

        if ($this->hasAny($message, ['balance', 'left', 'remaining money'])) {
            return 'balance';
        }

        if ($this->hasAny($message, ['category', 'biggest', 'highest', 'most'])) {
            return 'biggest_category';
        }

        if ($this->hasAny($message, ['spend', 'spent', 'expenses', 'expense'])) {
            return 'monthly_expenses';
        }

        return 'fallback';
    }

    private function ruleBasedReply(string $intent, array $context): string
    {
        return match ($intent) {
            'budget_overspending' => $this->overspendingReply($context),
            'budget_status' => $this->budgetStatusReply($context),
            'savings_progress' => $this->savingsReply($context),
            'income' => "Your total income for {$context['summary']['month']} is {$this->money($context['summary']['total_income'])}.",
            'balance' => "Your balance for {$context['summary']['month']} is {$this->money($context['summary']['balance'])}.",
            'biggest_category' => $this->biggestCategoryReply($context),
            'monthly_expenses' => "You spent {$this->money($context['summary']['total_expenses'])} in {$context['summary']['month']}.",
            'financial_tip' => $this->financialTip($context)['message'],
            default => 'I can help with spending, budgets, savings goals, income, balance, and biggest expense categories. Try asking which category you are overspending on.',
        };
    }

    private function overspendingReply(array $context): string
    {
        $budget = $context['overspent_budgets']->first();

        if (!$budget) {
            return $context['budgets']->isEmpty()
                ? 'You do not have budgets for this month yet. Add a monthly budget and I can watch for overspending.'
                : 'Good news: you are not over budget in any tracked category this month.';
        }

        return "You are currently overspending on {$budget['category']} by {$this->money($budget['over_by'])} this month.";
    }

    private function budgetStatusReply(array $context): string
    {
        if ($context['budgets']->isEmpty()) {
            return 'You do not have budgets for this month yet.';
        }

        $overCount = $context['overspent_budgets']->count();

        if ($overCount > 0) {
            return "You have {$overCount} over-budget category this month. ".$this->overspendingReply($context);
        }

        $bestRemaining = $context['budgets']->sortByDesc('remaining')->first();

        return "Your budgets are currently within their limits. {$bestRemaining['category']} has the most remaining budget at {$this->money(max($bestRemaining['remaining'], 0))}.";
    }

    private function savingsReply(array $context): string
    {
        if ($context['goals']->isEmpty()) {
            return 'You do not have savings goals yet. Create one and I can track your progress.';
        }

        $goal = $context['top_goal'];

        return "Your strongest savings progress is {$goal['title']} at {$goal['progress']}%, with {$this->money($goal['saved_amount'])} saved toward {$this->money($goal['target_amount'])}.";
    }

    private function biggestCategoryReply(array $context): string
    {
        if (!$context['biggest_category']) {
            return 'You do not have any expenses recorded for this month yet.';
        }

        return "Your biggest spending category this month is {$context['biggest_category']['category']} at {$this->money($context['biggest_category']['amount'])}.";
    }

    private function financialTip(array $context): array
    {
        if ($context['summary']['total_income'] > 0 && $context['summary']['total_expenses'] > ($context['summary']['total_income'] * 0.8)) {
            return [
                'type' => 'warning',
                'title' => 'Spending tip',
                'message' => 'Your expenses are above 80% of your income this month. Try trimming one flexible category before adding new spending.',
            ];
        }

        if ($context['overspent_budgets']->isNotEmpty()) {
            $budget = $context['overspent_budgets']->first();

            return [
                'type' => 'warning',
                'title' => 'Budget tip',
                'message' => "Pause extra {$budget['category']} spending until next month or move money from a lower-priority category.",
            ];
        }

        if ($context['goals']->isEmpty()) {
            return [
                'type' => 'info',
                'title' => 'Savings tip',
                'message' => 'Set one savings goal, even a small one, so your leftover balance has a clear job.',
            ];
        }

        return [
            'type' => 'success',
            'title' => 'Money habit',
            'message' => 'Keep reviewing your biggest category weekly. Small course corrections are easier than end-of-month fixes.',
        ];
    }

    private function suggestedQuestions(): array
    {
        return [
            'How much did I spend this month?',
            'Which category am I overspending on?',
            'How is my savings progress?',
            'Give me a budgeting tip',
        ];
    }

    private function hasAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function money(float $amount): string
    {
        return "\u{20B1}".number_format($amount, 2);
    }

    // Future paid or local AI integrations can use this same context shape.
    private function generateAiEnhancedReply(string $userMessage, array $financialContext): ?string
    {
        return null;
    }
}
