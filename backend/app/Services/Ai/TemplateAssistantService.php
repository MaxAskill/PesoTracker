<?php

namespace App\Services\Ai;

class TemplateAssistantService
{
    public function reply(string $message, ?string $action, array $financeContext): string
    {
        return match ($this->resolveAction($message, $action)) {
            'budget_warning' => $this->budgetWarning($financeContext),
            'savings_progress' => $this->savingsProgress($financeContext),
            'smart_tips' => $this->smartTips($financeContext),
            default => $this->spendingSummary($financeContext),
        };
    }

    private function resolveAction(string $message, ?string $action): string
    {
        $normalizedAction = strtolower(trim((string) $action));

        if (in_array($normalizedAction, ['spending_summary', 'budget_warning', 'savings_progress', 'smart_tips'], true)) {
            return $normalizedAction;
        }

        $normalizedMessage = strtolower($message);

        if (str_contains($normalizedMessage, 'budget')) {
            return 'budget_warning';
        }

        if (str_contains($normalizedMessage, 'saving') || str_contains($normalizedMessage, 'goal')) {
            return 'savings_progress';
        }

        if (str_contains($normalizedMessage, 'tip') || str_contains($normalizedMessage, 'advice')) {
            return 'smart_tips';
        }

        return 'spending_summary';
    }

    private function spendingSummary(array $context): string
    {
        $month = $this->month($context);
        $income = $this->number($context['income'] ?? $context['summary']['total_income'] ?? 0);
        $expenses = $this->number($context['expenses'] ?? $context['summary']['total_expenses'] ?? 0);
        $balance = $this->number($context['balance'] ?? $context['summary']['balance'] ?? 0);
        $transactionCount = (int) ($context['summary']['transaction_count_this_month'] ?? 0);

        if ($transactionCount === 0 && $income <= 0 && $expenses <= 0) {
            return "I do not see any transactions for {$month} yet. Add income and expenses first, then I can summarize your spending.";
        }

        $ratio = $income > 0 ? round(($expenses / $income) * 100) : 0;
        $insight = $this->spendingInsight($income, $expenses);

        return "Your spending summary for {$month}: You earned {$this->money($income)}, spent {$this->money($expenses)}, and have a remaining balance of {$this->money($balance)}. Your expense-to-income ratio is {$ratio}%. {$insight}";
    }

    private function budgetWarning(array $context): string
    {
        $budgets = collect($context['budgets'] ?? []);

        if ($budgets->isEmpty()) {
            return 'You do not have a budget for this month yet. Create a monthly budget so I can warn you before a category gets too high.';
        }

        $withUsage = $budgets->map(function (array $budget) {
            $budgeted = $this->number($budget['budgeted'] ?? $budget['amount'] ?? 0);
            $spent = $this->number($budget['spent'] ?? 0);
            $budget['usage_percent'] = $budgeted > 0 ? round(($spent / $budgeted) * 100) : 0;
            $budget['budgeted_amount'] = $budgeted;
            $budget['spent_amount'] = $spent;

            return $budget;
        });

        $overBudget = $withUsage
            ->filter(fn (array $budget) => $budget['usage_percent'] > 100)
            ->sortByDesc('usage_percent')
            ->first();

        if ($overBudget) {
            return "Budget warning: {$overBudget['category']} is over budget at {$overBudget['usage_percent']}% used. You spent {$this->money($overBudget['spent_amount'])} out of {$this->money($overBudget['budgeted_amount'])}. Pause extra spending in this category if you can.";
        }

        $nearLimit = $withUsage
            ->filter(fn (array $budget) => $budget['usage_percent'] >= 80)
            ->sortByDesc('usage_percent')
            ->first();

        if ($nearLimit) {
            return "Budget caution: {$nearLimit['category']} is already at {$nearLimit['usage_percent']}% of its budget. You spent {$this->money($nearLimit['spent_amount'])} out of {$this->money($nearLimit['budgeted_amount'])}, so keep an eye on it for the rest of the month.";
        }

        return 'Your budget usage looks controlled right now. No category is over 80% of its monthly budget.';
    }

    private function savingsProgress(array $context): string
    {
        $goals = collect($context['savings_progress']['goals'] ?? $context['savings_goals'] ?? []);

        if ($goals->isEmpty()) {
            return 'You do not have a savings goal yet. Create one clear goal so your remaining balance has a job.';
        }

        $goal = $goals
            ->sortByDesc(fn (array $goal) => $this->number($goal['progress_percent'] ?? $goal['progress'] ?? 0))
            ->first();

        $progress = (int) round($this->number($goal['progress_percent'] ?? $goal['progress'] ?? 0));
        $title = $goal['title'] ?? 'Savings goal';
        $saved = $this->number($goal['saved_amount'] ?? 0);
        $target = $this->number($goal['target_amount'] ?? 0);
        $encouragement = $progress >= 70
            ? 'You are making strong progress, so keep the habit going.'
            : 'Try setting aside a smaller consistent amount each payday to build momentum.';

        return "Savings progress: {$title} is {$progress}% complete. You have saved {$this->money($saved)} toward a target of {$this->money($target)}. {$encouragement}";
    }

    private function smartTips(array $context): string
    {
        $income = $this->number($context['income'] ?? $context['summary']['total_income'] ?? 0);
        $expenses = $this->number($context['expenses'] ?? $context['summary']['total_expenses'] ?? 0);
        $balance = $this->number($context['balance'] ?? $context['summary']['balance'] ?? 0);
        $budgets = collect($context['budgets'] ?? []);
        $goals = collect($context['savings_progress']['goals'] ?? $context['savings_goals'] ?? []);
        $topCategories = collect($context['top_categories'] ?? $context['top_expense_categories'] ?? []);

        if ($balance > 0 && $expenses < $income) {
            return "Smart tip: Your balance is positive, so consider saving part of the {$this->money($balance)} left this month before adding more flexible spending.";
        }

        if ($income > 0 && $expenses >= ($income * 0.7)) {
            $category = $topCategories->first()['category'] ?? 'your top spending categories';

            return "Smart tip: Your expenses are high compared with income. Review {$category} first and look for one expense you can reduce this week.";
        }

        if ($budgets->isEmpty()) {
            return 'Smart tip: Create a monthly budget for your most common categories so you can spot overspending earlier.';
        }

        if ($goals->isEmpty()) {
            return 'Smart tip: Create one savings goal, even a small one, so your leftover balance has a clear purpose.';
        }

        return 'Smart tip: Keep reviewing your budget and savings progress weekly. Small adjustments are easier than fixing everything at month end.';
    }

    private function spendingInsight(float $income, float $expenses): string
    {
        if ($income <= 0 && $expenses > 0) {
            return 'You have expenses recorded but no income for this month yet, so add income to get a clearer ratio.';
        }

        if ($expenses > $income) {
            return 'Warning: your expenses are higher than your income, so your cash flow is negative this month.';
        }

        if ($income > 0 && $expenses >= ($income * 0.7)) {
            return 'Caution: expenses are already at 70% or more of your income, so keep flexible spending tight.';
        }

        if ($income > 0 && $expenses < ($income * 0.6)) {
            return 'This month looks healthy because your expenses are below 60% of your income.';
        }

        return 'Your spending is within your income, but there is still room to improve your savings buffer.';
    }

    private function month(array $context): string
    {
        return (string) ($context['current_month'] ?? $context['period'] ?? now()->format('F Y'));
    }

    private function number(mixed $value): float
    {
        $number = (float) $value;

        return is_finite($number) ? $number : 0.0;
    }

    private function money(float $amount): string
    {
        return '₱'.number_format($amount, 2);
    }
}
