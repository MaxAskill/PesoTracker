<?php

namespace App\Services\Ai;

use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Services\FinancialHealthService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AiFinanceContextService
{
    public function __construct(private FinancialHealthService $financialHealth)
    {
    }

    public function forUser(int $userId): array
    {
        $now = Carbon::now();
        $month = $now->format('Y-m');
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthlyTransactions = Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->select(['type', 'category', 'amount'])
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
            ->map(fn (Collection $transactions) => round((float) $transactions->sum('amount'), 2))
            ->sortDesc()
            ->take(8);

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->where('month', $month)
            ->select(['category', 'amount'])
            ->get()
            ->map(function (Budget $budget) use ($categorySpending) {
                $spent = (float) ($categorySpending[$budget->category] ?? 0);
                $amount = (float) $budget->amount;

                return [
                    'category' => $budget->category,
                    'budgeted' => $amount,
                    'spent' => $spent,
                    'remaining' => round($amount - $spent, 2),
                    'is_over_budget' => $spent > $amount,
                ];
            })
            ->values();

        $goals = SavingsGoal::query()
            ->where('user_id', $userId)
            ->select(['title', 'target_amount', 'saved_amount'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (SavingsGoal $goal) {
                $target = (float) $goal->target_amount;
                $saved = (float) $goal->saved_amount;

                return [
                    'title' => $goal->title,
                    'target_amount' => $target,
                    'saved_amount' => $saved,
                    'progress_percent' => $target > 0 ? min(round(($saved / $target) * 100), 100) : 0,
                ];
            })
            ->values();

        return [
            'period' => $now->format('F Y'),
            'summary' => [
                'total_income' => round($totalIncome, 2),
                'total_expenses' => round($totalExpenses, 2),
                'balance' => round($totalIncome - $totalExpenses, 2),
                'transaction_count_this_month' => $monthlyTransactions->count(),
            ],
            'savings_score' => $this->savingsScore($userId, $month),
            'top_expense_categories' => $categorySpending
                ->map(fn (float $amount, string $category) => [
                    'category' => $category,
                    'amount' => $amount,
                ])
                ->values(),
            'budgets' => $budgets,
            'savings_goals' => $goals,
        ];
    }

    private function savingsScore(int $userId, string $month): array
    {
        $health = $this->financialHealth->calculate($userId, $month);

        return [
            'score' => $health['score'],
            'status' => $health['status'],
            'recommendation' => $health['recommendation'],
        ];
    }
}
